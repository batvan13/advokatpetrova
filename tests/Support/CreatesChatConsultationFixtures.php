<?php

namespace Tests\Support;

use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use Carbon\Carbon;

trait CreatesChatConsultationFixtures
{
    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @param  array<string, mixed>  $sessionOverrides
     * @return array{0: ChatConsultationBooking, 1: ChatSession}
     */
    protected function createConfirmedBookingWithSession(
        array $bookingOverrides = [],
        array $sessionOverrides = [],
    ): array {
        $startsAt = $bookingOverrides['starts_at'] ?? Carbon::parse('2026-06-15 15:00:00');

        if (! $startsAt instanceof Carbon) {
            $startsAt = Carbon::parse($startsAt);
        }

        $booking = ChatConsultationBooking::create(array_merge([
            'first_name'     => 'Ivan',
            'last_name'      => 'Petrov',
            'email'          => 'ivan@example.com',
            'phone'          => '+359888000000',
            'starts_at'      => $startsAt,
            'ends_at'        => $startsAt->copy()->addMinutes(ChatConsultationBooking::DURATION_MINUTES),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_CONFIRMED,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ], $bookingOverrides));

        $session = ChatSession::create(array_merge([
            'booking_id' => $booking->id,
            'phase'      => ChatSession::PHASE_WAITING,
            'started_at' => null,
            'ended_at'   => null,
        ], $sessionOverrides));

        return [$booking, $session];
    }
}
