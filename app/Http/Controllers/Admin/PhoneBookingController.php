<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhoneConsultationBooking;

class PhoneBookingController extends Controller
{
    public function index()
    {
        $bookings = PhoneConsultationBooking::whereNull('archived_at')
            ->orderByDesc('starts_at')
            ->paginate(30);

        return view('admin.phone-bookings.index', compact('bookings'));
    }

    public function archiveIndex()
    {
        $bookings = PhoneConsultationBooking::whereNotNull('archived_at')
            ->orderByDesc('archived_at')
            ->paginate(30);

        return view('admin.phone-bookings.archive', compact('bookings'));
    }

    public function show(PhoneConsultationBooking $phoneBooking)
    {
        return view('admin.phone-bookings.show', compact('phoneBooking'));
    }

    public function complete(PhoneConsultationBooking $phoneBooking)
    {
        if ($phoneBooking->archived_at !== null) {
            return redirect()
                ->route('admin.phone-bookings.show', $phoneBooking)
                ->with('info', 'Архивираните записвания не могат да бъдат променяни.');
        }

        if ($phoneBooking->status !== PhoneConsultationBooking::STATUS_BOOKED) {
            return redirect()
                ->route('admin.phone-bookings.show', $phoneBooking)
                ->with('info', 'Консултацията е вече маркирана като проведена.');
        }

        $phoneBooking->update(['status' => PhoneConsultationBooking::STATUS_COMPLETED]);

        return redirect()
            ->route('admin.phone-bookings.show', $phoneBooking)
            ->with('success', 'Консултацията е маркирана като проведена.');
    }

    public function destroy(PhoneConsultationBooking $phoneBooking)
    {
        if ($phoneBooking->archived_at === null) {
            return redirect()
                ->route('admin.phone-bookings.archived')
                ->with('error', 'Само архивирани записвания могат да бъдат изтрити.');
        }

        $phoneBooking->delete();

        return redirect()
            ->route('admin.phone-bookings.archived')
            ->with('success', 'Записването е изтрито.');
    }

    public function archive(PhoneConsultationBooking $phoneBooking)
    {
        if ($phoneBooking->archived_at !== null) {
            return redirect()
                ->route('admin.phone-bookings.show', $phoneBooking)
                ->with('info', 'Записването е вече архивирано.');
        }

        if ($phoneBooking->status !== PhoneConsultationBooking::STATUS_COMPLETED) {
            return redirect()
                ->route('admin.phone-bookings.show', $phoneBooking)
                ->with('error', 'Само проведени консултации могат да бъдат архивирани.');
        }

        $phoneBooking->update(['archived_at' => now()]);

        return redirect()
            ->route('admin.phone-bookings.show', $phoneBooking)
            ->with('success', 'Записването е архивирано успешно.');
    }
}
