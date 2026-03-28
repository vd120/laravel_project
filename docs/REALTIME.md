# Nexus - Real-Time Features

Complete documentation for real-time features in Nexus social networking platform.

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Polling Implementation](#polling-implementation)
4. [Chat Messages](#chat-messages)
5. [Notifications](#notifications)
6. [Online Status](#online-status)
7. [Typing Indicators](#typing-indicators)
8. [Conversation Updates](#conversation-updates)
9. [Performance Optimization](#performance-optimization)
10. [API Reference](#api-reference)

---

## Overview

Nexus implements real-time features using **polling-based architecture** instead of WebSockets. This approach provides real-time functionality without the complexity of WebSocket infrastructure.

### Why Polling?

**Advantages:**
- **Simplicity**: No WebSocket server required
- **Compatibility**: Works with all hosting providers
- **Firewall-Friendly**: Uses standard HTTP/HTTPS ports
- **Easy Scaling**: No sticky sessions required
- **Debugging**: Standard HTTP request/response cycle

### Trade-offs

- **Latency**: 2-10 second delay (configurable)
- **Server Load**: More HTTP requests than WebSocket
- **Battery**: Higher mobile battery consumption
- **Bandwidth**: More overhead than persistent connection

### Polling Intervals (VERIFIED)

- **Chat Messages**: 1 second (Source: `RealTimeConfig.chatRoomInterval`)
- **Conversations List**: 1 second (Source: `RealTimeConfig.chatListInterval`)
- **Notifications**: 2 seconds (Source: `RealTimeConfig.notificationsInterval`)
- **Online Status**: 10 seconds (Source: `RealTimeConfig.onlineStatusInterval`)
- **Typing Indicators**: 1 second with 5s cache (Implementation)
- **Account Status**: 5 seconds (Source: `RealTimeConfig.accountStatusInterval`)

**Note:** Actual polling intervals are defined in `resources/js/legacy/realtime.js` in the `window.RealTimeConfig` object.

---

## Architecture

### Polling Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      Real-Time Polling Architecture              │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Browser    │     │   Browser    │     │   Browser    │
│   Client A   │     │   Client B   │     │   Client C   │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘
       │                    │                    │
       │  Poll (1-10s)      │  Poll (1-10s)      │  Poll (1-10s)
       ▼                    ▼                    ▼
┌──────────────────────────────────────────────────────────────────┐
│                     Laravel Application                           │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │              RealtimeService (Backend)                      │  │
│  │  • getNewMessages()                                        │  │
│  │  • getUnreadNotifications()                                │  │
│  │  • getOnlineUsers()                                        │  │
│  │  • getTypingStatus()                                       │  │
│  └────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
       │                    │                    │
       ▼                    ▼                    ▼
┌──────────────────────────────────────────────────────────────────┐
│                        Database Layer                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │ messages │  │notifications│  │  users   │  │  cache   │        │
│  │  table   │  │   table   │  │(online)  │  │ (typing) │        │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘        │
└──────────────────────────────────────────────────────────────────┘
```

### Component Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    Real-Time Components                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Frontend (JavaScript):                                          │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ realtime.js     │  │ ui-utils.js     │  │ home.js         │ │
│  │ (Core polling)  │  │ (Online status) │  │ (Feed updates)  │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
│                                                                  │
│  Backend (Laravel):                                              │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ RealtimeService │  │ ChatController  │  │ UserController  │ │
│  │ (Business logic)│  │ (Messages)      │  │ (Online status) │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
│                                                                  │
│  Database:                                                       │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  messages, notifications, users, cache tables            │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Polling Implementation

### Frontend: Realtime.js Module

Location: `resources/js/legacy/realtime.js`

```javascript
/**
 * Real-time polling module for Nexus
 * Handles chat, notifications, and online status updates
 */

(function() {
    'use strict';

    // Configuration (Note: Actual values from RealTimeConfig)
    const POLLING_INTERVALS = {
        MESSAGES: 1000,        // 1 second (RealTimeConfig.chatRoomInterval)
        NOTIFICATIONS: 2000,   // 2 seconds (RealTimeConfig.notificationsInterval)
        ONLINE_STATUS: 10000,  // 10 seconds (RealTimeConfig.onlineStatusInterval)
        TYPING: 1000,          // 1 second with 5s cache
        CONVERSATIONS: 1000    // 1 second (RealTimeConfig.chatListInterval)
    };

    // State
    let pollingTimers = {};
    let lastMessageId = {};
    let lastNotificationCheck = {};

    /**
     * Initialize all real-time features
     */
    function initialize() {
        if (window.isAuthenticated) {
            startMessagePolling();
            startNotificationPolling();
            startOnlineStatusPolling();
            startConversationPolling();

            // Handle page unload
            window.addEventListener('beforeunload', cleanup);
        }
    }

    /**
     * Start polling for new messages
     */
    function startMessagePolling() {
        const conversationId = window.currentConversationId;
        if (!conversationId) return;

        pollingTimers.messages = setInterval(() => {
            fetchNewMessages(conversationId);
        }, POLLING_INTERVALS.MESSAGES);
    }

    /**
     * Fetch new messages from server
     */
    function fetchNewMessages(conversationId) {
        const afterId = lastMessageId[conversationId] || 0;

        fetch(`/chat/${conversationId}/messages?after=${afterId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    appendMessagesToChat(data.messages);
                    lastMessageId[conversationId] =
                        data.messages[data.messages.length - 1].id;

                    // Update conversation list
                    updateConversationLastMessage(conversationId, data.messages[0]);
                }
            })
            .catch(error => console.error('Error polling messages:', error));
    }

    /**
     * Start polling for notifications
     */
    function startNotificationPolling() {
        pollingTimers.notifications = setInterval(() => {
            fetchUnreadNotifications();
        }, POLLING_INTERVALS.NOTIFICATIONS);
    }

    /**
     * Fetch unread notifications
     */
    function fetchUnreadNotifications() {
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(data.count);

                if (data.count > 0 && data.newNotifications) {
                    showNotificationToast(data.newNotifications);
                }
            })
            .catch(error => console.error('Error polling notifications:', error));
    }

    /**
     * Start polling for online status
     */
    function startOnlineStatusPolling() {
        // Send initial heartbeat
        updateMyOnlineStatus();

        pollingTimers.onlineStatus = setInterval(() => {
            updateMyOnlineStatus();
            pollOtherUsersStatus();
        }, POLLING_INTERVALS.ONLINE_STATUS);
    }

    /**
     * Update own online status
     */
    function updateMyOnlineStatus() {
        fetch('/user/online-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        }).catch(error => console.error('Error updating online status:', error));
    }

    /**
     * Poll other users' online status
     */
    function pollOtherUsersStatus() {
        const userIds = collectVisibleUserIds();
        if (userIds.length === 0) return;

        fetch('/user/online-status/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            updateOnlineIndicators(data.statuses);
        })
        .catch(error => console.error('Error polling user status:', error));
    }

    /**
     * Cleanup polling timers on page unload
     */
    function cleanup() {
        Object.values(pollingTimers).forEach(timer => {
            clearInterval(timer);
        });
    }

    /**
     * Get CSRF token from meta tag
     */
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
```

---

## Chat Messages

### Implementation

**Backend:** `app/Http/Controllers/ChatController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Services\RealtimeService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get messages for a conversation
     */
    public function getMessages(Conversation $conversation, Request $request)
    {
        $afterId = $request->query('after', 0);
        
        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with('sender')
            ->latest('id')
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message
     */
    public function store(Conversation $conversation, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required_without:media|max:1000',
            'media' => 'nullable|file|max:51200',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'content' => $validated['content'] ?? '',
            'media_type' => $validated['media']?->getClientMimeType(),
            'media_path' => $validated['media']?->store('messages', 'public'),
        ]);

        // Update conversation last message
        $conversation->update([
            'last_message_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Conversation $conversation)
    {
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Send typing indicator
     */
    public function sendTypingIndicator(Conversation $conversation, Request $request)
    {
        app(RealtimeService::class)->setTypingIndicator(
            $conversation->id,
            auth()->id()
        );

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get typing status
     */
    public function getTypingStatus(Conversation $conversation)
    {
        $typingUsers = app(RealtimeService::class)->getTypingUsers(
            $conversation->id,
            auth()->id()
        );

        return response()->json([
            'typing' => $typingUsers,
        ]);
    }
}
```

### Message Flow

```
┌──────────────┐
│   User A     │
│   Sends      │
│   Message    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST /chat/ │
│  {conv}      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Database    │
│  Insert      │
│  Message     │
└──────┬───────┘
       │
       ▼
┌──────────────┐     ┌──────────────┐
│   User B     │◀────│   User B     │
│   Polling    │     │   Sees New   │
│   (every 1s) │     │   Message    │
└──────────────┘     └──────────────┘
```

---

## Notifications

### Implementation

**Backend:** `app/Http/Controllers/Api/NotificationController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $count = auth()->user()->notifications()
            ->whereNull('read_at')
            ->count();

        $newNotifications = auth()->user()->notifications()
            ->whereNull('read_at')
            ->where('created_at', '>', now()->subSeconds(3))
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'count' => $count,
            'newNotifications' => $newNotifications,
        ]);
    }

    /**
     * Get realtime updates
     */
    public function getRealtimeUpdates()
    {
        $notifications = auth()->user()->notifications()
            ->where('created_at', '>', request()->query('since', now()->subHour()))
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        auth()->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
```

### Notification Polling Flow

```javascript
// resources/js/legacy/realtime.js
function pollNotifications() {
    setInterval(() => {
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                // Update badge
                updateNotificationBadge(data.count);
                
                // Show toast for new notifications
                if (data.count > 0 && data.newNotifications) {
                    showNotificationToast(data.newNotifications);
                }
            });
    }, 2000); // 2 second polling (RealTimeConfig.notificationsInterval)
}
```

---

## Online Status

### Implementation

**Backend:** `app/Http/Controllers/UserController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Update own online status
     */
    public function updateOnlineStatus(Request $request)
    {
        auth()->user()->update([
            'is_online' => true,
            'last_active' => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get single user's online status
     */
    public function getOnlineStatus(User $user)
    {
        return response()->json([
            'is_online' => $user->is_online,
            'last_active' => $user->last_active?->diffForHumans(),
        ]);
    }

    /**
     * Get multiple users' online status (batch)
     */
    public function getMultipleOnlineStatus(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        
        $statuses = User::whereIn('id', $userIds)
            ->get(['id', 'is_online', 'last_active'])
            ->mapWithKeys(fn($user) => [
                $user->id => [
                    'is_online' => $user->is_online,
                    'last_active' => $user->last_active?->diffForHumans(),
                ]
            ]);

        return response()->json([
            'statuses' => $statuses,
        ]);
    }

    /**
     * Set offline status
     */
    public function setOfflineStatus()
    {
        auth()->user()->update([
            'is_online' => false,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
```

### Online Status Flow

```javascript
// resources/js/legacy/realtime.js
function startOnlineStatusPolling() {
    // Send heartbeat
    updateMyOnlineStatus();

    // Poll every 10 seconds
    setInterval(() => {
        updateMyOnlineStatus();
        pollOtherUsersStatus();
    }, 10000);
}

function updateMyOnlineStatus() {
    fetch('/user/online-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    });
}

function pollOtherUsersStatus() {
    const userIds = collectVisibleUserIds();
    if (userIds.length === 0) return;

    fetch('/user/online-status/batch', {
        method: 'POST',
        body: JSON.stringify({ user_ids: userIds })
    })
    .then(response => response.json())
    .then(data => {
        updateOnlineIndicators(data.statuses);
    });
}
```

---

## Typing Indicators

### Implementation

**Backend:** `app/Services/RealtimeService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RealtimeService
{
    /**
     * Set typing indicator in cache
     */
    public function setTypingIndicator(int $conversationId, int $userId): void
    {
        $key = "typing:{$conversationId}:{$userId}";
        Cache::set($key, now()->timestamp, 5); // 5 second TTL
    }

    /**
     * Get typing users for a conversation
     */
    public function getTypingUsers(int $conversationId, int $excludeUserId): array
    {
        $typingUsers = [];
        $pattern = "typing:{$conversationId}:*";

        // Get all typing keys for this conversation
        $keys = Cache::getMultiple($pattern);
        
        foreach ($keys as $key => $timestamp) {
            // Check if still valid (within 5 seconds)
            if (now()->timestamp - $timestamp < 5) {
                $userId = (int) last(explode(':', $key));
                if ($userId !== $excludeUserId) {
                    $typingUsers[] = $userId;
                }
            }
        }

        return $typingUsers;
    }

    /**
     * Clear typing indicator
     */
    public function clearTypingIndicator(int $conversationId, int $userId): void
    {
        $key = "typing:{$conversationId}:{$userId}";
        Cache::forget($key);
    }
}
```

### Typing Indicator Flow

```javascript
// resources/js/legacy/realtime.js
let typingTimeout;
let isTyping = false;

messageInput.addEventListener('input', function() {
    if (!isTyping) {
        sendTypingIndicator();
        isTyping = true;
    }

    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        isTyping = false;
    }, 5000); // Stop sending after 5 seconds
});

function sendTypingIndicator() {
    fetch(`/chat/${conversationId}/typing`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ is_typing: true })
    });
}

// Poll for typing status
function pollTypingStatus() {
    setInterval(() => {
        fetch(`/chat/${conversationId}/typing-status`)
            .then(response => response.json())
            .then(data => {
                if (data.typing.length > 0) {
                    showTypingIndicator(data.typing);
                } else {
                    hideTypingIndicator();
                }
            });
    }, 1000); // 1 second polling
}
```

---

## Conversation Updates

### Implementation

**Backend:** `app/Http/Controllers/ChatController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get updated conversations
     */
    public function getUpdatedConversations(Request $request)
    {
        $lastUpdate = $request->query('last_update');
        
        $conversations = auth()->user()->conversations()
            ->where(function($query) use ($lastUpdate) {
                if ($lastUpdate) {
                    $query->where('updated_at', '>', $lastUpdate);
                }
            })
            ->with(['recipient', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Check for new conversations
        $newConversations = null;
        if ($lastUpdate) {
            $newConversations = auth()->user()->conversations()
                ->where('created_at', '>', $lastUpdate)
                ->with(['recipient'])
                ->get();
        }

        return response()->json([
            'conversations' => $conversations,
            'newConversations' => $newConversations,
        ]);
    }
}
```

---

## Performance Optimization

### Server Load Calculation

**Example Scenario:** 1000 concurrent users

**Server Load Calculation:**
- **Chat Messages**: 1000 users / 1s = 1000 req/s
- **Notifications**: 1000 users / 2s = 500 req/s
- **Online Status**: 1000 users / 10s = 100 req/s
- **Total**: 1600 req/s

**With Optimization:**
- **Conditional Polling** (tab hidden): -50% load
- **Batch Requests** (online status): -30% load
- **Effective Load**: ~800-1100 req/s (manageable)

### Optimization Strategies

1. **Conditional Polling**
   - Only poll when user is on relevant page
   - Pause polling when tab is hidden (Page Visibility API)
   - Reduce frequency for background tabs

2. **Efficient Queries**
   - Use database indexes on `created_at`, `user_id`
   - Limit result sets (e.g., `LIMIT 20`)
   - Use `WHERE id > last_id` instead of timestamps

3. **Caching**
   - Cache typing indicators (5s TTL)
   - Cache online status (10s TTL)
   - Use Eloquent eager loading

4. **Batch Requests**
   - Batch online status requests
   - Combine multiple updates in single response

### Page Visibility API

```javascript
// Pause polling when tab is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Tab is hidden - reduce polling frequency
        pausePolling();
    } else {
        // Tab is visible - resume normal polling
        resumePolling();
    }
});

function pausePolling() {
    Object.values(pollingTimers).forEach(timer => {
        clearInterval(timer);
    });
}

function resumePolling() {
    initialize();
}
```

---

## API Reference

### Messages

- **GET** `/chat/{conversation}/messages` - Get messages (with `?after=id`) (Auth required)
- **POST** `/chat/{conversation}` - Send message (Auth required)
- **POST** `/chat/{conversation}/read` - Mark messages as read (Auth required)
- **POST** `/chat/message/delivered` - Confirm message delivery (Auth required)
- **POST** `/chat/{conversation}/typing` - Send typing indicator (Auth required)
- **GET** `/chat/{conversation}/typing-status` - Get typing status (Auth required)

### Notifications

- **GET** `/api/notifications` - Get all notifications (Auth required)
- **GET** `/api/notifications/unread-count` - Get unread count (Auth required)
- **GET** `/api/notifications/realtime-updates` - Get new notifications (Auth required)
- **POST** `/api/notifications/{id}/read` - Mark as read (Auth required)
- **POST** `/api/notifications/mark-all-read` - Mark all as read (Auth required)
- **DELETE** `/api/notifications/{id}` - Delete notification (Auth required)

### Online Status

- **POST** `/user/online-status` - Update own status (Auth required)
- **GET** `/user/{user}/online-status` - Get single user status (Auth required)
- **POST** `/user/online-status/batch` - Get multiple users status (Auth required)
- **POST** `/user/online-status/offline` - Set offline status (Auth required)

### Conversations

- **GET** `/chat/conversations` - Get all conversations (Auth required)
- **GET** `/chat/conversations/updated` - Get updated conversations (Auth required)
- **GET** `/api/conversations` - API: Get conversations (Auth required)

---

## Migration to WebSockets (Future)

If you need to migrate to WebSockets in the future:

### Recommended Stack

- **Laravel Reverb**: Laravel's native WebSocket server
- **Laravel Echo**: Client-side WebSocket events
- **Pusher**: Hosted WebSocket service (alternative)

### Migration Steps

1. Install Laravel Reverb: `composer require laravel/reverb`
2. Configure broadcasting in `config/broadcasting.php`
3. Replace polling intervals with Echo listeners
4. Update JavaScript to use `Echo.private()` channels
5. Test thoroughly before deployment

### Example Migration

**Before (Polling):**
```javascript
setInterval(() => {
    fetch('/chat/messages').then(...);
}, 2000);
```

**After (WebSocket):**
```javascript
Echo.private(`chat.${conversationId}`)
    .listen('MessageSent', (event) => {
        appendMessage(event.message);
    });
```

---

## Troubleshooting

### Common Issues

- **High server load**: Increase polling intervals
- **Delayed messages**: Decrease polling interval to 1s
- **Battery drain**: Use Page Visibility API to pause when hidden
- **Missing updates**: Check database indexes on `created_at`
- **Typing lag**: Ensure cache is working (Redis recommended)

### Debugging

```javascript
// Enable debug logging
window.REALTIME_DEBUG = true;

// Check polling status
console.log('Active timers:', Object.keys(pollingTimers));

// Manually trigger poll
fetchNewMessages(currentConversationId);
```

---

<div align="center">

**Nexus - Real-Time Features**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
