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
        Schema::table('phone_consultation_bookings', function (Blueprint $table) {
            // One booking per slot start time — valid because all slots are
            // fixed 30-minute intervals generated server-side from the same grid.
            // This is the database-level backstop against concurrent double-booking.
            $table->unique('starts_at', 'pcb_unique_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phone_consultation_bookings', function (Blueprint $table) {
            $table->dropUnique('pcb_unique_starts_at');
        });
    }
};
