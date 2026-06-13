<?php

namespace Tests\Feature\Admin;

use App\Models\ConsultationWorkingHours;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConsultationWorkingHoursTest extends TestCase
{
    use DatabaseTransactions;

    private function workingHour(array $attributes = []): ConsultationWorkingHours
    {
        $day = $attributes['day_of_week'] ?? 1;

        return ConsultationWorkingHours::updateOrCreate(
            ['day_of_week' => $day],
            array_merge([
                'is_open'    => true,
                'start_time' => '09:00:00',
                'end_time'   => '17:00:00',
            ], $attributes)
        );
    }

    public function test_guest_is_redirected_from_working_hours_admin_page(): void
    {
        $workingHour = $this->workingHour();

        $this->get(route('admin.working-hours.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.working-hours.edit', $workingHour))
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_open_edit_page(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $this->actingAs($admin)
            ->get(route('admin.working-hours.edit', $workingHour))
            ->assertOk()
            ->assertSee('Работно време')
            ->assertSee($workingHour->dayLabel());
    }

    public function test_edit_page_uses_twenty_four_hour_selects(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $response = $this->actingAs($admin)
            ->get(route('admin.working-hours.edit', $workingHour));

        $response->assertOk();
        $response->assertSee('id="start_time"', false);
        $response->assertSee('id="end_time"', false);
        $this->assertMatchesRegularExpression(
            '/<option value="17:00"(?: selected)?>17:00<\/option>/',
            $response->getContent()
        );
        $response->assertDontSee('type="time"', false);
    }

    public function test_database_value_with_seconds_selects_matching_option(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour([
            'start_time' => '17:00:00',
            'end_time'   => '20:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.working-hours.edit', $workingHour));

        $response->assertOk();
        $this->assertStringContainsString(
            '<option value="17:00" selected>17:00</option>',
            $response->getContent()
        );
        $this->assertStringContainsString(
            '<option value="20:00" selected>20:00</option>',
            $response->getContent()
        );
    }

    public function test_old_validation_value_is_preserved_on_edit_form(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $response = $this->actingAs($admin)
            ->withSession([
                '_old_input' => [
                    'is_open'    => '1',
                    'start_time' => '17:00',
                    'end_time'   => '09:00',
                ],
            ])
            ->get(route('admin.working-hours.edit', $workingHour));

        $response->assertOk();
        $this->assertStringContainsString(
            '<option value="17:00" selected>17:00</option>',
            $response->getContent()
        );
        $this->assertStringContainsString(
            '<option value="09:00" selected>09:00</option>',
            $response->getContent()
        );
    }

    public function test_open_day_with_valid_times_saves_successfully(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $response = $this->actingAs($admin)->put(
            route('admin.working-hours.update', $workingHour),
            [
                'is_open'    => '1',
                'start_time' => '17:00',
                'end_time'   => '20:00',
            ]
        );

        $response->assertRedirect(route('admin.working-hours.index'));
        $response->assertSessionHas('success');

        $workingHour->refresh();
        $this->assertTrue($workingHour->is_open);
        $this->assertSame('17:00:00', $workingHour->start_time);
        $this->assertSame('20:00:00', $workingHour->end_time);
    }

    public function test_end_time_before_start_time_fails_validation(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $response = $this->actingAs($admin)
            ->from(route('admin.working-hours.edit', $workingHour))
            ->put(route('admin.working-hours.update', $workingHour), [
                'is_open'    => '1',
                'start_time' => '17:00',
                'end_time'   => '09:00',
            ]);

        $response->assertRedirect(route('admin.working-hours.edit', $workingHour));
        $response->assertSessionHasErrors('end_time');
    }

    public function test_equal_start_and_end_times_fail_validation(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $response = $this->actingAs($admin)
            ->from(route('admin.working-hours.edit', $workingHour))
            ->put(route('admin.working-hours.update', $workingHour), [
                'is_open'    => '1',
                'start_time' => '09:00',
                'end_time'   => '09:00',
            ]);

        $response->assertRedirect(route('admin.working-hours.edit', $workingHour));
        $response->assertSessionHasErrors('end_time');
    }

    public function test_invalid_tampered_time_value_fails_validation(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour();

        $response = $this->actingAs($admin)
            ->from(route('admin.working-hours.edit', $workingHour))
            ->put(route('admin.working-hours.update', $workingHour), [
                'is_open'    => '1',
                'start_time' => '25:00',
                'end_time'   => '20:00',
            ]);

        $response->assertRedirect(route('admin.working-hours.edit', $workingHour));
        $response->assertSessionHasErrors('start_time');
    }

    public function test_closing_a_day_saves_both_times_as_null(): void
    {
        $admin = User::factory()->create();
        $workingHour = $this->workingHour([
            'is_open'    => true,
            'start_time' => '09:00:00',
            'end_time'   => '17:00:00',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.working-hours.update', $workingHour),
            [
                'is_open'    => '0',
                'start_time' => '17:00',
                'end_time'   => '20:00',
            ]
        );

        $response->assertRedirect(route('admin.working-hours.index'));
        $response->assertSessionHas('success');

        $workingHour->refresh();
        $this->assertFalse($workingHour->is_open);
        $this->assertNull($workingHour->start_time);
        $this->assertNull($workingHour->end_time);
    }
}
