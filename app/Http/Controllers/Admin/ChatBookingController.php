<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ChatMessageSendRejectedException;
use App\Exceptions\ChatSessionLifecycleException;
use App\Http\Controllers\Controller;
use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Support\ChatMessageService;
use App\Support\ChatSessionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatBookingController extends Controller
{
    public function __construct(
        private readonly ChatSessionLifecycleService $sessionLifecycle,
        private readonly ChatMessageService $messageService,
    ) {}

    public function index()
    {
        $bookings = ChatConsultationBooking::with('session')
            ->whereNull('archived_at')
            ->orderBy('starts_at', 'desc')
            ->paginate(25);

        return view('admin.chat-bookings.index', compact('bookings'));
    }

    public function archiveIndex()
    {
        $bookings = ChatConsultationBooking::with('session')
            ->whereNotNull('archived_at')
            ->orderByDesc('archived_at')
            ->paginate(25);

        return view('admin.chat-bookings.archive', compact('bookings'));
    }

    public function show(ChatConsultationBooking $chatBooking)
    {
        $chatBooking->load('session');

        return view('admin.chat-bookings.show', compact('chatBooking'));
    }

    public function start(ChatConsultationBooking $chatBooking)
    {
        if ($chatBooking->archived_at !== null) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Архивираните записвания не могат да бъдат променяни.');
        }

        if ($chatBooking->status !== ChatConsultationBooking::STATUS_CONFIRMED) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Консултацията не може да бъде стартирана в текущия си статус.');
        }

        $chatBooking->load('session');

        if (! $chatBooking->session) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('error', 'Чат сесията не е налична.');
        }

        if ($chatBooking->session->phase !== ChatSession::PHASE_WAITING) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('error', 'Консултацията не може да бъде стартирана в текущата фаза.');
        }

        try {
            $this->sessionLifecycle->start($chatBooking->session);
        } catch (ChatSessionLifecycleException) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('error', 'Консултацията не може да бъде стартирана.');
        }

        return redirect()
            ->route('admin.chat-bookings.show', $chatBooking)
            ->with('success', 'Чат консултацията е стартирана успешно.');
    }

    public function complete(ChatConsultationBooking $chatBooking)
    {
        if ($chatBooking->archived_at !== null) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Архивираните записвания не могат да бъдат променяни.');
        }

        if ($chatBooking->status !== ChatConsultationBooking::STATUS_CONFIRMED) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Консултацията е вече маркирана като проведена или не е потвърдена.');
        }

        try {
            $this->sessionLifecycle->completeBooking($chatBooking);
        } catch (ChatSessionLifecycleException) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('error', 'Консултацията не може да бъде маркирана като проведена.');
        }

        return redirect()
            ->route('admin.chat-bookings.show', $chatBooking)
            ->with('success', 'Консултацията е маркирана като проведена.');
    }

    public function destroy(ChatConsultationBooking $chatBooking)
    {
        if ($chatBooking->archived_at === null) {
            return redirect()
                ->route('admin.chat-bookings.archived')
                ->with('error', 'Само архивирани записвания могат да бъдат изтрити.');
        }

        $chatBooking->session?->delete();
        $chatBooking->delete();

        return redirect()
            ->route('admin.chat-bookings.archived')
            ->with('success', 'Записването е изтрито.');
    }

    public function archive(ChatConsultationBooking $chatBooking)
    {
        if ($chatBooking->archived_at !== null) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Записването е вече архивирано.');
        }

        $archivable = [
            ChatConsultationBooking::STATUS_COMPLETED,
            ChatConsultationBooking::STATUS_PENDING_PAYMENT,
            ChatConsultationBooking::STATUS_EXPIRED,
        ];

        if (! in_array($chatBooking->status, $archivable, true)) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('error', 'Тази консултация не може да бъде архивирана в текущия си статус.');
        }

        $chatBooking->update(['archived_at' => now()]);

        return redirect()
            ->route('admin.chat-bookings.show', $chatBooking)
            ->with('success', 'Записването е архивирано успешно.');
    }

    /**
     * GET /admin/chat-bookings/{chatBooking}/chat
     */
    public function chat(ChatConsultationBooking $chatBooking)
    {
        $chatBooking->load('session');

        if (! $chatBooking->session) {
            abort(404);
        }

        if (! in_array($chatBooking->session->phase, [
            ChatSession::PHASE_WAITING,
            ChatSession::PHASE_ACTIVE,
            ChatSession::PHASE_ENDING,
            ChatSession::PHASE_COMPLETED,
        ], true)) {
            abort(404);
        }

        return view('admin.chat-bookings.chat', compact('chatBooking'));
    }

    /**
     * GET /admin/chat-bookings/{chatBooking}/messages
     */
    public function messagesIndex(Request $request, ChatConsultationBooking $chatBooking): JsonResponse
    {
        $session = $this->messageService->resolveAdminSession($chatBooking);
        $afterId = $this->messageService->parseAfterId($request->query('after_id'));

        return response()->json(
            $this->messageService->adminFetchPayload($session, $afterId)
        );
    }

    /**
     * POST /admin/chat-bookings/{chatBooking}/messages
     */
    public function messagesStore(Request $request, ChatConsultationBooking $chatBooking): JsonResponse
    {
        $session = $this->messageService->resolveAdminSession($chatBooking);

        $request->validate([
            'message' => ['required', 'string', 'max:' . ChatMessageService::MAX_MESSAGE_LENGTH],
        ]);

        $messageBody = $this->messageService->validateMessageInput($request->input('message', ''));

        try {
            $message = $this->messageService->sendMessage(
                $session,
                ChatMessage::SENDER_LAWYER,
                $messageBody,
            );
        } catch (ChatMessageSendRejectedException) {
            return response()->json([
                'message' => 'В момента не можете да изпращате съобщения.',
            ], 422);
        }

        return response()->json(
            $this->messageService->serializeMessage($message),
            201
        );
    }
}
