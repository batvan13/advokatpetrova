<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation: links to any bookable/requestable model.
            $table->string('payable_type', 100);
            $table->unsignedBigInteger('payable_id');

            $table->string('provider', 40)->default('fake_epay');
            $table->string('payment_method', 20);
            $table->string('invoice_number', 60)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status', 20)->default('pending');
            $table->string('provider_status', 40)->nullable();
            $table->text('description')->nullable();

            // Lifecycle timestamps
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->dateTime('expired_at')->nullable();

            // Notification tracking
            $table->dateTime('notification_received_at')->nullable();
            $table->longText('notification_payload')->nullable();

            // Provider-specific fields (reserved for real ePay/EasyPay integration)
            $table->string('stan', 40)->nullable();
            $table->string('bcode', 40)->nullable();

            // Exact-once finalization guard
            $table->boolean('is_finalized')->default(false);
            $table->dateTime('finalized_at')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id'], 'payments_payable_index');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
