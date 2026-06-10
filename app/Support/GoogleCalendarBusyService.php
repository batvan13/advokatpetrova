<?php

namespace App\Support;

use App\Exceptions\GoogleCalendarUnavailableException;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\FreeBusyRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Reads busy periods from Google Calendar via the FreeBusy API.
 *
 * Used to block online consultation slots when the lawyer's calendar
 * has manual events. Write/sync remains in GoogleCalendarService.
 */
class GoogleCalendarBusyService
{
    private readonly string $calendarId;
    private readonly string $credentialsPath;
    private readonly string $timezone;
    private readonly int $cacheSeconds;

    public function __construct()
    {
        $this->calendarId      = (string) config('services.google_calendar.calendar_id', '');
        $this->credentialsPath = (string) config('services.google_calendar.credentials_path', '');
        $this->timezone        = (string) config('services.google_calendar.timezone', 'Europe/Sofia');
        $this->cacheSeconds    = (int) config('services.google_calendar.busy_cache_seconds', 120);
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.google_calendar.busy_check_enabled', false);
    }

    /**
     * Busy periods for a calendar day, clipped to that day in the configured timezone.
     *
     * @return array<int, array{start: Carbon, end: Carbon}>
     *
     * @throws GoogleCalendarUnavailableException
     */
    public function busyPeriodsForDate(CarbonInterface $date, bool $useCache = true): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $day      = Carbon::parse($date)->setTimezone($this->timezone)->startOfDay();
        $cacheKey = $this->cacheKey($day);

        if ($useCache) {
            $cached = Cache::get($cacheKey);

            if (is_array($cached)) {
                return $this->hydrateBusyPeriods($cached);
            }
        }

        $periods = $this->fetchBusyPeriodsForDay($day);

        if ($useCache) {
            Cache::put($cacheKey, $this->serializeBusyPeriods($periods), $this->cacheSeconds);
        }

        return $periods;
    }

    /**
     * @throws GoogleCalendarUnavailableException
     */
    public function intervalIsBusy(
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        bool $useCache = false,
    ): bool {
        if (! $this->isEnabled()) {
            return false;
        }

        $day = Carbon::parse($startsAt)->setTimezone($this->timezone)->startOfDay();

        return $this->intervalOverlapsAny(
            Carbon::parse($startsAt)->setTimezone($this->timezone),
            Carbon::parse($endsAt)->setTimezone($this->timezone),
            $this->busyPeriodsForDate($day, $useCache),
        );
    }

    /**
     * @return string[]
     */
    private function resolveBusyCalendarIds(): array
    {
        $raw = (string) config('services.google_calendar.busy_calendar_ids', '');

        $ids = array_values(array_unique(array_filter(
            array_map('trim', explode(',', $raw)),
            fn (string $id) => $id !== '',
        )));

        if ($ids !== []) {
            return $ids;
        }

        $fallback = trim($this->calendarId);

        return $fallback !== '' ? [$fallback] : [];
    }

    /**
     * @return array<int, array{start: Carbon, end: Carbon}>
     *
     * @throws GoogleCalendarUnavailableException
     */
    private function fetchBusyPeriodsForDay(Carbon $day): array
    {
        $this->guardConfiguration();

        $busyCalendarIds = $this->resolveBusyCalendarIds();

        try {
            $client  = $this->buildClient();
            $service = new GoogleCalendar($client);

            $dayStart = $day->copy()->startOfDay();
            $dayEnd   = $day->copy()->endOfDay();

            $request = new FreeBusyRequest([
                'timeMin'  => $dayStart->toRfc3339String(),
                'timeMax'  => $dayEnd->toRfc3339String(),
                'timeZone' => $this->timezone,
                'items'    => array_map(
                    fn (string $id) => ['id' => $id],
                    $busyCalendarIds,
                ),
            ]);

            $response  = $service->freebusy->query($request);
            $calendars = $response->getCalendars();

            if (! is_array($calendars)) {
                throw new GoogleCalendarUnavailableException(
                    'Google Calendar FreeBusy response did not include calendars.'
                );
            }

            return $this->extractBusyPeriodsFromCalendars(
                $calendars,
                $busyCalendarIds,
                $dayStart,
                $dayEnd,
            );
        } catch (GoogleCalendarUnavailableException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Google Calendar FreeBusy request failed', [
                'date'               => $day->toDateString(),
                'calendar_id'        => $this->calendarId,
                'busy_calendar_ids'  => $busyCalendarIds,
                'error'              => $e->getMessage(),
                'class'              => get_class($e),
            ]);

            throw new GoogleCalendarUnavailableException(
                'Google Calendar FreeBusy request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @param  array<string, object>  $calendars
     * @param  string[]  $expectedIds
     * @return array<int, array{start: Carbon, end: Carbon}>
     *
     * @throws GoogleCalendarUnavailableException
     */
    private function extractBusyPeriodsFromCalendars(
        array $calendars,
        array $expectedIds,
        Carbon $dayStart,
        Carbon $dayEnd,
    ): array {
        $periods = [];

        foreach ($expectedIds as $id) {
            if (! isset($calendars[$id])) {
                throw new GoogleCalendarUnavailableException(
                    'Google Calendar FreeBusy response did not include calendar: ' . $id
                );
            }

            $calendar = $calendars[$id];
            $errors   = method_exists($calendar, 'getErrors') ? ($calendar->getErrors() ?? []) : [];

            if ($errors !== []) {
                throw new GoogleCalendarUnavailableException(
                    'Google Calendar FreeBusy returned errors for calendar ' . $id . ': '
                    . json_encode($errors, JSON_UNESCAPED_UNICODE)
                );
            }

            $busyEntries = method_exists($calendar, 'getBusy') ? ($calendar->getBusy() ?? []) : [];

            $periods = array_merge(
                $periods,
                $this->normalizeBusyEntries($busyEntries, $dayStart, $dayEnd),
            );
        }

        return $periods;
    }

    /**
     * @param  array<int, object>  $busyEntries
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    private function normalizeBusyEntries(array $busyEntries, Carbon $dayStart, Carbon $dayEnd): array
    {
        $periods = [];

        foreach ($busyEntries as $entry) {
            $startRaw = method_exists($entry, 'getStart') ? $entry->getStart() : null;
            $endRaw   = method_exists($entry, 'getEnd') ? $entry->getEnd() : null;

            if (! $startRaw || ! $endRaw) {
                continue;
            }

            $start = Carbon::parse($startRaw)->setTimezone($this->timezone);
            $end   = Carbon::parse($endRaw)->setTimezone($this->timezone);

            if ($start->gte($dayEnd) || $end->lte($dayStart)) {
                continue;
            }

            $clippedStart = $start->lt($dayStart) ? $dayStart->copy() : $start->copy();
            $clippedEnd   = $end->gt($dayEnd) ? $dayEnd->copy() : $end->copy();

            if ($clippedStart->lt($clippedEnd)) {
                $periods[] = [
                    'start' => $clippedStart,
                    'end'   => $clippedEnd,
                ];
            }
        }

        return $periods;
    }

    /**
     * Half-open overlap: [start, end).
     *
     * @param  array<int, array{start: Carbon, end: Carbon}>  $busyPeriods
     */
    private function intervalOverlapsAny(Carbon $startsAt, Carbon $endsAt, array $busyPeriods): bool
    {
        foreach ($busyPeriods as $period) {
            if ($startsAt->lt($period['end']) && $endsAt->gt($period['start'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array{start: Carbon, end: Carbon}>  $periods
     * @return array<int, array{start: string, end: string}>
     */
    private function serializeBusyPeriods(array $periods): array
    {
        return array_map(
            fn (array $period) => [
                'start' => $period['start']->toIso8601String(),
                'end'   => $period['end']->toIso8601String(),
            ],
            $periods,
        );
    }

    /**
     * @param  array<int, array{start: string, end: string}>  $serialized
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    private function hydrateBusyPeriods(array $serialized): array
    {
        return array_map(
            fn (array $period) => [
                'start' => Carbon::parse($period['start'])->setTimezone($this->timezone),
                'end'   => Carbon::parse($period['end'])->setTimezone($this->timezone),
            ],
            $serialized,
        );
    }

    private function cacheKey(Carbon $day): string
    {
        $ids = $this->resolveBusyCalendarIds();
        sort($ids);

        return sprintf(
            'google_calendar:freebusy:%s:%s',
            sha1(implode('|', $ids)),
            $day->format('Y-m-d'),
        );
    }

    /**
     * @throws GoogleCalendarUnavailableException
     */
    private function guardConfiguration(): void
    {
        if ($this->resolveBusyCalendarIds() === []) {
            throw new GoogleCalendarUnavailableException(
                'GOOGLE_CALENDAR_ID or GOOGLE_CALENDAR_BUSY_CALENDAR_IDS must be configured.'
            );
        }

        if ($this->credentialsPath === '') {
            throw new GoogleCalendarUnavailableException('GOOGLE_SERVICE_ACCOUNT_JSON is not configured.');
        }

        if (! file_exists($this->credentialsPath)) {
            throw new GoogleCalendarUnavailableException(
                'Google service account credentials file not found: ' . $this->credentialsPath
            );
        }
    }

    private function buildClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope(GoogleCalendar::CALENDAR_READONLY);

        return $client;
    }
}
