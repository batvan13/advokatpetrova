<?php

namespace App\Http\Controllers;

use App\Models\ChatConsultationBooking;
use App\Models\Payment;
use App\Models\PhoneConsultationBooking;
use App\Models\ViberConsultationBooking;
use App\Models\WrittenConsultationRequest;
use App\Support\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    // ── Simulate page ─────────────────────────────────────────────────────────

    /**
     * GET /payment/{invoice}/simulate
     *
     * Shows the fake payment terminal for a given invoice.
     * Accessible as long as the payment record exists.
     */
    public function simulate(string $invoice)
    {
        $payment = Payment::where('invoice_number', $invoice)->firstOrFail();
        $payment->load('payable');

        return view('payment.simulate', compact('payment'));
    }

    // ── Fake notification endpoint ────────────────────────────────────────────

    /**
     * POST /payment/notify/fake-epay
     *
     * The ONLY endpoint that transitions payment to paid / failed / expired.
     * Receives the simulated provider response from the simulate page form.
     */
    public function notify(Request $request)
    {
        $data = $request->validate([
            'invoice_number'  => ['required', 'string', 'max:60'],
            'provider_status' => ['required', 'in:PAID,DENIED,EXPIRED'],
        ]);

        $payment = $this->paymentService->processFakeNotification(
            $data['invoice_number'],
            $data['provider_status'],
            $request->except(['_token']),
        );

        return redirect()->to($this->returnUrl($payment));
    }

    // ── Return URL ────────────────────────────────────────────────────────────

    private function returnUrl(Payment $payment): string
    {
        $payable = $payment->payable;

        if (! $payable) {
            return route('home');
        }

        try {
            return match (true) {
                $payable instanceof PhoneConsultationBooking =>
                    route('phone-consultation.success', ['token' => $payable->public_token]),
                $payable instanceof ViberConsultationBooking =>
                    route('viber-consultation.success', ['token' => $payable->public_token]),
                $payable instanceof ChatConsultationBooking =>
                    route('chat-consultation.success', ['token' => $payable->public_token]),
                $payable instanceof WrittenConsultationRequest =>
                    route('written-consultation.success', ['ref' => $payable->public_token]),
                default => route('home'),
            };
        } catch (\Throwable) {
            return route('home');
        }
    }
}
