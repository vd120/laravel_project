# API Reference

Complete RESTful API documentation for Nexus.

---

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Rate Limiting](#rate-limiting)
- [Endpoints](#endpoints)
  - [Authentication](#authentication-endpoints)
  - [Posts](#posts)
  - [Comments](#comments)
  - [Stories](#stories)
  - [Users](#users)
  - [Chat](#chat)
  - [Groups](#groups)
  - [Notifications](#notifications)
  - [Admin](#admin)
- [Error Handling](#error-handling)
- [Response Formats](#response-formats)

---

## Overview

### Base URL

```
Development: http://localhost:8000
Production:  https://your-domain.com
```

### Content Types

| Type | Header |
|------|--------|
| JSON | `Content-Type: application/json` |
| Form Data | `Content-Type: multipart/form-data` |
| URL Encoded | `Content-Type: application/x-www-form-urlencoded` |

### Authentication

Most endpoints require authentication via session cookie or Sanctum token.

```
Authorization: Bearer {token}  (API)
Cookie: laravel_session={session}  (Web)
```

---

## Rate Limiting

API endpoints are rate-limited to prevent abuse.

| Endpoint Type | Limit | Window |
|---------------|-------|--------|
| Authentication | 5 requests | 1 minute |
| Posts | 30 requests | 1 minute |
| Comments | 20 requests | 1 minute |
| Verification | 3 requests | 1 hour |
| General API | 60 requests | 1 minute |

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
Retry-After: 60
```

### Rate Limit Response (429)

```json
{
    "success": false,
    "message": "Too many requests. Please try again in 60 seconds.",
    "retry_after": 60
}
```

---

## Authentication Endpoints

### Register User

**POST** `/register`

**Rate Limit:** 5 per minute

**Request:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (302):**
```
Redirect: /email/verify
Session: pending_verification_user_id
```

**Validation Errors (422):**
```json
{
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

### Login

**POST** `/login`

**Rate Limit:** 5 per minute

**Request:**
```json
{
    "email": "john@example.com",
    "password": "password123",
    "remember": true
}
```

**Response (302):**
```json
{
    "success": true,
    "redirect": "/"
}
```

**Error (401):**
```json
{
    "success": false,
    "message": "Invalid credentials."
}
```

---

### Logout

**POST** `/logout`

**Auth Required:** Yes

**Response (302):**
```
Redirect: /
```

---

### Request Password Reset

**POST** `/forgot-password`

**Rate Limit:** 5 per minute

**Request:**
```json
{
    "email": "john@example.com"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password reset link sent to your email."
}
```

---

### Reset Password

**POST** `/reset-password`

**Request:**
```json
{
    "token": "reset-token",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password reset successfully."
}
```

---

### Verify Email Code

**POST** `/email/verify-code`

**Rate Limit:** 3 per hour

**Request:**
```json
{
    "code": "123456"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Email verified successfully."
}
```

**Error (400):**
```json
{
    "success": false,
    "message": "Invalid verification code."
}
```

---

### Resend Verification Code

**POST** `/email/verification-notification`

**Rate Limit:** 3 per hour

**Response (200):**
```json
{
    "success": true,
    "message": "Verification code sent!"
}
```

---

### Google OAuth

**GET** `/auth/google`

**Description:** Redirects to Google OAuth consent screen.

**Response (302):**
```
Redirect: https://accounts.google.com/o/oauth2/auth?...
```

---

**GET** `/auth/google/callback`

**Description:** Google OAuth callback handler.

**Response (302):**
```
Redirect: / (if authenticated)
Redirect: /set-password (if new user without password)
```

---

## Posts

### Get Post Feed

**GET** `/`

**Auth Required:** Yes

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 15 | Items per page |

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "slug": "abc123...",
            "content": "Hello world!",
            "is_private": false,
            "created_at": "2026-03-15T10:00:00Z",
            "user": {
                "id": 1,
                "name": "John Doe",
                "username": "johndoe",
                "profile": {
                    "avatar": "avatars/user1.jpg",
                    "is_private": false
                }
            },
            "media": [
                {
                    "id": 1,
                    "media_type": "image",
                    "media_path": "posts/abc123.jpg",
                    "media_thumbnail": null,
                    "sort_order": 1
                }
            ],
            "likes_count": 5,
            "comments_count": 2,
            "is_liked": false,
            "is_saved": false
        }
    ],
    "links": {
        "first": "/?page=1",
        "last": "/?page=5",
        "prev": null,
        "next": "/?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

---

### Create Post

**POST** `/posts`

**Auth Required:** Yes

**Rate Limit:** 30 per minute

**Content-Type:** `multipart/form-data`

**Request:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| content | string | Conditional | Post text (max 280 chars) |
| is_private | boolean | No | Privacy setting (default: false) |
| media[] | file[] | Conditional | Up to 30 files (max 50MB each) |

**Note:** Either `content` OR `media` is required.

**Response (302):**
```
Redirect: back
Flash: success = "Post created successfully!"
```

**Validation Errors (422):**
```json
{
    "errors": {
        "content": ["Post must have content or media."],
        "media.0": ["The file must not exceed 50MB."]
    }
}
```

---

### Get Single Post

**GET** `/posts/{slug}`

**Auth Required:** Yes

**Response (200):**
```json
{
    "id": 1,
    "slug": "abc123...",
    "content": "Hello world!",
    "is_private": false,
    "created_at": "2026-03-15T10:00:00Z",
    "updated_at": "2026-03-15T10:00:00Z",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "profile": {
            "avatar": "avatars/user1.jpg",
            "bio": "Software developer",
            "is_private": false
        }
    },
    "media": [
        {
            "id": 1,
            "media_type": "image",
            "media_path": "/storage/posts/abc123.jpg",
            "media_thumbnail": null,
            "sort_order": 1
        }
    ],
    "likes": [
        {
            "id": 1,
            "user_id": 2,
            "user": {
                "name": "Jane Doe",
                "username": "janedoe",
                "avatar": "avatars/user2.jpg"
            }
        }
    ],
    "comments": [
        {
            "id": 1,
            "content": "Great post!",
            "user_id": 2,
            "parent_id": null,
            "created_at": "2026-03-15T10:30:00Z",
            "user": {
                "name": "Jane Doe",
                "username": "janedoe",
                "avatar": "avatars/user2.jpg"
            },
            "likes_count": 2
        }
    ],
    "is_liked": false,
    "is_saved": false
}
```

---

### Like Post

**POST** `/posts/{post}/like`

**Auth Required:** Yes

**Rate Limit:** 30 per minute

**Route Parameter:**
- `post`: 24-character slug

**Response (302):**
```
Redirect: back
Flash: success = "Post liked!" or "Post unliked."
```

---

### Save Post

**POST** `/posts/{post}/save`

**Auth Required:** Yes

**Rate Limit:** 30 per minute

**Route Parameter:**
- `post`: 24-character slug

**Response (302):**
```
Redirect: back
Flash: success = "Post saved!" or "Post unsaved."
```

---

### Get Post Likers

**GET** `/posts/{post}/likers`

**Auth Required:** Yes

**Route Parameter:**
- `post`: 24-character slug

**Response (200):**
```json
{
    "likers": [
        {
            "id": 1,
            "name": "John Doe",
            "username": "johndoe",
            "avatar": "avatars/user1.jpg",
            "is_following": true
        }
    ],
    "total": 5
}
```

---

### Delete Post

**DELETE** `/posts/{slug}`

**Auth Required:** Yes

**Authorization:** Post owner or Admin

**Response (302):**
```
Redirect: back
Flash: success = "Post deleted successfully!"
```

**Error (403):**
```json
{
    "success": false,
    "message": "Unauthorized action."
}
```

---

## Comments

### Create Comment

**POST** `/comments`

**Auth Required:** Yes

**Rate Limit:** 20 per minute

**Request:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| post_id | int | Yes | Post ID |
| content | string | Yes | Comment text (max 280 chars) |
| parent_id | int | No | Parent comment ID (for replies) |

**Request Body:**
```json
{
    "post_id": 1,
    "content": "Great post!",
    "parent_id": null
}
```

**Response (302):**
```
Redirect: back
Flash: success = "Comment added!"
```

**Validation Errors (422):**
```json
{
    "errors": {
        "post_id": ["The post does not exist."],
        "content": ["The comment text is required."]
    }
}
```

---

### Delete Comment

**DELETE** `/comments/{comment}`

**Auth Required:** Yes

**Authorization:** Comment owner, Post owner, or Admin

**Response (302):**
```
Redirect: back
Flash: success = "Comment deleted!"
```

---

### Like Comment

**POST** `/comments/{comment}/like`

**Auth Required:** Yes

**Response (302):**
```
Redirect: back
Flash: success = "Comment liked!" or "Comment unliked."
```

---

## Stories

### Get Stories

**GET** `/stories`

**Auth Required:** Yes

**Response (200):**
```json
{
    "stories": [
        {
            "id": 1,
            "slug": "story123...",
            "user": {
                "id": 1,
                "name": "John Doe",
                "username": "johndoe",
                "avatar": "avatars/user1.jpg"
            },
            "media_type": "image",
            "media_path": "/storage/stories/story123.jpg",
            "content": "Having a great day!",
            "expires_at": "2026-03-16T10:00:00Z",
            "views": 25,
            "has_viewed": false,
            "has_reaction": null,
            "created_at": "2026-03-15T10:00:00Z"
        }
    ]
}
```

---

### Create Story

**POST** `/stories`

**Auth Required:** Yes

**Rate Limit:** 30 per minute

**Content-Type:** `multipart/form-data`

**Request:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| media | file | Yes | Image or video (max 50MB) |
| content | string | No | Caption (max 280 chars) |

**Response (302):**
```
Redirect: /stories
Flash: success = "Story created!"
```

**Validation Errors (422):**
```json
{
    "errors": {
        "media": ["The media field is required."],
        "content": ["The content must not exceed 280 characters."]
    }
}
```

---

### View Story

**GET** `/stories/{user}/{story}`

**Auth Required:** Yes

**Route Parameters:**
- `user`: Username
- `story`: 24-character slug

**Response (200):**
```json
{
    "id": 1,
    "slug": "story123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "avatar": "avatars/user1.jpg",
        "profile": {
            "is_private": false
        }
    },
    "media_type": "image",
    "media_path": "/storage/stories/story123.jpg",
    "content": "Having a great day!",
    "expires_at": "2026-03-16T10:00:00Z",
    "views": 25,
    "created_at": "2026-03-15T10:00:00Z"
}
```

**Error (403):**
```json
{
    "success": false,
    "message": "This story is private."
}
```

**Error (404):**
```json
{
    "success": false,
    "message": "Story expired"
}
```

---

### React to Story

**POST** `/stories/{user}/{story}/react`

**Auth Required:** Yes

**Rate Limit:** 30 per minute

**Request:**
```json
{
    "reaction": "❤️"
}
```

**Response (302):**
```
Redirect: back
Flash: success = "Reaction added!"
```

---

### Get Story Reactions

**GET** `/stories/{user}/{story}/reactions`

**Auth Required:** Yes

**Response (200):**
```json
{
    "reactions": [
        {
            "id": 1,
            "user_id": 2,
            "reaction_type": "❤️",
            "user": {
                "name": "Jane Doe",
                "username": "janedoe",
                "avatar": "avatars/user2.jpg"
            },
            "created_at": "2026-03-15T10:30:00Z"
        }
    ],
    "total": 5
}
```

---

### Get Story Viewers

**GET** `/stories/{user}/{story}/viewers`

**Auth Required:** Yes

**Authorization:** Story owner only

**Response (200):**
```json
{
    "viewers": [
        {
            "id": 2,
            "user_id": 2,
            "user": {
                "name": "Jane Doe",
                "username": "janedoe",
                "avatar": "avatars/user2.jpg"
            },
            "viewed_at": "2026-03-15T10:30:00Z"
        }
    ],
    "total_views": 25
}
```

---

### Delete Story

**DELETE** `/stories/{user}/{story}`

**Auth Required:** Yes

**Authorization:** Story owner or Admin

**Response (302):**
```
Redirect: back
Flash: success = "Story deleted!"
```

---

## Users

### Get User Profile

**GET** `/users/{user}`

**Auth Required:** Yes

**Route Parameter:**
- `user`: Username or ID

**Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "profile": {
        "avatar": "/storage/avatars/user1.jpg",
        "cover_image": "/storage/covers/user1.jpg",
        "bio": "Software developer",
        "website": "https://johndoe.com",
        "location": "New York",
        "birth_date": "1990-01-01",
        "occupation": "Developer",
        "is_private": false,
        "social_links": {
            "twitter": "@johndoe",
            "github": "johndoe"
        }
    },
    "followers_count": 150,
    "following_count": 100,
    "posts_count": 45,
    "is_following": false,
    "is_follower": false,
    "is_blocked": false
}
```

---

### Check Username Availability

**GET** `/api/check-username`

**Auth Required:** No (Public endpoint)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| username | string | Yes | Username to check |

**Response (200):**
```json
{
    "available": true,
    "username": "johndoe"
}
```

**Error (400):**
```json
{
    "available": false,
    "message": "Invalid username format"
}
```

---

### Check Username Availability (Legacy)

**GET** `/api/check-username/{username}`

**Auth Required:** No (Public endpoint)

**Route Parameter:**
- `username`: Username to check

**Response (200):**
```json
{
    "available": true,
    "username": "johndoe"
}
```

**Note:** This is a legacy endpoint for backward compatibility. Use the query parameter version above for new implementations.

---

### Search Users

**GET** `/api/search-users`

**Auth Required:** Yes (Web session)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| query | string | Yes | Search query |

**Response (200):**
```json
{
    "users": [
        {
            "id": 1,
            "username": "johndoe",
            "name": "John Doe",
            "avatar_url": "/storage/avatars/user1.jpg"
        }
    ],
    "total": 5
}
```

---

### Get User Posts

**GET** `/users/{user}/posts`

**Auth Required:** Yes

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "slug": "post123...",
            "content": "Hello world!",
            "media": [...],
            "likes_count": 5,
            "comments_count": 2,
            "created_at": "2026-03-15T10:00:00Z"
        }
    ],
    "meta": {
        "total": 45,
        "per_page": 15,
        "current_page": 1
    }
}
```

---

### Get Followers

**GET** `/users/{user}/followers`

**Auth Required:** Yes

**Response (200):**
```json
{
    "followers": [
        {
            "id": 2,
            "name": "Jane Doe",
            "username": "janedoe",
            "avatar": "avatars/user2.jpg",
            "bio": "Designer",
            "is_following": true,
            "pivot": {
                "created_at": "2026-03-01T10:00:00Z"
            }
        }
    ],
    "total": 150
}
```

---

### Get Following

**GET** `/users/{user}/following`

**Auth Required:** Yes

**Response (200):**
```json
{
    "following": [
        {
            "id": 3,
            "name": "Bob Smith",
            "username": "bobsmith",
            "avatar": "avatars/user3.jpg",
            "bio": "Photographer",
            "is_following": false,
            "pivot": {
                "created_at": "2026-03-02T10:00:00Z"
            }
        }
    ],
    "total": 100
}
```

---

### Follow User

**POST** `/users/{user}/follow`

**Auth Required:** Yes

**Response (302):**
```
Redirect: back
Flash: success = "Following!" or "Unfollowed."
```

---

### Block User

**POST** `/users/{user}/block`

**Auth Required:** Yes

**Response (302):**
```
Redirect: back
Flash: success = "User blocked!" or "User unblocked."
```

---

### Get Blocked Users

**GET** `/users/{user}/blocked`

**Auth Required:** Yes

**Response (200):**
```json
{
    "blocked": [
        {
            "id": 5,
            "name": "Spam User",
            "username": "spamuser",
            "avatar": "avatars/user5.jpg",
            "blocked_at": "2026-03-10T10:00:00Z"
        }
    ],
    "total": 3
}
```

---

### Get Username Lookup

**GET** `/api/user/{user}/username`

**Auth Required:** Yes

**Route Parameter:**
- `user`: User ID or username

**Response (200):**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "username": "johndoe"
    }
}
```

---

### Update Online Status

**POST** `/user/online-status`

**Auth Required:** Yes

**Description:** Update the current user's online status.

**Request Body:**
```json
{
    "is_online": true
}
```

**Response (200):**
```json
{
    "success": true,
    "is_online": true,
    "last_active": "2026-03-15T10:30:00Z"
}
```

---

### Set Offline Status

**POST** `/user/online-status/offline`

**Auth Required:** Yes

**Description:** Explicitly set the current user's status to offline.

**Response (200):**
```json
{
    "success": true,
    "is_online": false
}
```

---

### Get User Online Status

**GET** `/user/{user}/online-status`

**Auth Required:** Yes

**Route Parameter:**
- `user`: User ID or username

**Response (200):**
```json
{
    "success": true,
    "user_id": 1,
    "username": "johndoe",
    "is_online": true,
    "last_active": "2026-03-15T10:30:00Z"
}
```

---

### Batch Online Status Check

**POST** `/user/online-status/batch`

**Auth Required:** Yes

**Description:** Check online status for multiple users at once.

**Request Body:**
```json
{
    "user_ids": [1, 2, 3]
}
```

**Response (200):**
```json
{
    "success": true,
    "statuses": [
        {
            "user_id": 1,
            "username": "johndoe",
            "is_online": true,
            "last_active": "2026-03-15T10:30:00Z"
        },
        {
            "user_id": 2,
            "username": "janedoe",
            "is_online": false,
            "last_active": "2026-03-15T09:00:00Z"
        }
    ]
}
```

---

### Get Saved Posts

**GET** `/saved-posts`

**Auth Required:** Yes

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "slug": "post123...",
            "content": "Great tips!",
            "user": {
                "name": "Jane Doe",
                "username": "janedoe"
            },
            "media": [...],
            "saved_at": "2026-03-14T10:00:00Z"
        }
    ],
    "meta": {
        "total": 20,
        "per_page": 15,
        "current_page": 1
    }
}
```

---

### Update Profile

**POST** `/profile/{user}/update`

**Auth Required:** Yes

**Content-Type:** `multipart/form-data`

**Request:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | No | Display name |
| username | string | No | Username |
| bio | string | No | Bio (max 255 chars) |
| website | string | No | Website URL |
| location | string | No | Location |
| birth_date | date | No | Birth date |
| occupation | string | No | Occupation |
| about | text | No | Extended about |
| phone | string | No | Phone number |
| gender | string | No | Gender |
| is_private | boolean | No | Private account |
| social_links | json | No | Social media links |
| avatar | file | No | Avatar image |
| cover_image | file | No | Cover image |

**Response (302):**
```
Redirect: back
Flash: success = "Profile updated successfully!"
```

---

### Delete Avatar

**DELETE** `/profile/delete-avatar`

**Auth Required:** Yes

**Response (302):**
```
Redirect: back
Flash: success = "Avatar deleted!"
```

---

### Delete Cover Image

**DELETE** `/profile/delete-cover`

**Auth Required:** Yes

**Response (302):**
```
Redirect: back
Flash: success = "Cover image deleted!"
```

---

### Delete Account

**DELETE** `/profile/delete-account`

**Auth Required:** Yes

**Response (302):**
```
Redirect: /login
Flash: success = "Account deleted successfully."
```

---

### Explore Users

**GET** `/explore`

**Auth Required:** Yes

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |

**Response (200):**
```json
{
    "users": [
        {
            "id": 10,
            "name": "New User",
            "username": "newuser",
            "avatar": "avatars/user10.jpg",
            "bio": "Just joined!",
            "followers_count": 5,
            "is_following": false
        }
    ],
    "meta": {
        "total": 500,
        "per_page": 20,
        "current_page": 1
    }
}
```

---

### Search Users

**GET** `/search`

**Auth Required:** Yes

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Search query |
| page | int | No | Page number |

**Response (200):**
```json
{
    "users": [
        {
            "id": 10,
            "name": "John Smith",
            "username": "johnsmith",
            "avatar": "avatars/user10.jpg",
            "bio": "Developer",
            "is_following": false
        }
    ],
    "query": "john",
    "total": 15
}
```

---

## Chat

### Get Conversations

**GET** `/chat/conversations`

**Auth Required:** Yes

**Response (200):**
```json
[
    {
        "id": 1,
        "slug": "conv123...",
        "is_group": false,
        "display_name": "Jane Doe",
        "display_avatar": "avatars/user2.jpg",
        "latest_message": {
            "id": 5,
            "content": "Hey! How are you?",
            "type": "text",
            "sender_id": 2,
            "created_at": "2026-03-15T10:30:00Z",
            "sender": {
                "name": "Jane Doe",
                "avatar": "avatars/user2.jpg"
            }
        },
        "unread_count": 2,
        "updated_at": "2026-03-15T10:30:00Z"
    }
]
```

---

### Get Updated Conversations (Polling)

**GET** `/chat/conversations/updated`

**Auth Required:** Yes

**Description:** Polling endpoint for updated conversations. Returns conversations with new messages or activity since the last check.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| last_message_at | string | No | ISO timestamp of last message check |
| last_unread_check | string | No | ISO timestamp of last unread check |

**Response (200):**
```json
{
    "success": true,
    "conversations": [
        {
            "id": 1,
            "slug": "conv123...",
            "is_group": false,
            "last_message_at": "2026-03-15T10:30:00Z",
            "unread_count": 2,
            "other_user": {
                "id": 2,
                "username": "janedoe",
                "avatar_url": "/storage/avatars/user2.jpg"
            },
            "latest_message": {
                "id": 5,
                "content": "Hey! How are you?",
                "sender_id": 2,
                "created_at": "2026-03-15T10:30:00Z",
                "read_at": null
            }
        }
    ],
    "timestamp": "2026-03-15T10:30:00Z"
}
```

---

### Get Conversations (API)

**GET** `/api/conversations`

**Auth Required:** Yes

**Description:** Alternative endpoint for fetching all conversations. Returns all user conversations including cleared threads.

**Response (200):**
```json
{
    "success": true,
    "conversations": [
        {
            "id": 1,
            "slug": "conv123...",
            "user1_id": 1,
            "user2_id": 2,
            "last_message_at": "2026-03-15T10:30:00Z",
            "unread_count": 2,
            "other_user": {
                "id": 2,
                "username": "janedoe",
                "avatar_url": "/storage/avatars/user2.jpg",
                "is_online": true,
                "last_active": "2026-03-15T10:30:00Z"
            },
            "latest_message": {
                "id": 5,
                "content": "Hey! How are you?",
                "type": "text",
                "media_path": null,
                "sender_id": 2,
                "created_at": "2026-03-15T10:30:00Z",
                "read_at": null
            }
        }
    ]
}
```

---

### Get Single Conversation

**GET** `/chat/{conversation}`

**Auth Required:** Yes

**Response (200):**
```json
{
    "id": 1,
    "slug": "conv123...",
    "is_group": false,
    "display_name": "Jane Doe",
    "display_avatar": "avatars/user2.jpg",
    "messages": [
        {
            "id": 1,
            "content": "Hello!",
            "type": "text",
            "sender_id": 1,
            "created_at": "2026-03-15T09:00:00Z",
            "read_at": "2026-03-15T09:01:00Z",
            "delivered_at": "2026-03-15T09:00:01Z",
            "sender": {
                "name": "You",
                "avatar": "avatars/user1.jpg"
            }
        },
        {
            "id": 2,
            "content": "Hi there!",
            "type": "text",
            "sender_id": 2,
            "created_at": "2026-03-15T09:02:00Z",
            "read_at": null,
            "delivered_at": "2026-03-15T09:02:01Z",
            "sender": {
                "name": "Jane Doe",
                "avatar": "avatars/user2.jpg"
            }
        }
    ],
    "other_user": {
        "id": 2,
        "name": "Jane Doe",
        "username": "janedoe",
        "avatar": "avatars/user2.jpg",
        "is_online": true,
        "last_active": "2026-03-15T10:30:00Z"
    }
}
```

---

### Send Message

**POST** `/chat/{conversation}`

**Auth Required:** Yes

**Content-Type:** `multipart/form-data`

**Request:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| content | string | Conditional | Message text |
| type | string | No | text/image/file |
| media | file | Conditional | Attachment |

**Response (200):**
```json
{
    "message": {
        "id": 10,
        "content": "Hello!",
        "type": "text",
        "sender_id": 1,
        "conversation_id": 1,
        "created_at": "2026-03-15T10:35:00Z",
        "delivered_at": null,
        "read_at": null,
        "sender": {
            "id": 1,
            "name": "You",
            "avatar": "avatars/user1.jpg"
        }
    }
}
```

---

### Mark as Read

**POST** `/chat/{conversation}/read`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true
}
```

---

### Get Message Statuses

**POST** `/chat/{conversation}/status`

**Auth Required:** Yes

**Response (200):**
```json
{
    "statuses": [
        {
            "message_id": 5,
            "delivered_at": "2026-03-15T10:30:01Z",
            "read_at": "2026-03-15T10:31:00Z"
        }
    ]
}
```

---

### Delete Message

**DELETE** `/chat/message/{message}`

**Auth Required:** Yes

**Request:**
```json
{
    "delete_for": "me"  // or "everyone"
}
```

**Response (200):**
```json
{
    "success": true
}
```

---

### Clear Conversation

**DELETE** `/chat/{conversation}/clear`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true
}
```

---

### Start Conversation

**GET** `/chat/start/{userId}`

**Auth Required:** Yes

**Route Parameter:**
- `userId`: User ID to chat with

**Response (302):**
```
Redirect: /chat/{conversationSlug}
```

---

### Typing Indicator

**POST** `/chat/{conversation}/typing`

**Auth Required:** Yes

**Request:**
```json
{
    "is_typing": true
}
```

**Response (200):**
```json
{
    "success": true
}
```

---

### Get Typing Status

**GET** `/chat/{conversation}/typing`

**Auth Required:** Yes

**Response (200):**
```json
{
    "is_typing": true,
    "typing_users": [
        {
            "id": 2,
            "name": "Jane Doe"
        }
    ]
}
```

---

### Get Online Status

**GET** `/user/{user}/online-status`

**Auth Required:** Yes

**Response (200):**
```json
{
    "user_id": 2,
    "is_online": true,
    "last_active": "2026-03-15T10:30:00Z"
}
```

---

### Update Online Status

**POST** `/user/online-status`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true,
    "is_online": true
}
```

---

### Set Offline Status

**POST** `/user/online-status/offline`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true,
    "is_online": false
}
```

---

## Groups

### Get All Groups

**GET** `/groups`

**Auth Required:** Yes

**Response (200):**
```json
{
    "groups": [
        {
            "id": 1,
            "name": "Developer Community",
            "slug": "developer-community-abc123",
            "description": "A group for developers",
            "avatar": "/storage/groups/group1.jpg",
            "is_private": false,
            "member_count": 50,
            "is_member": true,
            "is_admin": false,
            "created_at": "2026-03-01T10:00:00Z"
        }
    ]
}
```

---

### Create Group

**POST** `/groups`

**Auth Required:** Yes

**Content-Type:** `multipart/form-data`

**Request:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Group name (max 255) |
| description | string | No | Description (max 1000) |
| is_private | boolean | No | Private group |
| avatar | file | No | Group avatar |
| member_ids[] | int[] | No | Initial member IDs |

**Response (302):**
```
Redirect: /groups/{slug}
Flash: success = "Group created successfully!"
```

---

### Get Group

**GET** `/groups/{slug}`

**Auth Required:** Yes

**Response (200):**
```json
{
    "id": 1,
    "name": "Developer Community",
    "slug": "developer-community-abc123",
    "description": "A group for developers",
    "avatar": "/storage/groups/group1.jpg",
    "is_private": false,
    "creator": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe"
    },
    "members": [
        {
            "id": 1,
            "user_id": 1,
            "role": "admin",
            "user": {
                "name": "John Doe",
                "username": "johndoe",
                "avatar": "avatars/user1.jpg"
            }
        }
    ],
    "member_count": 50,
    "is_member": true,
    "is_admin": false,
    "invite_link": "https://app.com/join/abc123xyz",
    "created_at": "2026-03-01T10:00:00Z"
}
```

---

### Update Group

**PUT** `/groups/{slug}`

**Auth Required:** Yes

**Authorization:** Group admin only

**Request:**

| Field | Type | Required |
|-------|------|----------|
| name | string | No |
| description | string | No |
| is_private | boolean | No |
| avatar | file | No |

**Response (302):**
```
Redirect: back
Flash: success = "Group updated successfully!"
```

---

### Delete Group

**DELETE** `/groups/{slug}`

**Auth Required:** Yes

**Authorization:** Group admin only

**Response (302):**
```
Redirect: /groups
Flash: success = "Group deleted!"
```

---

### Add Members

**POST** `/groups/{slug}/members`

**Auth Required:** Yes

**Authorization:** Group admin only

**Request:**
```json
{
    "member_ids": [2, 3, 4]
}
```

**Response (302):**
```
Redirect: back
Flash: success = "Members added!"
```

---

### Remove Member

**DELETE** `/groups/{slug}/members/{userId}`

**Auth Required:** Yes

**Authorization:** Group admin or self

**Response (302):**
```
Redirect: back
Flash: success = "Member removed!"
```

---

### Make Admin

**POST** `/groups/{slug}/members/{userId}/admin`

**Auth Required:** Yes

**Authorization:** Group admin only

**Response (302):**
```
Redirect: back
Flash: success = "User promoted to admin!"
```

---

### Remove Admin

**DELETE** `/groups/{slug}/members/{userId}/admin`

**Auth Required:** Yes

**Authorization:** Group admin only

**Response (302):**
```
Redirect: back
Flash: success = "Admin role removed!"
```

---

### Regenerate Invite Link

**POST** `/groups/{slug}/regenerate-invite`

**Auth Required:** Yes

**Authorization:** Group admin only

**Response (302):**
```
Redirect: back
Flash: success = "Invite link regenerated!"
```

---

### Quick Invite

**POST** `/groups/{slug}/quick-invite`

**Auth Required:** Yes

**Authorization:** Group member only

**Request:**
```json
{
    "usernames": ["user1", "user2"]
}
```

**Response (302):**
```
Redirect: back
Flash: success = "Invitations sent!"
```

---

### Accept Invite

**POST** `/groups/accept-invite/{inviteLink}`

**Auth Required:** Yes

**Response (302):**
```
Redirect: /groups/{slug}
Flash: success = "Joined group!"
```

---

### Join via Invite Link

**GET** `/join/{inviteLink}`

**Auth Required:** Yes

**Response (302):**
```
Redirect: /groups/{slug}
Flash: success = "Joined group!"
```

---

## Notifications

### Get Notifications

**GET** `/notifications`

**Auth Required:** Yes

**Response (200):**
```json
{
    "notifications": [
        {
            "id": 1,
            "type": "like",
            "data": {
                "user_id": 2,
                "user_name": "Jane Doe",
                "post_id": 5
            },
            "read_at": null,
            "created_at": "2026-03-15T10:30:00Z",
            "related": {
                "type": "post",
                "id": 5,
                "url": "/posts/abc123"
            }
        }
    ],
    "unread_count": 5
}
```

---

### Mark Notification as Read

**POST** `/notifications/{id}/read`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true
}
```

---

### Mark All as Read

**POST** `/notifications/read-all`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true,
    "marked_count": 5
}
```

---

### Delete Notification

**DELETE** `/notifications/{id}`

**Auth Required:** Yes

**Response (200):**
```json
{
    "success": true
}
```

---

### Get Realtime Notification Updates

**GET** `/api/notifications/realtime-updates`

**Auth Required:** Yes (Web session)

**Description:** Polling endpoint for real-time notification updates. Returns unread count and new notifications since last check.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| last_check | string | No | ISO timestamp of last check |

**Response (200):**
```json
{
    "success": true,
    "unread_count": 5,
    "new_notifications": [
        {
            "id": 10,
            "type": "like",
            "data": {
                "user_id": 2,
                "user_name": "Jane Doe",
                "post_id": 5
            },
            "read_at": null,
            "created_at": "2026-03-15T11:00:00Z"
        }
    ],
    "timestamp": "2026-03-15T11:00:00Z"
}
```

---

## Admin

### Get Dashboard

**GET** `/admin`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (200):**
```json
{
    "stats": {
        "total_users": 1500,
        "total_posts": 5000,
        "total_comments": 15000,
        "total_stories": 500
    },
    "recent_users": [...],
    "recent_posts": [...]
}
```

---

### Get All Users

**GET** `/admin/users`

**Auth Required:** Yes

**Authorization:** Admin only

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| search | string | null | Search query |
| status | string | all | active/suspended |

**Response (200):**
```json
{
    "users": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "is_admin": false,
            "is_suspended": false,
            "email_verified_at": "2026-03-01T10:00:00Z",
            "created_at": "2026-03-01T10:00:00Z"
        }
    ],
    "meta": {
        "total": 1500,
        "per_page": 20,
        "current_page": 1
    }
}
```

---

### Get User Detail

**GET** `/admin/users/{user}`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "is_admin": false,
    "is_suspended": false,
    "email_verified_at": "2026-03-01T10:00:00Z",
    "created_at": "2026-03-01T10:00:00Z",
    "profile": {...},
    "posts_count": 45,
    "followers_count": 150,
    "following_count": 100
}
```

---

### Update User

**PUT** `/admin/users/{user}`

**Auth Required:** Yes

**Authorization:** Admin only

**Request:**

| Field | Type | Required |
|-------|------|----------|
| name | string | No |
| email | string | No |
| username | string | No |
| is_admin | boolean | No |
| is_suspended | boolean | No |

**Response (302):**
```
Redirect: back
Flash: success = "User updated!"
```

---

### Delete User

**DELETE** `/admin/users/{user}`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (302):**
```
Redirect: /admin/users
Flash: success = "User deleted!"
```

---

### Get All Posts

**GET** `/admin/posts`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (200):**
```json
{
    "posts": [
        {
            "id": 1,
            "content": "Post content...",
            "user": {
                "name": "John Doe",
                "email": "john@example.com"
            },
            "is_private": false,
            "created_at": "2026-03-15T10:00:00Z"
        }
    ],
    "meta": {...}
}
```

---

### Delete Post

**DELETE** `/admin/posts/{post}`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (302):**
```
Redirect: back
Flash: success = "Post deleted!"
```

---

### Get All Comments

**GET** `/admin/comments`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (200):**
```json
{
    "comments": [...],
    "meta": {...}
}
```

---

### Delete Comment

**DELETE** `/admin/comments/{comment}`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (302):**
```
Redirect: back
Flash: success = "Comment deleted!"
```

---

### Get All Stories

**GET** `/admin/stories`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (200):**
```json
{
    "stories": [...],
    "meta": {...}
}
```

---

### Delete Story

**DELETE** `/admin/stories/{story}`

**Auth Required:** Yes

**Authorization:** Admin only

**Response (302):**
```
Redirect: back
Flash: success = "Story deleted!"
```

---

### Create Admin

**POST** `/admin/create-admin`

**Auth Required:** Yes

**Authorization:** Admin only

**Request:**
```json
{
    "name": "New Admin",
    "email": "admin2@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (302):**
```
Redirect: back
Flash: success = "Admin account created!"
```

---

## Error Handling

### Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Error message"]
    }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 302 | Redirect |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 419 | CSRF Token Mismatch |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

### Validation Error Response (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Unauthorized Response (401)

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### Forbidden Response (403)

```json
{
    "success": false,
    "message": "Unauthorized action."
}
```

### Not Found Response (404)

```json
{
    "success": false,
    "message": "Not found."
}
```

---

## Response Formats

### Success Response

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {...}
}
```

### Paginated Response

```json
{
    "data": [...],
    "links": {
        "first": "/api/posts?page=1",
        "last": "/api/posts?page=10",
        "prev": null,
        "next": "/api/posts?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

### Redirect Response

```json
{
    "success": true,
    "redirect": "/dashboard"
}
```

---

## Next Steps

Continue reading:

- [Database Schema](DATABASE.md) - Table definitions
- [Features](FEATURES.md) - Feature documentation
- [Frontend Guide](FRONTEND.md) - Vue.js architecture
