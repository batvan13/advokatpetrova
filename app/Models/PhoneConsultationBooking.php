<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PhoneConsultationBooking extends Model
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
        'archived_at',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'price_eur'      => 'decimal:2',
        'price_bgn'      => 'decimal:2',
        'show_bgn_price' => 'boolean',
        'archived_at'    => 'datetime',
    ];

    public const STATUS_BOOKED    = 'booked';
    public const STATUS_COMPLETED = 'completed';

    public const GOOGLE_SYNC_SYNCED = 'synced';
    public const GOOGLE_SYNC_FAILED = 'failed';

    /**
     * Statuses that occupy a slot and must be considered when checking availability.
     * Any status NOT in this list is treated as non-blocking (e.g. a future
     * "cancelled" or "no_show" status should be excluded from here).
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

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }
}
