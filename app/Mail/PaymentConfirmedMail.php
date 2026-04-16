<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Client-facing email sent after successful payment finalization.
 *
 * Covers all four consultation types:
 *   phone  → slot-based, uses mail.payment-confirmed (booking variant)
 *   viber  → slot-based, uses mail.payment-confirmed (booking variant)
 *   chat   → slot-based, uses mail.payment-confirmed (booking variant)
 *   written → uses mail.payment-confirmed-written
 *
 * $contactNumber is relevant only for phone and viber.
 */
class PaymentConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object  $payable,
        public readonly string  $type,
        public readonly ?string $contactNumber = null,
        public readonly string  $successUrl    = '',
        public readonly ?string $contactEmail  = null,
    ) {}

    public function build(): static
    {
        $typeLabels = [
            'phone'   => 'Телефонна консултация',
            'viber'   => 'Viber видео консултация',
            'chat'    => 'Чат консултация',
            'written' => 'Писмена консултация',
        ];

        $label = $typeLabels[$this->type] ?? 'Консултация';

        $view = $this->type === 'written'
            ? 'mail.payment-confirmed-written'
            : 'mail.payment-confirmed';

        return $this
            ->subject('Консултацията е потвърдена — ' . $label)
            ->view($view);
    }
}
