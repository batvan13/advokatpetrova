<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationService extends Model
{
    protected $fillable = [
        'type',
        'price_eur',
        'price_bgn',
        'price_eur_60',
        'price_bgn_60',
        'show_bgn_price',
    ];

    protected $casts = [
        'price_eur'      => 'decimal:2',
        'price_bgn'      => 'decimal:2',
        'price_eur_60'   => 'decimal:2',
        'price_bgn_60'   => 'decimal:2',
        'show_bgn_price' => 'boolean',
    ];

    public const TYPES = ['phone', 'chat', 'written', 'video'];

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
