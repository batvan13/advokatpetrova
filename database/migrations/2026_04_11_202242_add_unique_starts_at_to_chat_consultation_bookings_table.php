<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a database-level UNIQUE constraint on starts_at.
     *
     * This is valid because:
     *   - Chat consultations are always exactly 30 minutes.
     *   - starts_at always comes from a valid 30-minute grid slot.
     *   - There is no variable duration and no arbitrary start time.
     *
     * This is Layer 2 of the three-layer double-booking defence
     * (lockForUpdate = Layer 1, this index = Layer 2,
     *  QueryException catch = Layer 3), mirroring the pattern
     *  already applied to phone_consultation_bookings.
     */
    public function up(): void
    {
        Schema::table('chat_consultation_bookings', function (Blueprint $table) {
            $table->unique('starts_at', 'ccb_unique_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_consultation_bookings', function (Blueprint $table) {
            $table->dropUnique('ccb_unique_starts_at');
        });
    }
};
