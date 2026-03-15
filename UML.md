# UML Diagrams

This document contains all UML diagrams for the Laravel Social Media Application.

## Table of Contents

1. [Class Diagram](#class-diagram)
2. [Entity Relationship Diagram (ERD)](#entity-relationship-diagram)
3. [Sequence Diagrams](#sequence-diagrams)
4. [Use Case Diagram](#use-case-diagram)

---

## Class Diagram

This diagram shows the main Eloquent models and their relationships.

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string username
        +string email
        +string password
        +boolean is_admin
        +boolean is_suspended
        +datetime last_active
        +boolean is_online
        +getAvatarUrlAttribute() string
        +posts() HasMany
        +follows() HasMany
        +followers() HasMany
        +following() BelongsToMany
        +profile() HasOne
        +stories() HasMany
        +conversations() BelongsToMany
        +messages() HasMany
        +groups() BelongsToMany
        +comments() HasMany
        +savedPosts() HasMany
        +notifications() HasMany
        +isFollowing(User) bool
        +isBlocking(User) bool
        +canChangeUsername() bool
    }

    class Post {
        +int id
        +int user_id
        +string content
        +string slug
        +string media_type
        +string media_path
        +boolean is_private
        +user() BelongsTo
        +likes() HasMany
        +comments() HasMany
        +media() HasMany
        +savedPosts() HasMany
        +likedBy(User) bool
    }

    class Comment {
        +int id
        +int user_id
        +int post_id
        +int parent_id
        +string content
        +user() BelongsTo
        +post() BelongsTo
        +parent() BelongsTo
        +replies() HasMany
        +likes() HasMany
    }

    class Like {
        +int id
        +int user_id
        +int post_id
        +user() BelongsTo
        +post() BelongsTo
    }

    class CommentLike {
        +int id
        +int user_id
        +int comment_id
        +user() BelongsTo
        +comment() BelongsTo
    }

    class Follow {
        +int id
        +int follower_id
        +int followed_id
        +follower() BelongsTo
        +followed() BelongsTo
    }

    class Profile {
        +int id
        +int user_id
        +string bio
        +string avatar
        +string cover_image
        +boolean is_private
        +user() BelongsTo
    }

    class Story {
        +int id
        +int user_id
        +string media_path
        +string slug
        +datetime expires_at
        +user() BelongsTo
        +views() HasMany
        +reactions() HasMany
    }

    class StoryView {
        +int id
        +int story_id
        +int user_id
        +story() BelongsTo
        +user() BelongsTo
    }

    class StoryReaction {
        +int id
        +int story_id
        +int user_id
        +string reaction_type
        +story() BelongsTo
        +user() BelongsTo
    }

    class Conversation {
        +int id
        +int user1_id
        +int user2_id
        +string slug
        +boolean is_group
        +int group_id
        +datetime last_message_at
        +user1() BelongsTo
        +user2() BelongsTo
        +group() BelongsTo
        +messages() HasMany
        +getRecipients() array
    }

    class Message {
        +int id
        +int conversation_id
        +int sender_id
        +string content
        +string media_path
        +string system_type
        +datetime read_at
        +datetime delivered_at
        +conversation() BelongsTo
        +sender() BelongsTo
    }

    class Group {
        +int id
        +string name
        +string description
        +int creator_id
        +string slug
        +string invite_link
        +boolean is_private
        +creator() BelongsTo
        +members() HasMany
        +conversation() HasOne
        +addMember() GroupMember
        +removeMember() bool
    }

    class GroupMember {
        +int id
        +int group_id
        +int user_id
        +string role
        +group() BelongsTo
        +user() BelongsTo
    }

    class SavedPost {
        +int id
        +int user_id
        +int post_id
        +user() BelongsTo
        +post() BelongsTo
    }

    class Block {
        +int id
        +int blocker_id
        +int blocked_id
        +blocker() BelongsTo
        +blocked() BelongsTo
    }

    class Notification {
        +int id
        +int user_id
        +string type
        +boolean is_read
        +user() BelongsTo
    }

    class PostMedia {
        +int id
        +int post_id
        +string media_path
        +string media_type
        +int order
        +post() BelongsTo
    }

    class Mention {
        +int id
        +int user_id
        +int post_id
        +user() BelongsTo
        +post() BelongsTo
    }

    User "1" *-- "0..*" Post : creates
    User "1" *-- "0..*" Comment : creates
    User "1" *-- "0..*" Like : creates
    User "1" *-- "0..*" CommentLike : creates
    User "1" *-- "0..*" Follow : follows
    User "1" *-- "0..*" Story : creates
    User "1" *-- "0..*" StoryView : views
    User "1" *-- "0..*" StoryReaction : reacts
    User "1" *-- "0..*" Message : sends
    User "1" *-- "0..*" GroupMember : belongs to
    User "1" *-- "0..*" SavedPost : saves
    User "1" *-- "0..*" Block : blocks
    User "1" *-- "0..*" Notification : receives
    User "1" -- "1" Profile : has

    Post "1" *-- "0..*" Like : receives
    Post "1" *-- "0..*" Comment : receives
    Post "1" *-- "0..*" PostMedia : contains
    Post "1" *-- "0..*" SavedPost : saved by

    Comment "1" *-- "0..*" CommentLike : receives
    Comment "1" -- "0..*" Comment : has replies

    Story "1" *-- "0..*" StoryView : viewed by
    Story "1" *-- "0..*" StoryReaction : reacted by

    Conversation "1" *-- "0..*" Message : contains
    Conversation "2" -- "2" User : participants

    Group "1" *-- "0..*" GroupMember : has members
    Group "1" -- "0..1" Conversation : has
    GroupMember "1" -- "1" User : belongs to
    GroupMember "1" -- "1" Group : belongs to
```

---

## Entity Relationship Diagram

This diagram shows the database table relationships.

```mermaid
erDiagram
    USERS ||--o{ POSTS : creates
    USERS ||--o{ COMMENTS : creates
    USERS ||--o{ LIKES : creates
    USERS ||--o{ COMMENT_LIKES : creates
    USERS ||--o{ FOLLOWS : creates
    USERS ||--o{ STORIES : creates
    USERS ||--o{ STORY_VIEWS : creates
    USERS ||--o{ STORY_REACTIONS : creates
    USERS ||--o{ MESSAGES : sends
    USERS ||--o{ GROUP_MEMBERS : joins
    USERS ||--o{ SAVED_POSTS : saves
    USERS ||--o{ BLOCKS : creates
    USERS ||--o{ NOTIFICATIONS : receives
    USERS ||--|| PROFILES : has

    POSTS ||--o{ LIKES : receives
    POSTS ||--o{ COMMENTS : receives
    POSTS ||--o{ POST_MEDIA : contains
    POSTS ||--o{ SAVED_POSTS : saved_by
    POSTS ||--o{ MENTIONS : mentioned_in

    COMMENTS ||--o{ COMMENT_LIKES : receives
    COMMENTS ||--o{ COMMENTS : has_replies

    STORIES ||--o{ STORY_VIEWS : viewed_by
    STORIES ||--o{ STORY_REACTIONS : reacted_by

    CONVERSATIONS ||--o{ MESSAGES : contains
    CONVERSATIONS ||--o{ USERS : participates

    GROUPS ||--o{ GROUP_MEMBERS : has
    GROUPS ||--o| CONVERSATIONS : has

    USERS {
        bigint id PK
        string name
        string username UK
        string email UK
        string password
        boolean is_admin
        boolean is_suspended
        timestamp last_active
        boolean is_online
        timestamp email_verified_at
    }

    POSTS {
        bigint id PK
        bigint user_id FK
        string content
        string slug UK
        string media_type
        string media_path
        boolean is_private
    }

    COMMENTS {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
        bigint parent_id FK
        string content
    }

    LIKES {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
    }

    COMMENT_LIKES {
        bigint id PK
        bigint user_id FK
        bigint comment_id FK
    }

    FOLLOWS {
        bigint id PK
        bigint follower_id FK
        bigint followed_id FK
    }

    PROFILES {
        bigint id PK
        bigint user_id FK UK
        string bio
        string avatar
        string cover_image
        boolean is_private
    }

    STORIES {
        bigint id PK
        bigint user_id FK
        string media_path
        string slug UK
        timestamp expires_at
    }

    STORY_VIEWS {
        bigint id PK
        bigint story_id FK
        bigint user_id FK
    }

    STORY_REACTIONS {
        bigint id PK
        bigint story_id FK
        bigint user_id FK
        string reaction_type
    }

    CONVERSATIONS {
        bigint id PK
        bigint user1_id FK
        bigint user2_id FK
        string slug UK
        boolean is_group
        bigint group_id FK
        timestamp last_message_at
    }

    MESSAGES {
        bigint id PK
        bigint conversation_id FK
        bigint sender_id FK
        string content
        string media_path
        string system_type
        timestamp read_at
        timestamp delivered_at
    }

    GROUPS {
        bigint id PK
        string name
        string description
        bigint creator_id FK
        string slug UK
        string invite_link UK
        boolean is_private
    }

    GROUP_MEMBERS {
        bigint id PK
        bigint group_id FK
        bigint user_id FK
        string role
        timestamp joined_at
    }

    SAVED_POSTS {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
    }

    BLOCKS {
        bigint id PK
        bigint blocker_id FK
        bigint blocked_id FK
    }

    NOTIFICATIONS {
        bigint id PK
        bigint user_id FK
        string type
        boolean is_read
    }

    POST_MEDIA {
        bigint id PK
        bigint post_id FK
        string media_path
        string media_type
        int order
    }

    MENTIONS {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
    }
```

---

## Sequence Diagrams

### 1. User Authentication Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant C as AuthController
    participant M as User Model
    participant DB as Database
    participant E as Email Service

    U->>B: Enter credentials
    B->>C: POST /login
    C->>DB: Validate credentials
    DB-->>C: User data
    C->>M: Generate verification code
    M->>DB: Save verification code
    C->>E: Send verification email
    E-->>U: Receive email
    U->>B: Enter verification code
    B->>C: POST /email/verify-code
    C->>DB: Verify code
    DB-->>C: Valid code
    C->>DB: Mark email as verified
    C->>B: Redirect to home
    B-->>U: Show feed
```

### 2. Post Creation Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant PC as PostController
    participant PM as Post Model
    participant DB as Database
    participant S as Storage

    U->>B: Create post with media
    B->>PC: POST /posts
    PC->>S: Upload media files
    S-->>PC: Media paths
    PC->>PM: Create post instance
    PM->>DB: Insert post record
    DB-->>PM: Post ID
    loop For each media file
        PM->>DB: Insert post_media record
    end
    PM-->>PC: Post created
    PC->>B: Redirect to post
    B-->>U: Show post
```

### 3. Like Post Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant PC as PostController
    participant L as Like Model
    participant DB as Database
    participant N as Notification

    U->>B: Click like button
    B->>PC: POST /posts/{id}/like
    PC->>DB: Check existing like
    DB-->>PC: No existing like
    PC->>L: Create like record
    L->>DB: Insert like
    DB-->>L: Like created
    PC->>N: Create notification
    N->>DB: Insert notification
    PC-->>B: Return success
    B-->>U: Update UI
```

### 4. Send Message Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant CC as ChatController
    participant C as Conversation
    participant M as Message
    participant DB as Database
    participant WS as WebSocket

    U->>B: Type message
    B->>CC: POST /chat/{conversation}
    CC->>DB: Find conversation
    DB-->>CC: Conversation data
    CC->>M: Create message
    M->>DB: Insert message
    DB-->>M: Message ID
    CC->>WS: Broadcast to recipients
    WS-->>B: Real-time update
    CC-->>B: Return message
    B-->>U: Show message
```

### 5. Follow User Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant UC as UserController
    participant F as Follow Model
    participant DB as Database
    participant N as Notification

    U->>B: Click follow button
    B->>UC: POST /users/{id}/follow
    UC->>DB: Check existing follow
    DB-->>UC: No existing follow
    UC->>F: Create follow record
    F->>DB: Insert follow
    DB-->>F: Follow created
    UC->>N: Create notification
    N->>DB: Insert notification
    UC-->>B: Return success
    B-->>U: Update button state
```

### 6. Create Group Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant GC as GroupController
    participant G as Group Model
    participant GM as GroupMember
    participant C as Conversation
    participant DB as Database

    U->>B: Create group form
    B->>GC: POST /groups
    GC->>G: Create group
    G->>DB: Insert group
    DB-->>G: Group ID
    GC->>GM: Add creator as admin
    GM->>DB: Insert group_member
    GC->>C: Create conversation
    C->>DB: Insert conversation
    GC-->>B: Redirect to group
    B-->>U: Show group page
```

---

## Use Case Diagram

This diagram shows the main actors and their interactions with the system.

```mermaid
usecaseDiagram
    actor "Guest User" as Guest
    actor "Registered User" as User
    actor "Admin" as Admin

    package "Authentication" {
        usecase "Register Account" as UC1
        usecase "Login" as UC2
        usecase "Logout" as UC3
        usecase "Reset Password" as UC4
        usecase "Verify Email" as UC5
        usecase "Login with Google" as UC6
    }

    package "Posts" {
        usecase "Create Post" as UC10
        usecase "View Posts" as UC11
        usecase "Edit Post" as UC12
        usecase "Delete Post" as UC13
        usecase "Like Post" as UC14
        usecase "Save Post" as UC15
        usecase "Comment on Post" as UC16
        usecase "Like Comment" as UC17
    }

    package "Stories" {
        usecase "Create Story" as UC20
        usecase "View Stories" as UC21
        usecase "React to Story" as UC22
        usecase "View Story Viewers" as UC23
        usecase "Delete Story" as UC24
    }

    package "Social Features" {
        usecase "Follow User" as UC30
        usecase "Unfollow User" as UC31
        usecase "Block User" as UC32
        usecase "View Profile" as UC33
        usecase "Edit Profile" as UC34
        usecase "Explore Users" as UC35
    }

    package "Messaging" {
        usecase "Send Message" as UC40
        usecase "View Conversations" as UC41
        usecase "Delete Message" as UC42
        usecase "Mark as Read" as UC43
        usecase "Send Typing Indicator" as UC44
    }

    package "Groups" {
        usecase "Create Group" as UC50
        usecase "Join Group" as UC51
        usecase "Leave Group" as UC52
        usecase "Add Members" as UC53
        usecase "Remove Members" as UC54
        usecase "Make Admin" as UC55
    }

    package "Admin" {
        usecase "View Dashboard" as UC60
        usecase "Manage Users" as UC61
        usecase "Delete Posts" as UC62
        usecase "Delete Comments" as UC63
        usecase "Delete Stories" as UC64
        usecase "Create Admin" as UC65
    }

    package "AI Features" {
        usecase "Chat with AI" as UC70
    }

    Guest --> UC1
    Guest --> UC2
    Guest --> UC4
    Guest --> UC6
    Guest --> UC11
    Guest --> UC21
    Guest --> UC33
    Guest --> UC35

    User --> UC3
    User --> UC5
    User --> UC10
    User --> UC11
    User --> UC12
    User --> UC13
    User --> UC14
    User --> UC15
    User --> UC16
    User --> UC17
    User --> UC20
    User --> UC21
    User --> UC22
    User --> UC23
    User --> UC24
    User --> UC30
    User --> UC31
    User --> UC32
    User --> UC33
    User --> UC34
    User --> UC35
    User --> UC40
    User --> UC41
    User --> UC42
    User --> UC43
    User --> UC44
    User --> UC50
    User --> UC51
    User --> UC52
    User --> UC53
    User --> UC54
    User --> UC55
    User --> UC70

    Admin --> UC60
    Admin --> UC61
    Admin --> UC62
    Admin --> UC63
    Admin --> UC64
    Admin --> UC65
```

---

## System Architecture Overview

```mermaid
graph TB
    subgraph "Frontend"
        V[Vue.js / Blade Templates]
        CSS[Tailwind CSS]
        JS[Alpine.js]
    end

    subgraph "Backend"
        LC[Laravel Controllers]
        MD[Eloquent Models]
        MW[Middleware]
        SV[Services]
    end

    subgraph "Data Layer"
        MySQL[MySQL Database]
        RED[Redis Cache]
    end

    subgraph "External Services"
        G[Google OAuth]
        ML[Mail Service]
        AI[AI Assistant]
    end

    V --> LC
    CSS --> V
    JS --> V
    LC --> MD
    LC --> MW
    LC --> SV
    MD --> MySQL
    SV --> MySQL
    SV --> RED
    LC --> G
    SV --> ML
    SV --> AI
```

---

## Notes

- All diagrams are written in **Mermaid.js** syntax and will render automatically on GitHub
- For local viewing, use a Markdown editor with Mermaid support (VS Code, Obsidian, etc.)
- Diagrams are auto-generated based on the current codebase structure
- Last updated: March 15, 2026
