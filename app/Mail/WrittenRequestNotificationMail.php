<?php

namespace App\Mail;

use App\Models\WrittenConsultationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Admin notification for a new written consultation request.
 *
 * $adminUrl is the fully-qualified URL to the admin show page.
 */
class WrittenRequestNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly WrittenConsultationRequest $consultationRequest,
        public readonly string $adminUrl,
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Нова заявка — Писмена консултация от ' . $this->consultationRequest->fullName())
            ->view('mail.written-notification');
    }
}
