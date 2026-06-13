<?php

namespace Tests\Feature;

use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatConsultationChatUiTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_active_client_room_contains_message_form_and_endpoints(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $messagesUrl = route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]);
        $storeUrl = route('chat-consultation.messages.store', ['client_access_token' => $session->client_access_token]);

        $response = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]));

        $response->assertOk()
            ->assertSee('id="chat-send-form"', false)
            ->assertSee($messagesUrl, false)
            ->assertSee($storeUrl, false)
            ->assertSee('name="_token"', false)
            ->assertSee('Изпрати', false);
    }

    public function test_waiting_client_room_has_disabled_send_form(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('id="chat-send-form"', false)
            ->assertSee('disabled', false);
    }

    public function test_early_client_room_has_no_chat_form(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(11));

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertDontSee('id="chat-send-form"', false)
            ->assertDontSee('data-chat-poll-enabled', false);
    }

    public function test_completed_client_room_has_no_chat_form_or_transcript(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Secret transcript line XYZ123',
        ]);

        Carbon::setTestNow($booking->ends_at->copy()->addDay());

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertDontSee('id="chat-send-form"', false)
            ->assertDontSee('Secret transcript line XYZ123', false)
            ->assertSee('data-chat-room-state="completed"', false);
    }

    public function test_client_script_uses_after_id_and_text_content(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $content = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('after_id', $content);
        $this->assertStringContainsString('textContent', $content);
        $this->assertStringNotContainsString('innerHTML', $content);
    }

    public function test_client_send_script_builds_form_data_before_disabling_fields(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $content = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->getContent();

        $submitPos = strpos($content, "sendForm.addEventListener('submit'");
        $this->assertNotFalse($submitPos);

        $handler = substr($content, $submitPos, 2500);

        $formDataPos = strpos($handler, 'new FormData(sendForm)');
        $disablePos = strpos($handler, 'setSendEnabled(false)');

        $this->assertNotFalse($formDataPos);
        $this->assertNotFalse($disablePos);
        $this->assertLessThan($disablePos, $formDataPos);
        $this->assertStringContainsString("formData.set('message', text)", $handler);
    }

    public function test_client_textarea_uses_high_contrast_text_class(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $content = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('id="chat-message-input"', false)
            ->assertSee('name="message"', false)
            ->assertSee('text-white', false)
            ->assertSee('placeholder:text-gray-400', false)
            ->assertSee('focus:border-petrova-gold', false)
            ->assertSee('focus:ring-2', false)
            ->assertSee('focus:ring-petrova-gold/40', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/id="chat-message-input"[^>]*class="[^"]*text-white/',
            $content
        );
        $this->assertDoesNotMatchRegularExpression(
            '/id="chat-message-input"[^>]*class="[^"]*text-gray-900/',
            $content
        );
    }

    public function test_waiting_room_script_keeps_submit_listener_on_stable_form(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $content = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="chat-send-form"', $content);
        $this->assertStringContainsString("sendForm.addEventListener('submit'", $content);
        $this->assertStringContainsString('showChatInterface', $content);
        $this->assertStringNotContainsString('innerHTML', $content);
    }

    public function test_client_store_url_matches_messages_route(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $storeUrl = route('chat-consultation.messages.store', ['client_access_token' => $session->client_access_token]);

        $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->assertSee('action="' . $storeUrl . '"', false)
            ->assertSee('data-chat-messages-url="' . route('chat-consultation.messages.index', ['client_access_token' => $session->client_access_token]) . '"', false);
    }

    public function test_client_script_clears_transcript_on_completed_transition(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $content = $this->get(route('chat-consultation.room', ['client_access_token' => $session->client_access_token]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('clearMessages', $content);
        $this->assertStringContainsString('hideChatInterface', $content);
        $this->assertStringContainsString("'completed'", $content);
    }

    public function test_unauthenticated_admin_chat_route_redirects_to_login(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $this->get(route('admin.chat-bookings.chat', $booking))
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_open_chat_page(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee('Чат консултация #' . $booking->id, false)
            ->assertSee($booking->fullName(), false);
    }

    public function test_admin_waiting_chat_page_shows_start_control(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        Carbon::setTestNow($booking->starts_at->copy()->subMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee(route('admin.chat-bookings.start', $booking), false)
            ->assertSee('Стартирай чат консултацията', false);
    }

    public function test_admin_active_chat_page_shows_lawyer_send_form(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee('id="admin-chat-send-form"', false)
            ->assertSee('Изпрати', false);
    }

    public function test_admin_completed_chat_page_shows_transcript_ui_without_send_form(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'    => ChatSession::PHASE_COMPLETED,
            'ended_at' => Carbon::parse('2026-06-15 15:30:00'),
        ]);
        $booking->update(['status' => ChatConsultationBooking::STATUS_COMPLETED]);

        Carbon::setTestNow($booking->ends_at->copy()->addDay());

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee('id="admin-chat-phase-badge"', false)
            ->assertSee('data-admin-chat-phase-label', false)
            ->assertSee('Приключена', false)
            ->assertSee('bg-green-50', false)
            ->assertSee('text-green-700', false)
            ->assertSee('id="admin-chat-messages"', false)
            ->assertDontSee('id="admin-chat-send-form"', false)
            ->assertSee('Консултацията е приключена', false);
    }

    public function test_admin_polling_script_updates_phase_badge_on_completion(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $content = $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee('id="admin-chat-phase-badge"', false)
            ->assertSee('data-admin-chat-phase-label', false)
            ->getContent();

        $this->assertStringContainsString('function updatePhaseBadge', $content);
        $this->assertStringContainsString('updatePhaseBadge(data.session_phase)', $content);
        $this->assertStringContainsString("text: 'Приключена'", $content);
        $this->assertStringContainsString('bg-green-50', $content);
        $this->assertStringContainsString('text-green-700', $content);
        $this->assertStringContainsString('id="admin-chat-messages"', $content);
        $this->assertStringContainsString("display === 'completed'", $content);
        $this->assertStringContainsString('stopPolling', $content);
    }

    public function test_admin_chat_page_references_message_endpoints_and_csrf(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $indexUrl = route('admin.chat-bookings.messages.index', $booking);
        $storeUrl = route('admin.chat-bookings.messages.store', $booking);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee($indexUrl, false)
            ->assertSee($storeUrl, false)
            ->assertSee('name="_token"', false);
    }

    public function test_admin_script_uses_after_id_and_text_content(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $content = $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('after_id', $content);
        $this->assertStringContainsString('textContent', $content);
        $this->assertStringNotContainsString('innerHTML', $content);
    }

    public function test_admin_send_script_builds_form_data_before_disabling_fields(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $content = $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->getContent();

        $submitPos = strpos($content, "sendForm.addEventListener('submit'");
        $this->assertNotFalse($submitPos);

        $handler = substr($content, $submitPos, 2500);

        $formDataPos = strpos($handler, 'new FormData(sendForm)');
        $disablePos = strpos($handler, 'setSendEnabled(false)');

        $this->assertNotFalse($formDataPos);
        $this->assertNotFalse($disablePos);
        $this->assertLessThan($disablePos, $formDataPos);
        $this->assertStringContainsString("formData.set('message', text)", $handler);
    }

    public function test_admin_textarea_uses_readable_text_class_and_message_name(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee('id="admin-chat-message-input"', false)
            ->assertSee('name="message"', false)
            ->assertSee('text-gray-900', false);
    }

    public function test_admin_store_url_matches_messages_post_action(): void
    {
        [$booking] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $storeUrl = route('admin.chat-bookings.messages.store', $booking);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertSee('action="' . $storeUrl . '"', false)
            ->assertSee('data-admin-messages-url="' . route('admin.chat-bookings.messages.index', $booking) . '"', false);
    }

    public function test_admin_chat_page_does_not_expose_client_access_token(): void
    {
        [$booking, $session] = $this->createPaidBookingWithSession([], [
            'phase'      => ChatSession::PHASE_ACTIVE,
            'started_at' => Carbon::parse('2026-06-15 15:01:00'),
        ]);

        Carbon::setTestNow($booking->starts_at->copy()->addMinutes(5));

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.chat', $booking))
            ->assertOk()
            ->assertDontSee($session->client_access_token, false);
    }

    public function test_admin_show_page_has_open_chat_link(): void
    {
        [$booking] = $this->createPaidBookingWithSession();

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.chat-bookings.show', $booking))
            ->assertOk()
            ->assertSee('Отвори чат', false)
            ->assertSee(route('admin.chat-bookings.chat', $booking), false);
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
            'invoice_number' => 'INV-UI-' . Str::upper(Str::random(8)),
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
