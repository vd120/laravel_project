# Real-Time Features Documentation

Comprehensive documentation of Nexus real-time features implemented via polling-based architecture.

---

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Real-Time Features](#real-time-features)
- [Implementation Details](#implementation-details)
- [Performance Considerations](#performance-considerations)
- [API Endpoints](#api-endpoints)
- [JavaScript Modules](#javascript-modules)

---

## Overview

Nexus implements real-time features using **polling-based architecture** instead of WebSockets. This approach provides real-time functionality without the complexity of WebSocket infrastructure.

### Why Polling?

| Advantage | Description |
|-----------|-------------|
| **Simplicity** | No WebSocket server required |
| **Compatibility** | Works with all hosting providers |
| **Firewall-Friendly** | Uses standard HTTP/HTTPS ports |
| **Easy Scaling** | No sticky sessions required |
| **Debugging** | Standard HTTP request/response cycle |

### Trade-offs

| Consideration | Impact |
|---------------|--------|
| **Latency** | 2-10 second delay (configurable) |
| **Server Load** | More HTTP requests than WebSocket |
| **Battery** | Higher mobile battery consumption |
| **Bandwidth** | More overhead than persistent connection |

---

## Architecture

### Polling Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      Real-Time Polling Architecture                     │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Browser    │     │   Browser    │     │   Browser    │
│   Client A   │     │   Client B   │     │   Client C   │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘
       │                    │                    │
       │  Poll every 2s     │  Poll every 2s     │  Poll every 2s
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

### Polling Intervals

| Feature | Interval | Rationale |
|---------|----------|-----------|
| **Chat Messages** | 2 seconds | Near real-time conversation |
| **Conversations List** | 2 seconds | Update last message |
| **Notifications** | 3 seconds | Balance between UX and load |
| **Online Status** | 10 seconds | Less critical, reduce load |
| **Typing Indicators** | 1 second | Immediate feedback |

---

## Real-Time Features

### 1. Chat Messages

**Implementation:** `resources/js/legacy/realtime.js`, `ChatController.php`

**Polling Interval:** 2 seconds

**Features:**
- New message detection
- Message delivery confirmation
- Read receipts tracking
- Message status updates (sent, delivered, read)

**Flow:**
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
│   (every 2s) │     │   Message    │
└──────────────┘     └──────────────┘
```

**Code Example:**
```javascript
// resources/js/legacy/realtime.js
function pollNewMessages(conversationId, lastMessageId) {
    setInterval(() => {
        fetch(`/chat/${conversationId}/messages?after=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages.length > 0) {
                    appendMessages(data.messages);
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
            });
    }, 2000); // 2 second polling
}
```

---

### 2. Notifications

**Implementation:** `resources/js/legacy/realtime.js`, `NotificationController.php`

**Polling Interval:** 3 seconds

**Features:**
- Unread notification count badge
- New notification detection
- Notification type indicators
- Auto-dismiss on click

**API Endpoints:**
```
GET /api/notifications/unread-count    - Get unread count
GET /api/notifications/realtime-updates - Get new notifications
POST /api/notifications/{id}/read      - Mark as read
POST /api/notifications/mark-all-read  - Mark all as read
```

**Code Example:**
```javascript
// resources/js/legacy/realtime.js
function pollNotifications() {
    setInterval(() => {
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(data.count);
                if (data.count > 0 && data.newNotifications) {
                    showNotificationToast(data.newNotifications);
                }
            });
    }, 3000); // 3 second polling
}
```

---

### 3. Online Status

**Implementation:** `UserController.php`, `resources/js/legacy/realtime.js`

**Polling Interval:** 10 seconds

**Features:**
- Real-time online/offline indicators
- Last active timestamp
- Batch status updates for efficiency

**API Endpoints:**
```
POST /user/online-status              - Update own status
GET  /user/{user}/online-status       - Get single user status
POST /user/online-status/batch        - Get multiple users status
```

**Database Schema:**
```sql
ALTER TABLE users ADD COLUMN is_online BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN last_active TIMESTAMP NULL;
```

**Code Example:**
```javascript
// resources/js/legacy/realtime.js
function updateOnlineStatus() {
    // Send heartbeat
    fetch('/user/online-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    // Poll other users' status
    setInterval(() => {
        fetch('/user/online-status/batch', {
            method: 'POST',
            body: JSON.stringify({ userIds: getUserIds() })
        })
        .then(response => response.json())
        .then(data => {
            updateOnlineIndicators(data.statuses);
        });
    }, 10000); // 10 second polling
}
```

---

### 4. Typing Indicators

**Implementation:** `ChatController.php`, `RealtimeService.php`

**Polling Interval:** 1 second

**Features:**
- Real-time typing status
- 5-second cache expiry
- Per-conversation tracking

**Cache Structure:**
```
typing:{conversation_id}:{user_id} => timestamp (5 second TTL)
```

**API Endpoints:**
```
POST /chat/{conversation}/typing        - Send typing indicator
GET  /chat/{conversation}/typing-status - Get typing status
```

**Flow:**
```
┌──────────────┐
│   User A     │
│   Typing...  │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST /chat/ │
│  {id}/typing │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   Cache      │
│   Set Key    │
│   (5s TTL)   │
└──────┬───────┘
       │
       ▼
┌──────────────┐     ┌──────────────┐
│   User B     │◀────│   User B     │
│   Polling    │     │   Sees       │
│   (every 1s) │     │   "typing..."│
└──────────────┘     └──────────────┘
```

**Code Example:**
```javascript
// resources/js/legacy/realtime.js
// Chat room functionality - typing indicators
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
```

---

### 5. Conversation Updates

**Implementation:** `ChatController.php`

**Polling Interval:** 2 seconds

**Features:**
- Last message timestamp updates
- Unread message count per conversation
- New conversation detection

**API Endpoints:**
```
GET /chat/conversations/updated - Get updated conversations
```

**Code Example:**
```javascript
// resources/js/legacy/realtime.js
// Conversation list polling
function pollConversations() {
    setInterval(() => {
        fetch('/chat/conversations/updated?last_update=' + lastUpdate)
            .then(response => response.json())
            .then(data => {
                if (data.conversations.length > 0) {
                    updateConversationsList(data.conversations);
                }
                if (data.newConversations) {
                    addNewConversations(data.newConversations);
                }
            });
    }, 2000);
}
```

---

## Implementation Details

### Backend: RealtimeService

**File:** `app/Services/RealtimeService.php`

```php
<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RealtimeService
{
    /**
     * Get new messages for a conversation since a given timestamp
     */
    public function getNewMessages(int $conversationId, int $afterMessageId = null): array
    {
        $query = Message::where('conversation_id', $conversationId)
            ->with('sender');
        
        if ($afterMessageId) {
            $query->where('id', '>', $afterMessageId);
        }
        
        return $query->latest('id')->get()->toArray();
    }
    
    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(int $userId): array
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->latest()
            ->limit(20)
            ->get()
            ->toArray();
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
    
    /**
     * Update user's online status
     */
    public function updateOnlineStatus(int $userId): void
    {
        User::where('id', $userId)->update([
            'is_online' => true,
            'last_active' => now(),
        ]);
    }
    
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
        foreach (Cache::getMultiple($pattern) as $key => $timestamp) {
            if (now()->timestamp - $timestamp < 5) {
                $userId = (int) last(explode(':', $key));
                if ($userId !== $excludeUserId) {
                    $typingUsers[] = $userId;
                }
            }
        }
        
        return $typingUsers;
    }
}
```

---

### Frontend: Realtime.js Module

**File:** `resources/js/legacy/realtime.js`

```javascript
/**
 * Real-time polling module for Nexus
 * Handles chat, notifications, and online status updates
 */

(function() {
    'use strict';
    
    // Configuration
    const POLLING_INTERVALS = {
        MESSAGES: 2000,
        NOTIFICATIONS: 3000,
        ONLINE_STATUS: 10000,
        TYPING: 1000,
        CONVERSATIONS: 2000
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

## Performance Considerations

### Server Load Calculation

**Example Scenario:** 1000 concurrent users

| Feature | Requests/Second | Total Requests/sec |
|---------|-----------------|-------------------|
| Chat Messages | 1000 users / 2s | 500 req/s |
| Notifications | 1000 users / 3s | 333 req/s |
| Online Status | 1000 users / 10s | 100 req/s |
| **Total** | - | **933 req/s** |

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

---

## API Endpoints

### Messages

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/chat/{conversation}/messages` | Get messages (with `?after=id`) | Yes |
| POST | `/chat/{conversation}` | Send message | Yes |
| POST | `/chat/{conversation}/read` | Mark messages as read | Yes |
| POST | `/chat/message/delivered` | Confirm message delivery | Yes |
| POST | `/chat/{conversation}/typing` | Send typing indicator | Yes |
| GET | `/chat/{conversation}/typing-status` | Get typing status | Yes |

### Notifications

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/notifications` | Get all notifications | Yes |
| GET | `/api/notifications/unread-count` | Get unread count | Yes |
| GET | `/api/notifications/realtime-updates` | Get new notifications | Yes |
| POST | `/api/notifications/{id}/read` | Mark as read | Yes |
| POST | `/api/notifications/mark-all-read` | Mark all as read | Yes |
| DELETE | `/api/notifications/{id}` | Delete notification | Yes |

### Online Status

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/user/online-status` | Update own status | Yes |
| GET | `/user/{user}/online-status` | Get single user status | Yes |
| POST | `/user/online-status/batch` | Get multiple users status | Yes |
| POST | `/user/online-status/offline` | Set offline status | Yes |

### Conversations

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/chat/conversations` | Get all conversations | Yes |
| GET | `/chat/conversations/updated` | Get updated conversations | Yes |
| GET | `/api/conversations` | API: Get conversations | Yes |

---

## JavaScript Modules

### Module Files

| File | Purpose | Polling Interval |
|------|---------|------------------|
| `realtime.js` | Core real-time polling (messages, notifications, online status, typing, conversations) | 2-10s |
| `home.js` | Feed updates | On-demand |
| `ui-utils.js` | Online status indicators | 10s |
| `posts.js` | Post interactions | On-demand |
| `comments.js` | Comment system | On-demand |
| `groups-show.js` | Group page functionality | On-demand |
| `ai-chat.js` | AI chatbot interface | On-demand |

### Module Architecture

```
┌─────────────────────────────────────────┐
│           realtime.js (Core)            │
│  • Message polling (chat)               │
│  • Notification polling                 │
│  • Online status polling                │
│  • Typing indicators                    │
│  • Conversation list updates            │
└─────────────────────────────────────────┘
         │
         │ Handles all real-time features
         │ via polling (2-10s intervals)
         ▼
┌─────────────────────────────────────────┐
│  Other modules (on-demand features)     │
│  • home.js - Feed functionality         │
│  • posts.js - Post interactions         │
│  • comments.js - Comment system         │
│  • groups-show.js - Group pages         │
│  • ui-utils.js - Online indicators      │
│  • ai-chat.js - AI chatbot              │
└─────────────────────────────────────────┘
```

---

## Migration to WebSockets (Future)

If you need to migrate to WebSockets in the future:

### Recommended Stack

| Technology | Purpose |
|------------|---------|
| **Laravel Reverb** | Laravel's native WebSocket server |
| **Laravel Echo** | Client-side WebSocket events |
| **Pusher** | Hosted WebSocket service (alternative) |

### Migration Steps

1. Install Laravel Reverb: `composer require laravel/reverb`
2. Configure broadcasting in `config/broadcasting.php`
3. Replace polling intervals with Echo listeners
4. Update JavaScript to use `Echo.private()` channels
5. Migrate typing indicators to WebSocket events
6. Test thoroughly before deployment

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

| Issue | Solution |
|-------|----------|
| High server load | Increase polling intervals |
| Delayed messages | Decrease polling interval to 1s |
| Battery drain | Use Page Visibility API to pause when hidden |
| Missing updates | Check database indexes on `created_at` |
| Typing lag | Ensure cache is working (Redis recommended) |

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

## Next Steps

Continue reading:

- [API Reference](API.md) - Complete API documentation
- [Architecture](ARCHITECTURE.md) - System design diagrams
- [Features](FEATURES.md) - Feature documentation
- [Troubleshooting](TROUBLESHOOTING.md) - Common issues
