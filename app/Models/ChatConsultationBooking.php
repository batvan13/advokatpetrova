<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ChatConsultationBooking extends Model
{
    protected $fillable = [
        'public_token',
        'first_name',
        'last_name',
        'email',
        'phone',
        'description',
        'starts_at',
        'ends_at',
        'payment_method',
        'status',
        'price_eur',
        'price_bgn',
        'show_bgn_price',
        'google_event_id',
        'google_sync_status',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'price_eur'      => 'decimal:2',
        'price_bgn'      => 'decimal:2',
        'show_bgn_price' => 'boolean',
    ];

    /** Chat is always 30 minutes. */
    public const DURATION_MINUTES = 30;

    public const STATUS_BOOKED    = 'booked';
    public const STATUS_COMPLETED = 'completed';

    public const GOOGLE_SYNC_SYNCED = 'synced';
    public const GOOGLE_SYNC_FAILED = 'failed';

    /**
     * Statuses that occupy a slot and must block availability.
     * Mirrors the same pattern as Phone/Viber booking models.
     */
    public const BLOCKING_STATUSES = [
        self::STATUS_BOOKED,
        self::STATUS_COMPLETED,
    ];

    public const PAYMENT_METHODS = [
        'card'    => 'Плащане с дебитна/кредитна карта',
        'easypay' => 'Плащане с Easy Pay',
        'epay'    => 'Плащане с ePay',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->public_token)) {
                $model->public_token = Str::random(48);
            }
        });
    }

    public function session(): HasOne
    {
        return $this->hasOne(ChatSession::class, 'booking_id');
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
