<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function getNewMessages(Request $request)
    {
        $user = auth()->user();

        
        $conversations = $user->conversations()
            ->with(['latestMessage.sender', 'user1', 'user2'])
            ->whereHas('messages', function($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                      ->whereNull('read_at');
            })
            ->get();

        $messages = [];

        foreach ($conversations as $conversation) {
            $unreadMessages = $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->take(5) 
                ->get();

            foreach ($unreadMessages as $message) {
                $messages[] = [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->profile ? $message->sender->profile->avatar : null,
                    ],
                    'conversation_id' => $conversation->id,
                    'created_at' => $message->created_at,
                ];
            }
        }

        
        usort($messages, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        
        $messages = array_slice($messages, 0, 10);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
