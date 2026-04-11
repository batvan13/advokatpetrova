<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WrittenConsultationRequest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'title',
        'description',
        'payment_method',
        'status',
        'price_eur',
        'price_bgn',
        'show_bgn_price',
        'submitted_at',
        'public_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->public_token)) {
                $model->public_token = Str::random(48);
            }
        });
    }

    protected $casts = [
        'price_eur'      => 'decimal:2',
        'price_bgn'      => 'decimal:2',
        'show_bgn_price' => 'boolean',
        'submitted_at'   => 'datetime',
    ];

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ANSWERED  = 'answered';

    public const PAYMENT_METHODS = [
        'card'    => 'Плащане с дебитна/кредитна карта',
        'easypay' => 'Плащане с Easy Pay',
        'epay'    => 'Плащане с ePay',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(WrittenConsultationAttachment::class);
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }
}
