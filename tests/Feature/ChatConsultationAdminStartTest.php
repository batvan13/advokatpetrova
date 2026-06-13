<?php

namespace Tests\Feature;

use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatConsultationAdminStartTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_unauthenticated_user_cannot_start_session(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->patch(route('admin.chat-bookings.start', $booking))
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_start_waiting_session(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:05:00'));

        [$booking, $session] = $this->createPaidBookingWithSession();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('success', 'Чат консултацията е стартирана успешно.');

        $session->refresh();
        $this->assertSame(ChatSession::PHASE_ACTIVE, $session->phase);
        $this->assertNotNull($session->started_at);
    }

    public function test_start_keeps_booking_confirmed_and_ends_at_unchanged(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:05:00'));

        [$booking] = $this->createPaidBookingWithSession();
        $originalEndsAt = $booking->ends_at->copy();
        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $booking->refresh();
        $this->assertSame(ChatConsultationBooking::STATUS_CONFIRMED, $booking->status);
        $this->assertTrue($booking->ends_at->equalTo($originalEndsAt));
    }

    public function test_archived_booking_cannot_be_started(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:05:00'));

        [$booking] = $this->createPaidBookingWithSession(['archived_at' => now()]);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('info');
    }

    public function test_non_confirmed_booking_cannot_be_started(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:05:00'));

        [$booking] = $this->createPaidBookingWithSession([
            'status' => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
        ]);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('info');
    }

    public function test_missing_session_is_handled_safely(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:05:00'));

        $startsAt = Carbon::parse('2026-06-15 15:00:00');
        $booking = ChatConsultationBooking::create([
            'first_name'     => 'Ivan',
            'last_name'      => 'Petrov',
            'email'          => 'ivan@example.com',
            'phone'          => '+359888000000',
            'starts_at'      => $startsAt,
            'ends_at'        => $startsAt->copy()->addMinutes(30),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_CONFIRMED,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('error', 'Чат сесията не е налична.');
    }

    public function test_active_session_cannot_be_started_again(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:05:00'));

        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('error');

        $session->refresh();
        $this->assertSame(ChatSession::PHASE_ACTIVE, $session->phase);
    }

    public function test_completed_session_cannot_be_started(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:35:00'));

        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('info');

        $session->refresh();
        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
    }

    public function test_start_at_ends_at_does_not_activate_session(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy());
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('error', 'Консултацията не може да бъде стартирана.');

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
        $this->assertNull($session->started_at);
    }

    public function test_start_after_ends_at_does_not_activate_session(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy()->addMinute());
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.chat-bookings.start', $booking));

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('error');

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertNull($session->started_at);
    }

    public function test_show_page_displays_start_button_for_waiting_confirmed_session(): void
    {
        [$booking] = $this->createPaidBookingWithSession();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.show', $booking))
            ->assertOk()
            ->assertSee('Стартирай чат консултацията', false);
    }

    public function test_show_page_hides_start_button_for_active_session(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.show', $booking))
            ->assertOk()
            ->assertDontSee('Стартирай чат консултацията', false);
    }

    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @param  array<string, mixed>  $sessionOverrides
     * @return array{0: ChatConsultationBooking, 1: ChatSession}
     */
    private function createPaidBookingWithSession(
        array $bookingOverrides = [],
        array $sessionOverrides = [],
    ): array {
        [$booking, $session] = $this->createConfirmedBookingWithSession($bookingOverrides, $sessionOverrides);

        Payment::create([
            'payable_type'   => ChatConsultationBooking::class,
            'payable_id'     => $booking->id,
            'provider'       => 'fake_epay',
            'payment_method' => 'epay',
            'invoice_number' => 'INV-START-' . Str::upper(Str::random(8)),
            'amount'         => 50.00,
            'currency'       => 'EUR',
            'status'         => Payment::STATUS_PAID,
            'description'    => 'Test paid chat booking',
            'paid_at'        => now(),
            'is_finalized'   => true,
            'finalized_at'   => now(),
        ]);

        return [$booking->fresh(['session', 'payment']), $session->fresh()];
    }
}
