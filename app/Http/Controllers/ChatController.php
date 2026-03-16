<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        // Get direct conversations with their latest message. we no longer require messages to
        // exist so that an emptied/cleared conversation stays visible in the sidebar.  The
        // client handles displaying "Start a conversation" when there is no latest message.
        $directConversations = Conversation::where(function($q) {
                $q->where('user1_id', auth()->id())
                  ->orWhere('user2_id', auth()->id());
            })
            ->with(['user1', 'user2', 'latestMessage.sender'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

        // Get group conversations
        $groupIds = auth()->user()->groupMemberships()->pluck('group_id');
        $groupConversations = Conversation::where('is_group', true)
            ->whereIn('group_id', $groupIds)
            ->with(['group.members.user', 'latestMessage.sender'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

        // Merge and sort by last message
        $conversations = $directConversations->merge($groupConversations)
            ->sortByDesc('last_message_at');

        return view('chat.index', compact('conversations'));
    }

    public function getConversations()
    {
        // When polling for conversations we return all known conversations so that
        // cleared threads continue to be tracked client‑side.  The client will render
        // an empty preview if there is no latest_message.
        
        // Get direct conversations
        $directConversations = Conversation::where(function($q) {
                $q->where('user1_id', auth()->id())
                  ->orWhere('user2_id', auth()->id());
            })
            ->with(['user1', 'user2', 'latestMessage.sender'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

        // Get group conversations
        $groupIds = auth()->user()->groupMemberships()->pluck('group_id');
        $groupConversations = Conversation::where('is_group', true)
            ->whereIn('group_id', $groupIds)
            ->with(['group.members.user', 'latestMessage.sender'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

        // Merge and sort by last message
        $conversations = $directConversations->merge($groupConversations)
            ->sortByDesc('last_message_at')
            ->values()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'slug' => $conversation->slug,
                    'is_group' => (bool) $conversation->is_group,
                    'user1_id' => $conversation->user1_id,
                    'user2_id' => $conversation->user2_id,
                    'last_message_at' => $conversation->last_message_at ? \Carbon\Carbon::parse($conversation->last_message_at)->toISOString() : null,
                    'unread_count' => $conversation->unread_count,
                    'other_user' => $conversation->other_user ? [
                            'id' => $conversation->other_user->id,
                            'username' => $conversation->other_user->username,
                            'avatar_url' => $conversation->other_user->avatar_url,
                            'is_online' => $conversation->other_user->is_online,
                            'last_active' => $conversation->other_user->last_active ? \Carbon\Carbon::parse($conversation->other_user->last_active)->toISOString() : null,
                        ] : null,
                    'typing' => $conversation->other_user ? (bool) cache()->get("typing:{$conversation->id}:{$conversation->other_user->id}", false) : false,
                    'latest_message' => $conversation->latestMessage ? [
                        'id' => $conversation->latestMessage->id,
                        'content' => $conversation->latestMessage->content,
                        'type' => $conversation->latestMessage->type,
                        'media_path' => $conversation->latestMessage->media_path,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'sender_username' => $conversation->latestMessage->sender->username ?? null,
                        'created_at' => $conversation->latestMessage->created_at ? \Carbon\Carbon::parse($conversation->latestMessage->created_at)->toISOString() : null,
                        'read_at' => $conversation->latestMessage->read_at,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get updated conversations for polling (includes new messages and unread counts)
     */
    public function getUpdatedConversations(Request $request)
    {
        $lastMessageAt = $request->query('last_message_at');
        $lastUnreadCheck = $request->query('last_unread_check');

        // Get direct conversations
        $directQuery = Conversation::where('user1_id', auth()->id())
            ->orWhere('user2_id', auth()->id())
            ->with(['user1', 'user2', 'latestMessage.sender'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC');

        // If we have a timestamp, only get conversations with new activity
        if ($lastMessageAt) {
            $directQuery->where('last_message_at', '>', $lastMessageAt);
        }

        $directConversations = $directQuery->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

        // Get group conversations
        $groupIds = auth()->user()->groupMemberships()->pluck('group_id');
        $groupQuery = Conversation::where('is_group', true)
            ->whereIn('group_id', $groupIds)
            ->with(['group.members.user', 'latestMessage.sender'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC');

        if ($lastMessageAt) {
            $groupQuery->where('last_message_at', '>', $lastMessageAt);
        }

        $groupConversations = $groupQuery->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

        // Merge and sort by last message
        $conversations = $directConversations->merge($groupConversations)
            ->sortByDesc('last_message_at')
            ->values()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'slug' => $conversation->slug,
                    'is_group' => (bool) $conversation->is_group,
                    'last_message_at' => $conversation->last_message_at ? \Carbon\Carbon::parse($conversation->last_message_at)->toISOString() : null,
                    'unread_count' => $conversation->unread_count,
                    'other_user' => $conversation->other_user ? [
                        'id' => $conversation->other_user->id,
                        'username' => $conversation->other_user->username,
                        'avatar_url' => $conversation->other_user->avatar_url,
                    ] : null,
                    'typing' => $conversation->other_user ? (bool) cache()->get("typing:{$conversation->id}:{$conversation->other_user->id}", false) : false,
                    'latest_message' => $conversation->latestMessage ? [
                        'id' => $conversation->latestMessage->id,
                        'content' => strip_tags($conversation->latestMessage->content),
                        'type' => $conversation->latestMessage->type,
                        'media_path' => $conversation->latestMessage->media_path,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'sender_username' => $conversation->latestMessage->sender->username ?? null,
                        'created_at' => $conversation->latestMessage->created_at ? \Carbon\Carbon::parse($conversation->latestMessage->created_at)->toISOString() : null,
                        'read_at' => $conversation->latestMessage->read_at ? \Carbon\Carbon::parse($conversation->latestMessage->read_at)->toISOString() : null,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
            'timestamp' => now()->toISOString(),
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
            ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();

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
                    'username' => $message->sender->username,
                    'avatar_url' => $message->sender->avatar_url,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $formattedMessages,
        ]);
    }

    public function show($conversation)
    {
        // Try to find by ID first (for numeric), then by slug
        if (is_numeric($conversation)) {
            $conversation = Conversation::findOrFail($conversation);
        } else {
            $conversation = Conversation::where('slug', $conversation)->firstOrFail();
        }
        
        // Check if user has access to this conversation
        if (!$conversation->isMember(auth()->id())) {
            abort(403);
        }

        // Mark messages as read
        Message::markConversationAsRead($conversation->id, auth()->id());

        $userId = auth()->id();

        // Load messages - exclude messages deleted for current user, include soft-deleted for "deleted by sender"
        // If the conversation was just cleared, we reset last_message_at to null. In that
        // case we don't want to fetch any records (soft-deleted or otherwise) so the UI
        // shows an empty chat rather than a series of "message deleted" placeholders.
        if ($conversation->last_message_at === null) {
            $messages = collect();
        } else {
            $messages = $conversation->messages()
                ->with('sender.profile')
                ->withTrashed()  // Include soft-deleted to show "message deleted"
                ->where(function($q) use ($userId) {
                    // Messages visible to everyone or to current user
                    $q->whereNull('visible_to')
                      ->orWhere('visible_to', $userId);
                })
                ->where(function($q) use ($userId) {
                    // Exclude messages deleted for current user
                    $q->whereNull('deleted_for')  // Not deleted for anyone
                      ->orWhereJsonDoesntContain('deleted_for', $userId)  // Or not deleted for this user
                      ->orWhere('deleted_by_sender', true);  // Or deleted by sender (show "message deleted")
                })
                ->orderBy('id', 'asc')
                ->limit(50)
                ->get();
        }

        return view('chat.show', compact('conversation', 'messages'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        // Eager load participants to avoid N+1 queries
        $conversation->load(['user1', 'user2']);

        // Check if user has access to this conversation
        if (!$conversation->isMember(auth()->id())) {
            abort(403);
        }

        $currentUser = auth()->user();
        
        // CRITICAL FIX: Check if sender is blocked by any participant or has blocked any participant
        foreach ($conversation->participants as $participant) {
            if ($participant->id !== $currentUser->id) {
                // Check if current user has blocked this participant
                if ($currentUser->isBlocking($participant)) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.cannot_send_message_blocked_user')
                    ], 403);
                }
                
                // Check if this participant has blocked current user
                if ($participant->isBlocking($currentUser)) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.user_has_blocked_you')
                    ], 403);
                }
            }
        }

        $request->validate([
            'content' => 'nullable|string|max:1000',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm|max:51200', // 50MB max
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

        // Handle media upload (supports multiple files)
        if ($request->hasFile('media')) {
            $mediaItems = [];
            $files = $request->file('media');
            
            // Ensure files is an array
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $mimeType = $file->getMimeType();
                $originalFilename = $file->getClientOriginalName();
                $fileSize = $file->getSize();

                // Determine media type
                if (str_starts_with($mimeType, 'image/')) {
                    $mediaType = 'image';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $mediaType = 'video';
                } else {
                    $mediaType = 'file';
                }

                // Store the file
                $path = $file->store('chat/media', 'public');
                
                $mediaItems[] = [
                    'type' => $mediaType,
                    'path' => $path,
                    'original_filename' => $originalFilename,
                    'size' => $fileSize,
                ];
            }

            // Set message type based on first media item
            $messageData['type'] = $mediaItems[0]['type'] ?? 'text';
            // Store media items as JSON
            $messageData['media_path'] = json_encode($mediaItems);
        }

        $message = Message::create($messageData);

        // Update conversation last message timestamp
        $conversation->update(['last_message_at' => now()]);

        // Create notifications for all recipients
        $recipientIds = $conversation->getRecipients(auth()->id());
        
        foreach ($recipientIds as $recipientId) {
            \App\Http\Controllers\NotificationController::createMessageNotification(
                $recipientId,
                auth()->user(),
                $message
            );
        }

        // Ensure sender includes accessor-based attributes for immediate client rendering
        $message->load('sender.profile');
        if ($message->sender) {
            $message->sender->append('avatar_url');
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function startConversation($userId)
    {
        $user = User::findOrFail($userId);
        $currentUser = auth()->user();

        // Can't start conversation with yourself
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => __('messages.cannot_chat_with_self')], 400);
            }
            return redirect()->back()->with('error', __('messages.cannot_chat_with_self'));
        }

        // CRITICAL FIX: Check if users have blocked each other
        if ($currentUser->isBlocking($user)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => __('messages.cannot_chat_with_blocked_user')], 403);
            }
            return redirect()->back()->with('error', __('messages.cannot_chat_with_blocked_user'));
        }
        
        if ($user->isBlocking($currentUser)) {
            if (request()->expectsJson()) {
                return response()->json(['error' => __('messages.user_has_blocked_you')], 403);
            }
            return redirect()->back()->with('error', __('messages.user_has_blocked_you'));
        }

        // Check if conversation already exists
        $conversation = Conversation::getConversationBetween(auth()->id(), $user->id);

        if (!$conversation) {
            $conversation = Conversation::createConversation(auth()->id(), $user->id);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'slug' => $conversation->slug,
                'conversation_id' => $conversation->id,
                'url' => route('chat.show', $conversation)
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }

    public function getMessages(Conversation $conversation, Request $request)
    {
        // Check if user has access to this conversation
        if (!$conversation->isMember(auth()->id())) {
            abort(403);
        }

        // Get messages after a specific ID for incremental polling
        $afterId = $request->query('after_id', $request->query('after'));

        // Get ALL new messages (including own) to prevent race conditions when sending fast
        // This ensures messages aren't missed due to timing issues between send and poll
        $query = $conversation->messages()
            ->with('sender.profile')
            ->where(function($q) {
                $q->whereNull('visible_to')  // Messages visible to everyone
                  ->orWhere('visible_to', auth()->id());  // Or messages visible to current user
            })
            ->orderBy('id', 'asc');

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        $newMessages = $query->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at ? \Carbon\Carbon::parse($message->created_at)->toISOString() : null,
                    'type' => $message->type,
                    'media_path' => $message->media_path,
                    'sender_id' => $message->sender_id,
                    'sender' => [
                        'id' => $message->sender->id,
                        'username' => $message->sender->username,
                        'avatar_url' => $message->sender->avatar_url,
                    ],
                    'read_at' => $message->read_at ? \Carbon\Carbon::parse($message->read_at)->toISOString() : null,
                ];
            });

        // Get recent messages for deletion detection (includes all messages, not just current user's)
        // Include trashed messages so clients can detect deletions
        $recentMessages = $conversation->messages()
            ->withTrashed()  // Include soft-deleted messages
            ->orderBy('created_at', 'desc')
            ->limit(100)  // Get more messages to detect deletions
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'read_at' => $message->read_at,
                    'created_at' => $message->created_at,
                    'deleted_at' => $message->deleted_at ? \Carbon\Carbon::parse($message->deleted_at)->toISOString() : null,
                    'deleted_for' => $message->deleted_for,
                    'deleted_by_sender' => $message->deleted_by_sender,
                ];
            });

        return response()->json([
            'success' => true,
            'new_messages' => $newMessages,
            'recent_messages' => $recentMessages
        ]);
    }

    public function markAsRead(Conversation $conversation)                                   
    {                                                                                        
        // Check if user has access to this conversation                                     
        if (!$conversation->isMember(auth()->id())) {                                        
            abort(403);                                                                      
        }                                                                                    
                                                                                             
        // Get messages that will be marked as read                                          
        $messagesToMark = Message::where('conversation_id', $conversation->id)               
            ->where('sender_id', '!=', auth()->id())                                         
            ->whereNull('read_at')                                                           
            ->pluck('id');                                                                   
                                                                                             
        $count = Message::markConversationAsRead($conversation->id, auth()->id());           
                                                                                             
        return response()->json([                                                            
            'success' => true,                                                               
            'read_count' => $count,                                                          
            'read_message_ids' => $messagesToMark->toArray()                                 
        ]);                                                                                  
    }

    public function destroy(Request $request, Message $message)
    {
        $userId = auth()->id();

        // Check if message belongs to a conversation the user is part of
        $conversation = $message->conversation;

        // For group conversations, check membership via group
        if ($conversation->is_group) {
            if (!$conversation->group || !$conversation->group->hasMember(auth()->user())) {
                abort(403, 'You are not a member of this conversation.');
            }
        } else {
            // For direct messages, check if user is user1 or user2
            if ($conversation->user1_id != $userId && $conversation->user2_id != $userId) {
                abort(403, 'You are not a member of this conversation.');
            }
        }

        $deleteType = $request->input('type', 'me'); // 'me' or 'everyone'
        $messageId = $message->id;
        $conversationSlug = $conversation->slug;
        $isSender = $message->sender_id == $userId;

        if ($deleteType === 'everyone') {
            // Only sender can delete for everyone
            if (!$isSender) {
                abort(403, 'Only the sender can delete for everyone.');
            }
            
            // Delete for everyone - soft delete and mark
            $message->update([
                'deleted_by_sender' => true,
            ]);
            $message->delete(); // Soft delete
            
            // Update conversation last_message_at to previous non-deleted message (or null)
            $latest = Message::where('conversation_id', $conversation->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->first();

            $conversation->update(['last_message_at' => $latest ? $latest->created_at : null]);

            // Broadcast to all users in conversation
            $this->broadcastMessageDeleted($message, $conversation);
        } else {
            // Delete for me only: add current user to deleted_for list.
            $deletedFor = $message->deleted_for ?? [];
            if (!in_array($userId, $deletedFor)) {
                $deletedFor[] = $userId;
                $message->update(['deleted_for' => $deletedFor]);
            }

            // We used to automatically soft-delete when the sender removed their
            // own message, but that caused the other participant to see
            // "message deleted". The expectation for "delete for me" is that
            // only the requester loses visibility. Messages are now only
            // soft-deleted when the sender explicitly chooses the *everyone*
            // option above (which also sets deleted_by_sender).
        }

        // Return the deleted message info for real-time updates
        return response()->json([
            'success' => true,
            'deleted_message_id' => $messageId,
            'conversation_slug' => $conversationSlug,
            'delete_type' => $deleteType,
            'deleted_for' => $message->deleted_for,
            'deleted_by_sender' => $message->deleted_by_sender,
        ]);
    }

    /**
     * Broadcast message deleted event
     */
    private function broadcastMessageDeleted($message, $conversation)
    {
        // Get all user IDs in the conversation
        $userIds = [];
        if ($conversation->is_group && $conversation->group) {
            $userIds = $conversation->group->members()->pluck('user_id')->toArray();
        } else {
            $userIds = [$conversation->user1_id, $conversation->user2_id];
        }

        // For simplicity, we'll rely on polling for updates
        // In a production app, you might want to use Laravel Echo/Pusher
    }

    public function clearChat(Conversation $conversation)
    {
        // Check if user is part of this conversation
        if ($conversation->user1_id !== auth()->id() && $conversation->user2_id !== auth()->id()) {
            abort(403);
        }

        // Delete all messages in the conversation (full chat deletion)
        // use forceDelete so they are permanently removed instead of soft-deleted. 
        // soft-deleted records would still be returned by withTrashed() and render as
        // "message deleted" placeholders, which is confusing after a clear-all action.
        Message::where('conversation_id', $conversation->id)->forceDelete();

        // Reset conversation timestamp to indicate it's empty
        $conversation->update(['last_message_at' => null]);

        return response()->json(['success' => true]);
    }

    /**
     * Get new messages for toast notification (polling)
     */
    public function getNewMessagesForToast()
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        // Get user's group IDs for group conversations
        $groupIds = $user->groupMemberships()->pluck('group_id');
        
        // Get unread messages not from current user, not yet notified
        // Include both direct messages and group messages
        $messages = Message::whereHas('conversation', function($query) use ($userId, $groupIds) {
                $query->where(function($q) use ($userId, $groupIds) {
                    // Direct messages
                    $q->where(function($dq) use ($userId) {
                        $dq->where('user1_id', $userId)
                           ->orWhere('user2_id', $userId);
                    });
                    
                    // Group messages - user must be a member of the group
                    if ($groupIds->count() > 0) {
                        $q->orWhereIn('group_id', $groupIds);
                    }
                });
            })
            ->where('sender_id', '!=', $userId)
            ->whereNull('notified_at')
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();
        
        // Mark messages as notified
        if ($messages->count() > 0) {
            Message::whereIn('id', $messages->pluck('id'))
                ->update(['notified_at' => now()]);
        }
        
        $formatted = $messages->map(function($msg) {
            $conversation = $msg->conversation;
            $isGroup = $conversation && $conversation->is_group;
            $groupName = $isGroup && $conversation->group ? $conversation->group->name : null;
            
            return [
                'id' => $msg->id,
                'sender_username' => $msg->sender->username,
                'content' => substr($msg->content, 0, 50) . (strlen($msg->content) > 50 ? '...' : ''),
                'is_group' => $isGroup,
                'group_name' => $groupName,
                'conversation_id' => $msg->conversation_id,
            ];
        });
        
        return response()->json(['new_messages' => $formatted]);
    }

    /**
     * Get statuses (read/delivered) for specific message IDs.
     * This is used by the frontend to refresh read receipts for messages
     * currently visible in the DOM, avoiding the 100-message limit of
     * the standard /messages endpoint.
     */
    public function getMessageStatuses(Conversation $conversation, Request $request)
    {
        // Ensure user is part of conversation
        if (!$conversation->isMember(auth()->id())) {
            return response()->json(['success' => false], 403);
        }

        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:messages,id'
        ]);

        $ids = $request->input('message_ids');
        $statuses = Message::whereIn('id', $ids)
            ->where('conversation_id', $conversation->id)
            ->get(['id', 'read_at', 'delivered_at'])
            ->map(function($m) {
                // sometimes the attributes may be returned as strings instead of
                // Carbon instances (e.g. legacy rows or manual SQL). Safely
                // coerce them before calling toISOString().
                $readAt = $m->read_at;
                if ($readAt && !($readAt instanceof \Illuminate\Support\Carbon)) {
                    $readAt = \Illuminate\Support\Carbon::parse($readAt);
                }
                $deliveredAt = $m->delivered_at;
                if ($deliveredAt && !($deliveredAt instanceof \Illuminate\Support\Carbon)) {
                    $deliveredAt = \Illuminate\Support\Carbon::parse($deliveredAt);
                }
                return [
                    'id' => $m->id,
                    'read_at' => $readAt ? $readAt->toISOString() : null,
                    'delivered_at' => $deliveredAt ? $deliveredAt->toISOString() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'statuses' => $statuses
        ]);
    }

    /**
     * Confirm message delivery (mark as delivered)
     */
    public function confirmDelivery(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id'
        ]);

        $message = Message::find($request->message_id);

        // Check if user has access to this conversation
        if (!$message->conversation->isMember(auth()->id())) {
            return response()->json(['success' => false], 403);
        }

        // Update delivered_at timestamp
        $message->update(['delivered_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Send typing indicator
     */
    public function sendTypingIndicator(Request $request, Conversation $conversation)
    {
        $request->validate([
            'is_typing' => 'boolean'
        ]);

        // Check if user is member of conversation
        if (!$conversation->isMember(auth()->id())) {
            return response()->json(['success' => false], 403);
        }

        // Store typing status in cache for 5 seconds
        $cacheKey = "typing:{$conversation->id}:" . auth()->id();

        if ($request->is_typing) {
            // Store user info for display
            cache()->put($cacheKey, [
                'user_id' => auth()->id(),
                'username' => auth()->user()->username,
                'timestamp' => now()->timestamp,
            ], 5);
        } else {
            cache()->forget($cacheKey);
        }

        return response()->json([
            'success' => true,
            'is_typing' => $request->is_typing
        ]);
    }

    /**
     * Get typing status for conversation
     */
    public function getTypingStatus(Conversation $conversation)
    {
        // Check if user is member of conversation
        if (!$conversation->isMember(auth()->id())) {
            return response()->json(['success' => false], 403);
        }

        // Get all typing users in this conversation
        $typingUsers = [];
        
        // For direct messages, check the other user
        if (!$conversation->is_group && $conversation->other_user) {
            $cacheKey = "typing:{$conversation->id}:{$conversation->other_user->id}";
            $data = cache()->get($cacheKey);
            if ($data && is_array($data) && isset($data['user_id'])) {
                $typingUsers[] = [
                    'user_id' => $data['user_id'],
                    'username' => $data['username'],
                ];
            }
        } else {
            // For group chats, check all group members
            $groupMembers = $conversation->group?->members()->with('user')->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();
            foreach ($groupMembers as $member) {
                if ($member->user_id !== auth()->id()) {
                    $cacheKey = "typing:{$conversation->id}:{$member->user_id}";
                    $data = cache()->get($cacheKey);
                    if ($data && is_array($data) && isset($data['user_id'])) {
                        $typingUsers[] = [
                            'user_id' => $data['user_id'],
                            'username' => $data['username'],
                        ];
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'typing_users' => $typingUsers,
            'typing_count' => count($typingUsers)
        ]);
    }
}
