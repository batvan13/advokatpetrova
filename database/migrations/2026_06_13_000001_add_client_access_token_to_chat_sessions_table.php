<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->string('client_access_token', 64)->nullable()->after('booking_id');
        });

        // Backfill existing rows before enforcing NOT NULL + UNIQUE (safe across MySQL/SQLite).
        $existingIds = DB::table('chat_sessions')
            ->whereNull('client_access_token')
            ->orderBy('id')
            ->pluck('id');

        foreach ($existingIds as $id) {
            DB::table('chat_sessions')
                ->where('id', $id)
                ->update(['client_access_token' => $this->generateUniqueToken()]);
        }

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->unique('client_access_token', 'chat_sessions_client_access_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropUnique('chat_sessions_client_access_token_unique');
            $table->dropColumn('client_access_token');
        });
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(48);
        } while (DB::table('chat_sessions')->where('client_access_token', $token)->exists());

        return $token;
    }
};
