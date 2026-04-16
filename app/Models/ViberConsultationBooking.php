<?php

namespace App\Models;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class ViberConsultationBooking extends Model
{
    protected $fillable = [
        'public_token',
        'first_name',
        'last_name',
        'email',
        'phone',
        'description',
        'duration_minutes',
        'starts_at',
        'ends_at',
        'payment_method',
        'status',
        'price_eur',
        'price_bgn',
        'show_bgn_price',
        'google_event_id',
        'google_sync_status',
        'archived_at',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'duration_minutes' => 'integer',
        'price_eur'        => 'decimal:2',
        'price_bgn'        => 'decimal:2',
        'show_bgn_price'   => 'boolean',
        'archived_at'      => 'datetime',
    ];

    public const DURATION_30 = 30;
    public const DURATION_60 = 60;
    public const ALLOWED_DURATIONS = [self::DURATION_30, self::DURATION_60];

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_CONFIRMED       = 'confirmed';
    public const STATUS_COMPLETED       = 'completed';
    public const STATUS_EXPIRED         = 'expired';

    public const GOOGLE_SYNC_SYNCED = 'synced';
    public const GOOGLE_SYNC_FAILED = 'failed';

    /**
     * Statuses that occupy a slot and block availability.
     * expired is intentionally excluded — expired bookings free the slot.
     */
    public const BLOCKING_STATUSES = [
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_CONFIRMED,
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

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function durationLabel(): string
    {
        return $this->duration_minutes . ' минути';
    }
}
