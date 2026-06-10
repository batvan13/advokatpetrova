<?php

namespace Tests\Unit;

use App\Support\ConsultationAvailabilityService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ConsultationAvailabilityFilterTest extends TestCase
{
    private ConsultationAvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConsultationAvailabilityService();
    }

    public function test_overlap_blocks_slot_starting_at_busy_period_start(): void
    {
        $day = Carbon::parse('2026-06-10 00:00:00', 'Europe/Sofia');

        $slotStarts = [
            $day->copy()->setTime(10, 0),
            $day->copy()->setTime(11, 0),
        ];

        $busyPeriods = [[
            'start' => $day->copy()->setTime(10, 0),
            'end'   => $day->copy()->setTime(11, 0),
        ]];

        $available = $this->service->filterAvailableSlotTimes(
            $slotStarts,
            [],
            $busyPeriods,
            30,
        );

        $this->assertSame(['11:00'], $available);
    }

    public function test_adjacent_slot_is_allowed_when_busy_ends_at_slot_start(): void
    {
        $day = Carbon::parse('2026-06-10 00:00:00', 'Europe/Sofia');

        $startsAt = $day->copy()->setTime(11, 0);
        $endsAt   = $day->copy()->setTime(11, 30);

        $busyPeriods = [[
            'start' => $day->copy()->setTime(10, 0),
            'end'   => $day->copy()->setTime(11, 0),
        ]];

        $this->assertFalse(
            $this->service->intervalOverlapsBusyPeriods($startsAt, $endsAt, $busyPeriods)
        );
    }

    public function test_sixty_minute_slot_is_blocked_when_it_overlaps_busy_period(): void
    {
        $day = Carbon::parse('2026-06-10 00:00:00', 'Europe/Sofia');

        $slotStarts = [
            $day->copy()->setTime(10, 0),
        ];

        $busyPeriods = [[
            'start' => $day->copy()->setTime(10, 30),
            'end'   => $day->copy()->setTime(11, 30),
        ]];

        $available = $this->service->filterAvailableSlotTimes(
            $slotStarts,
            [],
            $busyPeriods,
            60,
        );

        $this->assertSame([], $available);
    }

    public function test_db_blocked_grid_times_and_google_busy_periods_combine(): void
    {
        $day = Carbon::parse('2026-06-10 00:00:00', 'Europe/Sofia');

        $slotStarts = [
            $day->copy()->setTime(9, 0),
            $day->copy()->setTime(10, 0),
            $day->copy()->setTime(11, 0),
            $day->copy()->setTime(12, 0),
        ];

        $busyPeriods = [[
            'start' => $day->copy()->setTime(11, 0),
            'end'   => $day->copy()->setTime(12, 30),
        ]];

        $available = $this->service->filterAvailableSlotTimes(
            $slotStarts,
            ['10:00'],
            $busyPeriods,
            30,
        );

        $this->assertSame(['09:00'], $available);
    }
}
