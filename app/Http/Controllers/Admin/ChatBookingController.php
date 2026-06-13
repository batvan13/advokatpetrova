<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ChatSessionLifecycleException;
use App\Http\Controllers\Controller;
use App\Models\ChatConsultationBooking;
use App\Support\ChatSessionLifecycleService;

class ChatBookingController extends Controller
{
    public function __construct(
        private readonly ChatSessionLifecycleService $sessionLifecycle,
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
}
