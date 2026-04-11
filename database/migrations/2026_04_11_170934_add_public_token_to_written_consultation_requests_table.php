<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add nullable first so existing rows don't violate NOT NULL
        Schema::table('written_consultation_requests', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->after('submitted_at');
        });

        // Backfill existing rows with a unique random token
        DB::table('written_consultation_requests')
            ->whereNull('public_token')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('written_consultation_requests')
                    ->where('id', $row->id)
                    ->update(['public_token' => Str::random(48)]);
            });

        // Now enforce NOT NULL + unique index
        Schema::table('written_consultation_requests', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable(false)->change();
            $table->unique('public_token');
        });
    }

    public function down(): void
    {
        Schema::table('written_consultation_requests', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
