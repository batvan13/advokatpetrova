<?php

namespace Tests\Unit;

use App\Models\ChatConsultationBooking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\Support\CreatesChatConsultationFixtures;
use Tests\TestCase;

class ChatSessionModelTest extends TestCase
{
    use CreatesChatConsultationFixtures;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_new_chat_session_receives_unique_client_access_token(): void
    {
        [, $session] = $this->createConfirmedBookingWithSession();

        $this->assertNotNull($session->client_access_token);
        $this->assertSame(48, strlen($session->client_access_token));
    }

    public function test_two_sessions_cannot_receive_the_same_client_access_token(): void
    {
        [, $sessionA] = $this->createConfirmedBookingWithSession([
            'email' => 'a@example.com',
            'starts_at' => now()->addDay()->setTime(10, 0),
        ]);

        [, $sessionB] = $this->createConfirmedBookingWithSession([
            'email' => 'b@example.com',
            'starts_at' => now()->addDay()->setTime(11, 0),
        ]);

        $this->assertNotSame($sessionA->client_access_token, $sessionB->client_access_token);
    }

    public function test_client_access_token_is_separate_from_booking_public_token(): void
    {
        [$booking, $session] = $this->createConfirmedBookingWithSession();

        $this->assertNotSame($booking->public_token, $session->client_access_token);
    }

    public function test_chat_message_belongs_to_chat_session(): void
    {
        [, $session] = $this->createConfirmedBookingWithSession();

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Здравейте.',
        ]);

        $this->assertTrue($message->session->is($session));
        $this->assertTrue($session->messages->contains($message));
    }

    public function test_chat_message_accepts_client_and_lawyer_sender_types(): void
    {
        [, $session] = $this->createConfirmedBookingWithSession();

        $this->assertTrue(ChatMessage::isValidSenderType(ChatMessage::SENDER_CLIENT));
        $this->assertTrue(ChatMessage::isValidSenderType(ChatMessage::SENDER_LAWYER));
        $this->assertFalse(ChatMessage::isValidSenderType('admin'));

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_CLIENT,
            'message'         => 'Client message',
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => ChatMessage::SENDER_LAWYER,
            'message'         => 'Lawyer message',
        ]);

        $this->assertSame(2, $session->messages()->count());
    }

    public function test_chat_message_rejects_invalid_sender_type(): void
    {
        [, $session] = $this->createConfirmedBookingWithSession();

        $this->expectException(InvalidArgumentException::class);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type'     => 'bot',
            'message'         => 'Invalid sender',
        ]);
    }
}
