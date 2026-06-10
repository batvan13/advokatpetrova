<?php

namespace Tests\Feature;

use App\Exceptions\GoogleCalendarUnavailableException;
use App\Models\ConsultationWorkingHours;
use App\Support\GoogleCalendarBusyService;
use Carbon\Carbon;
use Tests\TestCase;

class ChatConsultationGoogleBusyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConsultationWorkingHours::firstOrCreate(
            ['day_of_week' => 4],
            ['is_open' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
        );
    }

    public function test_chat_slots_returns_fail_closed_json_when_google_calendar_unavailable(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'Europe/Sofia'));

        $this->mock(GoogleCalendarBusyService::class, function ($mock) {
            $mock->shouldReceive('busyPeriodsForDate')
                ->once()
                ->andThrow(new GoogleCalendarUnavailableException('API unavailable'));
        });

        $response = $this->getJson('/consultation/chat/slots?date=2026-06-11');

        $response->assertOk()
            ->assertJson([
                'slots'                => [],
                'calendar_unavailable' => true,
            ]);

        Carbon::setTestNow();
    }

    public function test_chat_slots_excludes_google_busy_interval(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'Europe/Sofia'));

        $day = Carbon::parse('2026-06-11', 'Europe/Sofia');

        $this->mock(GoogleCalendarBusyService::class, function ($mock) use ($day) {
            $mock->shouldReceive('busyPeriodsForDate')
                ->once()
                ->andReturn([[
                    'start' => $day->copy()->setTime(10, 0),
                    'end'   => $day->copy()->setTime(11, 0),
                ]]);
        });

        $response = $this->getJson('/consultation/chat/slots?date=2026-06-11');

        $response->assertOk();

        $slots = $response->json('slots');
        $this->assertIsArray($slots);
        $this->assertNotContains('10:00', $slots);
        $this->assertContains('11:00', $slots);

        Carbon::setTestNow();
    }
}
