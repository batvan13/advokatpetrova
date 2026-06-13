<?php

namespace App\Support;

use App\Exceptions\ChatMessageSendRejectedException;
use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatMessageService
{
    public const MAX_MESSAGE_LENGTH = 2000;

    public const FETCH_LIMIT = 100;

    public function __construct(
        private readonly ChatSessionLifecycleService $sessionLifecycle,
    ) {}

    public function resolveClientSession(string $clientAccessToken): ChatSession
    {
        if (strlen($clientAccessToken) < 32) {
            abort(404);
        }

        $session = ChatSession::with(['booking.payment'])
            ->where('client_access_token', $clientAccessToken)
            ->first();

        if (! $session || ! $session->booking || ! $this->isRoomEligible($session->booking)) {
            abort(404);
        }

        return $session;
    }

    public function resolveAdminSession(ChatConsultationBooking $booking): ChatSession
    {
        $booking->loadMissing('session');

        if (! $booking->session) {
            abort(404);
        }

        return $booking->session;
    }

    /**
     * @return array{0: ChatSession, 1: ChatConsultationBooking, 2: CarbonInterface}
     */
    public function syncExpiryAndRefresh(ChatSession $session): array
    {
        $booking = $session->booking;
        $now     = Carbon::now('Europe/Sofia');

        if ($booking->status === ChatConsultationBooking::STATUS_CONFIRMED
            && $now->gte($booking->ends_at)) {
            $this->sessionLifecycle->completeIfExpired($session, $now);
            $session->refresh();
            $booking->refresh();
        }

        return [$session, $booking, $now];
    }

    public function parseAfterId(mixed $afterId): int
    {
        if ($afterId === null || $afterId === '') {
            return 0;
        }

        if (! is_numeric($afterId) || (int) $afterId < 0) {
            abort(422, 'Invalid after_id.');
        }

        return (int) $afterId;
    }

    /**
     * @return array<string, mixed>
     */
    public function clientFetchPayload(ChatSession $session, int $afterId): array
    {
        [$session, $booking, $now] = $this->syncExpiryAndRefresh($session);

        if (! $this->isKnownPhase($session->phase)) {
            abort(404);
        }

        $messages = $this->clientVisibleMessages($session, $afterId);

        return $this->buildPayload($session, $booking, $now, $messages);
    }

    /**
     * @return array<string, mixed>
     */
    public function adminFetchPayload(ChatSession $session, int $afterId): array
    {
        [$session, $booking, $now] = $this->syncExpiryAndRefresh($session);

        if (! $this->isKnownPhase($session->phase)) {
            abort(404);
        }

        $messages = $this->queryMessages($session, $afterId);

        return $this->buildPayload($session, $booking, $now, $messages);
    }

    public function sendMessage(ChatSession $session, string $senderType, string $messageBody): ChatMessage
    {
        if (! ChatMessage::isValidSenderType($senderType)) {
            throw new ChatMessageSendRejectedException();
        }

        $rejected = false;
        $created  = null;

        DB::transaction(function () use ($session, $senderType, $messageBody, &$rejected, &$created) {
            $lockedSession = ChatSession::where('id', $session->id)->lockForUpdate()->firstOrFail();

            $lockedBooking = ChatConsultationBooking::where('id', $lockedSession->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            $now = Carbon::now('Europe/Sofia');

            if ($lockedBooking->status !== ChatConsultationBooking::STATUS_CONFIRMED) {
                $rejected = true;

                return;
            }

            if ($now->gte($lockedBooking->ends_at)) {
                $this->sessionLifecycle->completeIfExpired($lockedSession, $now);
                $rejected = true;

                return;
            }

            if (! in_array($lockedSession->phase, [
                ChatSession::PHASE_ACTIVE,
                ChatSession::PHASE_ENDING,
            ], true)) {
                $rejected = true;

                return;
            }

            $created = ChatMessage::create([
                'chat_session_id' => $lockedSession->id,
                'sender_type'     => $senderType,
                'message'         => $messageBody,
            ]);
        });

        if ($rejected || ! $created instanceof ChatMessage) {
            throw new ChatMessageSendRejectedException();
        }

        return $created;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeMessage(ChatMessage $message): array
    {
        return [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'message'     => $message->message,
            'created_at'  => $message->created_at
                ->setTimezone('Europe/Sofia')
                ->toIso8601String(),
        ];
    }

    public function validateMessageInput(string $rawMessage): string
    {
        $trimmed = trim($rawMessage);

        if ($trimmed === '') {
            throw ValidationException::withMessages([
                'message' => ['Моля, въведете съобщение.'],
            ]);
        }

        if (mb_strlen($trimmed) > self::MAX_MESSAGE_LENGTH) {
            throw ValidationException::withMessages([
                'message' => ['Съобщението не може да надвишава ' . self::MAX_MESSAGE_LENGTH . ' символа.'],
            ]);
        }

        return $trimmed;
    }

    public function isRoomEligible(ChatConsultationBooking $booking): bool
    {
        if (! in_array($booking->status, [
            ChatConsultationBooking::STATUS_CONFIRMED,
            ChatConsultationBooking::STATUS_COMPLETED,
        ], true)) {
            return false;
        }

        $payment = $booking->payment;

        return $payment !== null && $payment->isPaid();
    }

    /**
     * @param  Collection<int, ChatMessage>  $messages
     * @return array<string, mixed>
     */
    private function buildPayload(
        ChatSession $session,
        ChatConsultationBooking $booking,
        CarbonInterface $now,
        Collection $messages,
    ): array {
        return [
            'session_phase'  => $session->phase,
            'booking_status' => $booking->status,
            'server_time'    => $now->toIso8601String(),
            'ends_at'        => $booking->ends_at->setTimezone('Europe/Sofia')->toIso8601String(),
            'can_send'       => $this->canSend($session, $booking, $now),
            'messages'       => $messages->map(fn (ChatMessage $message) => $this->serializeMessage($message))->values()->all(),
        ];
    }

    private function canSend(
        ChatSession $session,
        ChatConsultationBooking $booking,
        CarbonInterface $now,
    ): bool {
        if ($booking->status !== ChatConsultationBooking::STATUS_CONFIRMED) {
            return false;
        }

        if ($now->gte($booking->ends_at)) {
            return false;
        }

        return in_array($session->phase, [
            ChatSession::PHASE_ACTIVE,
            ChatSession::PHASE_ENDING,
        ], true);
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    private function clientVisibleMessages(ChatSession $session, int $afterId): Collection
    {
        if (! in_array($session->phase, [
            ChatSession::PHASE_ACTIVE,
            ChatSession::PHASE_ENDING,
        ], true)) {
            return collect();
        }

        return $this->queryMessages($session, $afterId);
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    private function queryMessages(ChatSession $session, int $afterId): Collection
    {
        return ChatMessage::query()
            ->where('chat_session_id', $session->id)
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(self::FETCH_LIMIT)
            ->get();
    }

    private function isKnownPhase(string $phase): bool
    {
        return in_array($phase, [
            ChatSession::PHASE_WAITING,
            ChatSession::PHASE_ACTIVE,
            ChatSession::PHASE_ENDING,
            ChatSession::PHASE_COMPLETED,
        ], true);
    }
}
