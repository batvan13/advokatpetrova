<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Support\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStalePayments extends Command
{
    protected $signature   = 'payments:expire-stale';
    protected $description = 'Expire pending payments whose expires_at has passed';

    public function handle(PaymentService $paymentService): int
    {
        $stale = Payment::where('status', Payment::STATUS_PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->where('is_finalized', false)
            ->get();

        $scanned = $stale->count();
        $expired = 0;
        $skipped = 0;
        $failed  = 0;

        foreach ($stale as $payment) {
            // Double-check: skip anything already finalized (race with concurrent web request).
            if ($payment->is_finalized) {
                $skipped++;
                continue;
            }

            try {
                $result = $paymentService->expireStalePayment($payment);

                // expireStalePayment returns the payment unchanged when already finalized.
                if ($result->is_finalized && $result->status === Payment::STATUS_EXPIRED) {
                    $expired++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $failed++;

                Log::error('payments:expire-stale failed for payment', [
                    'payment_id'     => $payment->id,
                    'invoice_number' => $payment->invoice_number,
                    'expires_at'     => $payment->expires_at?->toIso8601String(),
                    'error'          => $e->getMessage(),
                    'class'          => get_class($e),
                ]);
            }
        }

        $this->line(sprintf(
            'payments:expire-stale | scanned: %d | expired: %d | skipped: %d | failed: %d',
            $scanned, $expired, $skipped, $failed
        ));

        if ($failed > 0) {
            $this->warn("{$failed} payment(s) failed to expire — check logs.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
