<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViberConsultationBooking;

class ViberBookingController extends Controller
{
    public function index()
    {
        $bookings = ViberConsultationBooking::whereNull('archived_at')
            ->orderBy('starts_at', 'desc')
            ->paginate(25);

        return view('admin.viber-bookings.index', compact('bookings'));
    }

    public function show(ViberConsultationBooking $viberBooking)
    {
        return view('admin.viber-bookings.show', compact('viberBooking'));
    }

    public function complete(ViberConsultationBooking $viberBooking)
    {
        if ($viberBooking->archived_at !== null) {
            return redirect()
                ->route('admin.viber-bookings.show', $viberBooking)
                ->with('info', 'Архивираните записвания не могат да бъдат променяни.');
        }

        if ($viberBooking->status !== ViberConsultationBooking::STATUS_BOOKED) {
            return redirect()
                ->route('admin.viber-bookings.show', $viberBooking)
                ->with('info', 'Консултацията е вече маркирана като проведена.');
        }

        $viberBooking->update(['status' => ViberConsultationBooking::STATUS_COMPLETED]);

        return redirect()
            ->route('admin.viber-bookings.show', $viberBooking)
            ->with('success', 'Консултацията е маркирана като проведена.');
    }

    public function archive(ViberConsultationBooking $viberBooking)
    {
        if ($viberBooking->archived_at !== null) {
            return redirect()
                ->route('admin.viber-bookings.show', $viberBooking)
                ->with('info', 'Записването е вече архивирано.');
        }

        if ($viberBooking->status !== ViberConsultationBooking::STATUS_COMPLETED) {
            return redirect()
                ->route('admin.viber-bookings.show', $viberBooking)
                ->with('error', 'Само проведени консултации могат да бъдат архивирани.');
        }

        $viberBooking->update(['archived_at' => now()]);

        return redirect()
            ->route('admin.viber-bookings.show', $viberBooking)
            ->with('success', 'Записването е архивирано успешно.');
    }
}
