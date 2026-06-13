<?php

namespace App\Support;

use App\Exceptions\ChatSessionLifecycleException;
use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ChatSessionLifecycleService
{
    /**
     * Transition a waiting session to active when the lawyer starts the consultation.
     *
     * Idempotent when the session is already active (started_at is never reset).
     *
     * If the scheduled end time has passed while the session is still waiting, the
     * session and booking are completed atomically and a lifecycle exception is thrown.
     */
    public function start(ChatSession $session): ChatSession
    {
        $expired = false;

        $result = DB::transaction(function () use ($session, &$expired) {
            $locked = ChatSession::where('id', $session->id)->lockForUpdate()->firstOrFail();

            $booking = ChatConsultationBooking::where('id', $locked->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== ChatConsultationBooking::STATUS_CONFIRMED) {
                throw new ChatSessionLifecycleException(
                    'Chat session can only be started for a confirmed booking.'
                );
            }

            if ($locked->isCompleted()) {
                throw new ChatSessionLifecycleException(
                    'A completed chat session cannot be started.'
                );
            }

            if ($locked->isActive()) {
                return $locked;
            }

            if ($locked->phase !== ChatSession::PHASE_WAITING) {
                throw new ChatSessionLifecycleException(
                    'Chat session can only be started from the waiting phase.'
                );
            }

            $now = now();

            if ($now->gte($booking->ends_at)) {
                $this->applyCompletionToLockedRows($locked, $booking, $now);
                $expired = true;

                return $locked->fresh(['booking']);
            }

            $locked->update([
                'phase'      => ChatSession::PHASE_ACTIVE,
                'started_at' => $locked->started_at ?? $now,
            ]);

            return $locked->fresh(['booking']);
        });

        if ($expired) {
            throw new ChatSessionLifecycleException(
                'The scheduled consultation time has expired.'
            );
        }

        return $result;
    }

    /**
     * Complete the session and its booking atomically.
     *
     * Idempotent: repeat calls preserve the original ended_at once set.
     */
    public function complete(ChatSession $session, ?CarbonInterface $completedAt = null): ChatSession
    {
        $completedAt = $completedAt !== null ? Carbon::parse($completedAt) : now();

        return DB::transaction(function () use ($session, $completedAt) {
            $locked = ChatSession::where('id', $session->id)->lockForUpdate()->firstOrFail();

            $booking = ChatConsultationBooking::where('id', $locked->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isCompleted() && $booking->status === ChatConsultationBooking::STATUS_COMPLETED) {
                return $locked;
            }

            $this->applyCompletionToLockedRows($locked, $booking, $completedAt);

            return $locked->fresh(['booking']);
        });
    }

    /**
     * Complete a booking and its session in one transaction.
     *
     * Legacy recovery: when no session row exists, exactly one session is created
     * inside this transaction with started_at null, then immediately completed.
     */
    public function completeBooking(
        ChatConsultationBooking $booking,
        ?CarbonInterface $completedAt = null,
    ): ChatSession {
        $completedAt = $completedAt !== null ? Carbon::parse($completedAt) : now();

        return DB::transaction(function () use ($booking, $completedAt) {
            $lockedBooking = ChatConsultationBooking::where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== ChatConsultationBooking::STATUS_CONFIRMED
                && $lockedBooking->status !== ChatConsultationBooking::STATUS_COMPLETED) {
                throw new ChatSessionLifecycleException(
                    'Chat session can only be completed for a confirmed booking.'
                );
            }

            $lockedSession = ChatSession::where('booking_id', $lockedBooking->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSession) {
                $lockedSession = $this->createRecoverySession($lockedBooking);
            }

            if ($lockedSession->isCompleted() && $lockedBooking->status === ChatConsultationBooking::STATUS_COMPLETED) {
                return $lockedSession;
            }

            $this->applyCompletionToLockedRows($lockedSession, $lockedBooking, $completedAt);

            return $lockedSession->fresh(['booking']);
        });
    }

    /**
     * Complete the session when the planned booking end time has been reached.
     *
     * Safe to call repeatedly; returns true when completion applies or already
     * completed, false when ends_at has not yet passed.
     */
    public function completeIfExpired(ChatSession $session, ?CarbonInterface $now = null): bool
    {
        $now = $now !== null ? Carbon::parse($now) : now();

        return DB::transaction(function () use ($session, $now) {
            $locked = ChatSession::where('id', $session->id)->lockForUpdate()->firstOrFail();

            $booking = ChatConsultationBooking::where('id', $locked->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($now->lt($booking->ends_at)) {
                return false;
            }

            if ($locked->isCompleted() && $booking->status === ChatConsultationBooking::STATUS_COMPLETED) {
                return true;
            }

            $this->applyCompletionToLockedRows($locked, $booking, $now);

            return true;
        });
    }

    /**
     * Apply completion state to already-locked session and booking rows.
     *
     * Must be called only while holding row locks inside an outer transaction.
     */
    private function applyCompletionToLockedRows(
        ChatSession $session,
        ChatConsultationBooking $booking,
        CarbonInterface $completedAt,
    ): void {
        if ($booking->status !== ChatConsultationBooking::STATUS_CONFIRMED
            && $booking->status !== ChatConsultationBooking::STATUS_COMPLETED) {
            throw new ChatSessionLifecycleException(
                'Chat session can only be completed for a confirmed booking.'
            );
        }

        if (! $session->isCompleted()) {
            $session->update([
                'phase'    => ChatSession::PHASE_COMPLETED,
                'ended_at' => $session->ended_at ?? $completedAt,
            ]);
        }

        if ($booking->status !== ChatConsultationBooking::STATUS_COMPLETED) {
            $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);
        }
    }

    /**
     * Create a recovery session row, retrying once on a booking_id unique race.
     */
    private function createRecoverySession(ChatConsultationBooking $booking): ChatSession
    {
        try {
            return ChatSession::create([
                'booking_id' => $booking->id,
                'phase'      => ChatSession::DEFAULT_PHASE,
                'started_at' => null,
                'ended_at'   => null,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            $existing = ChatSession::where('booking_id', $booking->id)->lockForUpdate()->first();

            if ($existing) {
                return $existing;
            }

            throw $e;
        }
    }
}
