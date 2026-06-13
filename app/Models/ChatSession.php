<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    protected $fillable = [
        'booking_id',
        'client_access_token',
        'phase',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /**
     * Session phases — source of truth for session lifecycle.
     * These are intentionally separate from booking status.
     *
     * Transitions (Phase 3B+):
     *   waiting → active   (lawyer joins)
     *   active  → ending   (5 min before scheduled end)
     *   ending  → completed (scheduled end reached or lawyer ends manually)
     */
    public const PHASE_WAITING   = 'waiting';
    public const PHASE_ACTIVE    = 'active';
    public const PHASE_ENDING    = 'ending';
    public const PHASE_COMPLETED = 'completed';

    /** Default phase assigned at booking creation. */
    public const DEFAULT_PHASE = self::PHASE_WAITING;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->client_access_token)) {
                $model->client_access_token = static::generateUniqueClientAccessToken();
            }
        });
    }

    public static function generateUniqueClientAccessToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('client_access_token', $token)->exists());

        return $token;
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ChatConsultationBooking::class, 'booking_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_session_id');
    }

    public function isWaiting(): bool
    {
        return $this->phase === self::PHASE_WAITING;
    }

    public function isActive(): bool
    {
        return $this->phase === self::PHASE_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->phase === self::PHASE_COMPLETED;
    }

    public function canBeStarted(): bool
    {
        return $this->booking?->status === ChatConsultationBooking::STATUS_CONFIRMED
            && $this->isWaiting();
    }

    public function canBeCompleted(): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        return $this->booking?->status === ChatConsultationBooking::STATUS_CONFIRMED;
    }

    public function phaseLabel(): string
    {
        return match ($this->phase) {
            self::PHASE_WAITING   => 'Изчакване',
            self::PHASE_ACTIVE    => 'Активна',
            self::PHASE_ENDING    => 'Приключва',
            self::PHASE_COMPLETED => 'Приключена',
            default               => $this->phase,
        };
    }
}
