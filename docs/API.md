# Nexus API Documentation

## Overview

Nexus provides both web routes (Inertia.js) and a RESTful API for programmatic access. The API uses Laravel Sanctum for token-based authentication.

### Base URL
```
Production: https://your-domain.com
Development: http://localhost:8000
```

### Authentication

Most API endpoints require authentication via Sanctum tokens.

**Getting a Token:**
```bash
POST /api/token
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "token": "1|abc123...",
  "user": { ... }
}
```

**Using a Token:**
```bash
Authorization: Bearer 1|abc123...
```

### Response Format

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

### Rate Limiting

- API requests: 60 requests per minute
- Login attempts: 5 attempts per minute

---

## Table of Contents

1. [Authentication](#authentication)
2. [Users](#users)
3. [Posts](#posts)
4. [Comments](#comments)
5. [Stories](#stories)
6. [Notifications](#notifications)
7. [Chat & Messages](#chat--messages)
8. [Groups](#groups)
9. [Search & Explore](#search--explore)

---

## Authentication

### Login

**Endpoint:** `POST /api/login`

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123xyz",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "username": "johndoe",
      "avatar_url": "https://..."
    }
  }
}
```

---

### Register

**Endpoint:** `POST /api/register`

**Body:**
```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "user@example.com",
  "password": "strongpassword123",
  "password_confirmation": "strongpassword123"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `username`: required, string, unique, 3-30 chars, alphanumeric/underscore
- `email`: required, email, unique
- `password`: required, min:8, mixed case, numbers

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123xyz",
    "user": { ... }
  },
  "message": "Registration successful. Please verify your email."
}
```

---

### Logout

**Endpoint:** `POST /api/logout`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Password Reset Request

**Endpoint:** `POST /api/forgot-password`

**Body:**
```json
{
  "email": "user@example.com"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

---

### Password Reset

**Endpoint:** `POST /api/reset-password`

**Body:**
```json
{
  "token": "reset_token_from_email",
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

---

### Email Verification

**Endpoint:** `POST /api/email/verify-code`

**Body:**
```json
{
  "code": "123456"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

---

### Change Password (Authenticated)

**Endpoint:** `POST /api/password/change`

**Auth:** Required

**Body:**
```json
{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

## Users

### Get User Profile

**Endpoint:** `GET /api/users/{user}`

**Auth:** Required

**Path Parameters:**
- `user`: User ID or username

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "avatar_url": "https://...",
    "profile": {
      "bio": "Software developer",
      "location": "New York",
      "website": "https://johndoe.com",
      "occupation": "Engineer",
      "about": "Passionate about coding...",
      "is_private": false
    },
    "followers_count": 150,
    "following_count": 200,
    "posts_count": 45,
    "is_following": false,
    "is_blocked": false
  }
}
```

---

### Get User Posts

**Endpoint:** `GET /api/users/{user}/posts`

**Auth:** Required

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "posts": [ ... ],
    "links": {
      "first": "...",
      "last": "...",
      "prev": null,
      "next": "..."
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "path": "...",
      "per_page": 15,
      "to": 15,
      "total": 75
    }
  }
}
```

---

### Get Followers

**Endpoint:** `GET /api/users/{user}/followers`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "username": "janesmith",
      "avatar_url": "https://...",
      "is_following": true
    }
  ]
}
```

---

### Get Following

**Endpoint:** `GET /api/users/{user}/following`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "name": "Bob Wilson",
      "username": "bobwilson",
      "avatar_url": "https://...",
      "is_following": false
    }
  ]
}
```

---

### Follow User

**Endpoint:** `POST /api/users/{user}/follow`

**Auth:** Required

**Path Parameters:**
- `user`: User ID

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "following": true,
    "followers_count": 151
  },
  "message": "Successfully followed user"
}
```

---

### Unfollow User

**Endpoint:** `POST /api/users/{user}/unfollow`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "following": false,
    "followers_count": 150
  },
  "message": "Successfully unfollowed user"
}
```

---

### Block User

**Endpoint:** `POST /api/users/{user}/block`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "User blocked successfully"
}
```

---

### Unblock User

**Endpoint:** `POST /api/users/{user}/unblock`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "User unblocked successfully"
}
```

---

### Get Blocked Users

**Endpoint:** `GET /api/users/blocked`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "name": "Spam User",
      "username": "spamuser",
      "avatar_url": "https://...",
      "blocked_at": "2026-03-01T10:00:00Z"
    }
  ]
}
```

---

### Get Saved Posts

**Endpoint:** `GET /api/users/saved-posts`

**Auth:** Required

**Query Parameters:**
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "posts": [ ... ],
    "meta": { ... }
  }
}
```

---

### Update Profile

**Endpoint:** `POST /api/profile/{user}/update`

**Auth:** Required

**Body:**
```json
{
  "name": "John Updated",
  "bio": "Updated bio",
  "location": "Los Angeles",
  "website": "https://newsite.com",
  "occupation": "Senior Engineer",
  "about": "More about me...",
  "phone": "+1234567890",
  "gender": "male",
  "is_private": false,
  "social_links": {
    "twitter": "@johndoe",
    "github": "johndoe",
    "linkedin": "john-doe"
  }
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "profile": { ... }
  },
  "message": "Profile updated successfully"
}
```

---

### Upload Avatar

**Endpoint:** `POST /api/profile/{user}/avatar`

**Auth:** Required

**Body (multipart/form-data):**
```
avatar: (file)
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "avatar_url": "https://..."
  },
  "message": "Avatar uploaded successfully"
}
```

---

### Delete Avatar

**Endpoint:** `DELETE /api/profile/delete-avatar`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Avatar deleted successfully"
}
```

---

### Upload Cover Image

**Endpoint:** `POST /api/profile/{user}/cover`

**Auth:** Required

**Body (multipart/form-data):**
```
cover_image: (file)
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "cover_image_url": "https://..."
  },
  "message": "Cover image uploaded successfully"
}
```

---

### Delete Cover Image

**Endpoint:** `DELETE /api/profile/delete-cover`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Cover image deleted successfully"
}
```

---

### Delete Account

**Endpoint:** `DELETE /api/profile/delete-account`

**Auth:** Required

**Body:**
```json
{
  "password": "confirm_password"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Account deleted successfully"
}
```

---

### Check Username Availability

**Endpoint:** `GET /api/check-username/{username}`

**Auth:** Optional

**Response (200 OK):**
```json
{
  "success": true,
  "available": true
}
```

---

### Search Users

**Endpoint:** `GET /api/search-users`

**Auth:** Required

**Query Parameters:**
- `query`: Search term

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "avatar_url": "https://...",
      "is_following": false
    }
  ]
}
```

---

### Explore Users

**Endpoint:** `GET /api/explore`

**Auth:** Required

**Query Parameters:**
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "users": [ ... ],
    "meta": { ... }
  }
}
```

---

### Get Online Status

**Endpoint:** `GET /api/users/{user}/online-status`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "is_online": true,
    "last_active": "2026-03-04T10:30:00Z"
  }
}
```

---

## Posts

### Get Feed

**Endpoint:** `GET /api/posts`

**Auth:** Required

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15)
- `type`: Filter by type (all, following, public)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "posts": [
      {
        "id": 1,
        "slug": "abc123...",
        "content": "Hello world!",
        "media": [
          {
            "id": 1,
            "type": "image",
            "url": "https://...",
            "thumbnail": "https://..."
          }
        ],
        "user": {
          "id": 1,
          "name": "John Doe",
          "username": "johndoe",
          "avatar_url": "https://..."
        },
        "likes_count": 25,
        "comments_count": 5,
        "is_liked": false,
        "is_saved": false,
        "created_at": "2026-03-04T10:00:00Z",
        "is_private": false
      }
    ],
    "meta": { ... }
  }
}
```

---

### Get Single Post

**Endpoint:** `GET /api/posts/{post}`

**Auth:** Required

**Path Parameters:**
- `post`: Post ID or slug

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "abc123...",
    "content": "Hello world!",
    "media": [ ... ],
    "user": { ... },
    "likes_count": 25,
    "comments_count": 5,
    "comments": [ ... ],
    "is_liked": false,
    "is_saved": false,
    "created_at": "2026-03-04T10:00:00Z"
  }
}
```

---

### Create Post

**Endpoint:** `POST /api/posts`

**Auth:** Required

**Body (multipart/form-data):**
```
content: "Hello world!"
is_private: false
media[]: (files, max 30)
```

**Validation Rules:**
- `content`: required if no media, max:280 chars
- `media[]`: optional, max 30 files
- `media.*`: image, video (mp4, mov, avi, webm)

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "post": { ... }
  },
  "message": "Post created successfully"
}
```

---

### Update Post

**Endpoint:** `PUT /api/posts/{post}`

**Auth:** Required (owner only)

**Body:**
```json
{
  "content": "Updated content",
  "is_private": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "post": { ... }
  },
  "message": "Post updated successfully"
}
```

---

### Delete Post

**Endpoint:** `DELETE /api/posts/{post}`

**Auth:** Required (owner or admin)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Post deleted successfully"
}
```

---

### Like Post

**Endpoint:** `POST /api/posts/{post}/like`

**Auth:** Required

**Path Parameters:**
- `post`: Post ID

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "liked": true,
    "likes_count": 26
  },
  "message": "Post liked successfully"
}
```

---

### Unlike Post

**Endpoint:** `POST /api/posts/{post}/unlike`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "liked": false,
    "likes_count": 25
  },
  "message": "Post unliked successfully"
}
```

---

### Get Post Likers

**Endpoint:** `GET /api/posts/{post}/likers`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "username": "janesmith",
      "avatar_url": "https://..."
    }
  ]
}
```

---

### Save Post

**Endpoint:** `POST /api/posts/{post}/save`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "saved": true
  },
  "message": "Post saved successfully"
}
```

---

### Unsave Post

**Endpoint:** `POST /api/posts/{post}/unsave`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "saved": false
  },
  "message": "Post unsaved successfully"
}
```

---

## Comments

### Get Comments

**Endpoint:** `GET /api/posts/{post}/comments`

**Auth:** Required

**Query Parameters:**
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "comments": [
      {
        "id": 1,
        "content": "Great post!",
        "user": { ... },
        "likes_count": 5,
        "is_liked": false,
        "replies": [ ... ],
        "created_at": "2026-03-04T10:05:00Z"
      }
    ],
    "meta": { ... }
  }
}
```

---

### Create Comment

**Endpoint:** `POST /api/comments`

**Auth:** Required

**Body:**
```json
{
  "post_id": 1,
  "content": "Great post!",
  "parent_id": null
}
```

**Validation Rules:**
- `post_id`: required, exists
- `content`: required, max:280 chars
- `parent_id`: optional, exists (for replies)

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "comment": { ... }
  },
  "message": "Comment created successfully"
}
```

---

### Update Comment

**Endpoint:** `PUT /api/comments/{comment}`

**Auth:** Required (owner only)

**Body:**
```json
{
  "content": "Updated comment"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "comment": { ... }
  },
  "message": "Comment updated successfully"
}
```

---

### Delete Comment

**Endpoint:** `DELETE /api/comments/{comment}`

**Auth:** Required (owner or admin)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Comment deleted successfully"
}
```

---

### Like Comment

**Endpoint:** `POST /api/comments/{comment}/like`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "liked": true,
    "likes_count": 6
  },
  "message": "Comment liked successfully"
}
```

---

### Unlike Comment

**Endpoint:** `POST /api/comments/{comment}/unlike`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "liked": false,
    "likes_count": 5
  },
  "message": "Comment unliked successfully"
}
```

---

## Stories

### Get Stories

**Endpoint:** `GET /api/stories`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "avatar_url": "https://..."
      },
      "stories": [
        {
          "id": 1,
          "slug": "story123...",
          "media_type": "image",
          "media_url": "https://...",
          "content": "Story caption",
          "expires_at": "2026-03-05T10:00:00Z",
          "time_remaining": "23 hours",
          "views_count": 15,
          "has_viewed": false,
          "user_reaction": null
        }
      ]
    }
  ]
}
```

---

### Create Story

**Endpoint:** `POST /api/stories`

**Auth:** Required

**Body (multipart/form-data):**
```
media: (file, image or video)
content: "Story caption"
```

**Validation Rules:**
- `media`: required, image or video (mp4, mov, avi, webm)
- `content`: optional, max:280 chars
- Video max duration: 60 seconds

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "story": { ... }
  },
  "message": "Story created successfully"
}
```

---

### View Story

**Endpoint:** `GET /api/stories/{user}/{story}`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "story123...",
    "media_type": "image",
    "media_url": "https://...",
    "content": "Story caption",
    "user": { ... },
    "expires_at": "2026-03-05T10:00:00Z",
    "views_count": 16
  }
}
```

---

### Get Story Viewers

**Endpoint:** `GET /api/stories/{user}/{story}/viewers`

**Auth:** Required (story owner only)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "username": "janesmith",
      "avatar_url": "https://...",
      "viewed_at": "2026-03-04T10:30:00Z"
    }
  ]
}
```

---

### React to Story

**Endpoint:** `POST /api/stories/{user}/{story}/react`

**Auth:** Required

**Body:**
```json
{
  "reaction": "❤️"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "reaction": "❤️"
  },
  "message": "Reaction added successfully"
}
```

---

### Remove Story Reaction

**Endpoint:** `DELETE /api/stories/{user}/{story}/react`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Reaction removed successfully"
}
```

---

### Get Story Reactions

**Endpoint:** `GET /api/stories/{user}/{story}/reactions`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "username": "janesmith",
      "avatar_url": "https://...",
      "reaction": "❤️",
      "created_at": "2026-03-04T10:30:00Z"
    }
  ]
}
```

---

### Delete Story

**Endpoint:** `DELETE /api/stories/{user}/{story}`

**Auth:** Required (owner or admin)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Story deleted successfully"
}
```

---

## Notifications

### Get Notifications

**Endpoint:** `GET /api/notifications`

**Auth:** Required

**Query Parameters:**
- `page`: Page number
- `per_page`: Items per page (default: 20)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": 1,
        "type": "like",
        "message": "Jane Smith liked your post",
        "data": {
          "user": { ... },
          "post_id": 1
        },
        "read": false,
        "created_at": "2026-03-04T10:00:00Z"
      }
    ],
    "meta": { ... }
  }
}
```

---

### Get Unread Count

**Endpoint:** `GET /api/notifications/unread-count`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

### Mark Notification as Read

**Endpoint:** `POST /api/notifications/{notification}/read`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

---

### Mark All as Read

**Endpoint:** `POST /api/notifications/mark-all-read`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

---

### Delete Notification

**Endpoint:** `DELETE /api/notifications/{notification}`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

---

### Delete All Notifications

**Endpoint:** `DELETE /api/notifications`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "All notifications deleted"
}
```

---

### Real-time Updates

**Endpoint:** `GET /api/notifications/realtime-updates`

**Auth:** Required

**Query Parameters:**
- `last_update`: Timestamp of last update

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "has_updates": true,
    "unread_count": 5,
    "new_notifications": [ ... ]
  }
}
```

---

## Chat & Messages

### Get Conversations

**Endpoint:** `GET /api/conversations`

**Auth:** Required

**Query Parameters:**
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "slug": "conv123...",
      "is_group": false,
      "display_name": "Jane Smith",
      "display_avatar": "https://...",
      "other_user": {
        "id": 2,
        "name": "Jane Smith",
        "username": "janesmith",
        "avatar_url": "https://...",
        "is_online": true
      },
      "latest_message": {
        "id": 5,
        "content": "Hey! How are you?",
        "type": "text",
        "sender_id": 2,
        "created_at": "2026-03-04T10:30:00Z"
      },
      "unread_count": 2,
      "updated_at": "2026-03-04T10:30:00Z"
    }
  ]
}
```

---

### Get Single Conversation

**Endpoint:** `GET /api/chat/{conversation}`

**Auth:** Required

**Path Parameters:**
- `conversation`: Conversation ID or slug

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "conv123...",
    "is_group": false,
    "display_name": "Jane Smith",
    "display_avatar": "https://...",
    "other_user": { ... },
    "messages": [ ... ],
    "unread_count": 2
  }
}
```

---

### Get Messages

**Endpoint:** `GET /api/chat/{conversation}/messages`

**Auth:** Required

**Query Parameters:**
- `before`: Get messages before this timestamp
- `limit`: Number of messages (default: 50)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "messages": [
      {
        "id": 5,
        "conversation_id": 1,
        "sender_id": 2,
        "sender": { ... },
        "content": "Hey! How are you?",
        "type": "text",
        "media_url": null,
        "read_at": null,
        "delivered_at": "2026-03-04T10:30:05Z",
        "created_at": "2026-03-04T10:30:00Z"
      }
    ],
    "has_more": false
  }
}
```

---

### Send Message

**Endpoint:** `POST /api/chat/{conversation}`

**Auth:** Required

**Path Parameters:**
- `conversation`: Conversation ID or slug

**Body (multipart/form-data):**
```
content: "Hello!"
type: text
media: (optional file)
```

**Validation Rules:**
- `content`: required for text type
- `type`: text, image, file
- `media`: required for image/file type

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "message": { ... }
  },
  "message": "Message sent successfully"
}
```

---

### Start Conversation

**Endpoint:** `GET /api/chat/start/{userId}`

**Auth:** Required

**Path Parameters:**
- `userId`: User ID

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "conversation": {
      "id": 1,
      "slug": "conv123...",
      "other_user": { ... }
    },
    "redirect": "/chat/conv123..."
  }
}
```

---

### Mark Conversation as Read

**Endpoint:** `POST /api/chat/{conversation}/read`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Conversation marked as read"
}
```

---

### Delete Message

**Endpoint:** `DELETE /api/chat/message/{message}`

**Auth:** Required

**Body:**
```json
{
  "delete_for": "me"  // or "everyone" (sender only)
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Message deleted successfully"
}
```

---

### Clear Chat

**Endpoint:** `DELETE /api/chat/{conversation}/clear`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Chat cleared successfully"
}
```

---

### Get Message Statuses

**Endpoint:** `POST /api/chat/{conversation}/status`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "statuses": [
      {
        "message_id": 5,
        "read_at": "2026-03-04T10:31:00Z",
        "delivered_at": "2026-03-04T10:30:05Z"
      }
    ]
  }
}
```

---

### Send Typing Indicator

**Endpoint:** `POST /api/chat/{conversation}/typing`

**Auth:** Required

**Body:**
```json
{
  "is_typing": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Typing indicator sent"
}
```

---

### Get Typing Status

**Endpoint:** `GET /api/chat/{conversation}/typing`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "is_typing": true,
    "typing_user": {
      "id": 2,
      "name": "Jane Smith"
    }
  }
}
```

---

## Groups

### Get Groups

**Endpoint:** `GET /api/groups`

**Auth:** Required

**Query Parameters:**
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "groups": [
      {
        "id": 1,
        "name": "Developers",
        "slug": "developers",
        "avatar": "https://...",
        "description": "Group for developers",
        "members_count": 25,
        "is_admin": true,
        "created_at": "2026-03-01T10:00:00Z"
      }
    ],
    "meta": { ... }
  }
}
```

---

### Get Single Group

**Endpoint:** `GET /api/groups/{slug}`

**Auth:** Required

**Path Parameters:**
- `slug`: Group slug

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Developers",
    "slug": "developers",
    "avatar": "https://...",
    "description": "Group for developers",
    "creator": { ... },
    "members_count": 25,
    "is_member": true,
    "is_admin": false,
    "members": [ ... ],
    "created_at": "2026-03-01T10:00:00Z"
  }
}
```

---

### Create Group

**Endpoint:** `POST /api/groups`

**Auth:** Required

**Body (multipart/form-data):**
```
name: "Developers"
description: "Group for developers"
is_private: false
avatar: (optional file)
member_ids[]: [1, 2, 3]
```

**Validation Rules:**
- `name`: required, string, max:255
- `description`: optional, max:1000
- `is_private`: boolean
- `avatar`: optional, image
- `member_ids[]`: optional, array of user IDs

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "group": { ... }
  },
  "message": "Group created successfully"
}
```

---

### Update Group

**Endpoint:** `PUT /api/groups/{slug}`

**Auth:** Required (admin only)

**Body:**
```json
{
  "name": "Updated Name",
  "description": "Updated description",
  "is_private": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "group": { ... }
  },
  "message": "Group updated successfully"
}
```

---

### Delete Group

**Endpoint:** `DELETE /api/groups/{slug}`

**Auth:** Required (admin only)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Group deleted successfully"
}
```

---

### Add Members

**Endpoint:** `POST /api/groups/{slug}/members`

**Auth:** Required (admin only)

**Body:**
```json
{
  "member_ids": [4, 5, 6]
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Members added successfully"
}
```

---

### Remove Member

**Endpoint:** `DELETE /api/groups/{slug}/members/{userId}`

**Auth:** Required (admin only)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Member removed successfully"
}
```

---

### Make Admin

**Endpoint:** `POST /api/groups/{slug}/members/{userId}/admin`

**Auth:** Required (admin only)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Member promoted to admin"
}
```

---

### Remove Admin

**Endpoint:** `DELETE /api/groups/{slug}/members/{userId}/admin`

**Auth:** Required (admin only)

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Admin demoted to member"
}
```

---

### Join via Invite Link

**Endpoint:** `POST /api/groups/accept-invite/{inviteLink}`

**Auth:** Required

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Joined group successfully",
  "redirect": "/groups/developers"
}
```

---

### Regenerate Invite Link

**Endpoint:** `POST /api/groups/{slug}/regenerate-invite`

**Auth:** Required (admin only)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "invite_link": "https://..."
  },
  "message": "Invite link regenerated"
}
```

---

### Quick Invite

**Endpoint:** `POST /api/groups/{slug}/quick-invite`

**Auth:** Required (admin only)

**Body:**
```json
{
  "user_ids": [4, 5, 6],
  "message": "Join our group!"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Invites sent successfully"
}
```

---

## Search & Explore

### Search

**Endpoint:** `GET /api/search`

**Auth:** Required

**Query Parameters:**
- `q`: Search query
- `type`: users, posts, groups (default: users)
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "users": [ ... ],
    "posts": [ ... ],
    "groups": [ ... ],
    "meta": { ... }
  }
}
```

---

### Explore

**Endpoint:** `GET /api/explore`

**Auth:** Required

**Query Parameters:**
- `page`: Page number

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 5,
        "name": "New User",
        "username": "newuser",
        "avatar_url": "https://...",
        "followers_count": 100,
        "is_following": false
      }
    ],
    "trending_posts": [ ... ],
    "meta": { ... }
  }
}
```

---

## Error Codes

| HTTP Status | Meaning |
|-------------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request (validation error) |
| 401 | Unauthorized (not logged in) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not Found |
| 422 | Unprocessable Entity (validation failed) |
| 429 | Too Many Requests (rate limit) |
| 500 | Internal Server Error |

---

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| General API | 60 requests/minute |
| Login | 5 attempts/minute |
| Password Reset | 3 requests/hour |
| Message Send | 30 messages/minute |
| Story Create | 10 stories/hour |

---

## Webhooks

Nexus does not currently support webhooks. Real-time updates are handled via polling.

---

## Versioning

Current API version: v1 (implicit)

Future versions will be prefixed: `/api/v2/...`

---

## Support

For API issues or questions:
- Check documentation
- Review error response messages
- Contact development team

---

**Last Updated**: March 2026
