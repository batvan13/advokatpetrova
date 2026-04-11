<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhoneConsultationBooking;

class PhoneBookingController extends Controller
{
    public function index()
    {
        $bookings = PhoneConsultationBooking::orderByDesc('starts_at')->paginate(30);

        return view('admin.phone-bookings.index', compact('bookings'));
    }

    public function show(PhoneConsultationBooking $phoneBooking)
    {
        return view('admin.phone-bookings.show', compact('phoneBooking'));
    }
}
