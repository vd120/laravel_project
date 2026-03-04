<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure that the status endpoint returns read/delivered timestamps for
     * message IDs belonging to a conversation the authenticated user is part of.
     */
    public function test_status_endpoint_returns_statuses_for_own_messages()
    {
        // two users and a conversation between them
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::createConversation($user1->id, $user2->id);

        // create a message from user1
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'content' => 'hello',
            'type' => 'text',
        ]);

        // act as user1 and request status
        $this->actingAs($user1)
             ->postJson("/chat/{$conversation->slug}/status", ['message_ids' => [$message->id]])
             ->assertStatus(200)
             ->assertJson(["success" => true])
             ->assertJsonStructure(['statuses' => [['id', 'read_at', 'delivered_at']]]);

        // initially read_at should be null
        $response = $this->postJson("/chat/{$conversation->slug}/status", ['message_ids' => [$message->id]]);
        $this->assertNull($response->json('statuses.0.read_at'));

        // mark the message as read by user2
        Message::markConversationAsRead($conversation->id, $user2->id);

        // request again and expect a non-null read_at
        $response = $this->postJson("/chat/{$conversation->slug}/status", ['message_ids' => [$message->id]]);
        $this->assertNotNull($response->json('statuses.0.read_at'));
    }

    /**
     * Ensure that a user who is not part of a conversation cannot fetch statuses.
     */
    public function test_non_member_cannot_access_status_endpoint()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $stranger = User::factory()->create();

        $conversation = Conversation::createConversation($user1->id, $user2->id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'content' => 'secret',
            'type' => 'text',
        ]);

        $this->actingAs($stranger)
             ->postJson("/chat/{$conversation->slug}/status", ['message_ids' => [$message->id]])
             ->assertStatus(403);
    }
}
