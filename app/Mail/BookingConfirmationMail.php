<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Client-facing booking confirmation for slot-based consultations:
 * Phone, Viber (video), and Chat.
 *
 * The $booking may be any of:
 *   App\Models\PhoneConsultationBooking
 *   App\Models\ViberConsultationBooking
 *   App\Models\ChatConsultationBooking
 *
 * The $type string controls which content variant is rendered in the template.
 * Accepted values: 'phone' | 'viber' | 'chat'
 *
 * $contactNumber is the relevant contact number from SiteSetting:
 *   phone  → consultation_phone_number
 *   viber  → consultation_viber_number
 *   chat   → null (not needed)
 */
class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object  $booking,
        public readonly string  $type,
        public readonly ?string $contactNumber = null,
        public readonly string  $successUrl    = '',
        public readonly ?string $contactEmail  = null,
    ) {}

    public function build(): static
    {
        $typeLabels = [
            'phone' => 'Телефонна консултация',
            'viber' => 'Viber видео консултация',
            'chat'  => 'Чат консултация',
        ];

        $label = $typeLabels[$this->type] ?? 'Консултация';

        return $this
            ->subject('Потвърждение на заявка — ' . $label)
            ->view('mail.booking-confirmation');
    }
}
