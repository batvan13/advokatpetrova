<?php

namespace App\Support;

use App\Models\ConsultationClosure;
use App\Models\ConsultationWorkingHours;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class ConsultationAvailabilityService
{
    private const SLOT_MINUTES = 30;
    private const TIMEZONE     = 'Europe/Sofia';

    /**
     * Return theoretical 30-minute slot start times for the given date.
     *
     * Returns an array of Carbon instances (in Europe/Sofia timezone),
     * or an empty array when the day is closed or falls inside a closure.
     *
     * @return Carbon[]
     */
    public function slotsForDate(Carbon|string $date): array
    {
        $day = Carbon::parse($date, self::TIMEZONE)->startOfDay();

        // ISO day of week: 1=Monday … 7=Sunday
        $iso = $day->dayOfWeekIso;

        $workingHours = ConsultationWorkingHours::where('day_of_week', $iso)->first();

        if (! $workingHours || ! $workingHours->is_open) {
            return [];
        }

        if ($this->isInClosure($day)) {
            return [];
        }

        if (! $workingHours->start_time || ! $workingHours->end_time) {
            return [];
        }

        return $this->generateSlots(
            $day,
            $workingHours->start_time,
            $workingHours->end_time
        );
    }

    /**
     * Check whether the given day falls entirely or partially inside any closure.
     * A day is considered blocked if any moment of it overlaps a closure period.
     */
    private function isInClosure(Carbon $day): bool
    {
        $dayStart = $day->copy()->startOfDay();
        $dayEnd   = $day->copy()->endOfDay();

        return ConsultationClosure::query()
            ->where('starts_at', '<=', $dayEnd)
            ->where('ends_at',   '>=', $dayStart)
            ->exists();
    }

    /**
     * Generate slot start times from start_time to (end_time - SLOT_MINUTES).
     *
     * @return Carbon[]
     */
    private function generateSlots(Carbon $day, string $startTime, string $endTime): array
    {
        [$sh, $sm] = array_map('intval', explode(':', $startTime));
        [$eh, $em] = array_map('intval', explode(':', $endTime));

        $cursor  = $day->copy()->setTime($sh, $sm, 0);
        $cutoff  = $day->copy()->setTime($eh, $em, 0)->subMinutes(self::SLOT_MINUTES);

        $slots = [];

        while ($cursor->lte($cutoff)) {
            $slots[] = $cursor->copy();
            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }
}
