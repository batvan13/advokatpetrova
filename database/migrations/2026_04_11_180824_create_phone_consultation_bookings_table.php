<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phone_consultation_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 64)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('payment_method', 20);
            $table->string('status', 20)->default('booked');
            $table->decimal('price_eur', 8, 2);
            $table->decimal('price_bgn', 8, 2)->nullable();
            $table->boolean('show_bgn_price')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_consultation_bookings');
    }
};
