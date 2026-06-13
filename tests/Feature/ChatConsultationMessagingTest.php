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

class ChatConsultationMessagingTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_fetch_invalid_short_token_returns_404(): void
    {
        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => 'short']))
            ->assertNotFound();
    }

    public function test_fetch_unknown_token_returns_404(): void
    {
        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => Str::random(48)]))
            ->assertNotFound();
    }

    public function test_fetch_public_token_returns_404(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => $booking->public_token]))
            ->assertNotFound();
    }

    public function test_fetch_ineligible_booking_returns_404(): void
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

        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]))
            ->assertNotFound();
    }

    public function test_fetch_waiting_returns_empty_messages_and_can_send_false(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase'  => ChatSession::PHASE_WAITING,
                'can_send'       => false,
                'messages'       => [],
            ]);
    }

    public function test_fetch_active_returns_messages(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Client hello',
        ]);

        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJsonPath('can_send', true)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.message', 'Client hello');
    }

    public function test_fetch_ending_returns_messages(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ENDING,
            'started_at' => Carbon::parse('2026-06-15 15:00:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(25));

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'Lawyer note',
        ]);

        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJsonPath('session_phase', ChatSession::PHASE_ENDING)
            ->assertJsonCount(1, 'messages');
    }

    public function test_fetch_completed_returns_empty_messages(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Hidden after complete',
        ]);

        Carbon::setTestNow($booking->ends_at->copy()->addMinutes(5));

        $this->getJson(route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertJson([
                'session_phase' => ChatSession::PHASE_COMPLETED,
                'can_send'      => false,
                'messages'      => [],
            ]);
    }

    public function test_fetch_after_id_returns_only_newer_messages(): void
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

        $this->getJson(route('chat-consultation.messages.index', [
            'client_access_token' => $session->client_access_token,
            'after_id'            => $first->id,
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.message', 'Second');
    }

    public function test_fetch_messages_are_ordered_by_id_ascending(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'One',
        ]);
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'Two',
        ]);

        $response = $this->getJson(route('chat-consultation.messages.index', [
            'client_access_token' => $session->client_access_token,
        ]));

        $ids = collect($response->json('messages'))->pluck('id')->all();
        $this->assertSame($ids, collect($ids)->sort()->values()->all());
    }

    public function test_fetch_response_is_capped_at_100_messages(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        for ($i = 1; $i <= 101; $i++) {
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_type'     => ChatMessage::SENDER_CLIENT,
                'message'         => 'Message ' . $i,
            ]);
        }

        $this->getJson(route('chat-consultation.messages.index', [
            'client_access_token' => $session->client_access_token,
        ]))
            ->assertOk()
            ->assertJsonCount(100, 'messages');
    }

    public function test_fetch_response_contains_no_personal_data(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $content = $this->getJson(route('chat-consultation.messages.index', [
            'client_access_token' => $session->client_access_token,
        ]))->getContent();

        $this->assertStringNotContainsString($booking->email, $content);
        $this->assertStringNotContainsString($booking->phone, $content);
        $this->assertStringNotContainsString($booking->public_token, $content);
        $this->assertStringNotContainsString('"booking_id"', $content);
    }

    public function test_fetch_token_isolation_between_sessions(): void
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
            'message'         => 'Session A secret',
        ]);

        $this->getJson(route('chat-consultation.messages.index', [
            'client_access_token' => $sessionB->client_access_token,
        ]))
            ->assertOk()
            ->assertJsonCount(0, 'messages');
    }

    public function test_fetch_at_ends_at_completes_and_returns_no_client_transcript(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Should not leak',
        ]);

        Carbon::setTestNow($booking->ends_at->copy());

        $this->getJson(route('chat-consultation.messages.index', [
            'client_access_token' => $session->client_access_token,
        ]))
            ->assertOk()
            ->assertJson([
                'session_phase' => ChatSession::PHASE_COMPLETED,
                'can_send'      => false,
                'messages'      => [],
            ]);

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_waiting_session_cannot_send(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => 'Hello'])
            ->assertUnprocessable()
            ->assertJson(['message' => 'В момента не можете да изпращате съобщения.']);
    }

    public function test_active_session_can_send(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => '  Client message  '])
            ->assertCreated()
            ->assertJsonPath('sender_type', ChatMessage::SENDER_CLIENT)
            ->assertJsonPath('message', 'Client message');

        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Client message',
        ]);
    }

    public function test_ending_session_can_send(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ENDING,
            'started_at' => Carbon::parse('2026-06-15 15:00:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(25));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => 'Late message'])
            ->assertCreated();
    }

    public function test_completed_session_cannot_send(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        Carbon::setTestNow($booking->ends_at->copy()->addMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => 'Too late'])
            ->assertUnprocessable();
    }

    public function test_send_at_ends_at_completes_and_rejects(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->ends_at->copy());

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => 'At end'])
            ->assertUnprocessable();

        $session->refresh();
        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
    }

    public function test_send_after_ends_at_is_rejected(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->ends_at->copy()->addMinute());

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => 'After end'])
            ->assertUnprocessable();
    }

    public function test_send_invalid_token_returns_404(): void
    {
        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => Str::random(48),
        ]), ['message' => 'Hello'])->assertNotFound();
    }

    public function test_send_public_token_returns_404(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $booking->public_token,
        ]), ['message' => 'Hello'])->assertNotFound();
    }

    public function test_send_whitespace_only_is_rejected(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => '   '])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_send_empty_message_is_rejected(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => ''])
            ->assertUnprocessable();
    }

    public function test_send_over_limit_is_rejected(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), ['message' => str_repeat('a', 2001)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_send_ignores_injected_sender_type(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $this->postJson(route('chat-consultation.messages.store', [
            'client_access_token' => $session->client_access_token,
        ]), [
            'message'     => 'Test',
            'sender_type' => ChatMessage::SENDER_LAWYER,
        ])
            ->assertCreated()
            ->assertJsonPath('sender_type', ChatMessage::SENDER_CLIENT);
    }

    public function test_client_message_routes_exist(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByName('chat-consultation.messages.index'));
        $this->assertNotNull(Route::getRoutes()->getByName('chat-consultation.messages.store'));
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
            'invoice_number' => 'INV-MSG-' . Str::upper(Str::random(8)),
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
