# Nexus - UML Diagrams

Complete UML diagrams and visual documentation for the Nexus social networking platform.

---

## Table of Contents

1. [Class Diagram](#class-diagram)
2. [Entity Relationship Diagram](#entity-relationship-diagram)
3. [Use Case Diagram](#use-case-diagram)
4. [Sequence Diagrams](#sequence-diagrams)
5. [Activity Diagrams](#activity-diagrams)
6. [Component Diagram](#component-diagram)
7. [Deployment Diagram](#deployment-diagram)
8. [State Machine Diagrams](#state-machine-diagrams)

---

## Class Diagram

This diagram shows all Eloquent models, their attributes, methods, and relationships.

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string username
        +string email
        +string password
        +bool is_admin
        +bool is_suspended
        +datetime last_active
        +bool is_online
        +string language
        +generateVerificationCode() string
        +verifyCode(string) bool
        +canChangeUsername() bool
        +updateUsername(string)
        +markAsOffline()
        +updateLastActive()
        +isInactiveFor(int) bool
        +posts() HasMany
        +comments() HasMany
        +likes() HasMany
        +follows() HasMany
        +followers() HasMany
        +following() BelongsToMany
        +profile() HasOne
        +stories() HasMany
        +conversations() Custom
        +messages() HasMany
        +groups() BelongsToMany
        +notifications() HasMany
        +savedPosts() HasMany
        +blockedUsers() HasMany
        +isFollowing(User) bool
        +isBlocking(User) bool
    }

    class Profile {
        +int id
        +int user_id
        +string avatar
        +string cover_image
        +string bio
        +string website
        +string location
        +bool is_private
        +json social_links
        +user() BelongsTo
    }

    class Post {
        +int id
        +int user_id
        +string content
        +string slug
        +bool is_private
        +datetime pinned_at
        +user() BelongsTo
        +likes() HasMany
        +comments() HasMany
        +media() HasMany
        +savedPosts() HasMany
        +hashtags() BelongsToMany
        +event() HasOne
        +likedBy(User) bool
        +isPinned() bool
        +pin()
        +unpin()
    }

    class PostMedia {
        +int id
        +int post_id
        +string media_type
        +string media_path
        +string media_thumbnail
        +int sort_order
        +post() BelongsTo
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

    class CommentLike {
        +int id
        +int user_id
        +int comment_id
        +user() BelongsTo
        +comment() BelongsTo
    }

    class Like {
        +int id
        +int user_id
        +int post_id
        +user() BelongsTo
        +post() BelongsTo
    }

    class Follow {
        +int id
        +int follower_id
        +int followed_id
        +follower() BelongsTo
        +followed() BelongsTo
    }

    class Block {
        +int id
        +int blocker_id
        +int blocked_id
        +blocker() BelongsTo
        +blocked() BelongsTo
    }

    class SavedPost {
        +int id
        +int user_id
        +int post_id
        +user() BelongsTo
        +post() BelongsTo
    }

    class Story {
        +int id
        +int user_id
        +string slug
        +string media_type
        +string media_path
        +string content
        +json metadata
        +datetime expires_at
        +user() BelongsTo
        +views() HasMany
        +reactions() HasMany
        +isActive() bool
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
        +bool is_group
        +int group_id
        +string slug
        +datetime last_message_at
        +messages() HasMany
        +getRecipients() array
    }

    class Message {
        +int id
        +int conversation_id
        +int sender_id
        +string content
        +string media_type
        +string media_path
        +string system_type
        +datetime read_at
        +datetime delivered_at
        +json deleted_for
        +conversation() BelongsTo
        +sender() BelongsTo
    }

    class Group {
        +int id
        +string name
        +string description
        +string avatar
        +int creator_id
        +string slug
        +string invite_link
        +bool is_private
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
        +datetime joined_at
        +group() BelongsTo
        +user() BelongsTo
    }

    class Notification {
        +int id
        +int user_id
        +string type
        +json data
        +datetime read_at
        +string related_type
        +int related_id
        +user() BelongsTo
    }

    class Mention {
        +int id
        +int user_id
        +int post_id
        +user() BelongsTo
        +post() BelongsTo
    }

    class Hashtag {
        +int id
        +string name
        +string slug
        +posts() BelongsToMany
    }

    class PostReport {
        +int id
        +int user_id
        +int post_id
        +string reason
        +string description
        +string status
        +datetime reviewed_at
        +int reviewed_by
        +string admin_action
        +string slug
        +user() BelongsTo
        +post() BelongsTo
        +reviewer() BelongsTo
    }

    class PushSubscription {
        +int id
        +int user_id
        +string content
        +string p256dh
        +string auth
        +user() BelongsTo
    }

    class ActivityLog {
        +int id
        +int user_id
        +string action
        +string description
        +string ip_address
        +string user_agent
        +string country
        +string city
        +int session_id
        +user() BelongsTo
    }

    class Event {
        +int id
        +int user_id
        +int post_id
        +string title
        +string type
        +datetime event_date
        +json metadata
        +user() BelongsTo
        +post() BelongsTo
        +reactions() HasMany
    }

    class EventReaction {
        +int id
        +int event_id
        +int user_id
        +string reaction_type
        +event() BelongsTo
        +user() BelongsTo
    }

    User "1" -- "1" Profile : has
    User "1" *-- "0..*" Post : creates
    User "1" *-- "0..*" Comment : creates
    User "1" *-- "0..*" Like : creates
    User "1" *-- "0..*" CommentLike : creates
    User "1" *-- "0..*" Follow : follows
    User "1" *-- "0..*" Block : blocks
    User "1" *-- "0..*" SavedPost : saves
    User "1" *-- "0..*" Story : creates
    User "1" *-- "0..*" StoryView : views
    User "1" *-- "0..*" StoryReaction : reacts
    User "1" *-- "0..*" Message : sends
    User "1" *-- "0..*" GroupMember : joins
    User "1" *-- "0..*" Group : belongs to
    User "1" *-- "0..*" Notification : receives
    User "1" *-- "0..*" Mention : creates
    User "1" *-- "0..*" PushSubscription : subscribes
    User "1" *-- "0..*" ActivityLog : generates
    User "1" *-- "0..*" Event : creates
    User "1" *-- "0..*" EventReaction : reacts

    Post "1" *-- "0..*" PostMedia : contains
    Post "1" *-- "0..*" Like : receives
    Post "1" *-- "0..*" Comment : receives
    Post "1" *-- "0..*" SavedPost : saved by
    Post "1" *-- "0..*" Mention : mentioned in
    Post "1" *-- "0..*" Hashtag : tagged with
    Post "1" -- "0..1" Event : associated with

    Comment "1" *-- "0..*" CommentLike : receives
    Comment "1" -- "0..*" Comment : has replies

    Story "1" *-- "0..*" StoryView : viewed by
    Story "1" *-- "0..*" StoryReaction : reacted by

    Conversation "1" *-- "0..*" Message : contains
    Conversation "2" -- "2" User : participants
    Conversation "0..1" -- "0..1" Group : linked to

    Group "1" *-- "0..*" GroupMember : has members
    Group "1" -- "0..1" Conversation : has conversation
    GroupMember "1" -- "1" User : belongs to
    GroupMember "1" -- "1" Group : belongs to

    PostReport "1" -- "1" User : reported by
    PostReport "1" -- "1" Post : reports
    PostReport "0..1" -- "1" User : reviewed by
```

---

## Entity Relationship Diagram

This diagram shows the database table relationships with cardinality.

```mermaid
erDiagram
    USERS ||--o{ POSTS : creates
    USERS ||--o{ COMMENTS : creates
    USERS ||--o{ LIKES : creates
    USERS ||--o{ COMMENT_LIKES : creates
    USERS ||--o{ FOLLOWS : creates
    USERS ||--o{ BLOCKS : creates
    USERS ||--o{ SAVED_POSTS : saves
    USERS ||--o{ STORIES : creates
    USERS ||--o{ STORY_VIEWS : views
    USERS ||--o{ STORY_REACTIONS : reacts
    USERS ||--o{ MESSAGES : sends
    USERS ||--o{ GROUP_MEMBERS : joins
    USERS ||--o{ NOTIFICATIONS : receives
    USERS ||--o{ MENTIONS : creates
    USERS ||--o{ PUSH_SUBSCRIPTIONS : subscribes
    USERS ||--o{ ACTIVITY_LOGS : generates
    USERS ||--o{ EVENTS : creates
    USERS ||--o{ EVENT_REACTIONS : reacts
    USERS ||--|| PROFILES : has

    POSTS ||--o{ POST_MEDIA : contains
    POSTS ||--o{ LIKES : receives
    POSTS ||--o{ COMMENTS : receives
    POSTS ||--o{ SAVED_POSTS : saved_by
    POSTS ||--o{ MENTIONS : mentioned_in
    POSTS }|--|{ HASHTAGS : tagged_with
    POSTS ||--o| EVENTS : associated_with

    COMMENTS ||--o{ COMMENT_LIKES : receives
    COMMENTS ||--o{ COMMENTS : has_replies

    STORIES ||--o{ STORY_VIEWS : viewed_by
    STORIES ||--o{ STORY_REACTIONS : reacted_by

    CONVERSATIONS ||--o{ MESSAGES : contains
    CONVERSATIONS }|--|| USERS : participants
    CONVERSATIONS }|--o| GROUPS : linked_to

    GROUPS ||--o{ GROUP_MEMBERS : has
    GROUPS ||--o| CONVERSATIONS : has
    GROUP_MEMBERS }|--|| USERS : belongs_to
    GROUP_MEMBERS }|--|| GROUPS : belongs_to

    POST_REPORTS }|--|| USERS : reported_by
    POST_REPORTS }|--|| POSTS : reports
    POST_REPORTS }|--o| USERS : reviewed_by

    EVENTS ||--o{ EVENT_REACTIONS : reacted_by
    EVENT_REACTIONS }|--|| USERS : created_by
```

---

## Use Case Diagram

This diagram shows all actors and their interactions with the system.

```mermaid
flowchart TB
    Guest["Guest User"]
    User["Registered User"]
    Admin["Admin User"]

    Guest --> Register[Register Account]
    Guest --> Login[Login]
    Guest --> ResetPwd[Reset Password]
    Guest --> GoogleLogin[Login with Google]
    Guest --> ViewFeed[View Feed]
    Guest --> ViewStories[View Stories]
    Guest --> ViewProfile[View Profile]
    Guest --> ExploreUsers[Explore Users]
    Guest --> SearchUsers[Search Users]

    User --> Logout[Logout]
    User --> VerifyEmail[Verify Email]
    User --> SetPwdOAuth[Set Password OAuth]
    User --> CreatePost[Create Post]
    User --> EditPost[Edit Post]
    User --> DeletePost[Delete Post]
    User --> LikePost[Like Post]
    User --> SavePost[Save Post]
    User --> CommentPost[Comment on Post]
    User --> LikeComment[Like Comment]
    User --> ReportPost[Report Post]
    User --> PinPost[Pin Post]
    User --> AddMedia[Add Media]
    User --> CreateStory[Create Story]
    User --> ReactStory[React to Story]
    User --> ViewStoryViewers[View Story Viewers]
    User --> DeleteStory[Delete Story]
    User --> FollowUser[Follow User]
    User --> UnfollowUser[Unfollow User]
    User --> BlockUser[Block User]
    User --> EditProfile[Edit Profile]
    User --> GenerateQR[Generate QR Code]
    User --> SendMessage[Send Message]
    User --> ViewConversations[View Conversations]
    User --> DeleteMessage[Delete Message]
    User --> MarkRead[Mark as Read]
    User --> SendTyping[Send Typing Indicator]
    User --> SendVoice[Send Voice Message]
    User --> SendMediaMsg[Send Media Message]
    User --> CreateGroup[Create Group]
    User --> JoinGroup[Join Group]
    User --> LeaveGroup[Leave Group]
    User --> AddMembers[Add Members]
    User --> RemoveMembers[Remove Members]
    User --> MakeAdmin[Make Admin]
    User --> RemoveAdmin[Remove Admin]
    User --> GenInvite[Generate Invite Link]
    User --> AcceptInvite[Accept Invite]
    User --> ViewNotif[View Notifications]
    User --> MarkNotifRead[Mark as Read]
    User --> MarkAllRead[Mark All Read]
    User --> DeleteNotif[Delete Notification]
    User --> ViewActivity[View Activity Log]
    User --> ExportActivity[Export Activity]
    User --> TerminateSession[Terminate Session]
    User --> ClearActivity[Clear Old Activity]
    User --> CreateEvent[Create Event]
    User --> EditEvent[Edit Event]
    User --> DeleteEvent[Delete Event]
    User --> ReactEvent[React to Event]
    User --> ViewMemory[View Memory Book]
    User --> ChatAI[Chat with AI]

    Admin --> AdminDashboard[View Dashboard]
    Admin --> ManageUsers[Manage Users]
    Admin --> SuspendUser[Suspend User]
    Admin --> DeleteAnyPost[Delete Any Post]
    Admin --> DeleteAnyComment[Delete Any Comment]
    Admin --> DeleteAnyStory[Delete Any Story]
    Admin --> CreateAdmin[Create Admin]
    Admin --> ReviewReports[Review Reports]
    Admin --> AcceptReport[Accept Report]
    Admin --> RejectReport[Reject Report]
```

---

## Sequence Diagrams

### 1. User Registration Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant RC as RegisterController
    participant UserM as User Model
    participant ProfileM as Profile Model
    participant DB as Database
    participant Mail as Mail Service

    U->>B: Visit /register
    B->>U: Show registration form
    U->>B: Fill form & submit
    B->>RC: POST /register

    Note over RC: Validation
    RC->>RC: Validate name, email, password
    RC->>RC: Check reserved usernames
    RC->>RC: Check disposable emails
    RC->>RC: Verify password strength

    RC->>DB: Check email uniqueness
    DB-->>RC: Email available

    Note over RC: Create User
    RC->>UserM: Create with hashed password
    UserM->>DB: Insert user record
    DB-->>UserM: User ID
    UserM->>RC: User created

    RC->>ProfileM: Create profile
    ProfileM->>DB: Insert profile
    DB-->>ProfileM: Profile created

    Note over RC: Email Verification
    RC->>UserM: Generate 6-digit code
    UserM->>DB: Save code + expiry (10 min)
    RC->>Mail: Send verification email
    Mail->>U: Deliver email

    RC->>B: Redirect to /email/verify
    B->>U: Show verification page
```

### 2. Login Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant LC as LoginController
    participant UserM as User Model
    participant DB as Database
    participant Auth as Auth Manager

    U->>B: Visit /login
    B->>U: Show login form
    U->>B: Enter credentials
    B->>LC: POST /login

    Note over LC: Validation
    LC->>LC: Validate email & password
    LC->>DB: Check rate limit (5/min)

    LC->>UserM: Find by email
    UserM->>DB: Query user
    DB-->>UserM: User data

    alt Invalid Credentials
        LC->>B: Redirect with error
        B->>U: Show error message
    else Valid Credentials
        LC->>UserM: Check is_suspended
        alt Account Suspended
            LC->>B: Redirect to /suspended
            B->>U: Show suspended page
        else Account Active
            LC->>Auth: Attempt login
            Auth->>DB: Create session
            Auth-->>LC: Login successful

            Note over LC: Verification Check
            LC->>UserM: Check email_verified_at
            alt Not Verified
                LC->>B: Redirect to /email/verify
                B->>U: Show verification page
            else Verified
                LC->>UserM: Check password null (OAuth)
                alt No Password (OAuth)
                    LC->>B: Redirect to set-password
                    B->>U: Show set password form
                else Has Password
                    LC->>UserM: Update last_active
                    LC->>B: Redirect to /
                    B->>U: Show home feed
                end
            end
        end
    end
```

### 3. Create Post Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant PC as PostController
    participant PostM as Post Model
    participant MediaM as PostMedia Model
    participant MentionS as MentionService
    participant Storage as File Storage
    participant DB as Database

    U->>B: Click "New Post"
    B->>U: Show post form
    U->>B: Add content & media
    B->>PC: POST /posts (multipart)

    Note over PC: Validation
    PC->>PC: Validate content (max 280)
    PC->>PC: Validate media (max 30 files)
    PC->>PC: Check file sizes (max 50MB)
    PC->>PC: Verify MIME types

    Note over PC: Create Post
    PC->>PostM: Create with slug
    PostM->>DB: Insert post record
    DB-->>PostM: Post ID + slug

    Note over PC: Process Media
    loop For each file
        PC->>Storage: Upload file
        Storage-->>PC: File path
        PC->>MediaM: Create PostMedia record
        MediaM->>DB: Insert media record
        alt Video file
            PC->>PC: Generate thumbnail (FFmpeg)
        end
    end

    Note over PC: Process Mentions
    PC->>MentionS: Parse @username
    MentionS->>DB: Find mentioned users
    MentionS->>DB: Create Mention records
    MentionS->>DB: Create Notifications

    PC->>B: Redirect to post
    B->>U: Show created post
```

### 4. Like Post Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant PC as PostController
    participant LikeM as Like Model
    participant NotifM as Notification Model
    participant DB as Database

    U->>B: Click like button
    B->>PC: POST /posts/{id}/like

    PC->>DB: Check existing like
    DB-->>PC: Like status

    alt Already Liked
        Note over PC: Unlike
        PC->>LikeM: Delete like
        LikeM->>DB: Remove like record
        PC->>B: Return unliked
    else Not Liked
        Note over PC: Like
        PC->>LikeM: Create like
        LikeM->>DB: Insert like record
        PC->>NotifM: Create notification
        NotifM->>DB: Insert notification
        PC->>B: Return liked
    end

    B->>U: Update button state
```

### 5. Send Message Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant CC as ChatController
    participant ConvM as Conversation Model
    participant MsgM as Message Model
    participant RealtimeS as RealtimeService
    participant Cache as Cache
    participant DB as Database

    U->>B: Type message
    B->>CC: POST /chat/{conversation}

    Note over CC: Find/Create Conversation
    CC->>ConvM: Find conversation
    alt No conversation exists
        CC->>ConvM: Create new conversation
        ConvM->>DB: Insert conversation
    end

    Note over CC: Create Message
    CC->>MsgM: Create message
    MsgM->>DB: Insert message record
    DB-->>MsgM: Message ID

    Note over CC: Real-time Updates
    CC->>RealtimeS: Broadcast to recipients
    RealtimeS->>Cache: Set typing indicator

    CC->>B: Return message data
    B->>U: Show message in chat

    Note over B: Polling (every 2s)
    B->>CC: GET /chat/{conv}/messages
    CC->>DB: Query new messages
    DB-->>CC: Messages
    CC->>B: Return messages
    B->>U: Append to chat
```

### 6. Follow User Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant UC as UserController
    participant FollowM as Follow Model
    participant NotifM as Notification Model
    participant DB as Database

    U->>B: Click follow button
    B->>UC: POST /users/{id}/follow

    UC->>DB: Check existing follow
    DB-->>UC: Follow status

    alt Already Following
        Note over UC: Unfollow
        UC->>FollowM: Delete follow
        FollowM->>DB: Remove follow record
        UC->>B: Return unfollowed
    else Not Following
        Note over UC: Follow
        UC->>FollowM: Create follow
        FollowM->>DB: Insert follow record
        UC->>NotifM: Create notification
        NotifM->>DB: Insert notification
        UC->>B: Return followed
    end

    B->>U: Update button state
```

### 7. Create Story Flow

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant SC as StoryController
    participant StoryM as Story Model
    participant Storage as File Storage
    participant DB as Database

    U->>B: Click "Create Story"
    B->>U: Show story form
    U->>B: Add media/content
    B->>SC: POST /stories

    Note over SC: Validation
    SC->>SC: Validate media type
    SC->>SC: Check file size
    SC->>SC: Verify content length

    Note over SC: Create Story
    SC->>StoryM: Create with slug
    StoryM->>DB: Insert story record
    DB-->>StoryM: Story ID + slug

    alt Has media
        SC->>Storage: Upload file
        Storage-->>SC: File path
        SC->>DB: Update story media_path
    end

    SC->>DB: Set expires_at (+24 hours)
    SC->>B: Redirect to stories
    B->>U: Show story viewer
```

### 8. Admin Delete Post Flow

```mermaid
sequenceDiagram
    participant A as Admin
    participant B as Browser
    participant AC as AdminController
    participant PostM as Post Model
    participant Storage as File Storage
    participant DB as Database

    A->>B: Visit /admin/posts
    B->>A: Show posts list
    A->>B: Click delete on post
    B->>AC: DELETE /admin/posts/{id}

    Note over AC: Authorization
    AC->>AC: Check is_admin middleware
    AC->>AC: Verify admin privileges

    AC->>PostM: Find post
    PostM->>DB: Query post
    DB-->>PostM: Post data

    Note over AC: Delete Post
    AC->>Storage: Delete media files
    loop For each media
        Storage->>Storage: Remove file
    end
    AC->>PostM: Soft delete
    PostM->>DB: Set deleted_at

    AC->>B: Redirect with success
    B->>A: Show updated list
```

---

## Activity Diagrams

### 1. Post Creation Activity

```mermaid
flowchart TD
    A[Start] --> B{Authenticated?}
    B -->|No| C[Redirect to Login]
    B -->|Yes| D[Show Post Form]
    D --> E[User enters content/media]
    E --> F{Submit Form}
    F --> G{Has content or media?}
    G -->|No| H[Show error]
    H --> E
    G -->|Yes| I{Media valid?}
    I -->|No| J[Show media error]
    J --> E
    I -->|Yes| K[Create Post record]
    K --> L[Generate unique slug]
    L --> M[Upload media files]
    M --> N{Has videos?}
    N -->|Yes| O[Generate thumbnails]
    N -->|No| P[Process mentions]
    O --> P
    P --> Q[Create Mention records]
    Q --> R[Create Notifications]
    R --> S[Redirect to post]
    S --> T[End]
```

### 2. User Authentication Activity

```mermaid
flowchart TD
    A[Start] --> B[Visit /login]
    B --> C[Enter credentials]
    C --> D{Submit}
    D -->|No| E[Show error]
    E --> F{Rate limited?}
    F -->|Yes| G[Wait and retry]
    F -->|No| C
    D -->|Yes| H{Account suspended?}
    H -->|Yes| I[Show suspended page]
    I --> J[End]
    H -->|No| K[Create session]
    K --> L{Email verified?}
    L -->|No| M[Redirect to verification]
    M --> N[Show verify page]
    N --> J
    L -->|Yes| O{OAuth user?}
    O -->|Yes| P[Redirect to set password]
    P --> J
    O -->|No| Q[Update last_active]
    Q --> R[Redirect to home]
    R --> J
```

### 3. Message Sending Activity

```mermaid
flowchart TD
    A[Start] --> B[Open chat]
    B --> C[Load conversation]
    C --> D[Type message]
    D --> E{Message type?}
    E -->|Text| F{Valid text?}
    E -->|Voice| G[Record voice]
    E -->|Media| H[Select media]
    F -->|No| I[Show error]
    I --> D
    F -->|Yes| J[Send message]
    G --> J
    H --> J
    J --> K[Insert to database]
    K --> L[Broadcast via polling]
    L --> M[Update UI]
    M --> N{Recipient active?}
    N -->|Yes| O[Mark as delivered]
    N -->|No| P[Wait for read]
    O --> Q[End]
    P --> Q
```

---

## Component Diagram

This diagram shows the high-level architecture components and their relationships.

```mermaid
flowchart TB
    Browser[Web Browser]
    Mobile[Mobile Browser]
    API[API Clients]
    Blade[Blade Templates]
    Vue[Vue Components]
    JS[JavaScript Modules]
    CSS[Tailwind CSS]
    Routes[Routes]
    Middleware[Middleware]
    Controllers[Controllers]
    Services[Services]
    Models[Models]
    Mail[Mail Classes]
    Eloquent[Eloquent ORM]
    QueryBuilder[Query Builder]
    Migrations[Migrations]
    Database[(Database)]
    Cache[(Cache)]
    Storage[(File Storage)]
    Sessions[(Sessions)]
    Google[Google OAuth]
    MailService[Email Service]
    Cloudflare[Cloudflare Tunnel]

    Browser --> Blade
    Browser --> Vue
    Browser --> JS
    Mobile --> Blade
    API --> Routes
    Blade --> Routes
    Vue --> Routes
    JS --> Routes
    Routes --> Middleware
    Middleware --> Controllers
    Controllers --> Services
    Controllers --> Models
    Controllers --> Mail
    Services --> Eloquent
    Models --> Eloquent
    Mail --> Eloquent
    Eloquent --> QueryBuilder
    QueryBuilder --> Migrations
    Migrations --> Database
    Eloquent --> Database
    Eloquent --> Cache
    Eloquent --> Storage
    Eloquent --> Sessions
    Controllers --> Google
    Mail --> MailService
    Browser --> Cloudflare
```

---

## Deployment Diagram

This diagram shows the physical deployment architecture.

```mermaid
flowchart TB
    Desktop[Desktop Browser]
    Mobile[Mobile Browser]
    Tablet[Tablet Browser]
    Cloudflare[Cloudflare]
    Nginx[Nginx/Apache]
    AppServer1[Laravel App Server 1]
    AppServer2[Laravel App Server 2]
    QueueWorker[Queue Worker]
    Scheduler[Task Scheduler]
    MySQL[(MySQL Database)]
    Redis[(Redis Cache)]
    FileStorage[File Storage]
    Google[Google OAuth API]
    SMTP[SMTP Mail Server]

    Desktop --> Cloudflare
    Mobile --> Cloudflare
    Tablet --> Cloudflare
    Cloudflare --> Nginx
    Nginx --> AppServer1
    Nginx --> AppServer2
    AppServer1 --> MySQL
    AppServer1 --> Redis
    AppServer1 --> FileStorage
    AppServer1 --> QueueWorker
    AppServer1 --> Scheduler
    AppServer2 --> MySQL
    AppServer2 --> Redis
    AppServer2 --> FileStorage
    QueueWorker --> MySQL
    QueueWorker --> FileStorage
    Scheduler --> MySQL
    AppServer1 --> Google
    AppServer1 --> SMTP
```

---

## State Machine Diagrams

### 1. User Account State

```mermaid
flowchart LR
    A[Start] --> B[Unregistered]
    B --> C[Registered]
    C --> D[EmailPending]
    D --> E[Verified]
    D --> B
    D --> C
    E --> F[PasswordRequired]
    F --> G[Active]
    E --> G
    G --> H[Suspended]
    H --> G
    G --> I[Offline]
    I --> G
    G --> J[Deleted]
    H --> J
    J --> K[End]
```

### 2. Post State

```mermaid
flowchart LR
    A[Start] --> B[Draft]
    B --> C[Published]
    C --> D[Pinned]
    D --> C
    C --> E[Reported]
    E --> C
    E --> F[Deleted]
    C --> F
    D --> F
    F --> G[End]
```

### 3. Story State

```mermaid
flowchart LR
    A[Start] --> B[Creating]
    B --> C[Active]
    C --> D[Expired]
    C --> E[Deleted]
    D --> F[End]
    E --> G[End]
```

### 4. Message State

```mermaid
flowchart LR
    A[Start] --> B[Composing]
    B --> C[Sent]
    C --> D[Delivered]
    D --> E[Read]
    C --> F[DeletedSender]
    D --> F
    E --> F
    C --> G[DeletedEveryone]
    D --> G
    E --> G
    F --> H[End]
    G --> I[End]
```

### 5. Conversation State

```mermaid
flowchart LR
    A[Start] --> B[Inactive]
    B --> C[Active]
    C --> B
    C --> D[HasUnread]
    D --> C
    D --> B
    C --> E[Deleted]
    B --> E
    E --> F[End]
```

### 6. Group Member State

```mermaid
flowchart LR
    A[Start] --> B[Invited]
    B --> C[Member]
    B --> D[End]
    C --> E[Admin]
    E --> C
    C --> F[Left]
    E --> F
    C --> G[Removed]
    E --> G
    F --> H[End]
    G --> I[End]
```

---

## Component Interaction Matrix

```mermaid
flowchart LR
    A[Routes] --> B[Controllers]
    A --> C[Services]
    A --> D[Models]
    A --> E[Middleware]
    B --> C
    B --> D
    B --> E
    C --> D
    E --> A
    E --> B
```

---

## Technology Stack Diagram

```mermaid
flowchart LR
    F1[Blade Templates]
    F2[Vue.js 3.4]
    F3[Alpine.js]
    F4[Tailwind CSS 3.2]
    F5[Axios]
    B1[Vite 6.4]
    B2[TypeScript 5.6]
    B3[ESLint]
    B4[Prettier]
    B5[JS Obfuscator]
    BK1[Laravel 12]
    BK2[PHP 8.2+]
    BK3[Eloquent ORM]
    BK4[Sanctum]
    BK5[Socialite]
    D1[SQLite/MySQL]
    D2[Database Cache]
    D3[File System]
    D4[Database Sessions]
    E1[Google OAuth]
    E2[SMTP Server]
    E3[FFmpeg]

    F1 --> B1
    F2 --> B1
    F3 --> B1
    F4 --> B1
    F5 --> B1
    B1 --> BK1
    BK1 --> BK2
    BK1 --> BK3
    BK1 --> BK4
    BK1 --> BK5
    BK3 --> D1
    BK1 --> D2
    BK1 --> D3
    BK1 --> D4
    BK5 --> E1
    BK1 --> E2
    BK1 --> E3
```

---

## Data Flow Diagrams

### 1. Read Operations (Feed Loading)

```mermaid
flowchart LR
    U[User] -->|Request| B[Browser]
    B -->|HTTP GET| LC[Laravel Controller]
    LC -->|Auth Check| M[Middleware]
    M -->|Query| PM[Post Model]
    PM -->|Eloquent Query| DB[(Database)]
    DB -->|Posts Data| PM
    PM -->|Eager Load| UR[User Relations]
    PM -->|Eager Load| MR[Media Relations]
    PM -->|Eager Load| LR[Like Relations]
    UR --> DB
    MR --> DB
    LR --> DB
    DB -->|Related Data| PM
    PM -->|Collection| LC
    LC -->|Blade View| B
    B -->|Render| U
```

### 2. Write Operations (Post Creation)

```mermaid
flowchart LR
    U[User] -->|Submit Form| B[Browser]
    B -->|POST posts| LC[PostController]
    LC -->|Validate| VR[Validation Rules]
    VR -->|Valid| PM[Post Model]
    PM -->|Insert| DB[(Database)]
    DB -->|Post ID| PM
    PM -->|Upload| FS[File Storage]
    FS -->|Paths| PMM[PostMedia Model]
    PMM -->|Insert| DB
    PM -->|Parse| MS[MentionService]
    MS -->|Create| MN[Mention Model]
    MN -->|Insert| DB
    MS -->|Notify| NM[Notification Model]
    NM -->|Insert| DB
    DB -->|Success| LC
    LC -->|Redirect| B
    B -->|Show| U
```

---

## Performance Optimization Diagram

```mermaid
flowchart TB
    C1[Config Cache]
    C2[Route Cache]
    C3[View Cache]
    C4[Query Cache]
    D1[Indexes on columns]
    D2[Composite Indexes]
    D3[Query Optimization]
    D4[Eager Loading]
    F1[Vite Build]
    F2[Code Splitting]
    F3[Lazy Loading]
    F4[Asset Minification]
    R1[Polling Intervals]
    R2[Conditional Polling]
    R3[Batch Requests]
    R4[Cache Typing]

    C1 --> D1
    C2 --> D2
    C3 --> F1
    C4 --> D3
    D4 --> R1
    F2 --> R2
    F3 --> R3
    F4 --> R4
```

---

## Security Architecture Diagram

```mermaid
flowchart TB
    L1[Network Layer]
    L2[Application Layer]
    L3[Data Layer]
    L4[Business Logic]
    A1[Password Hashing]
    A2[Email Verification]
    A3[Rate Limiting]
    A4[Session Security]
    Z1[Middleware Guards]
    Z2[Policy Checks]
    Z3[Privacy Controls]
    Z4[User Blocking]
    P1[CSRF Protection]
    P2[XSS Prevention]
    P3[SQL Injection]
    P4[File Upload]

    L1 --> L2
    L2 --> L3
    L3 --> L4
    A1 --> Z1
    A2 --> Z2
    A3 --> Z3
    A4 --> Z4
    Z1 --> P1
    Z2 --> P2
    Z3 --> P3
    Z4 --> P4
```

---

## Module Dependency Graph

```mermaid
flowchart LR
    C1[User Module]
    C2[Post Module]
    C3[Comment Module]
    S1[Follow Module]
    S2[Block Module]
    S3[Story Module]
    M1[Chat Module]
    M2[Notification Module]
    M3[Group Module]
    A1[Report Module]
    A2[Admin Panel]
    A3[Activity Log]

    C1 --> C2
    C1 --> C3
    C1 --> S1
    C1 --> S2
    C1 --> S3
    C1 --> M1
    C1 --> M2
    C1 --> M3
    C2 --> C3
    C2 --> S3
    C2 --> A1
    C3 --> M2
    S1 --> M2
    S3 --> M2
    M1 --> M2
    M3 --> M1
    M3 --> M2
    A1 --> A2
    A3 --> A2
```

---

<div align="center">

**Nexus - Complete UML Documentation**

Last Updated: March 28, 2026 | Laravel 12.x | PHP 8.2+

</div>
