# Database Schema Documentation

Complete database schema, entity relationships, and table definitions for Nexus.

---

## Table of Contents

- [Overview](#overview)
- [Entity Relationship Diagram](#entity-relationship-diagram)
- [Table Definitions](#table-definitions)
- [Indexes](#indexes)
- [Foreign Key Relationships](#foreign-key-relationships)
- [Migrations](#migrations)

---

## Overview

### Database Statistics

| Metric | Value |
|--------|-------|
| **Total Tables** | 24 |
| **Total Migrations** | 58 |
| **Database Type** | SQLite (Dev) / MySQL (Prod) |
| **ORM** | Eloquent (Laravel) |

### Core Entities

```
┌─────────────────────────────────────────────────────────────────┐
│                      Core Entities                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │  User    │  │  Post    │  │ Comment  │  │  Story   │       │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘       │
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │ Message  │  │  Group   │  │Conversation│ │Notification│     │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘       │
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │ Profile  │  │  Follow  │  │  Like    │  │  Block   │       │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘       │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Nexus ER Diagram                                   │
└─────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────┐
                              │     users       │
                              ├─────────────────┤
                              │ PK id           │
                              │    name         │
                              │    username     │
                              │    email        │
                              │    password     │
                              │    is_admin     │
                              │    is_suspended │
                              │    is_online    │
                              │    last_active  │
                              │    timestamps   │
                              └────────┬────────┘
                                       │
         ┌─────────────────────────────┼─────────────────────────────┐
         │                ┌────────────┴────────────┐                │
         │                │                         │                │
         ▼                ▼                         ▼                │
┌─────────────────┐ ┌─────────────────┐   ┌─────────────────┐        │
│    profiles     │ │     posts       │   │    follows      │        │
├─────────────────┤ ├─────────────────┤   ├─────────────────┤        │
│ PK id           │ │ PK id           │   │ PK id           │        │
│ FK user_id      │─┘ │ FK user_id    │   │ FK follower_id  │─┐      │
│    avatar       │   │    slug       │   │ FK followed_id  │─┘      │
│    cover_image  │   │    content    │   │    timestamps   │        │
│    bio          │   │    is_private │   └─────────────────┘        │
│    is_private   │   │    timestamps │                              │
│    social_links │   └────────┬──────┘                              │
│    timestamps   │            │                                     │
└─────────────────┘            │                                     │
                               │                                     │
         ┌─────────────────────┼─────────────────────┐               │
         │                     │                     │               │
         ▼                     ▼                     ▼               │
┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐      │
│  post_media     │   │     likes       │   │  saved_posts    │      │
├─────────────────┤   ├─────────────────┤   ├─────────────────┤      │
│ PK id           │   │ PK id           │   │ PK id           │      │
│ FK post_id      │──┐│ FK user_id      │──┐│ FK user_id      │──┐   │
│    media_type   │  ││ FK post_id      │──┘│ FK post_id      │──┘   │
│    media_path   │  ││    timestamps   │  ││    timestamps   │  │   │
│    thumbnail    │  │└─────────────────┘  │└─────────────────┘  │   │
│    sort_order   │  │                     │                     │   │
│    timestamps   │  │                     │                     │   │
└─────────────────┘  │                     │                     │   │
                     │                     │                     │   │
         ┌───────────┴──────────┐          │                     │   │
         │                      │          │                     │   │
         ▼                      ▼          │                     │   │
┌─────────────────┐   ┌─────────────────┐  │                     │   │
│   comments      │   │    mentions     │  │                     │   │
├─────────────────┤   ├─────────────────┤  │                     │   │
│ PK id           │   │ PK id           │  │                     │   │
│ FK user_id      │──┐│ FK mentioner_id │  │                     │   │
│ FK post_id      │──┤│ FK mentioned_id │  │                     │   │
│ FK parent_id    │──┘│ FK mentionable_* │  │                     │   │
│    content      │  ││    timestamps   │  │                     │   │
│    timestamps   │  │└─────────────────┘  │                     │   │
└────────┬────────┘  │                     │                     │   │
         │            │                     │                     │   │
         │            ▼                     │                     │   │
         │   ┌─────────────────┐            │                     │   │
         │   │  comment_likes  │            │                     │   │
         │   ├─────────────────┤            │                     │   │
         │   │ PK id           │            │                     │   │
         │   │ FK user_id      │────────────┘                     │   │
         │   │ FK comment_id   │──────────────────────────────────┘   │
         │   │    timestamps   │                                      │
         │   └─────────────────┘                                      │
         │                                                            │
         ▼                                                            │
┌─────────────────┐                                                  │
│  story_views    │                                                  │
├─────────────────┤                                                  │
│ PK id           │                                                  │
│ FK user_id      │──────────────────────────────────────────────────┘
│ FK story_id     │
│    timestamps   │
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│    stories      │       │story_reactions  │
├─────────────────┤       ├─────────────────┤
│ PK id           │──────▶│ PK id           │
│ FK user_id      │       │ FK user_id      │
│    slug         │       │ FK story_id     │
│    media_type   │       │ reaction_type   │
│    media_path   │       │ timestamps      │
│    content      │       └─────────────────┘
│    expires_at   │
│    views        │
│    timestamps   │
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│  conversations  │       │    messages     │
├─────────────────┤       ├─────────────────┤
│ PK id           │──────▶│ PK id           │
│ FK user1_id     │       │ FK conversation │
│ FK user2_id     │       │ FK sender_id    │
│    is_group     │       │    content      │
│ FK group_id     │       │    type         │
│    slug         │       │    media_path   │
│    timestamps   │       │    read_at      │
│    last_message │       │    delivered_at │
└─────────────────┘       │    deleted_for  │
                          │    timestamps   │
┌─────────────────┐       └─────────────────┘
│     groups      │
├─────────────────┤
│ PK id           │       ┌─────────────────┐
│    name         │       │ group_members   │
│ FK creator_id   │◀──────├─────────────────┤
│    description  │       │ PK id           │
│    avatar       │       │ FK group_id     │
│    is_private   │       │ FK user_id      │
│    slug         │       │    role         │
│    invite_link  │       │    joined_at    │
│    timestamps   │       └─────────────────┘
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│ notifications   │       │     blocks      │
├─────────────────┤       ├─────────────────┤
│ PK id           │       │ PK id           │
│ FK user_id      │       │ FK blocker_id   │
│    type         │       │ FK blocked_id   │
│    data (JSON)  │       │    timestamps   │
│    read_at      │       └─────────────────┘
│    related_*    │
│    timestamps   │
└─────────────────┘
```

---

## Table Definitions

### users

Stores user account information and authentication credentials.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    language VARCHAR(255) NOT NULL DEFAULT 'en',
    password VARCHAR(255) NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    is_suspended BOOLEAN NOT NULL DEFAULT FALSE,
    verification_code VARCHAR(6) NULL,
    verification_code_expires_at TIMESTAMP NULL,
    last_active TIMESTAMP NULL,
    inactive_reminder_sent_at TIMESTAMP NULL,
    is_online BOOLEAN NOT NULL DEFAULT FALSE,
    username_changed_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_last_active (last_active)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NO | - | User's display name |
| username | VARCHAR(255) | NO | - | Unique username |
| email | VARCHAR(255) | NO | - | Unique email address |
| email_verified_at | TIMESTAMP | YES | NULL | Email verification timestamp |
| language | VARCHAR(255) | NO | 'en' | User's language preference (from 2026_03_10_232405) |
| password | VARCHAR(255) | YES | NULL | Bcrypt hashed password |
| is_admin | BOOLEAN | NO | FALSE | Admin privilege flag |
| is_suspended | BOOLEAN | NO | FALSE | Account suspension flag |
| verification_code | VARCHAR(6) | YES | NULL | 6-digit email verification code |
| verification_code_expires_at | TIMESTAMP | YES | NULL | Verification code expiry |
| last_active | TIMESTAMP | YES | NULL | Last activity timestamp |
| inactive_reminder_sent_at | TIMESTAMP | YES | NULL | Last inactive reminder sent (from 2026_03_09_210144) |
| is_online | BOOLEAN | NO | FALSE | Current online status |
| username_changed_at | TIMESTAMP | YES | NULL | Last username change time |
| remember_token | VARCHAR(100) | YES | NULL | Remember me token |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

---

### profiles

Extended user profile information.

```sql
CREATE TABLE profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    avatar VARCHAR(255) NULL,
    cover_image VARCHAR(255) NULL,
    bio VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    location VARCHAR(255) NULL,
    birth_date DATE NULL,
    occupation VARCHAR(255) NULL,
    about TEXT NULL,
    phone VARCHAR(50) NULL,
    gender VARCHAR(50) NULL,
    is_private BOOLEAN NOT NULL DEFAULT FALSE,
    social_links JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_is_private (is_private)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| avatar | VARCHAR(255) | YES | NULL | Avatar image path |
| cover_image | VARCHAR(255) | YES | NULL | Cover image path |
| bio | VARCHAR(255) | YES | NULL | Short biography |
| website | VARCHAR(255) | YES | NULL | Website URL |
| location | VARCHAR(255) | YES | NULL | Location |
| birth_date | DATE | YES | NULL | Birth date |
| occupation | VARCHAR(255) | YES | NULL | Occupation |
| about | TEXT | YES | NULL | Extended about section |
| phone | VARCHAR(50) | YES | NULL | Phone number |
| gender | VARCHAR(50) | YES | NULL | Gender |
| is_private | BOOLEAN | NO | FALSE | Private account flag |
| social_links | JSON | YES | NULL | Social media links |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

---

### posts

User posts with text and media content.

```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    content TEXT NULL,
    slug VARCHAR(24) NOT NULL UNIQUE,
    is_private BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_privacy (user_id, is_private),
    INDEX idx_created_at (created_at)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| content | TEXT | YES | NULL | Post content (max 280 chars) |
| slug | VARCHAR(24) | NO | - | Unique 24-character slug |
| is_private | BOOLEAN | NO | FALSE | Privacy flag |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### post_media

Media attachments for posts (images and videos).

```sql
CREATE TABLE post_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    media_path VARCHAR(255) NOT NULL,
    media_thumbnail VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post_order (post_id, sort_order)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| media_type | ENUM('image', 'video') | NO | - | Media type |
| media_path | VARCHAR(255) | NO | - | File storage path |
| media_thumbnail | VARCHAR(255) | YES | NULL | Video thumbnail path |
| sort_order | INT | NO | 0 | Display order |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### comments

Comments on posts with support for nested replies.

```sql
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_post_parent (post_id, parent_id),
    INDEX idx_created_at (created_at)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| parent_id | BIGINT UNSIGNED | YES | NULL | Parent comment (for replies) |
| content | TEXT | NO | - | Comment content |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### likes

Post likes (reactions).

```sql
CREATE TABLE likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post (user_id, post_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### comment_likes

Comment likes.

```sql
CREATE TABLE comment_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    comment_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_comment (user_id, comment_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| comment_id | BIGINT UNSIGNED | NO | - | Foreign key to comments |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### follows

User follow relationships.

```sql
CREATE TABLE follows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    follower_id BIGINT UNSIGNED NOT NULL,
    followed_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, followed_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| follower_id | BIGINT UNSIGNED | NO | - | User who is following |
| followed_id | BIGINT UNSIGNED | NO | - | User being followed |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### blocks

User block relationships.

```sql
CREATE TABLE blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    blocker_id BIGINT UNSIGNED NOT NULL,
    blocked_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (blocker_id, blocked_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| blocker_id | BIGINT UNSIGNED | NO | - | User who is blocking |
| blocked_id | BIGINT UNSIGNED | NO | - | User being blocked |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### saved_posts

Saved/bookmarked posts.

```sql
CREATE TABLE saved_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post (user_id, post_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### stories

Ephemeral stories (24-hour lifespan).

```sql
CREATE TABLE stories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(24) NOT NULL UNIQUE,
    media_type ENUM('image', 'video') NOT NULL,
    media_path VARCHAR(255) NOT NULL,
    content TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| slug | VARCHAR(24) | NO | - | Unique 24-character slug |
| media_type | ENUM('image', 'video') | NO | - | Media type |
| media_path | VARCHAR(255) | NO | - | File storage path |
| content | TEXT | YES | NULL | Story caption |
| expires_at | TIMESTAMP | NO | - | Expiration timestamp |
| views | INT UNSIGNED | NO | 0 | View count |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### story_views

Story view tracking.

```sql
CREATE TABLE story_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    story_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    INDEX idx_story_user (story_id, user_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| story_id | BIGINT UNSIGNED | NO | - | Foreign key to stories |
| created_at | TIMESTAMP | YES | NULL | View timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### story_reactions

Story emoji reactions.

```sql
CREATE TABLE story_reactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    story_id BIGINT UNSIGNED NOT NULL,
    reaction_type VARCHAR(10) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_story (user_id, story_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| story_id | BIGINT UNSIGNED | NO | - | Foreign key to stories |
| reaction_type | VARCHAR(10) | NO | - | Emoji reaction |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### conversations

Chat conversations (direct messages and groups).

```sql
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user1_id BIGINT UNSIGNED NOT NULL,
    user2_id BIGINT UNSIGNED NULL,
    is_group BOOLEAN NOT NULL DEFAULT FALSE,
    group_id BIGINT UNSIGNED NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    last_message_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    INDEX idx_user1 (user1_id),
    INDEX idx_user2 (user2_id),
    INDEX idx_last_message (last_message_at)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user1_id | BIGINT UNSIGNED | NO | - | First participant |
| user2_id | BIGINT UNSIGNED | YES | NULL | Second participant (DM) |
| is_group | BOOLEAN | NO | FALSE | Group conversation flag |
| group_id | BIGINT UNSIGNED | YES | NULL | Foreign key to groups |
| slug | VARCHAR(255) | NO | - | Unique slug |
| name | VARCHAR(255) | YES | NULL | Conversation name |
| avatar | VARCHAR(255) | YES | NULL | Conversation avatar |
| last_message_at | TIMESTAMP | YES | NULL | Last message timestamp |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### messages

Chat messages.

```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    content TEXT NULL,
    type ENUM('text', 'image', 'file', 'system', 'group_invite') NOT NULL DEFAULT 'text',
    media_path JSON NULL,
    original_filename VARCHAR(255) NULL,
    media_size INT NULL,
    read_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    deleted_for JSON NULL,
    deleted_by_sender BOOLEAN NOT NULL DEFAULT FALSE,
    visible_to BIGINT UNSIGNED NULL,
    system_type VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (visible_to) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id, created_at),
    INDEX idx_sender (sender_id),
    INDEX idx_read (read_at)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| conversation_id | BIGINT UNSIGNED | NO | - | Foreign key to conversations |
| sender_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| content | TEXT | YES | NULL | Message content |
| type | ENUM | NO | 'text' | Message type |
| media_path | JSON | YES | NULL | Attached file paths |
| original_filename | VARCHAR(255) | YES | NULL | Original file name |
| media_size | INT | YES | NULL | File size in bytes |
| read_at | TIMESTAMP | YES | NULL | Read timestamp |
| delivered_at | TIMESTAMP | YES | NULL | Delivery timestamp (from 2026_03_02_000000) |
| deleted_for | JSON | YES | NULL | User IDs who deleted for themselves |
| deleted_by_sender | BOOLEAN | NO | FALSE | Sender deleted for everyone flag (from 2026_03_02_000001) |
| visible_to | BIGINT UNSIGNED | YES | NULL | User ID for visibility restriction (from 2026_02_28_172610) |
| system_type | VARCHAR(50) | YES | NULL | System message type |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### groups

User groups/communities.

```sql
CREATE TABLE groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    creator_id BIGINT UNSIGNED NOT NULL,
    avatar VARCHAR(255) NULL,
    is_private BOOLEAN NOT NULL DEFAULT FALSE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    invite_link VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_invite (invite_link),
    INDEX idx_private (is_private)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NO | - | Group name |
| description | TEXT | YES | NULL | Group description |
| creator_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| avatar | VARCHAR(255) | YES | NULL | Group avatar |
| is_private | BOOLEAN | NO | FALSE | Private group flag |
| slug | VARCHAR(255) | NO | - | Unique slug |
| invite_link | VARCHAR(255) | NO | - | Unique invite link |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### group_members

Group membership.

```sql
CREATE TABLE group_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    joined_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_user (group_id, user_id),
    INDEX idx_role (role)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| group_id | BIGINT UNSIGNED | NO | - | Foreign key to groups |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| role | ENUM('admin', 'member') | NO | 'member' | Member role |
| joined_at | TIMESTAMP | YES | NULL | Join timestamp |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### notifications

User notifications.

```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    data JSON NOT NULL,
    read_at TIMESTAMP NULL,
    related_id BIGINT UNSIGNED NULL,
    related_type VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, read_at),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| type | VARCHAR(50) | NO | - | Notification type |
| data | JSON | NO | - | Notification data |
| read_at | TIMESTAMP | YES | NULL | Read timestamp |
| related_id | BIGINT UNSIGNED | YES | NULL | Related entity ID |
| related_type | VARCHAR(255) | YES | NULL | Related entity type |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### mentions

User mentions in posts and comments.

```sql
CREATE TABLE mentions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mentioner_id BIGINT UNSIGNED NOT NULL,
    mentioned_id BIGINT UNSIGNED NOT NULL,
    mentionable_id BIGINT UNSIGNED NOT NULL,
    mentionable_type VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (mentioner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentioned_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mentionable (mentionable_type, mentionable_id),
    INDEX idx_mentioned (mentioned_id)
);
```

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| mentioner_id | BIGINT UNSIGNED | NO | - | User who mentioned |
| mentioned_id | BIGINT UNSIGNED | NO | - | User mentioned |
| mentionable_id | BIGINT UNSIGNED | NO | - | Entity ID (post/comment) |
| mentionable_type | VARCHAR(255) | NO | - | Entity type |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

---

### cache

Laravel cache storage.

```sql
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);
```

---

### cache_locks

Laravel cache locks.

```sql
CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);
```

---

### sessions

Laravel session storage.

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
);
```

---

### personal_access_tokens

Laravel Sanctum API tokens.

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id)
);
```

---

## Indexes

### Performance Indexes

| Table | Index | Columns | Purpose |
|-------|-------|---------|---------|
| users | idx_email | email | Fast email lookup |
| users | idx_username | username | Fast username lookup |
| users | idx_last_active | last_active | Online status queries |
| posts | idx_user_privacy | user_id, is_private | Feed filtering |
| posts | idx_created_at | created_at | Sorting by date |
| comments | idx_post_parent | post_id, parent_id | Comment threading |
| stories | idx_expires_at | expires_at | Expired story cleanup |
| conversations | idx_last_message | last_message_at | Conversation ordering |
| messages | idx_conversation | conversation_id, created_at | Message loading |
| notifications | idx_user_read | user_id, read_at | Unread count |
| groups | idx_slug | slug | Group URL lookup |
| groups | idx_invite | invite_link | Invite link lookup |

### Unique Indexes

| Table | Index | Columns |
|-------|-------|---------|
| users | UNIQUE | username |
| users | UNIQUE | email |
| posts | UNIQUE | slug |
| stories | UNIQUE | slug |
| follows | UNIQUE | follower_id, followed_id |
| likes | UNIQUE | user_id, post_id |
| comment_likes | UNIQUE | user_id, comment_id |
| blocks | UNIQUE | blocker_id, blocked_id |
| saved_posts | UNIQUE | user_id, post_id |
| story_reactions | UNIQUE | user_id, story_id |
| group_members | UNIQUE | group_id, user_id |
| conversations | UNIQUE | slug |
| groups | UNIQUE | slug, invite_link |

---

## Foreign Key Relationships

### users

| Referenced By | On Delete |
|---------------|-----------|
| profiles | CASCADE |
| posts | CASCADE |
| comments | CASCADE |
| likes | CASCADE |
| follows (follower_id) | CASCADE |
| follows (followed_id) | CASCADE |
| blocks (blocker_id) | CASCADE |
| blocks (blocked_id) | CASCADE |
| saved_posts | CASCADE |
| stories | CASCADE |
| story_views | CASCADE |
| story_reactions | CASCADE |
| conversations (user1_id) | CASCADE |
| conversations (user2_id) | CASCADE |
| messages | CASCADE |
| groups | CASCADE |
| group_members | CASCADE |
| notifications | CASCADE |
| mentions (mentioner_id) | CASCADE |
| mentions (mentioned_id) | CASCADE |

### posts

| Referenced By | On Delete |
|---------------|-----------|
| post_media | CASCADE |
| comments | CASCADE |
| likes | CASCADE |
| saved_posts | CASCADE |
| mentions | CASCADE |

### comments

| Referenced By | On Delete |
|---------------|-----------|
| comments (parent_id) | CASCADE |
| comment_likes | CASCADE |
| mentions | CASCADE |

### conversations

| Referenced By | On Delete |
|---------------|-----------|
| messages | CASCADE |

### groups

| Referenced By | On Delete |
|---------------|-----------|
| conversations | CASCADE |
| group_members | CASCADE |

---

## Migrations

### Migration Order

Migrations are executed in the following order (by filename prefix):

```
1. 0001_01_01_000000_create_users_table.php
2. 0001_01_01_000001_create_cache_table.php
3. 2025_12_31_183416_create_posts_table.php
4. 2025_12_31_183428_create_follows_table.php
5. 2025_12_31_183440_create_likes_table.php
6. 2025_12_31_184455_create_comments_table.php
7. 2025_12_31_184509_create_comment_likes_table.php
8. 2025_12_31_185456_create_personal_access_tokens_table.php
9. 2025_12_31_190832_create_profiles_table.php
10. 2025_12_31_195043_add_is_private_to_profiles_table.php
11. 2025_12_31_195638_create_blocks_table.php
12. 2025_12_31_201829_add_media_to_posts_table.php
13. 2025_12_31_203558_add_is_private_to_posts_table.php
14. 2025_12_31_204120_create_post_media_table.php
15. 2025_12_31_204526_make_content_nullable_in_posts_table.php
16. 2025_12_31_211517_create_saved_posts_table.php
17. 2026_01_01_020301_create_stories_table.php
18. 2026_01_01_023011_add_views_to_stories_table.php
19. 2026_01_01_024005_create_story_views_table.php
20. 2026_01_01_024641_create_story_reactions_table.php
21. 2026_01_02_165014_create_conversations_table.php
22. 2026_01_02_165034_create_messages_table.php
23. 2026_01_02_171409_add_slug_to_conversations_table.php
24. 2026_01_02_180145_add_soft_deletes_to_messages_table.php
25. 2026_01_02_215252_create_notifications_table.php
26. 2026_01_03_215758_add_indexes_for_performance.php
27. 2026_01_05_200731_create_mentions_table.php
28. 2026_02_21_170301_create_groups_table.php
29. 2026_02_21_170303_create_group_members_table.php
30. 2026_02_21_170304_add_is_group_to_conversations_table.php
... (and more)
```

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Fresh migration
php artisan migrate:fresh

# Fresh with seeders
php artisan migrate:fresh --seed
```

---

## Next Steps

Continue reading:

- [Features](FEATURES.md) - Feature documentation with flows
- [API Reference](API.md) - RESTful API documentation
- [Frontend Guide](FRONTEND.md) - Vue.js architecture
