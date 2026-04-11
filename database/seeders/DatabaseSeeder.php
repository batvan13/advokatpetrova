<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ConsultationServiceSeeder;
use Database\Seeders\ConsultationWorkingHoursSeeder;
use Database\Seeders\PageSectionSeeder;
use Database\Seeders\SiteSettingSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PageSectionSeeder::class,
            ServiceSeeder::class,
            SiteSettingSeeder::class,
            ConsultationServiceSeeder::class,
            ConsultationWorkingHoursSeeder::class,
        ]);
    }
}
