<?php

namespace App\Support;

use App\Mail\BookingNotificationMail;
use App\Mail\PaymentConfirmedMail;
use App\Mail\WrittenRequestNotificationMail;
use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use App\Models\Payment;
use App\Models\PhoneConsultationBooking;
use App\Models\SiteSetting;
use App\Models\ViberConsultationBooking;
use App\Models\WrittenConsultationRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentService
{
    private const PAYMENT_EXPIRY_MINUTES = 30;

    /**
     * Create a pending payment record linked to a booking or request.
     * Call this immediately after the booking/request is created.
     */
    public function createPendingPayment(
        object $payable,
        float  $amount,
        string $currency,
        string $paymentMethod,
        string $description,
    ): Payment {
        return Payment::create([
            'payable_type'   => get_class($payable),
            'payable_id'     => $payable->id,
            'provider'       => 'fake_epay',
            'payment_method' => $paymentMethod,
            'invoice_number' => $this->generateInvoiceNumber(),
            'amount'         => $amount,
            'currency'       => $currency,
            'status'         => Payment::STATUS_PENDING,
            'description'    => $description,
            'expires_at'     => Carbon::now()->addMinutes(self::PAYMENT_EXPIRY_MINUTES),
        ]);
    }

    /**
     * Process a fake ePay-style provider notification.
     *
     * Accepted $providerStatus values:
     *   PAID    → marks payment paid, confirms booking/request, fires side-effects
     *   DENIED  → marks payment failed, booking/request stays pending_payment
     *   EXPIRED → marks payment expired, booking/request status set to expired
     *
     * Idempotent: if payment is already finalized, no state is changed.
     */
    public function processFakeNotification(
        string $invoiceNumber,
        string $providerStatus,
        array  $payload = [],
    ): Payment {
        $payment = Payment::where('invoice_number', $invoiceNumber)->firstOrFail();

        // Always record that a notification arrived.
        $payment->update([
            'notification_received_at' => Carbon::now(),
            'notification_payload'     => json_encode($payload),
            'provider_status'          => $providerStatus,
        ]);

        // Exact-once guard: once finalized, ignore all further notifications.
        if ($payment->is_finalized) {
            return $payment->fresh();
        }

        return match ($providerStatus) {
            Payment::PROVIDER_STATUS_PAID    => $this->handlePaid($payment),
            Payment::PROVIDER_STATUS_DENIED  => $this->handleDenied($payment),
            Payment::PROVIDER_STATUS_EXPIRED => $this->handleExpired($payment),
            default                          => $payment->fresh(),
        };
    }

    // ── Private handlers ──────────────────────────────────────────────────────

    private function handlePaid(Payment $payment): Payment
    {
        // All DB state changes are inside one transaction.
        DB::transaction(function () use ($payment) {
            // Re-lock the row to prevent a race between two simultaneous PAID notifications.
            $locked = Payment::where('id', $payment->id)->lockForUpdate()->first();

            if ($locked->is_finalized) {
                return; // already done — exit quietly
            }

            $locked->update([
                'status'       => Payment::STATUS_PAID,
                'paid_at'      => Carbon::now(),
                'is_finalized' => true,
                'finalized_at' => Carbon::now(),
            ]);

            $payable = $locked->payable;

            if (! $payable) {
                return;
            }

            if ($payable instanceof WrittenConsultationRequest) {
                $payable->update(['status' => WrittenConsultationRequest::STATUS_SUBMITTED]);
            } else {
                // Phone, Viber, Chat
                $payable->update(['status' => $payable::STATUS_CONFIRMED]);

                if ($payable instanceof ChatConsultationBooking) {
                    $this->ensureChatSession($payable);
                }
            }
        });

        $payment->refresh();

        // Side-effects outside transaction: failures are logged and non-fatal.
        $this->syncCalendarAfterPayment($payment);
        $this->sendConfirmationEmails($payment);

        return $payment;
    }

    private function handleDenied(Payment $payment): Payment
    {
        // DENIED does not free the slot: booking stays pending_payment.
        // The client must contact the office to re-attempt or cancel.
        $payment->update([
            'status'       => Payment::STATUS_FAILED,
            'failed_at'    => Carbon::now(),
            'is_finalized' => true,
            'finalized_at' => Carbon::now(),
        ]);

        return $payment->fresh();
    }

    /**
     * Expose expiration for the scheduler command without going through the
     * fake-notification path (which writes notification_received_at / payload).
     *
     * Safe to call on already-finalized payments — returns the current state
     * without making any further changes.
     */
    public function expireStalePayment(Payment $payment): Payment
    {
        if ($payment->is_finalized) {
            return $payment;
        }

        return $this->handleExpired($payment);
    }

    private function handleExpired(Payment $payment): Payment
    {
        DB::transaction(function () use ($payment) {
            $locked = Payment::where('id', $payment->id)->lockForUpdate()->first();

            if ($locked->is_finalized) {
                return;
            }

            $locked->update([
                'status'          => Payment::STATUS_EXPIRED,
                'provider_status' => Payment::PROVIDER_STATUS_EXPIRED,
                'expired_at'      => Carbon::now(),
                'is_finalized'    => true,
                'finalized_at'    => Carbon::now(),
            ]);

            $payable = $locked->payable;

            if ($payable) {
                $payable->update(['status' => $payable::STATUS_EXPIRED]);
            }
        });

        return $payment->fresh();
    }

    // ── Chat session ──────────────────────────────────────────────────────────

    private function ensureChatSession(ChatConsultationBooking $booking): void
    {
        $session = $booking->session()->first();

        if ($session) {
            if (empty($session->client_access_token)) {
                $session->update([
                    'client_access_token' => ChatSession::generateUniqueClientAccessToken(),
                ]);
            }

            return;
        }

        ChatSession::create([
            'booking_id' => $booking->id,
            'phase'      => ChatSession::DEFAULT_PHASE,
            'started_at' => null,
            'ended_at'   => null,
        ]);
    }

    // ── Google Calendar ───────────────────────────────────────────────────────

    private function syncCalendarAfterPayment(Payment $payment): void
    {
        $payable = $payment->payable;

        if (! $payable || $payable instanceof WrittenConsultationRequest) {
            return;
        }

        if (! empty($payable->google_event_id)) {
            return; // already synced
        }

        $type = match (true) {
            $payable instanceof PhoneConsultationBooking => 'phone',
            $payable instanceof ViberConsultationBooking => 'viber',
            $payable instanceof ChatConsultationBooking  => 'chat',
            default                                      => null,
        };

        if (! $type) {
            return;
        }

        try {
            $calendar = app(GoogleCalendarService::class);
            $eventId  = $calendar->createEvent($payable, $type);

            $payable->update([
                'google_event_id'    => $eventId,
                'google_sync_status' => 'synced',
            ]);
        } catch (\Throwable $e) {
            Log::error('Google Calendar sync failed after payment confirmation', [
                'invoice'    => $payment->invoice_number,
                'type'       => $type,
                'payable_id' => $payable->id,
                'error'      => $e->getMessage(),
            ]);

            try {
                $payable->update(['google_sync_status' => 'failed']);
            } catch (\Throwable) {}
        }
    }

    // ── Emails ────────────────────────────────────────────────────────────────

    private function sendConfirmationEmails(Payment $payment): void
    {
        $payable      = $payment->payable;
        $contactEmail = SiteSetting::get('contact_email');

        if (! $payable) {
            return;
        }

        $type = match (true) {
            $payable instanceof PhoneConsultationBooking => 'phone',
            $payable instanceof ViberConsultationBooking => 'viber',
            $payable instanceof ChatConsultationBooking  => 'chat',
            $payable instanceof WrittenConsultationRequest => 'written',
            default => null,
        };

        if (! $type) {
            return;
        }

        $successUrl    = $this->buildSuccessUrl($payable, $type);
        $adminUrl      = $this->buildAdminUrl($payable, $type);
        $contactNumber = match ($type) {
            'phone' => SiteSetting::get('consultation_phone_number'),
            'viber' => SiteSetting::get('consultation_viber_number'),
            default => null,
        };

        // Client confirmation email
        try {
            Mail::to($payable->email)->send(
                new PaymentConfirmedMail($payable, $type, $contactNumber, $successUrl, $contactEmail)
            );
        } catch (\Throwable $e) {
            Log::error('Payment confirmed client mail failed', [
                'invoice' => $payment->invoice_number,
                'type'    => $type,
                'error'   => $e->getMessage(),
            ]);
        }

        // Admin notification email
        if ($contactEmail) {
            try {
                if ($type === 'written') {
                    $payable->loadMissing('attachments');
                    Mail::to($contactEmail)->send(new WrittenRequestNotificationMail($payable, $adminUrl));
                } else {
                    Mail::to($contactEmail)->send(new BookingNotificationMail($payable, $type, $adminUrl));
                }
            } catch (\Throwable $e) {
                Log::error('Payment confirmed admin mail failed', [
                    'invoice' => $payment->invoice_number,
                    'type'    => $type,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    // ── URL helpers ───────────────────────────────────────────────────────────

    private function buildSuccessUrl(object $payable, string $type): string
    {
        try {
            return match ($type) {
                'phone'   => route('phone-consultation.success', ['token' => $payable->public_token]),
                'viber'   => route('viber-consultation.success', ['token' => $payable->public_token]),
                'chat'    => route('chat-consultation.success', ['token' => $payable->public_token]),
                'written' => route('written-consultation.success', ['ref' => $payable->public_token]),
                default   => '',
            };
        } catch (\Throwable) {
            return '';
        }
    }

    private function buildAdminUrl(object $payable, string $type): string
    {
        try {
            return match ($type) {
                'phone'   => route('admin.phone-bookings.show', $payable),
                'viber'   => route('admin.viber-bookings.show', $payable),
                'chat'    => route('admin.chat-bookings.show', $payable),
                'written' => route('admin.written-consultations.show', $payable),
                default   => '',
            };
        } catch (\Throwable) {
            return '';
        }
    }

    // ── Invoice generation ────────────────────────────────────────────────────

    private function generateInvoiceNumber(): string
    {
        do {
            $candidate = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Payment::where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
