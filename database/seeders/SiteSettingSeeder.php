<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name'     => 'Моята Фирма',
            'site_tagline'  => 'Качество и надеждност от години.',
            'contact_email' => 'office@moqtafirma.bg',
            'contact_phone' => '+359 88 123 4567',
            'address'         => "ул. Примерна 1\nгр. София 1000",
            'google_maps_url' => '',
            'facebook_url'    => '',
            'instagram_url'   => '',
            'linkedin_url'  => '',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key'   => $key],
                ['value' => $value]
            );
        }
    }
}
