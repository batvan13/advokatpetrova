<?php

namespace Tests\Feature;

use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatConsultationWaitingRoomTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_invalid_short_token_returns_404(): void
    {
        $this->get(route('chat-consultation.room', ['client_access_token' => 'short']))
            ->assertNotFound();
    }

    public function test_unknown_48_character_token_returns_404(): void
    {
        $this->get(route('chat-consultation.room', ['client_access_token' => Str::random(48)]))
            ->assertNotFound();
    }

    public function test_booking_public_token_used_as_room_token_returns_404(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->get(route('chat-consultation.room', ['client_access_token' => $booking->public_token]))
            ->assertNotFound();
    }

    public function test_ineligible_unconfirmed_booking_returns_404(): void
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
            'status'         => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        $session = ChatSession::create([
            'booking_id' => $booking->id,
            'phase'      => ChatSession::PHASE_WAITING,
        ]);

        Carbon::setTestNow($startsAt->copy()->subMinutes(10));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertNotFound();
    }

    public function test_eleven_minutes_before_starts_at_shows_early_state(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(11));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="early"', false)
            ->assertSee('Чат консултацията още не е достъпна', false)
            ->assertDontSee('<textarea', false)
            ->assertDontSee('type="submit"', false);
    }

    public function test_exactly_ten_minutes_before_starts_at_shows_waiting_state(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(10));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="waiting"', false)
            ->assertSee('Вие сте в чакалнята', false);
    }

    public function test_after_starts_at_before_ends_at_with_waiting_phase_shows_waiting_state(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="waiting"', false)
            ->assertSee('Вие сте в чакалнята', false);
    }

    public function test_active_phase_shows_active_state_with_message_controls(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:00:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="active"', false)
            ->assertSee('Консултацията е активна', false)
            ->assertSee('id="chat-send-form"', false)
            ->assertSee('Изпрати', false);
    }

    public function test_ending_phase_is_treated_as_active_state(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ENDING,
            'started_at' => Carbon::parse('2026-06-15 15:00:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(25));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="active"', false)
            ->assertSee('Консултацията е активна', false);
    }

    public function test_exactly_at_ends_at_completes_session_and_shows_completed_state(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy());

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="completed"', false)
            ->assertSee('Чат консултацията приключи', false);

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_after_ends_at_shows_completed_state(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy()->addMinutes(5));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="completed"', false)
            ->assertSee('Чат консултацията приключи', false);
    }

    public function test_revisit_completed_session_shows_completed_without_transcript(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);

        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Secret transcript content that must not appear',
        ]);

        Carbon::setTestNow($booking->ends_at->copy()->addDay());

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('data-chat-room-state="completed"', false)
            ->assertDontSee('Secret transcript content that must not appear', false)
            ->assertDontSee('<textarea', false);
    }

    public function test_success_page_for_paid_confirmed_booking_shows_room_cta(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        $response = $this->get(route('chat-consultation.success', ['token' => $booking->public_token]));

        $response->assertOk()
            ->assertSee('Отвори чат консултацията', false)
            ->assertSee($session->client_access_token, false);
    }

    public function test_success_page_for_unpaid_pending_booking_does_not_show_room_cta(): void
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
            'status'         => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        Payment::create([
            'payable_type'   => ChatConsultationBooking::class,
            'payable_id'     => $booking->id,
            'provider'       => 'fake_epay',
            'payment_method' => 'epay',
            'invoice_number' => 'INV-ROOM-PENDING-001',
            'amount'         => 50.00,
            'currency'       => 'EUR',
            'status'         => Payment::STATUS_PENDING,
            'description'    => 'Test pending chat booking',
            'expires_at'     => now()->addMinutes(30),
        ]);

        $response = $this->get(route('chat-consultation.success', ['token' => $booking->public_token]));

        $response->assertOk()
            ->assertDontSee('Отвори чат консултацията', false);
    }

    public function test_status_endpoint_does_not_include_messages_array(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $response = $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringNotContainsString('"messages"', $content);
    }

    public function test_status_invalid_short_token_returns_404(): void
    {
        $this->getJson(route('chat-consultation.status', ['client_access_token' => 'short']))
            ->assertNotFound();
    }

    public function test_status_unknown_token_returns_404(): void
    {
        $this->getJson(route('chat-consultation.status', ['client_access_token' => Str::random(48)]))
            ->assertNotFound();
    }

    public function test_status_public_token_returns_404(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $booking->public_token]))
            ->assertNotFound();
    }

    public function test_status_ineligible_booking_returns_404(): void
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
            'status'         => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        $session = ChatSession::create([
            'booking_id' => $booking->id,
            'phase'      => ChatSession::PHASE_WAITING,
        ]);

        Carbon::setTestNow($startsAt->copy()->subMinutes(5));

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]))
            ->assertNotFound();
    }

    public function test_status_waiting_phase_json(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase'  => ChatSession::PHASE_WAITING,
                'booking_status' => ChatConsultationBooking::STATUS_CONFIRMED,
                'can_send'       => false,
            ])
            ->assertJsonStructure(['server_time', 'ends_at']);
    }

    public function test_status_active_phase_json(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase'  => ChatSession::PHASE_ACTIVE,
                'booking_status' => ChatConsultationBooking::STATUS_CONFIRMED,
                'can_send'       => false,
            ]);
    }

    public function test_status_ending_phase_json(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ENDING,
            'started_at' => Carbon::parse('2026-06-15 15:00:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(25));

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase'  => ChatSession::PHASE_ENDING,
                'can_send'       => false,
            ]);
    }

    public function test_status_completed_phase_json(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        Carbon::setTestNow($booking->ends_at->copy()->addMinutes(5));

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase'  => ChatSession::PHASE_COMPLETED,
                'booking_status' => ChatConsultationBooking::STATUS_COMPLETED,
                'can_send'       => false,
            ]);
    }

    public function test_status_at_ends_at_triggers_completion(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy());

        $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase'  => ChatSession::PHASE_COMPLETED,
                'booking_status' => ChatConsultationBooking::STATUS_COMPLETED,
            ]);

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_status_response_contains_no_messages_or_personal_data(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $response = $this->getJson(route('chat-consultation.status', ['client_access_token' => $session->client_access_token]));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringNotContainsString('"messages"', $content);
        $this->assertStringNotContainsString($booking->email, $content);
        $this->assertStringNotContainsString($booking->phone, $content);
        $this->assertStringNotContainsString($booking->public_token, $content);
        $this->assertStringNotContainsString('"id"', $content);
    }

    public function test_status_route_is_get_only(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $url = route('chat-consultation.status', ['client_access_token' => $session->client_access_token]);

        $this->postJson($url)->assertMethodNotAllowed();
    }

    public function test_waiting_room_page_includes_messages_polling_reference(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(10));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('data-chat-messages-url', false)
            ->assertSee('data-chat-poll-enabled="true"', false)
            ->assertSee(route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]), false);
    }

    public function test_active_room_page_includes_messages_polling_reference(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('data-chat-messages-url', false)
            ->assertSee('data-chat-poll-enabled="true"', false);
    }

    public function test_early_room_page_does_not_poll(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(11));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('data-chat-room-state="early"', false)
            ->assertDontSee('data-chat-poll-enabled', false);
    }

    public function test_completed_room_page_does_not_poll(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        Carbon::setTestNow($booking->ends_at->copy()->addDay());

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('data-chat-room-state="completed"', false)
            ->assertDontSee('data-chat-poll-enabled', false);
    }

    public function test_early_and_waiting_rooms_have_no_enabled_send_form(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(11));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertDontSee('id="chat-send-form"', false);

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('id="chat-send-form"', false)
            ->assertSee('disabled', false);
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
            'invoice_number' => 'INV-ROOM-' . Str::upper(Str::random(8)),
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
