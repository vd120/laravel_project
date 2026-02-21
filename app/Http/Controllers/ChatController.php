<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('user1_id', auth()->id())
            ->orWhere('user2_id', auth()->id())
            ->with(['user1', 'user2', 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('chat.index', compact('conversations'));
    }

    public function getConversations()
    {
        $conversations = Conversation::where('user1_id', auth()->id())
            ->orWhere('user2_id', auth()->id())
            ->with(['user1', 'user2', 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'user1_id' => $conversation->user1_id,
                    'user2_id' => $conversation->user2_id,
                    'last_message_at' => $conversation->last_message_at,
                    'unread_count' => $conversation->unread_count,
                    'other_user' => [
                        'id' => $conversation->other_user->id,
                        'name' => $conversation->other_user->name,
                        'avatar' => $conversation->other_user->profile?->avatar,
                    ],
                    'latest_message' => $conversation->latestMessage ? [
                        'content' => $conversation->latestMessage->content,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'created_at' => $conversation->latestMessage->created_at,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    public function getNewMessages()
    {
        // Get messages that are unread, from other users, and haven't been notified about yet
        $messages = Message::whereHas('conversation', function($query) {
                $query->where('user1_id', auth()->id())
                      ->orWhere('user2_id', auth()->id());
            })
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->whereNull('notified_at') // Only messages that haven't been notified about
            ->with(['sender', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->take(10) // Limit to prevent too many notifications
            ->get();

        // Mark these messages as notified
        if ($messages->count() > 0) {
            Message::whereIn('id', $messages->pluck('id'))
                ->update(['notified_at' => now()]);
        }

        $formattedMessages = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'content' => $message->content,
                'conversation_id' => $message->conversation_id,
                'created_at' => $message->created_at,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'avatar' => $message->sender->profile?->avatar,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $formattedMessages,
        ]);
    }

    public function show(Conversation $conversation)
    {
        // Check if user is part of this conversation
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        // Mark messages as read
        Message::markConversationAsRead($conversation->id, auth()->id());

        // Load active messages only - deleted messages are not shown
        $messages = $conversation->messages()->with('sender')->get();

        return view('chat.show', compact('conversation', 'messages'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        // Check if user is part of this conversation
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'nullable|string|max:1000',
            'media' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm|max:51200', // 50MB max
        ]);

        // Check if either content or media is provided
        if (!$request->filled('content') && !$request->hasFile('media')) {
            return response()->json([
                'success' => false,
                'error' => 'Message must contain text or media.'
            ], 422);
        }

        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => $request->content ?? '',
            'type' => 'text',
        ];

        // Handle media upload
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mimeType = $file->getMimeType();
            $originalFilename = $file->getClientOriginalName();
            $fileSize = $file->getSize();

            // Determine media type
            if (str_starts_with($mimeType, 'image/')) {
                $messageData['type'] = 'image';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $messageData['type'] = 'video';
            } else {
                $messageData['type'] = 'file';
            }

            // Store the file
            $path = $file->store('chat/media', 'public');
            $messageData['media_path'] = $path;
            $messageData['original_filename'] = $originalFilename;
            $messageData['media_size'] = $fileSize;

            // Generate thumbnail for images and videos
            if ($messageData['type'] === 'image') {
                $messageData['media_thumbnail'] = $path; // For images, thumbnail is the same
            } elseif ($messageData['type'] === 'video') {
                // For videos, we'll use the path for now (thumbnail generation would require ffmpeg)
                $messageData['media_thumbnail'] = null;
            }
        }

        $message = Message::create($messageData);

        // Update conversation last message timestamp
        $conversation->update(['last_message_at' => now()]);

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        // Create notification for the recipient
        $recipientId = $conversation->user1_id === auth()->id()
            ? $conversation->user2_id
            : $conversation->user1_id;

        // Import NotificationController and create notification
        \App\Http\Controllers\NotificationController::createMessageNotification(
            $recipientId,
            auth()->user(),
            $message
        );

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
        ]);
    }

    public function startConversation($userId)
    {
        $user = User::findOrFail($userId);

        // Can't start conversation with yourself
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You cannot start a conversation with yourself.'], 400);
            }
            return redirect()->back()->with('error', 'You cannot start a conversation with yourself.');
        }

        // Check if conversation already exists
        $conversation = Conversation::getConversationBetween(auth()->id(), $user->id);

        if (!$conversation) {
            $conversation = Conversation::createConversation(auth()->id(), $user->id);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'url' => route('chat.show', $conversation),
                'conversation_id' => $conversation->id
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }

    public function getMessages(Conversation $conversation)
    {
        // Check if user is part of this conversation
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        // Get unread messages from other users
        $newMessages = $conversation->messages()
            ->with('sender')
            ->where('sender_id', '!=', auth()->id())
            ->where('read_at', null)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'type' => $message->type,
                    'media_path' => $message->media_path,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->profile?->avatar,
                    ],
                ];
            });

        // Get all messages in conversation with their current status (including soft-deleted ones)
        $allMessages = $conversation->messages()
            ->with('sender')
            ->withTrashed() // Include soft-deleted messages
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'deleted' => $message->trashed(),
                    'updated_at' => $message->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $newMessages,
            'message_updates' => $allMessages
        ]);
    }

    public function markAsRead(Conversation $conversation)
    {
        // Check if user is part of this conversation
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        Message::markConversationAsRead($conversation->id, auth()->id());

        return response()->json(['success' => true]);
    }

    public function destroy(Message $message)
    {
        // Check if user owns this message
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        // Check if message belongs to a conversation the user is part of
        $conversation = $message->conversation;
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    public function clearChat(Conversation $conversation)
    {
        // Check if user is part of this conversation
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        // Delete all messages in the conversation (full chat deletion)
        Message::where('conversation_id', $conversation->id)->delete();

        // Reset conversation timestamp to indicate it's empty
        $conversation->update(['last_message_at' => null]);

        return response()->json(['success' => true]);
    }
}
