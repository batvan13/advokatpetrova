<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            $table->string('phase', 20)->default('waiting');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('booking_id', 'cs_booking_fk')
                ->references('id')
                ->on('chat_consultation_bookings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
