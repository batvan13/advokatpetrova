<?php

namespace Database\Seeders;

use App\Models\ConsultationWorkingHours;
use Illuminate\Database\Seeder;

class ConsultationWorkingHoursSeeder extends Seeder
{
    public function run(): void
    {
        // ISO-8601: 1=Monday … 7=Sunday
        $defaults = [
            1 => ['is_open' => true,  'start_time' => '09:00', 'end_time' => '17:00'],
            2 => ['is_open' => true,  'start_time' => '09:00', 'end_time' => '17:00'],
            3 => ['is_open' => true,  'start_time' => '09:00', 'end_time' => '17:00'],
            4 => ['is_open' => true,  'start_time' => '09:00', 'end_time' => '17:00'],
            5 => ['is_open' => true,  'start_time' => '09:00', 'end_time' => '17:00'],
            6 => ['is_open' => false, 'start_time' => null,    'end_time' => null],
            7 => ['is_open' => false, 'start_time' => null,    'end_time' => null],
        ];

        foreach ($defaults as $day => $values) {
            ConsultationWorkingHours::firstOrCreate(
                ['day_of_week' => $day],
                $values
            );
        }
    }
}
