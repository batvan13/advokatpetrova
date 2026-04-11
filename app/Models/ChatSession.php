<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSession extends Model
{
    protected $fillable = [
        'booking_id',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ChatConsultationBooking::class, 'booking_id');
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
