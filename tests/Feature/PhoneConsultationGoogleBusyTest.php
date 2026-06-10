<?php

namespace Tests\Feature;

use App\Exceptions\GoogleCalendarUnavailableException;
use App\Support\GoogleCalendarBusyService;
use Carbon\Carbon;
use Tests\TestCase;

class PhoneConsultationGoogleBusyTest extends TestCase
{
    public function test_phone_slots_returns_fail_closed_json_when_google_calendar_unavailable(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'Europe/Sofia'));

        $this->mock(GoogleCalendarBusyService::class, function ($mock) {
            $mock->shouldReceive('busyPeriodsForDate')
                ->once()
                ->andThrow(new GoogleCalendarUnavailableException('API unavailable'));
        });

        $response = $this->getJson('/consultation/phone/slots?date=2026-06-11');

        $response->assertOk()
            ->assertJson([
                'slots'                => [],
                'calendar_unavailable'   => true,
            ]);

        Carbon::setTestNow();
    }
}
