<?php

namespace Tests\Feature;

use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatConsultationAdminMessagingTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_unauthenticated_admin_cannot_fetch_messages(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->getJson(route('admin.chat-bookings.messages.index', $booking))
            ->assertUnauthorized();
    }

    public function test_authenticated_admin_fetches_active_messages(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'Admin visible',
        ]);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.chat-bookings.messages.index', $booking))
            ->assertOk()
            ->assertJsonPath('can_send', true)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.message', 'Admin visible');
    }

    public function test_admin_fetches_completed_transcript(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Archived transcript line',
        ]);

        Carbon::setTestNow($booking->ends_at->copy()->addDay());

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.chat-bookings.messages.index', $booking))
            ->assertOk()
            ->assertJsonPath('session_phase', ChatSession::PHASE_COMPLETED)
            ->assertJsonPath('can_send', false)
            ->assertJsonCount(1, 'messages');
    }

    public function test_admin_fetch_after_id_works(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $first = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'First',
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'Second',
        ]);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.chat-bookings.messages.index', $booking) . '?after_id=' . $first->id)
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.message', 'Second');
    }

    public function test_admin_fetch_messages_are_chronological(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'A',
        ]);
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'B',
        ]);

        $admin = User::factory()->create();

        $ids = collect(
            $this->actingAs($admin)
                ->getJson(route('admin.chat-bookings.messages.index', $booking))
                ->json('messages')
        )->pluck('id')->all();

        $this->assertSame($ids, collect($ids)->sort()->values()->all());
    }

    public function test_admin_fetch_isolates_sessions_between_bookings(): void
    {
        [$bookingA, $sessionA] = $this->createPaidBookingWithSession();
        [$bookingB, $sessionB] = $this->createPaidBookingWithSession([
            'starts_at' => Carbon::parse('2026-06-16 10:00:00'),
            'ends_at'   => Carbon::parse('2026-06-16 10:30:00'),
        ], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-16 10:01:00'),
        ]);

        Carbon::setTestNow($bookingB->starts_at->copy()->addMinutes(5));

        ChatMessage::create([
            'chat_session_id' => $sessionA->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Other session',
        ]);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.chat-bookings.messages.index', $bookingB))
            ->assertOk()
            ->assertJsonCount(0, 'messages');
    }

    public function test_admin_fetch_does_not_expose_client_access_token(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $content = $this->actingAs($admin)
            ->getJson(route('admin.chat-bookings.messages.index', $booking))
            ->getContent();

        $this->assertStringNotContainsString($session->client_access_token, $content);
        $this->assertStringNotContainsString('client_access_token', $content);
    }

    public function test_unauthenticated_admin_cannot_send(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->postJson(route('admin.chat-bookings.messages.store', $booking), [
            'message' => 'Hello',
        ])->assertUnauthorized();
    }

    public function test_admin_waiting_session_cannot_send(): void
    {
        [$booking] = $this->createPaidBookingWithSession();
        $admin = User::factory()->create();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => 'Hello',
            ])
            ->assertUnprocessable();
    }

    public function test_admin_active_session_can_send(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => 'Lawyer reply',
            ])
            ->assertCreated()
            ->assertJsonPath('sender_type', ChatMessage::SENDER_LAWYER);
    }

    public function test_admin_ending_session_can_send(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ENDING,
            'started_at' => Carbon::parse('2026-06-15 15:00:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(25));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => 'Closing note',
            ])
            ->assertCreated();
    }

    public function test_admin_completed_session_cannot_send(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        Carbon::setTestNow($booking->ends_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => 'Too late',
            ])
            ->assertUnprocessable();
    }

    public function test_admin_send_at_ends_at_completes_and_rejects(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->ends_at->copy());

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => 'At end',
            ])
            ->assertUnprocessable();

        $session->refresh();
        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
    }

    public function test_admin_whitespace_only_send_is_rejected(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => " \t\n ",
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_admin_over_limit_send_is_rejected(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message' => str_repeat('x', 2001),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_admin_send_ignores_injected_sender_type(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.chat-bookings.messages.store', $booking), [
                'message'     => 'Lawyer text',
                'sender_type' => ChatMessage::SENDER_CLIENT,
            ])
            ->assertCreated()
            ->assertJsonPath('sender_type', ChatMessage::SENDER_LAWYER);

        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'Lawyer text',
        ]);
    }

    public function test_admin_message_routes_exist_and_require_auth(): void
    {
        $index = Route::getRoutes()->getByName('admin.chat-bookings.messages.index');
        $store = Route::getRoutes()->getByName('admin.chat-bookings.messages.store');

        $this->assertNotNull($index);
        $this->assertNotNull($store);
        $this->assertContains('GET', $index->methods());
        $this->assertContains('POST', $store->methods());
    }

    public function test_admin_fetch_without_session_returns_404(): void
    {
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

        $this->actingAs($admin)
            ->getJson(route('admin.chat-bookings.messages.index', $booking))
            ->assertNotFound();
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
            'invoice_number' => 'INV-ADM-' . Str::upper(Str::random(8)),
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
