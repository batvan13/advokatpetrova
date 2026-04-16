<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'provider',
        'payment_method',
        'invoice_number',
        'amount',
        'currency',
        'status',
        'provider_status',
        'description',
        'expires_at',
        'paid_at',
        'failed_at',
        'expired_at',
        'notification_received_at',
        'notification_payload',
        'stan',
        'bcode',
        'is_finalized',
        'finalized_at',
    ];

    protected $casts = [
        'amount'                   => 'decimal:2',
        'expires_at'               => 'datetime',
        'paid_at'                  => 'datetime',
        'failed_at'                => 'datetime',
        'expired_at'               => 'datetime',
        'notification_received_at' => 'datetime',
        'finalized_at'             => 'datetime',
        'is_finalized'             => 'boolean',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID    = 'paid';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public const PROVIDER_STATUS_PAID    = 'PAID';
    public const PROVIDER_STATUS_DENIED  = 'DENIED';
    public const PROVIDER_STATUS_EXPIRED = 'EXPIRED';

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Очаква плащане',
            self::STATUS_PAID    => 'Платено',
            self::STATUS_FAILED  => 'Неуспешно',
            self::STATUS_EXPIRED => 'Изтекло',
            default              => $this->status,
        };
    }
}
