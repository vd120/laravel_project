/* eslint-disable no-undef */
/* Service Worker for Push Notifications - Nexus */

const CACHE_NAME = 'nexus-push-v2';
const API_BASE = '/api/push';

// Install event - cache assets
self.addEventListener('install', (event) => {
    console.log('[Push] Service Worker installing...');
    self.skipWaiting(); // Activate immediately
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Push] Service Worker activated');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    return self.clients.claim(); // Claim all clients immediately
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    console.log('[Push] Push event received:', event);

    let data = {};

    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = {
                title: 'Nexus',
                body: event.data.text(),
                url: '/',
            };
        }
    }

    const title = data.title || 'Nexus';
    const options = {
        body: data.body || 'You have a new notification',
        icon: data.icon || '/favicon.ico',
        badge: data.badge || '/favicon.ico',
        image: data.image,
        data: {
            url: data.url || '/',
            timestamp: data.timestamp || Date.now(),
            type: data.type || 'notification',
        },
        tag: data.tag || 'nexus-notification',
        requireInteraction: data.requireInteraction || false,
        silent: data.silent || false,
        actions: data.actions || [
            {
                action: 'view',
                title: 'View',
                icon: '/favicon.ico',
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/favicon.ico',
            },
        ],
        vibrate: data.vibrate || [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    // Get URL from notification data
    const urlToOpen = event.notification.data && event.notification.data.url ? event.notification.data.url : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Check if there's already a window open with the URL
            for (let client of windowClients) {
                if (client.url.includes(urlToOpen) && 'focus' in client) {
                    return client.focus();
                }
            }

            // No existing window, open a new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Handle messages from the main thread
self.addEventListener('message', (event) => {
    console.log('[Push] Message received:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLIENTS_CLAIM') {
        self.clients.claim();
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
});

// Log errors
self.addEventListener('error', (event) => {
    console.error('[Push] Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
    console.error('[Push] Unhandled promise rejection:', event.reason);
});

console.log('[Push] Service Worker loaded');
