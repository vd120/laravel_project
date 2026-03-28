# Nexus - API Reference

Complete RESTful API documentation for Nexus social networking platform.

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Posts API](#posts-api)
4. [Comments API](#comments-api)
5. [Users API](#users-api)
6. [Stories API](#stories-api)
7. [Chat API](#chat-api)
8. [Groups API](#groups-api)
9. [Notifications API](#notifications-api)
10. [Hashtags API](#hashtags-api)
11. [Events API](#events-api)
12. [Push Notifications API](#push-notifications-api)
13. [Error Handling](#error-handling)

---

## Overview

### Base URL

```
Development: http://localhost
Production: https://your-domain.com
```

### API Version

Routes in `routes/api.php` are automatically prefixed with `/api` by Laravel.
Routes in `routes/web.php` use the base URL without `/api` prefix.

### Authentication Types

Nexus uses two authentication methods for API endpoints:

1. **Sanctum Token Auth** (`auth:sanctum` middleware): For RESTful API endpoints in `routes/api.php`
   - Posts (`/api/posts`), Comments (`/api/comments`), Users (`/api/users`), Password change
   
2. **Web Session Auth** (`web` middleware): 
   - Routes in `routes/api.php` (prefixed with `/api/`): Notifications, Events, Push Notifications, Hashtags
   - Routes in `routes/web.php` (NO `/api/` prefix): Stories, Chat, Groups

### Rate Limiting

- **Authentication**: 5 requests per 1 minute
- **Posts**: 30 requests per 1 minute
- **Comments**: 20 requests per 1 minute
- **Email Verification**: 3 requests per 1 hour

---

## Authentication

### Get CSRF Token

Before making API requests, get a CSRF token:

```http
GET /sanctum/csrf-cookie
```

### Login

```http
POST /login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "username": "johndoe"
    }
}
```

### Register

```http
POST /register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Registration successful. Please verify your email.",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

### Logout

```http
POST /logout
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### Check Username Availability

```http
GET /api/check-username?username=johndoe
```

**Response (200 OK):**
```json
{
    "available": true,
    "username": "johndoe"
}
```

---

## Posts API

### Get Feed

```http
GET /api/posts
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15)

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "slug": "abc123def456",
            "content": "Hello world!",
            "media": [
                {
                    "id": 1,
                    "media_type": "image",
                    "media_path": "posts/image.jpg",
                    "media_thumbnail": null,
                    "sort_order": 1
                }
            ],
            "user": {
                "id": 1,
                "name": "John Doe",
                "username": "johndoe",
                "avatar_url": "storage/avatars/avatar.jpg"
            },
            "likes_count": 10,
            "comments_count": 5,
            "is_liked": false,
            "is_saved": false,
            "created_at": "2026-03-27T10:00:00Z"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

### Create Post

```http
POST /api/posts
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "content": "Hello world!",
    "media": [file1, file2],
    "is_private": false
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Post created successfully",
    "post": {
        "id": 1,
        "slug": "abc123def456",
        "content": "Hello world!",
        "is_private": false
    }
}
```

### Get Post

```http
GET /api/posts/{slug}
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "id": 1,
    "slug": "abc123def456",
    "content": "Hello world!",
    "media": [...],
    "user": {...},
    "likes_count": 10,
    "comments_count": 5,
    "created_at": "2026-03-27T10:00:00Z"
}
```

### Update Post

```http
PUT /api/posts/{slug}
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Updated content",
    "is_private": true
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Post updated successfully",
    "post": {...}
}
```

### Delete Post

```http
DELETE /api/posts/{slug}
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Post deleted successfully"
}
```

### Like Post

```http
POST /api/posts/{id}/like
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "liked": true,
    "likes_count": 11
}
```

### Save Post

```http
POST /api/posts/{id}/save
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "saved": true
}
```

---

## Comments API

### Create Comment

```http
POST /api/comments
Authorization: Bearer {token}
Content-Type: application/json

{
    "post_id": 1,
    "content": "Great post!",
    "parent_id": null
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Comment created successfully",
    "comment": {
        "id": 1,
        "content": "Great post!",
        "user": {...},
        "replies_count": 0,
        "likes_count": 0,
        "created_at": "2026-03-27T10:00:00Z"
    }
}
```

### Update Comment

```http
PUT /api/comments/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Updated comment"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Comment updated successfully",
    "comment": {...}
}
```

### Delete Comment

```http
DELETE /api/comments/{id}
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Comment deleted successfully"
}
```

### Like Comment

```http
POST /api/comments/{id}/like
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "liked": true,
    "likes_count": 1
}
```

---

## Users API

### Get User Profile

```http
GET /api/users/{username}
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "avatar_url": "storage/avatars/avatar.jpg",
    "profile": {
        "bio": "Software developer",
        "location": "New York",
        "website": "https://johndoe.com",
        "is_private": false
    },
    "followers_count": 100,
    "following_count": 50,
    "posts_count": 25,
    "is_following": false,
    "is_blocked": false
}
```

### Follow User

```http
POST /api/users/{username}/follow
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "following": true,
    "followers_count": 101
}
```

### Unfollow User

```http
DELETE /api/users/{username}/follow
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "following": false,
    "followers_count": 100
}
```

### Block User

```http
POST /api/users/{username}/block
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "blocked": true
}
```

### Unblock User

```http
DELETE /api/users/{username}/block
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "blocked": false
}
```

### Explore Users

```http
GET /api/explore
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 2,
            "name": "Jane Smith",
            "username": "janesmith",
            "avatar_url": "storage/avatars/avatar2.jpg",
            "followers_count": 200
        }
    ]
}
```

### Search Users

```http
GET /api/search-users?q=john
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "username": "johndoe",
            "avatar_url": "storage/avatars/avatar.jpg"
        }
    ]
}
```

---

## Stories API

> **Note**: Stories endpoints use web session authentication and are NOT prefixed with `/api/`.

### Get Active Stories

```http
GET /stories
Authorization: Cookie (web session)
```

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "slug": "story123",
            "user": {
                "id": 1,
                "name": "John Doe",
                "username": "johndoe",
                "avatar_url": "storage/avatars/avatar.jpg"
            },
            "media_type": "image",
            "media_path": "stories/story.jpg",
            "expires_at": "2026-03-28T10:00:00Z",
            "views_count": 5,
            "is_viewed": false
        }
    ]
}
```

### Create Story

```http
POST /stories
Authorization: Cookie (web session)
Content-Type: multipart/form-data
```

**Response (201 Created):**

### Delete Story

```http
DELETE /stories/{slug}
Authorization: Cookie (web session)
```

**Response (200 OK):**

### React to Story

```http
POST /stories/{user}/{story}/react
Authorization: Cookie (web session)
Content-Type: application/json
```

**Response (200 OK):**
```json
{
    "success": true,
    "reaction": {
        "id": 1,
        "reaction_type": "😍"
    }
}
```

---

## Chat API

> **Note**: Chat endpoints use web session authentication and are NOT prefixed with `/api/`.

### Get Conversations

```http
GET /chat/conversations
Authorization: Cookie (web session)
```

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "slug": "conv123",
            "is_group": false,
            "recipient": {
                "id": 2,
                "name": "Jane Smith",
                "username": "janesmith",
                "avatar_url": "storage/avatars/avatar2.jpg",
                "is_online": true
            },
            "last_message": {
                "id": 10,
                "content": "Hello!",
                "sender_id": 2,
                "created_at": "2026-03-27T10:00:00Z",
                "read_at": null
            },
            "unread_count": 2
        }
    ]
}
```

### Get Messages

```http
GET /chat/{conversation}/messages
Authorization: Cookie (web session)
```

**Query Parameters:**
- `after` (integer): Get messages after this ID
- `limit` (integer): Number of messages (default: 50)

**Response (200 OK):**

### Send Message

```http
POST /chat/{conversation}
Authorization: Cookie (web session)
Content-Type: application/json
```

**Response (201 Created):**

### Mark as Read

```http
POST /chat/{conversation}/read
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Send Typing Indicator

```http
POST /chat/{conversation}/typing
Authorization: Cookie (web session)
```
{
    "success": true,
    "typing": true
}
```

---

## Groups API

> **Note**: Groups endpoints use web session authentication and are NOT prefixed with `/api/`.

### Get Groups

```http
GET /groups
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Create Group

```http
POST /groups
Authorization: Cookie (web session)
Content-Type: application/json
```

**Response (201 Created):**

### Get Group

```http
GET /groups/{slug}
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Add Members

```http
POST /groups/{slug}/members
Authorization: Cookie (web session)
Content-Type: application/json
```

### Remove Member

```http
DELETE /groups/{slug}/members/{user_id}
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Make Admin

```http
POST /groups/{slug}/members/{user_id}/admin
Authorization: Cookie (web session)
```

**Response (200 OK):**

---

## Notifications API

> **Note**: Notifications endpoints are in `routes/api.php` and use web session authentication.

### Get Notifications

```http
GET /api/notifications
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Get Unread Count

```http
GET /api/notifications/unread-count
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Mark as Read

```http
POST /api/notifications/{id}/read
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Mark All as Read

```http
POST /api/notifications/mark-all-read
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Delete Notification

```http
DELETE /api/notifications/{id}
Authorization: Cookie (web session)
```

---

## Hashtags API

### Get Hashtag Suggestions

```http
GET /api/hashtags/suggestions?q=tech
```

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "technology",
            "slug": "technology",
            "posts_count": 100
        },
        {
            "id": 2,
            "name": "tech",
            "slug": "tech",
            "posts_count": 50
        }
    ]
}
```

---

## Events API

> **Note**: Events endpoints are in `routes/api.php` and use web session authentication.

### Get User Events

```http
GET /api/users/{user_id}/events
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Create Event

```http
POST /api/events
Authorization: Cookie (web session)
Content-Type: application/json
```

**Response (201 Created):**

### React to Event

```http
POST /api/events/{id}/react
Authorization: Cookie (web session)
Content-Type: application/json
```

---

## Push Notifications API

> **Note**: Push Notification endpoints are in `routes/api.php` and use web session authentication.

### Get VAPID Key

```http
GET /api/push/vapid-key
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Subscribe to Push

```http
POST /api/push/subscribe
Authorization: Cookie (web session)
Content-Type: application/json
```

**Response (201 Created):**

### Update Push Settings

```http
PUT /api/push/settings
Authorization: Cookie (web session)
Content-Type: application/json
```

**Response (200 OK):**

### Get Push Settings

```http
GET /api/push/settings
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Unsubscribe from Push

```http
DELETE /api/push/unsubscribe
Authorization: Cookie (web session)
```

**Response (200 OK):**

### Send Test Push

```http
POST /api/push/test
Authorization: Cookie (web session)
```

**Response (200 OK):**

---

## Error Handling

### Error Response Format

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Validation error"]
    }
}
```

### HTTP Status Codes

- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **429**: Too Many Requests
- **500**: Server Error

### Rate Limit Response

```json
HTTP 429 Too Many Requests

{
    "success": false,
    "message": "Too many requests. Please try again in 60 seconds.",
    "retry_after": 60
}
```

### Validation Error Response

```json
HTTP 422 Unprocessable Entity

{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

## SDK Examples

### JavaScript (Axios)

```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add auth token to requests
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Get feed
const getFeed = async () => {
    const response = await api.get('/posts');
    return response.data;
};

// Create post
const createPost = async (content, media) => {
    const formData = new FormData();
    formData.append('content', content);
    media.forEach(file => formData.append('media[]', file));
    
    const response = await api.post('/posts', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    });
    return response.data;
};
```

### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://localhost/api',
    'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $token,
    ],
]);

// Get feed
$response = $client->get('/posts');
$feed = json_decode($response->getBody(), true);

// Create post
$response = $client->post('/posts', [
    'multipart' => [
        [
            'name' => 'content',
            'contents' => 'Hello world!'
        ],
        [
            'name' => 'media[0]',
            'contents' => fopen('image.jpg', 'r')
        ]
    ]
]);
```

---

<div align="center">

**Nexus - API Reference**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
