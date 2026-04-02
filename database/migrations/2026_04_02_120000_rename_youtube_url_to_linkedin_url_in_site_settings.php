<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Preserve social URL: youtube_url row becomes linkedin_url when safe.
     */
    public function up(): void
    {
        $youtube = DB::table('site_settings')->where('key', 'youtube_url')->first();
        if (! $youtube) {
            return;
        }

        $linkedin = DB::table('site_settings')->where('key', 'linkedin_url')->first();

        if (! $linkedin) {
            DB::table('site_settings')->where('key', 'youtube_url')->update(['key' => 'linkedin_url']);

            return;
        }

        $linkedinEmpty = $linkedin->value === null || trim((string) $linkedin->value) === '';
        $youtubeHas = $youtube->value !== null && trim((string) $youtube->value) !== '';

        if ($linkedinEmpty && $youtubeHas) {
            DB::table('site_settings')->where('key', 'linkedin_url')->update(['value' => $youtube->value]);
        }

        DB::table('site_settings')->where('key', 'youtube_url')->delete();
    }

    /**
     * Best-effort reverse: only if youtube_url is absent and linkedin_url exists.
     */
    public function down(): void
    {
        if (DB::table('site_settings')->where('key', 'youtube_url')->exists()) {
            return;
        }

        $linkedin = DB::table('site_settings')->where('key', 'linkedin_url')->first();
        if (! $linkedin) {
            return;
        }

        DB::table('site_settings')->where('key', 'linkedin_url')->update(['key' => 'youtube_url']);
    }
};
