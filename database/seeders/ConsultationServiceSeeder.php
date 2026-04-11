<?php

namespace Database\Seeders;

use App\Models\ConsultationService;
use Illuminate\Database\Seeder;

class ConsultationServiceSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'phone' => [
                'price_eur'    => 30.00,
                'price_bgn'    => 58.00,
                'price_eur_60' => null,
                'price_bgn_60' => null,
            ],
            'chat' => [
                'price_eur'    => 30.00,
                'price_bgn'    => 58.00,
                'price_eur_60' => null,
                'price_bgn_60' => null,
            ],
            'written' => [
                'price_eur'    => 40.00,
                'price_bgn'    => 78.00,
                'price_eur_60' => null,
                'price_bgn_60' => null,
            ],
            'video' => [
                'price_eur'    => 30.00,
                'price_bgn'    => 58.00,
                'price_eur_60' => 55.00,
                'price_bgn_60' => 107.00,
            ],
        ];

        foreach ($defaults as $type => $prices) {
            ConsultationService::firstOrCreate(
                ['type' => $type],
                array_merge($prices, ['show_bgn_price' => true])
            );
        }
    }
}
