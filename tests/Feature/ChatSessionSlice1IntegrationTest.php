<?php

namespace Tests\Feature;

use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Payment;
use App\Models\User;
use App\Support\GoogleCalendarService;
use App\Support\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatSessionSlice1IntegrationTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('createEvent')->andReturn('google-event-test');
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_payment_paid_flow_creates_exactly_one_chat_session_with_token(): void
    {
        $booking = ChatConsultationBooking::create([
            'first_name'     => 'Maria',
            'last_name'      => 'Ivanova',
            'email'          => 'maria@example.com',
            'phone'          => '+359888111111',
            'starts_at'      => Carbon::parse('2026-06-20 10:00:00'),
            'ends_at'        => Carbon::parse('2026-06-20 10:30:00'),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        $payment = Payment::create([
            'payable_type'   => ChatConsultationBooking::class,
            'payable_id'     => $booking->id,
            'provider'       => 'fake_epay',
            'payment_method' => 'epay',
            'invoice_number' => 'INV-TEST-000001',
            'amount'         => 50.00,
            'currency'       => 'EUR',
            'status'         => Payment::STATUS_PENDING,
            'description'    => 'Test chat booking',
            'expires_at'     => now()->addMinutes(30),
        ]);

        $service = app(PaymentService::class);

        $service->processFakeNotification($payment->invoice_number, Payment::PROVIDER_STATUS_PAID);
        $service->processFakeNotification($payment->invoice_number, Payment::PROVIDER_STATUS_PAID);

        $booking->refresh();

        $this->assertSame(ChatConsultationBooking::STATUS_CONFIRMED, $booking->status);
        $this->assertSame(1, ChatSession::where('booking_id', $booking->id)->count());

        $session = $booking->session;

        $this->assertNotNull($session);
        $this->assertNotNull($session->client_access_token);
        $this->assertNotSame($booking->public_token, $session->client_access_token);
        $this->assertSame(ChatSession::PHASE_WAITING, $session->phase);
    }

    public function test_admin_complete_action_completes_booking_and_session_atomically(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(
            route('admin.chat-bookings.complete', $booking)
        );

        $response->assertRedirect(route('admin.chat-bookings.show', $booking));
        $response->assertSessionHas('success');

        $booking->refresh();
        $session->refresh();

        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
        $this->assertSame(ChatSession::PHASE_COMPLETED, $session->phase);
        $this->assertNotNull($session->ended_at);
    }

    public function test_admin_complete_creates_session_when_missing_for_confirmed_booking(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        $startsAt = Carbon::parse('2026-06-15 15:00:00');

        $booking = ChatConsultationBooking::create([
            'first_name'     => 'Legacy',
            'last_name'      => 'Booking',
            'email'          => 'legacy@example.com',
            'phone'          => '+359888222222',
            'starts_at'      => $startsAt,
            'ends_at'        => $startsAt->copy()->addMinutes(30),
            'payment_method' => 'epay',
            'status'         => ChatConsultationBooking::STATUS_CONFIRMED,
            'price_eur'      => 50.00,
            'price_bgn'      => null,
            'show_bgn_price' => false,
        ]);

        $this->assertNull($booking->session);

        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.chat-bookings.complete', $booking));

        $booking->refresh();

        $this->assertSame(ChatConsultationBooking::STATUS_COMPLETED, $booking->status);
        $this->assertNotNull($booking->session);
        $this->assertSame(ChatSession::PHASE_COMPLETED, $booking->session->phase);
        $this->assertNotNull($booking->session->client_access_token);
        $this->assertNull($booking->session->started_at);
        $this->assertNotNull($booking->session->ended_at);
    }

    public function test_archived_booking_with_messages_can_be_destroyed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();
        $booking->update(['archived_at' => now()]);

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Message to be deleted with booking',
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->delete(
            route('admin.chat-bookings.destroy', $booking)
        );

        $response->assertRedirect(route('admin.chat-bookings.archived'));
        $response->assertSessionHas('success');

        $this->assertNull(ChatConsultationBooking::find($booking->id));
        $this->assertNull(ChatSession::find($session->id));
        $this->assertNull(ChatMessage::find($message->id));
    }

    public function test_non_archived_booking_cannot_be_destroyed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->delete(
            route('admin.chat-bookings.destroy', $booking)
        );

        $response->assertRedirect(route('admin.chat-bookings.archived'));
        $response->assertSessionHas('error');

        $this->assertNotNull($booking->fresh());
        $this->assertNotNull($session->fresh());
    }

    public function test_archived_booking_without_messages_can_be_destroyed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:20:00'));

        [$booking, $session] = $this->createConfirmedBookingWithSession();
        $booking->update(['archived_at' => now()]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->delete(
            route('admin.chat-bookings.destroy', $booking)
        );

        $response->assertRedirect(route('admin.chat-bookings.archived'));
        $response->assertSessionHas('success');

        $this->assertNull(ChatConsultationBooking::find($booking->id));
        $this->assertNull(ChatSession::find($session->id));
    }
}
