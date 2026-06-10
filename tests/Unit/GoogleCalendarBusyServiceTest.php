<?php

namespace Tests\Unit;

use App\Exceptions\GoogleCalendarUnavailableException;
use App\Support\GoogleCalendarBusyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use ReflectionMethod;
use Tests\TestCase;

class GoogleCalendarBusyServiceTest extends TestCase
{
    public function test_disabled_busy_check_returns_empty_periods(): void
    {
        Config::set('services.google_calendar.busy_check_enabled', false);
        Config::set('services.google_calendar.calendar_id', 'primary');
        Config::set('services.google_calendar.credentials_path', '/missing/credentials.json');

        $service = new GoogleCalendarBusyService();

        $this->assertFalse($service->isEnabled());

        $periods = $service->busyPeriodsForDate(
            Carbon::parse('2026-06-10', 'Europe/Sofia'),
            true,
        );

        $this->assertSame([], $periods);
        $this->assertFalse(
            $service->intervalIsBusy(
                Carbon::parse('2026-06-10 10:00:00', 'Europe/Sofia'),
                Carbon::parse('2026-06-10 10:30:00', 'Europe/Sofia'),
                false,
            )
        );
    }

    public function test_falls_back_to_calendar_id_when_busy_calendar_ids_empty(): void
    {
        Config::set('services.google_calendar.calendar_id', 'fallback@group.calendar.google.com');
        Config::set('services.google_calendar.busy_calendar_ids', '');

        $ids = $this->resolveBusyCalendarIds();

        $this->assertSame(['fallback@group.calendar.google.com'], $ids);
    }

    public function test_parses_comma_separated_busy_calendar_ids(): void
    {
        Config::set('services.google_calendar.calendar_id', 'fallback@group.calendar.google.com');
        Config::set('services.google_calendar.busy_calendar_ids', 'cal-a@group.calendar.google.com,batvan4@gmail.com');

        $ids = $this->resolveBusyCalendarIds();

        $this->assertSame([
            'cal-a@group.calendar.google.com',
            'batvan4@gmail.com',
        ], $ids);
    }

    public function test_trims_whitespace_from_busy_calendar_ids(): void
    {
        Config::set('services.google_calendar.calendar_id', 'fallback@group.calendar.google.com');
        Config::set(
            'services.google_calendar.busy_calendar_ids',
            ' cal-a@group.calendar.google.com , batvan4@gmail.com ',
        );

        $ids = $this->resolveBusyCalendarIds();

        $this->assertSame([
            'cal-a@group.calendar.google.com',
            'batvan4@gmail.com',
        ], $ids);
    }

    public function test_deduplicates_busy_calendar_ids(): void
    {
        Config::set('services.google_calendar.calendar_id', 'fallback@group.calendar.google.com');
        Config::set(
            'services.google_calendar.busy_calendar_ids',
            'cal-a@group.calendar.google.com,cal-a@group.calendar.google.com,batvan4@gmail.com',
        );

        $ids = $this->resolveBusyCalendarIds();

        $this->assertSame([
            'cal-a@group.calendar.google.com',
            'batvan4@gmail.com',
        ], $ids);
    }

    public function test_merges_busy_periods_from_multiple_calendars(): void
    {
        $service = new GoogleCalendarBusyService();
        $day     = Carbon::parse('2026-06-11 00:00:00', 'Europe/Sofia');
        $dayStart = $day->copy()->startOfDay();
        $dayEnd   = $day->copy()->endOfDay();

        $calendars = [
            'cal-a@group.calendar.google.com' => $this->makeFreeBusyCalendar([
                $this->makeTimePeriod('2026-06-11T09:00:00+03:00', '2026-06-11T09:30:00+03:00'),
            ]),
            'batvan4@gmail.com' => $this->makeFreeBusyCalendar([
                $this->makeTimePeriod('2026-06-11T10:00:00+03:00', '2026-06-11T11:00:00+03:00'),
            ]),
        ];

        $periods = $this->extractBusyPeriodsFromCalendars(
            $service,
            $calendars,
            ['cal-a@group.calendar.google.com', 'batvan4@gmail.com'],
            $dayStart,
            $dayEnd,
        );

        $this->assertCount(2, $periods);
        $this->assertSame('2026-06-11 09:00:00', $periods[0]['start']->toDateTimeString());
        $this->assertSame('2026-06-11 09:30:00', $periods[0]['end']->toDateTimeString());
        $this->assertSame('2026-06-11 10:00:00', $periods[1]['start']->toDateTimeString());
        $this->assertSame('2026-06-11 11:00:00', $periods[1]['end']->toDateTimeString());
    }

    public function test_calendar_level_error_throws_google_calendar_unavailable_exception(): void
    {
        $service = new GoogleCalendarBusyService();
        $day     = Carbon::parse('2026-06-11 00:00:00', 'Europe/Sofia');

        $calendars = [
            'cal-a@group.calendar.google.com' => $this->makeFreeBusyCalendar([], [
                ['reason' => 'notFound', 'domain' => 'global'],
            ]),
        ];

        $this->expectException(GoogleCalendarUnavailableException::class);
        $this->expectExceptionMessage('errors for calendar cal-a@group.calendar.google.com');

        $this->extractBusyPeriodsFromCalendars(
            $service,
            $calendars,
            ['cal-a@group.calendar.google.com'],
            $day->copy()->startOfDay(),
            $day->copy()->endOfDay(),
        );
    }

    public function test_missing_calendar_in_response_throws_google_calendar_unavailable_exception(): void
    {
        $service = new GoogleCalendarBusyService();
        $day     = Carbon::parse('2026-06-11 00:00:00', 'Europe/Sofia');

        $this->expectException(GoogleCalendarUnavailableException::class);
        $this->expectExceptionMessage('did not include calendar: missing@group.calendar.google.com');

        $this->extractBusyPeriodsFromCalendars(
            $service,
            [],
            ['missing@group.calendar.google.com'],
            $day->copy()->startOfDay(),
            $day->copy()->endOfDay(),
        );
    }

    /**
     * @return string[]
     */
    private function resolveBusyCalendarIds(): array
    {
        $service = new GoogleCalendarBusyService();
        $method  = new ReflectionMethod(GoogleCalendarBusyService::class, 'resolveBusyCalendarIds');
        $method->setAccessible(true);

        return $method->invoke($service);
    }

    /**
     * @param  array<string, object>  $calendars
     * @param  string[]  $expectedIds
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    private function extractBusyPeriodsFromCalendars(
        GoogleCalendarBusyService $service,
        array $calendars,
        array $expectedIds,
        Carbon $dayStart,
        Carbon $dayEnd,
    ): array {
        $method = new ReflectionMethod(GoogleCalendarBusyService::class, 'extractBusyPeriodsFromCalendars');
        $method->setAccessible(true);

        return $method->invoke($service, $calendars, $expectedIds, $dayStart, $dayEnd);
    }

    /**
     * @param  array<int, object>  $busy
     * @param  array<int, array<string, string>>  $errors
     */
    private function makeFreeBusyCalendar(array $busy, array $errors = []): object
    {
        return new class($busy, $errors) {
            public function __construct(
                private readonly array $busy,
                private readonly array $errors,
            ) {}

            public function getBusy(): array
            {
                return $this->busy;
            }

            public function getErrors(): array
            {
                return $this->errors;
            }
        };
    }

    private function makeTimePeriod(string $start, string $end): object
    {
        return new class($start, $end) {
            public function __construct(
                private readonly string $start,
                private readonly string $end,
            ) {}

            public function getStart(): string
            {
                return $this->start;
            }

            public function getEnd(): string
            {
                return $this->end;
            }
        };
    }
}
