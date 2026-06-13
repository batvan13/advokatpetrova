<?php

namespace Tests\Unit;

use App\Exceptions\ChatSessionLifecycleException;
use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use App\Support\ChatSessionLifecycleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatSessionLifecycleServiceTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    private ChatSessionLifecycleService $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lifecycle = app(ChatSessionLifecycleService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_starting_waiting_session_sets_active_phase_and_started_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:07:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $result = $this->lifecycle->start($session);

        $booking->refresh();
        $result->refresh();

        $this->assertSame(ChatSession::PHASE_ACTIVE, $result->phase);
        $this->assertNotNull($result->started_at);
        $this->assertTrue($result->started_at->equalTo(now()));
    }

    public function test_starting_session_does_not_change_booking_ends_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:07:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();
        $originalEndsAt = $booking->ends_at->copy();

        $this->lifecycle->start($session);

        $booking->refresh();

        $this->assertTrue($booking->ends_at->equalTo($originalEndsAt));
        $this->assertSame('15:30', $booking->ends_at->format('H:i'));
    }

    public function test_repeated_start_does_not_reset_started_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:07:00'));

        [, $session] = $this->createConfirmedBookingWithSession();

        $this->lifecycle->start($session);
        $firstStartedAt = $session->fresh()->started_at;

        Carbon::setTestNow(Carbon::parse('2026-06-15 15:12:00'));

        $this->lifecycle->start($session->fresh());

        $this->assertTrue($session->fresh()->started_at->equalTo($firstStartedAt));
        $this->assertSame(ChatSession::PHASE_ACTIVE, $session->fresh()->phase);
    }

    public function test_manual_completion_before_ends_at_completes_session_and_booking(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $result = $this->lifecycle->complete($session);

        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $result->phase);
        $this->assertNotNull($result->ended_at);
        $this->assertTrue($result->ended_at->equalTo(now()));
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_automatic_expiry_completion_at_exactly_ends_at(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();
        $endsAt = $booking->ends_at->copy();

        Carbon::setTestNow($endsAt);

        $completed = $this->lifecycle->completeIfExpired($session);

        $session->refresh();
        $booking->refresh();

        $this->assertTrue($completed);
        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
        $this->assertTrue($session->ended_at->equalTo($endsAt));
    }

    public function test_no_automatic_completion_before_ends_at(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy()->subSecond());

        $completed = $this->lifecycle->completeIfExpired($session);

        $session->refresh();
        $booking->refresh();

        $this->assertFalse($completed);
        $this->assertSame(ChatSession::PHASE_WAITING, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_CONFIRMED, $booking->status);
        $this->assertNull($session->ended_at);
    }

    public function test_repeated_completion_is_idempotent_and_preserves_ended_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $this->lifecycle->complete($session);
        $firstEndedAt = $session->fresh()->ended_at;

        Carbon::setTestNow(Carbon::parse('2026-06-15 15:25:00'));

        $this->lifecycle->complete($session->fresh());

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
        $this->assertTrue($session->ended_at->equalTo($firstEndedAt));
    }

    public function test_completion_updates_booking_and_session_together(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:25:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $this->lifecycle->complete($session);

        $this->assertDatabaseHas('chat_sessions', [
            'id'    => $session->id,
            'phase' => ChatSession::PHASE_COMPLETED,
        ]);

        $this->assertDatabaseHas('chat_consultation_bookings', [
            'id'     => $booking->id,
            'status' => ChatConsultationBooking::STATUS_COMPLETED,
        ]);
    }

    public function test_complete_if_expired_is_idempotent_after_first_completion(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy());

        $this->assertTrue($this->lifecycle->completeIfExpired($session));
        $firstEndedAt = $session->fresh()->ended_at;

        Carbon::setTestNow($booking->ends_at->copy()->addMinutes(5));

        $this->assertTrue($this->lifecycle->completeIfExpired($session->fresh()));
        $this->assertTrue($session->fresh()->ended_at->equalTo($firstEndedAt));
    }

    public function test_start_before_ends_at_succeeds(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:07:00'));

        [, $session] = $this->createConfirmedBookingWithSession();

        $result = $this->lifecycle->start($session);

        $this->assertSame(ChatSession::PHASE_ACTIVE, $result->phase);
        $this->assertNotNull($result->started_at);
    }

    public function test_start_exactly_at_ends_at_does_not_activate_session(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy());

        try {
            $this->lifecycle->start($session);
            $this->fail('Expected ChatSessionLifecycleException was not thrown.');
        } catch (ChatSessionLifecycleException $e) {
            $this->assertSame('The scheduled consultation time has expired.', $e->getMessage());
        }

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertNull($session->started_at);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_start_after_ends_at_does_not_activate_session(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy()->addMinute());

        try {
            $this->lifecycle->start($session);
            $this->fail('Expected ChatSessionLifecycleException was not thrown.');
        } catch (ChatSessionLifecycleException) {
            // expected
        }

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertNull($session->started_at);
        $this->assertNotSame(ChatSession::PHASE_ACTIVE, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_phase_ending_cannot_be_started(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:07:00'));

        [, $session] = $this->createConfirmedBookingWithSession([], [
            'phase' => ChatSession::PHASE_ENDING,
        ]);

        $this->expectException(ChatSessionLifecycleException::class);
        $this->expectExceptionMessage('Chat session can only be started from the waiting phase.');

        $this->lifecycle->start($session);
    }

    public function test_complete_booking_with_missing_session_creates_and_completes_atomically(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        $startsAt = Carbon::parse('2026-06-15 15:00:00');

        $booking = ChatConsultationBooking::create([
            'first_name'     => 'Recovery',
            'last_name'      => 'Case',
            'email'          => 'recovery@example.com',
            'phone'          => '+359888333333',
            'starts_at'      => $startsAt,
            'ends_at'        => $startsAt->copy()->addMinutes(30),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_CONFIRMED,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        $this->assertNull($booking->session);

        $session = $this->lifecycle->completeBooking($booking);

        $booking->refresh();

        $this->assertSame(1, ChatSession::where('booking_id', $booking->id)->count());
        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertNull($session->started_at);
        $this->assertNotNull($session->ended_at);
        $this->assertTrue($session->ended_at->equalTo(now()));
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }

    public function test_complete_booking_failure_does_not_persist_recovery_session(): void
    {
        $booking = ChatConsultationBooking::create([
            'first_name'     => 'Failed',
            'last_name'      => 'Recovery',
            'email'          => 'failed@example.com',
            'phone'          => '+359888444444',
            'starts_at'      => Carbon::parse('2026-06-15 15:00:00'),
            'ends_at'        => Carbon::parse('2026-06-15 15:30:00'),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        try {
            $this->lifecycle->completeBooking($booking);
            $this->fail('Expected ChatSessionLifecycleException was not thrown.');
        } catch (ChatSessionLifecycleException) {
            // expected
        }

        $this->assertSame(0, ChatSession::where('booking_id', $booking->id)->count());
    }

    public function test_complete_booking_recovery_rolls_back_on_simulated_failure(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        $booking = ChatConsultationBooking::create([
            'first_name'     => 'Rollback',
            'last_name'      => 'Recovery',
            'email'          => 'rollback@example.com',
            'phone'          => '+359888555555',
            'starts_at'      => Carbon::parse('2026-06-15 15:00:00'),
            'ends_at'        => Carbon::parse('2026-06-15 15:30:00'),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_CONFIRMED,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        ChatSession::updated(function () {
            throw new RuntimeException('Simulated completion failure.');
        });

        try {
            $this->lifecycle->completeBooking($booking);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException) {
            // expected
        } finally {
            ChatSession::flushEventListeners();
        }

        $this->assertSame(0, ChatSession::where('booking_id', $booking->id)->count());
        $this->assertSame(ChatConsultationBooking::STATUS_CONFIRMED, $booking->fresh()->status);
    }

    public function test_complete_if_expired_uses_locked_database_state(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();

        Carbon::setTestNow($booking->ends_at->copy()->subSecond());

        $this->assertFalse($this->lifecycle->completeIfExpired($session));

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_WAITING, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_CONFIRMED, $booking->status);

        Carbon::setTestNow($booking->ends_at->copy());

        $this->assertTrue($this->lifecycle->completeIfExpired($session->fresh()));

        $session->refresh();
        $booking->refresh();

        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
    }
}
