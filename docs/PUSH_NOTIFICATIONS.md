# Push Notifications - Nexus

Browser-based push notifications for your Nexus social media platform. Users can receive real-time notifications about likes, comments, messages, and more - even when they're not actively using the app.

## Features

- ✅ **Browser Push Notifications** - Works on Chrome, Firefox, Safari, Edge
- ✅ **Polling-based** - Compatible with your existing architecture (no WebSockets needed)
- ✅ **Multilingual** - Full English & Arabic support
- ✅ **User Preferences** - Users can customize which notifications they receive
- ✅ **Privacy-focused** - No external services, all self-hosted
- ✅ **Mobile-friendly** - Works on mobile browsers too
- ✅ **PWA-ready** - Can be installed as a Progressive Web App

## What Was Implemented

### Backend (Laravel)

1. **Models**
   - `PushSubscription` - Stores user's push subscription data

2. **Services**
   - `PushNotificationService` - Handles sending push notifications via Web Push API

3. **Controllers**
   - `PushNotificationController` - API endpoints for subscription management

4. **Database**
   - Migration for `push_subscriptions` table
   - VAPID keys stored in `.env`

5. **Routes**
   - `/api/push/vapid-key` - Get VAPID public key
   - `/api/push/subscribe` - Subscribe to push notifications
   - `/api/push/settings` - Get/update notification preferences
   - `/api/push/unsubscribe` - Unsubscribe from push notifications
   - `/api/push/test` - Send test notification

### Frontend (JavaScript)

1. **Service Worker** (`/sw.js`)
   - Handles incoming push notifications
   - Shows notifications to users
   - Handles notification clicks

2. **Push Notification Manager** (`resources/js/push-notifications.js`)
   - Manages subscription lifecycle
   - Polls for new notifications
   - Handles user preferences
   - Fully translated (EN/AR)

3. **UI Component** (`resources/views/partials/push-notification-settings.blade.php`)
   - Modal for enabling/disabling notifications
   - Toggle switches for notification types
   - Test notification button

## How to Use

### For End Users

1. **Enable Notifications**
   - Click the bell icon in the navigation bar
   - Click "Enable Push Notifications"
   - Allow browser permission when prompted

2. **Customize Settings**
   - Open notification settings
   - Toggle which types of notifications you want to receive:
     - Likes on your posts
     - Comments on your posts
     - New followers
     - New messages
     - Mentions

3. **Test It**
   - Click "Test Notification" in settings
   - You should receive a browser notification

### For Developers

#### Setup (Already Done)

1. VAPID keys are generated and stored in `.env`
2. Migration has been run
3. Service worker is deployed to `/sw.js`

#### Sending Push Notifications

```php
use App\Services\PushNotificationService;
use App\Models\User;

$pushService = app(PushNotificationService::class);

// Send to a specific user
$pushService->sendToUser(
    $user,
    'New like on your post',
    'John liked your photo',
    route('posts.show', $post),
    ['type' => 'likes']
);

// Send to multiple users
$pushService->sendToUsers(
    [$user1, $user2],
    'Trending now',
    'Your post is trending!',
    route('posts.show', $post)
);

// Send to all subscribers
$pushService->sendToAll(
    'Platform Update',
    'New features available!',
    route('home')
);
```

#### Integration with Existing Notifications

Use the `SendsPushNotifications` trait:

```php
use App\Traits\SendsPushNotifications;

class NotificationService
{
    use SendsPushNotifications;
    
    public function createNotification($user, $type, $data)
    {
        $notification = Notification::create([...]);
        
        // Automatically send push notification
        $this->sendPushNotification($notification);
    }
}
```

## Configuration

### Environment Variables

```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
VAPID_SUBJECT=mailto:noreply@nexus.com
```

### Generate New VAPID Keys

If you need to regenerate keys:

```bash
php artisan push:vapid-generate
```

Then update your `.env` file and run:

```bash
php artisan config:clear
```

## Testing

### Manual Testing

1. **Subscribe**
   ```javascript
   // In browser console
   await window.pushManager.requestPermission()
   ```

2. **Send Test Notification**
   ```javascript
   // In browser console
   await window.pushManager.test()
   ```

3. **Check Subscription**
   ```javascript
   // In browser console
   window.pushManager.isEnabled()
   ```

### API Testing

```bash
# Get VAPID key
curl http://localhost:8000/api/push/vapid-key

# Get settings (requires auth)
curl http://localhost:8000/api/push/settings \
  -H "X-CSRF-TOKEN: {token}" \
  -H "Cookie: {session_cookie}"

# Test notification
curl -X POST http://localhost:8000/api/push/test \
  -H "X-CSRF-TOKEN: {token}" \
  -H "Cookie: {session_cookie}"
```

## Troubleshooting

### Notifications Not Showing

1. **Check browser support**
   ```javascript
   console.log('Service Worker' in navigator)
   console.log('PushManager' in window)
   ```

2. **Check permission**
   ```javascript
   console.log(Notification.permission)
   // Should be 'granted'
   ```

3. **Check subscription**
   ```javascript
   const reg = await navigator.serviceWorker.ready
   const sub = await reg.pushManager.getSubscription()
   console.log(sub)
   // Should not be null
   ```

### Service Worker Issues

1. **Clear cache**
   ```javascript
   // In browser console
   await caches.keys().then(keys => 
     Promise.all(keys.map(k => caches.delete(k)))
   )
   ```

2. **Re-register**
   ```javascript
   await navigator.serviceWorker.register('/sw.js', {scope: '/'})
   ```

3. **Check status**
   ```javascript
   const reg = await navigator.serviceWorker.getRegistration()
   console.log('Active:', reg?.active?.state)
   ```

## Browser Support

| Browser | Version | Support |
|---------|---------|---------|
| Chrome | 50+ | ✅ Full |
| Firefox | 44+ | ✅ Full |
| Safari | 16+ | ✅ Full |
| Edge | 17+ | ✅ Full |
| Opera | 37+ | ✅ Full |
| Samsung Internet | 5.0+ | ✅ Full |

## Security

- VAPID keys are used for authentication
- Private key never leaves the server
- All subscriptions are tied to authenticated users
- Users can unsubscribe anytime
- No third-party services involved

## Performance

- Polling interval: 30 seconds (configurable)
- Notifications are batched
- Service worker handles notifications even when page is closed
- Minimal impact on server resources

## Future Enhancements

- [ ] Badge API for unread count
- [ ] Action buttons on notifications
- [ ] Rich notifications with images
- [ ] Notification analytics
- [ ] Scheduled notifications
- [ ] Notification grouping

## Files Created

```
app/
├── Console/Commands/
│   └── GenerateVapidKeysCommand.php
├── Http/Controllers/
│   └── PushNotificationController.php
├── Models/
│   └── PushSubscription.php
├── Services/
│   └── PushNotificationService.php
└── Traits/
    └── SendsPushNotifications.php

database/
├── migrations/
│   ├── 2026_03_17_000000_create_push_subscriptions_table.php
│   └── 2026_03_17_042304_fix_push_subscriptions_indexes.php

public/
└── sw.js

resources/
├── js/
│   └── push-notifications.js
├── lang/en/
│   ├── messages.php (updated)
│   └── notifications.php (updated)
├── lang/ar/
│   ├── messages.php (updated)
│   └── notifications.php (updated)
└── views/partials/
    └── push-notification-settings.blade.php

routes/
└── api.php (updated)

config/
└── services.php (updated)

.env (updated)
```

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review browser console logs
3. Check server logs: `storage/logs/laravel.log`

---

**Last Updated:** March 17, 2026
**Version:** 1.0.0
