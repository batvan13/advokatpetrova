<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_consultation_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 64)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');  // always starts_at + 30 min
            $table->string('payment_method', 20);
            $table->string('status', 20)->default('booked');
            $table->decimal('price_eur', 8, 2);
            $table->decimal('price_bgn', 8, 2)->nullable();
            $table->boolean('show_bgn_price')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_consultation_bookings');
    }
};
