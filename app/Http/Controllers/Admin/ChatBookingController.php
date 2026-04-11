<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConsultationBooking;

class ChatBookingController extends Controller
{
    public function index()
    {
        $bookings = ChatConsultationBooking::with('session')
            ->orderBy('starts_at', 'desc')
            ->paginate(25);

        return view('admin.chat-bookings.index', compact('bookings'));
    }

    public function show(ChatConsultationBooking $chatBooking)
    {
        $chatBooking->load('session');

        return view('admin.chat-bookings.show', compact('chatBooking'));
    }
}
