<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConsultationBooking;

class ChatBookingController extends Controller
{
    public function index()
    {
        $bookings = ChatConsultationBooking::with('session')
            ->whereNull('archived_at')
            ->orderBy('starts_at', 'desc')
            ->paginate(25);

        return view('admin.chat-bookings.index', compact('bookings'));
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

        if ($chatBooking->status !== ChatConsultationBooking::STATUS_BOOKED) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Консултацията е вече маркирана като проведена.');
        }

        $chatBooking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        return redirect()
            ->route('admin.chat-bookings.show', $chatBooking)
            ->with('success', 'Консултацията е маркирана като проведена.');
    }

    public function archive(ChatConsultationBooking $chatBooking)
    {
        if ($chatBooking->archived_at !== null) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('info', 'Записването е вече архивирано.');
        }

        if ($chatBooking->status !== ChatConsultationBooking::STATUS_COMPLETED) {
            return redirect()
                ->route('admin.chat-bookings.show', $chatBooking)
                ->with('error', 'Само проведени консултации могат да бъдат архивирани.');
        }

        $chatBooking->update(['archived_at' => now()]);

        return redirect()
            ->route('admin.chat-bookings.show', $chatBooking)
            ->with('success', 'Записването е архивирано успешно.');
    }
}
