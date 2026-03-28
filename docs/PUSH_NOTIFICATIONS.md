# Nexus - Push Notifications

Complete documentation for web push notifications in Nexus social networking platform.

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Setup & Configuration](#setup--configuration)
4. [VAPID Keys](#vapid-keys)
5. [Service Worker](#service-worker)
6. [Frontend Implementation](#frontend-implementation)
7. [Backend Implementation](#backend-implementation)
8. [Notification Types](#notification-types)
9. [User Preferences](#user-preferences)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)

---

## Overview

Nexus implements web push notifications using the Web Push API and VAPID (Voluntary Application Server Identification) for secure push message delivery.

### Features

-  Browser-based push notifications
-  No native app required
-  Works even when browser is closed
-  Cross-platform (Desktop, Mobile)
-  User-controlled preferences
-  Secure VAPID authentication

### Supported Browsers

- **Chrome** (50+): Windows, Mac, Linux, Android
- **Firefox** (44+): Windows, Mac, Linux, Android
- **Safari** (16+): iOS, Mac
- **Edge** (17+): Windows, Mac
- **Opera** (37+): Windows, Mac, Linux

---

## Architecture

### Push Notification Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    Push Notification Flow                        │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Enables    │
│   Push       │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   Browser    │
│   Requests   │
│   Permission │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   User       │
│   Grants     │
│   Permission │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  Frontend: Subscribe to Push            │
│  ┌───────────────────────────────────┐  │
│  │ 1. Get VAPID public key           │  │
│  │ 2. Register service worker        │  │
│  │ 3. Create push subscription       │  │
│  │ 4. Send subscription to server    │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  Backend: Store Subscription            │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate subscription          │  │
│  │ 2. Store in push_subscriptions    │  │
│  │ 3. Link to user account           │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│   Event      │
│   Occurs     │
│   (Like,     │
│   Message,   │
│   etc.)      │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  Backend: Send Push Notification        │
│  ┌───────────────────────────────────┐  │
│  │ 1. Get user's subscriptions       │  │
│  │ 2. Create notification payload    │  │
│  │ 3. Encrypt with VAPID keys        │  │
│  │ 4. Send to push service           │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│   Push       │
│   Service    │
│   (FCM,      │
│   etc.)      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   Browser    │
│   Receives   │
│   Push       │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   Service    │
│   Worker     │
│   Shows      │
│   Notification│
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   User       │
│   Sees       │
│   Notification│
└──────────────┘
```

### Components

```
┌─────────────────────────────────────────────────────────────────┐
│                    Push Notification Components                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Frontend:                                                       │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ push-notifica-  │  │  Service Worker │  │  Permission     │ │
│  │ tions.js        │  │  (sw.js)        │  │  Request        │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
│                                                                  │
│  Backend:                                                        │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ PushNotification│  │  PushNotification│ │  VAPID Keys      │ │
│  │ Controller      │  │  Service         │  │  (Config)       │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
│                                                                  │
│  Database:                                                       │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  push_subscriptions table                                │   │
│  │  - user_id                                               │   │
│  │  - endpoint (push service URL)                           │   │
│  │  - p256dh (public key)                                   │   │
│  │  - auth (secret)                                         │   │
│  │  - content_encoding                                      │   │
│  │  - settings (preferences)                                │   │
│  │  - last_used_at                                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Setup & Configuration

### 1. Generate VAPID Keys

```bash
php artisan push:vapid-keys
```

This creates a key pair and updates your `.env` file:

```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
```

### 2. Configure Environment

Add to `.env`:

```env
# Push Notifications
VAPID_PUBLIC_KEY=BKxN...
VAPID_PRIVATE_KEY=abc123...
VAPID_SUBJECT=mailto:admin@your-domain.com
```

### 3. Run Migrations

```bash
php artisan migrate
```

This creates the `push_subscriptions` table.

### 4. Publish Service Worker

The service worker is located at `public/sw.js` and is automatically available.

---

## VAPID Keys

### What are VAPID Keys?

VAPID (Voluntary Application Server Identification) is a security mechanism that identifies your application to push services.

### Key Components

- **Public Key**: Shared with clients for subscription (Stored in: `.env`, frontend)
- **Private Key**: Used to sign push requests (Stored in: `.env` - secret)
- **Subject**: Contact email for push service (Stored in: `.env`)

### Key Generation

```php
// app/Console/Commands/GenerateVapidKeysCommand.php
public function handle()
{
    $keyPair = \Minishlink\WebPush\VAPID::createVapidKeys();
    
    $this->info('Public Key: ' . $keyPair['publicKey']);
    $this->info('Private Key: ' . $keyPair['privateKey']);
    
    // Update .env file
    $this->updateEnvFile([
        'VAPID_PUBLIC_KEY' => $keyPair['publicKey'],
        'VAPID_PRIVATE_KEY' => $keyPair['privateKey'],
    ]);
}
```

---

## Service Worker

### Service Worker File

Location: `public/sw.js`

```javascript
// public/sw.js
self.addEventListener('push', function(event) {
    if (event.data) {
        const data = event.data.json();
        
        const options = {
            body: data.body,
            icon: data.icon || '/favicon.ico',
            badge: '/favicon.ico',
            vibrate: [100, 50, 100],
            data: {
                url: data.url,
                type: data.type
            },
            actions: [
                {
                    action: 'open',
                    title: 'Open'
                },
                {
                    action: 'close',
                    title: 'Close'
                }
            ]
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
```

### Service Worker Registration

```javascript
// resources/js/push-notifications.js
async function registerServiceWorker() {
    const registration = await navigator.serviceWorker.register('/sw.js', {
        scope: '/'
    });
    return registration;
}
```

---

## Frontend Implementation

### Push Notification JavaScript Module

Location: `resources/js/push-notifications.js`

```javascript
/**
 * Push Notification Manager - Nexus
 * Handles browser push notifications with polling-based updates
 */

class PushNotificationManager {
    constructor() {
        this.registration = null;
        this.subscription = null;
        this.vapidPublicKey = null;
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.permission = Notification.permission;
        this.pollingInterval = null;
        this.pollingDelay = 30000; // 30 seconds
    }

    /**
     * Initialize push notifications
     */
    async init() {
        // Check if running on HTTPS or localhost
        const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost';

        if (!isSecure) {
            console.warn('[Push] Push notifications require HTTPS');
            return false;
        }

        // Get VAPID key from server
        const vapidLoaded = await this.getVapidKey();
        if (!vapidLoaded) {
            return false;
        }

        // Register service worker
        const swRegistered = await this.registerServiceWorker();
        if (!swRegistered) {
            return false;
        }

        // Get existing subscription
        await this.getSubscription();

        // Start polling for new notifications if subscribed
        if (this.subscription) {
            this.startPolling();
        }

        return true;
    }

    /**
     * Get VAPID public key from server
     */
    async getVapidKey() {
        try {
            const response = await fetch('/api/push/vapid-key');
            const data = await response.json();

            if (data.configured && data.public_key) {
                this.vapidPublicKey = data.public_key;
                return true;
            }

            console.warn('[Push] Push notifications not configured on server');
            return false;
        } catch (error) {
            console.error('[Push] Error getting VAPID key:', error);
            return false;
        }
    }

    /**
     * Register service worker
     */
    async registerServiceWorker() {
        try {
            this.registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/',
            });
            return true;
        } catch (error) {
            console.error('[Push] Service Worker registration failed:', error);
            return false;
        }
    }

    /**
     * Request permission and subscribe
     */
    async requestPermission() {
        if (this.permission !== 'granted') {
            const permission = await Notification.requestPermission();
            this.permission = permission;

            if (permission !== 'granted') {
                return false;
            }
        }

        // Subscribe to push notifications
        try {
            const subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey),
            });

            // Send subscription to server
            await this.saveSubscription(subscription);
            this.subscription = subscription;

            // Start polling
            this.startPolling();

            return true;
        } catch (error) {
            console.error('[Push] Subscription error:', error);
            return false;
        }
    }

    /**
     * Save subscription to server
     */
    async saveSubscription(subscription) {
        try {
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    p256dh: this.arrayBufferToBase64(subscription.getKey('p256dh')),
                    auth: this.arrayBufferToBase64(subscription.getKey('auth')),
                    content_encoding: 'aesgcm',
                }),
            });

            return await response.json();
        } catch (error) {
            console.error('[Push] Error saving subscription:', error);
            throw error;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        if (this.subscription) {
            await this.subscription.unsubscribe();

            // Remove from server
            await fetch('/api/push/unsubscribe', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    endpoint: this.subscription.endpoint,
                }),
            });

            this.subscription = null;
            this.stopPolling();
            return true;
        }
        return false;
    }

    /**
     * Convert base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return new Uint8Array(rawData.length);
    }

    /**
     * Convert ArrayBuffer to Base64
     */
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', async () => {
    const pushManager = new PushNotificationManager();
    window.pushManager = pushManager;
    await pushManager.init();
});
```

### Initialization in Blade Template

```blade
{{-- resources/views/partials/push-notifications.blade.php --}}
@if(auth()->check())
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Check if user has enabled push
        const pushEnabled = @json(auth()->user()->pushSubscriptions()->exists());
        
        if (!pushEnabled) {
            // Show opt-in prompt
            showPushOptIn();
        }
    });

    function showPushOptIn() {
        // Show toast/modal asking user to enable push
        const toast = document.getElementById('push-opt-in');
        toast.classList.remove('hidden');
    }

    async function enablePush() {
        const success = await window.PushNotifications.initialize();
        if (success) {
            document.getElementById('push-opt-in').classList.add('hidden');
            showToast('Push notifications enabled!');
        }
    }

    async function disablePush() {
        await window.PushNotifications.unsubscribe();
        showToast('Push notifications disabled');
    }
</script>
@endif
```

---

## Backend Implementation

### Push Notification Service

Location: `app/Services/PushNotificationService.php`

```php
<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    protected WebPush $webPush;
    protected string $vapidPublicKey;
    protected string $vapidPrivateKey;
    protected string $vapidSubject;

    public function __construct()
    {
        $this->vapidPublicKey = config('services.vapid.public_key');
        $this->vapidPrivateKey = config('services.vapid.private_key');
        $this->vapidSubject = config('services.vapid.subject');

        // Check for required extensions
        if (!extension_loaded('gmp') && !extension_loaded('bcmath')) {
            Log::warning('Push notifications: BCMath or GMP extension required for VAPID key operations');
        }

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => $this->vapidSubject,
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ]);
    }

    /**
     * Send a push notification to a user.
     */
    public function sendToUser(User $user, string $title, string $body, string $url = '/', array $data = []): bool
    {
        $subscriptions = $user->pushSubscriptions()->whereHas('user')->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        $payload = $this->buildPayload($title, $body, $url, $data);
        $sent = false;

        foreach ($subscriptions as $subscription) {
            if (!$subscription->isValid()) {
                continue;
            }

            // Check user preferences
            if (isset($data['type']) && !$subscription->getSetting($data['type'], true)) {
                continue;
            }

            // Create subscription (v10 API - individual parameters)
            $pushSubscription = new Subscription(
                $subscription->endpoint,
                $subscription->p256dh,
                $subscription->auth,
                $subscription->content_encoding
            );

            $this->webPush->queueNotification($pushSubscription, $payload);
            $sent = true;
        }

        // Send all queued notifications
        if ($sent) {
            $this->processQueue();
        }

        return $sent;
    }

    /**
     * Send like notification
     */
    public function sendLikeNotification(User $recipient, User $liker, int $postId): void
    {
        $this->sendToUser($recipient, [
            'title' => 'New Like',
            'body' => "{$liker->name} liked your post",
            'url' => route('posts.show', ['slug' => $postId]),
            'type' => 'like',
            'icon' => $liker->avatar_url,
        ]);
    }

    /**
     * Send comment notification
     */
    public function sendCommentNotification(User $recipient, User $commenter, int $postId): void
    {
        $this->sendToUser($recipient, [
            'title' => 'New Comment',
            'body' => "{$commenter->name} commented on your post",
            'url' => route('posts.show', ['slug' => $postId]),
            'type' => 'comment',
            'icon' => $commenter->avatar_url,
        ]);
    }

    /**
     * Send follow notification
     */
    public function sendFollowNotification(User $recipient, User $follower): void
    {
        $this->sendToUser($recipient, [
            'title' => 'New Follower',
            'body' => "{$follower->name} started following you",
            'url' => route('users.show', $follower->username),
            'type' => 'follow',
            'icon' => $follower->avatar_url,
        ]);
    }

    /**
     * Send message notification
     */
    public function sendMessageNotification(User $recipient, User $sender, int $conversationId): void
    {
        $this->sendToUser($recipient, [
            'title' => 'New Message',
            'body' => "{$sender->name} sent you a message",
            'url' => route('chat.show', $conversationId),
            'type' => 'message',
            'icon' => $sender->avatar_url,
        ]);
    }
}
```

### Push Notification Controller

Location: `app/Controllers/PushNotificationController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    /**
     * Get VAPID public key
     */
    public function getVapidKey()
    {
        return response()->json([
            'public_key' => config('services.vapid.public_key'),
            'configured' => !empty(config('services.vapid.public_key')),
        ]);
    }

    /**
     * Store push subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'p256dh' => 'required|string',
            'auth' => 'required|string',
            'content_encoding' => 'nullable|string|in:aesgcm,aes128gcm',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Check if subscription already exists
        $subscription = PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $validated['endpoint'])
            ->first();

        if ($subscription) {
            // Update existing subscription
            $subscription->update([
                'p256dh' => $validated['p256dh'],
                'auth' => $validated['auth'],
                'content_encoding' => $validated['content_encoding'] ?? 'aesgcm',
                'last_used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push subscription updated',
            ]);
        }

        // Create new subscription
        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => $validated['endpoint'],
            'p256dh' => $validated['p256dh'],
            'auth' => $validated['auth'],
            'content_encoding' => $validated['content_encoding'] ?? 'aesgcm',
            'settings' => PushSubscription::getDefaultSettings(),
            'last_used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push subscription created',
        ], 201);
    }

    /**
     * Update push settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'notifications' => 'array',
        ]);

        // Store user preferences
        auth()->user()->update([
            'push_notifications_enabled' => $validated['enabled'] ?? true,
            'push_notification_preferences' => $validated['notifications'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push settings updated',
        ]);
    }

    /**
     * Get push settings
     */
    public function getSettings()
    {
        $user = auth()->user();

        return response()->json([
            'enabled' => $user->push_notifications_enabled ?? true,
            'notifications' => $user->push_notification_preferences ?? [
                'messages' => true,
                'likes' => true,
                'comments' => true,
                'follows' => true,
            ],
        ]);
    }

    /**
     * Unsubscribe from push
     */
    public function destroy()
    {
        auth()->user()->pushSubscriptions()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Push subscription removed',
        ]);
    }

    /**
     * Send test notification (for development).
     */
    public function test()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $pushService = app(\App\Services\PushNotificationService::class);

        if (!$pushService) {
            return response()->json([
                'success' => false,
                'message' => 'Push notification service not available. Please install BCMath or GMP PHP extension.',
            ], 503);
        }

        try {
            $sent = $pushService->sendToUser(
                $user,
                'Test Notification',
                'Push notifications are working!',
                url('/notifications'),
                ['type' => 'test']
            );

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No push subscription found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Push notification test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

---

## Notification Types

### Supported Notification Types

- `like`: Someone likes your post (See payload example below)
- `comment`: Someone comments on your post (See payload example below)
- `follow`: Someone follows you (See payload example below)
- `message`: New message received (See payload example below)
- `mention`: Someone mentions you (See payload example below)
- `story_reaction`: Story reaction (See payload example below)

### Notification Payload Structure

```json
{
    "title": "Notification Title",
    "body": "Notification body text",
    "url": "https://your-domain.com/path",
    "type": "notification_type",
    "icon": "https://your-domain.com/avatar.jpg",
    "badge": "https://your-domain.com/favicon.ico",
    "data": {
        "notification_id": 123,
        "related_id": 456,
        "related_type": "App\\Models\\Post"
    }
}
```

### Example Payloads

**Like Notification:**
```json
{
    "title": "New Like",
    "body": "John Doe liked your post",
    "url": "https://your-domain.com/posts/abc123",
    "type": "like",
    "icon": "https://your-domain.com/avatars/john.jpg"
}
```

**Message Notification:**
```json
{
    "title": "New Message",
    "body": "Jane Smith sent you a message",
    "url": "https://your-domain.com/chat/123",
    "type": "message",
    "icon": "https://your-domain.com/avatars/jane.jpg"
}
```

---

## User Preferences

### Preference Settings

Users can control which notifications they receive:

```javascript
const preferences = {
    enabled: true,
    notifications: {
        messages: true,
        likes: true,
        comments: true,
        follows: true,
        mentions: false,
        story_reactions: false
    }
};
```

### Settings UI

```blade
{{-- resources/views/partials/push-settings.blade.php --}}
<div class="push-settings">
    <h3>Push Notifications</h3>
    
    <label>
        <input type="checkbox" id="push-enabled" checked>
        Enable Push Notifications
    </label>

    <div class="notification-types">
        <label>
            <input type="checkbox" name="messages" checked>
            Messages
        </label>
        <label>
            <input type="checkbox" name="likes" checked>
            Likes
        </label>
        <label>
            <input type="checkbox" name="comments" checked>
            Comments
        </label>
        <label>
            <input type="checkbox" name="follows" checked>
            New Followers
        </label>
    </div>

    <button onclick="savePushSettings()">Save Settings</button>
    <button onclick="testPushNotification()">Send Test</button>
</div>

<script>
async function savePushSettings() {
    const enabled = document.getElementById('push-enabled').checked;
    const notifications = {
        messages: document.querySelector('[name="messages"]').checked,
        likes: document.querySelector('[name="likes"]').checked,
        comments: document.querySelector('[name="comments"]').checked,
        follows: document.querySelector('[name="follows"]').checked,
    };

    const response = await fetch('/api/push/settings', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ enabled, notifications })
    });

    if (response.ok) {
        showToast('Settings saved!');
    }
}

async function testPushNotification() {
    const response = await fetch('/api/push/test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        }
    });

    if (response.ok) {
        showToast('Test notification sent!');
    }
}
</script>
```

---

## Testing

### Manual Testing

1. **Enable Push:**
```javascript
await window.PushNotifications.initialize();
```

2. **Send Test Notification:**
```bash
curl -X POST http://localhost/api/push/test \
  -H "X-CSRF-TOKEN: $(php artisan token:generate)" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

3. **Check Subscription:**
```bash
php artisan tinker
>>> App\Models\User::find(1)->pushSubscriptions;
```

### Automated Testing

```php
// tests/Feature/PushNotificationTest.php
public function test_push_notification_subscription()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/...',
            'p256dh' => 'public_key',
            'auth' => 'auth_token',
        ]);

    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('push_subscriptions', [
        'user_id' => $user->id,
    ]);
}

public function test_push_notification_sending()
{
    $user = User::factory()->create();
    $liker = User::factory()->create();

    // Create subscription
    PushSubscription::create([
        'user_id' => $user->id,
        'content' => 'endpoint',
        'p256dh' => 'public_key',
        'auth' => 'auth_token',
    ]);

    // Send like notification
    app(PushNotificationService::class)
        ->sendLikeNotification($user, $liker, 1);

    // Assert notification was queued (mock WebPush)
    // ...
}
```

---

## Troubleshooting

### Common Issues

#### 1. Push Not Supported

**Error:** `PushManager is not defined`

**Solution:**
- Check browser supports push notifications
- Use HTTPS (required for production)
- Check service worker registration

#### 2. Permission Denied

**Error:** User denied permission

**Solution:**
- Clear browser permissions
- Reset in browser settings
- Re-request permission

#### 3. Subscription Expired

**Error:** `Subscription expired`

**Solution:**
- Remove expired subscription from database
- Re-subscribe user
- Handle in service:
```php
if ($report->isSubscriptionExpired()) {
    $subscription->delete();
}
```

#### 4. Notifications Not Showing

**Error:** Push sent but no notification

**Solution:**
- Check browser notification settings
- Check service worker is registered
- Check notification permissions
- Verify payload format

#### 5. VAPID Key Error

**Error:** `Invalid VAPID key`

**Solution:**
- Regenerate VAPID keys
- Ensure keys are in `.env`
- Clear config cache: `php artisan config:clear`

---

## Best Practices

### 1. User Experience

-  Ask for permission at appropriate time
-  Explain value of notifications
-  Allow easy opt-out
-  Respect user
-  Don't spam

### 2. Performance

-  Batch notifications
-  Use queue for sending
-  Clean up expired subscriptions
-  Limit notification frequency

### 3. Security

-  Use HTTPS
-  Protect VAPID private key
-  Validate subscriptions
-  Rate limit test endpoint

---

<div align="center">

**Nexus - Push Notifications**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
