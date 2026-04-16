<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viber_consultation_bookings', function (Blueprint $table) {
            $table->unique('starts_at', 'viber_consultation_bookings_starts_at_unique');
        });
    }

    public function down(): void
    {
        Schema::table('viber_consultation_bookings', function (Blueprint $table) {
            $table->dropUnique('viber_consultation_bookings_starts_at_unique');
        });
    }
};
