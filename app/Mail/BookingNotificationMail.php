<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Admin notification for a new slot-based booking.
 * Mirrors BookingConfirmationMail but targets the office inbox.
 *
 * $adminUrl is the fully-qualified URL to the admin show page,
 * generated with route() in the controller before passing here.
 */
class BookingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $booking,
        public readonly string $type,
        public readonly string $adminUrl,
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
            ->subject('Нова заявка — ' . $label . ' от ' . $this->booking->fullName())
            ->view('mail.booking-notification');
    }
}
