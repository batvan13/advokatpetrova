<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clear demo title/subtitle/content on home.about_preview so homepage uses /about fallback by default.
     * Does not modify meta (buttons, image_path, etc.).
     */
    public function up(): void
    {
        DB::table('page_sections')
            ->where('page', 'home')
            ->where('section', 'about_preview')
            ->update([
                'title'    => null,
                'subtitle' => null,
                'content'  => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * Restore previous seed copy for rollback only.
     */
    public function down(): void
    {
        DB::table('page_sections')
            ->where('page', 'home')
            ->where('section', 'about_preview')
            ->update([
                'title'    => 'За нас',
                'subtitle' => 'Кратко представяне на бизнеса и неговите предимства.',
                'content'  => 'Работим с внимание към всеки детайл и индивидуален подход към всеки клиент.',
                'updated_at' => now(),
            ]);
    }
};
