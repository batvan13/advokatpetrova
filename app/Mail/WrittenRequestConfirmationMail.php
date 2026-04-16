<?php

namespace App\Mail;

use App\Models\WrittenConsultationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Client-facing confirmation for a submitted written consultation request.
 */
class WrittenRequestConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly WrittenConsultationRequest $consultationRequest,
        public readonly string  $successUrl   = '',
        public readonly ?string $contactEmail = null,
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Заявката е получена — Писмена консултация')
            ->view('mail.written-confirmation');
    }
}
