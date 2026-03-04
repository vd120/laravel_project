<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatClearTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clear_chat_and_messages_are_removed()
    {
        // create two users and a conversation between them
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        // start conversation using the helper method on Conversation model
        $conversation = Conversation::createConversation($sender->id, $recipient->id);

        // add a few messages to the conversation (no factory exists for Message)
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'hello',
            'type' => 'text'
        ]);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $recipient->id,
            'content' => 'hi there',
            'type' => 'text'
        ]);

        $this->assertDatabaseCount('messages', 2);

        // act as sender and clear the chat
        $response = $this->actingAs($sender)
            ->delete(route('chat.clear', $conversation));

        $response->assertJson(['success' => true]);

        // all messages should be permanently removed
        $this->assertDatabaseCount('messages', 0);

        // conversation should still be accessible and show empty UI
        $view = $this->actingAs($sender)->get(route('chat.show', $conversation));
        $view->assertStatus(200);
        // the page should no longer contain any of the previous message content
        $view->assertDontSee('hello');
        $view->assertDontSee('hi there');
        // expectation: user sees the "Start a conversation" placeholder
        $view->assertSee('Start a conversation');

        // chat list endpoint should continue returning the conversation
        $list = $this->actingAs($sender)->get(route('chat.conversations'));
        $list->assertJsonFragment(['slug' => $conversation->slug]);
    }
}
