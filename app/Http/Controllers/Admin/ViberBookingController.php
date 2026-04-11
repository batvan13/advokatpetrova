<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViberConsultationBooking;

class ViberBookingController extends Controller
{
    public function index()
    {
        $bookings = ViberConsultationBooking::orderBy('starts_at', 'desc')
            ->paginate(25);

        return view('admin.viber-bookings.index', compact('bookings'));
    }

    public function show(ViberConsultationBooking $viberBooking)
    {
        return view('admin.viber-bookings.show', compact('viberBooking'));
    }
}
