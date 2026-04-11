<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationWorkingHours extends Model
{
    protected $fillable = [
        'day_of_week',
        'is_open',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_open'     => 'boolean',
    ];

    // ISO-8601: 1=Monday … 7=Sunday
    public const DAY_LABELS = [
        1 => 'Понеделник',
        2 => 'Вторник',
        3 => 'Сряда',
        4 => 'Четвъртък',
        5 => 'Петък',
        6 => 'Събота',
        7 => 'Неделя',
    ];

    public function dayLabel(): string
    {
        return self::DAY_LABELS[$this->day_of_week] ?? "Ден {$this->day_of_week}";
    }
}
