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
     * Return theoretical slot start times for the given date.
     *
     * $durationMinutes controls the slot length and therefore the cutoff:
     *   - 30 (default): last valid start = end_time - 30 min
     *   - 60:           last valid start = end_time - 60 min
     *
     * Returns an array of Carbon instances (in Europe/Sofia timezone),
     * or an empty array when the day is closed or falls inside a closure.
     *
     * @return Carbon[]
     */
    public function slotsForDate(Carbon|string $date, int $durationMinutes = self::SLOT_MINUTES): array
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
            $workingHours->end_time,
            $durationMinutes
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
     * Generate slot start times from start_time to (end_time - $durationMinutes).
     *
     * The grid step is always 30 minutes (SLOT_MINUTES).
     * The cutoff depends on the requested duration so that no slot overflows
     * the working day:
     *   - 30 min: last start = end_time - 30 min
     *   - 60 min: last start = end_time - 60 min
     *
     * @return Carbon[]
     */
    private function generateSlots(Carbon $day, string $startTime, string $endTime, int $durationMinutes = self::SLOT_MINUTES): array
    {
        [$sh, $sm] = array_map('intval', explode(':', $startTime));
        [$eh, $em] = array_map('intval', explode(':', $endTime));

        $cursor  = $day->copy()->setTime($sh, $sm, 0);
        $cutoff  = $day->copy()->setTime($eh, $em, 0)->subMinutes($durationMinutes);

        $slots = [];

        while ($cursor->lte($cutoff)) {
            $slots[] = $cursor->copy();
            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    /**
     * Filter theoretical slot starts against DB blocked grid times and external busy periods.
     *
     * Uses half-open interval logic [start, end) for overlap checks.
     *
     * @param  Carbon[]  $slotStarts
     * @param  string[]  $blockedGridTimes
     * @param  array<int, array{start: CarbonInterface, end: CarbonInterface}>  $externalBusyPeriods
     * @return string[]  Available slot times as H:i
     */
    public function filterAvailableSlotTimes(
        array $slotStarts,
        array $blockedGridTimes,
        array $externalBusyPeriods,
        int $durationMinutes = self::SLOT_MINUTES,
    ): array {
        $blockedLookup = array_fill_keys($blockedGridTimes, true);
        $available     = [];

        foreach ($slotStarts as $slotStart) {
            $start = Carbon::parse($slotStart)->setTimezone(self::TIMEZONE);
            $time  = $start->format('H:i');

            if (isset($blockedLookup[$time])) {
                continue;
            }

            $slotEnd = $start->copy()->addMinutes($durationMinutes);

            if ($this->intervalOverlapsBusyPeriods($start, $slotEnd, $externalBusyPeriods)) {
                continue;
            }

            $available[] = $time;
        }

        return $available;
    }

    /**
     * Half-open overlap test: [startsAt, endsAt) against external busy periods.
     *
     * Adjacent intervals do not overlap (e.g. busy until 11:00, slot from 11:00 is free).
     *
     * @param  array<int, array{start: CarbonInterface, end: CarbonInterface}>  $externalBusyPeriods
     */
    public function intervalOverlapsBusyPeriods(
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        array $externalBusyPeriods,
    ): bool {
        $startsAt = Carbon::parse($startsAt)->setTimezone(self::TIMEZONE);
        $endsAt   = Carbon::parse($endsAt)->setTimezone(self::TIMEZONE);

        foreach ($externalBusyPeriods as $period) {
            $busyStart = Carbon::parse($period['start'])->setTimezone(self::TIMEZONE);
            $busyEnd   = Carbon::parse($period['end'])->setTimezone(self::TIMEZONE);

            if ($startsAt->lt($busyEnd) && $endsAt->gt($busyStart)) {
                return true;
            }
        }

        return false;
    }
}
