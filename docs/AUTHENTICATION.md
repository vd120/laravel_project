# Authentication & Authorization Documentation

## Overview

Nexus implements a comprehensive authentication and authorization system with multiple authentication methods, email verification, account suspension, and role-based access control.

---

## Authentication Methods

### 1. Email/Password Authentication

#### Registration Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Register  │ ──► │   Submit    │ ──► │  Validate   │
│    Page     │     │   Form      │     │   Input     │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                               │
                                               ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Redirect  │ ◄── │   Send      │ ◄── │   Create    │
│   to Verify │     │   Email     │     │   User      │
└─────────────┘     └─────────────┘     └─────────────┘
```

**Endpoint:** `POST /register`

**Validation Rules:**
```php
[
    'name' => ['required', 'string', 'max:255'],
    'username' => ['required', 'string', 'min:3', 'max:30', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
]
```

**Password Requirements:**
- Minimum 8 characters
- At least one uppercase letter
- At least one number
- Confirmation match required

**Username Rules:**
- 3-30 characters
- Alphanumeric and underscores only
- Must be unique
- Can only be changed once every 3 days (for regular users)

**Controller:** `RegisterController@store`

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'min:3', 'max:30', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
    ]);
    
    // Check for disposable email
    if ($this->isDisposableEmail($validated['email'])) {
        return back()->withErrors(['email' => 'Disposable email addresses are not allowed']);
    }
    
    $user = User::create([
        'name' => $validated['name'],
        'username' => $validated['username'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);
    
    // Create profile
    $user->profile()->create();
    
    // Generate verification code
    $user->generateVerificationCode();
    
    // Send verification email
    Mail::to($user->email)->send(new VerificationCodeMail($user->verification_code));
    
    Auth::login($user);
    
    return redirect()->route('verification.notice');
}
```

---

#### Login Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Login     │ ──► │   Submit    │ ──► │   Check     │
│    Page     │     │   Form      │     │  Credentials│
└─────────────┘     └─────────────┘     └──────┬──────┘
                                               │
                    ┌──────────────────────────┘
                    │
                    ▼
         ┌─────────────────────┐
         │  Check Account      │
         │  - Not suspended    │
         │  - Email verified   │
         │  (unless admin)     │
         └──────────┬──────────┘
                    │
                    ▼
         ┌─────────────────────┐
         │  Update Online      │
         │  Status             │
         └──────────┬──────────┘
                    │
                    ▼
         ┌─────────────────────┐
         │  Regenerate Session │
         └──────────┬──────────┘
                    │
                    ▼
         ┌─────────────────────┐
         │  Redirect to Home   │
         └─────────────────────┘
```

**Endpoint:** `POST /login`

**Validation Rules:**
```php
[
    'email' => ['required', 'string', 'email'],
    'password' => ['required', 'string'],
]
```

**Rate Limiting:** 5 attempts per minute

**Controller:** `LoginController@store`

```php
public function store(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);
    
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        
        $user = Auth::user();
        
        // Check if suspended
        if ($user->is_suspended) {
            Auth::logout();
            return redirect()->route('auth.suspended');
        }
        
        // Check email verification (except admins)
        if (!$user->is_admin && !$user->hasVerifiedEmail()) {
            Auth::logout();
            return redirect()->route('verification.notice');
        }
        
        // Update online status
        $user->update([
            'last_active' => now(),
            'is_online' => true,
        ]);
        
        return redirect()->intended(route('home'));
    }
    
    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
}
```

---

#### Email Verification

**6-Digit Code Verification**

**Endpoint:** `POST /email/verify-code`

**Flow:**
1. User receives 6-digit code via email
2. Code expires after 15 minutes
3. User enters code on verification page
4. System validates and marks email as verified

**Controller Logic:**
```php
public function verifyCode(Request $request)
{
    $request->validate([
        'code' => ['required', 'string', 'size:6'],
    ]);
    
    $user = Auth::user();
    
    if ($user->verifyCode($request->code)) {
        // Clear verification code
        $user->update([
            'verification_code' => null,
            'verification_code_expires_at' => null,
            'email_verified_at' => now(),
        ]);
        
        return redirect()->route('home')
            ->with('success', 'Email verified successfully!');
    }
    
    return back()->withErrors([
        'code' => 'Invalid or expired verification code.',
    ]);
}
```

**Resend Verification:**
```php
public function resend(Request $request)
{
    $user = Auth::user();
    
    // Generate new code
    $user->generateVerificationCode();
    
    // Send email
    Mail::to($user->email)->send(new VerificationCodeMail($user->verification_code));
    
    return back()->with('success', 'Verification code sent!');
}
```

---

### 2. Google OAuth Authentication

#### Setup Requirements

**Environment Variables:**
```env
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

**Google Cloud Console Setup:**
1. Create project in Google Cloud Console
2. Enable Google+ API
3. Create OAuth 2.0 credentials
4. Add authorized redirect URI
5. Download credentials

#### OAuth Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Click     │ ──► │  Redirect   │ ──► │   Google    │
│  "Login     │     │  to Google  │     │   Login     │
│  with       │     │             │     │   Page      │
│  Google"    │     │             │     │             │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                               │
                                               ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Redirect   │ ◄── │   Create    │ ◄── │   Handle    │
│  to Home    │     │   or Login  │     │  Callback   │
│             │     │   User      │     │             │
└─────────────┘     └─────────────┘     └─────────────┘
```

**Controller:** `SocialAuthController`

```php
// Redirect to Google
public function redirectToGoogle()
{
    return Socialite::driver('google')->redirect();
}

// Handle callback
public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->user();
        
        // Find existing user by email
        $user = User::where('email', $googleUser->getEmail())->first();
        
        if ($user) {
            // Login existing user
            Auth::login($user, true);
            
            // Update online status
            $user->update([
                'last_active' => now(),
                'is_online' => true,
            ]);
        } else {
            // Check if email domain is allowed
            if ($this->isDisposableEmail($googleUser->getEmail())) {
                return redirect()->route('register')
                    ->withErrors(['email' => 'Disposable email addresses are not allowed']);
            }
            
            // Create new user
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(), // Google-verified emails are trusted
                'password' => null, // No password for OAuth users
                'username' => $this->generateUniqueUsername($googleUser->getName()),
            ]);
            
            // Create profile with avatar
            $user->profile()->create([
                'avatar' => $googleUser->getAvatar(),
            ]);
            
            Auth::login($user, true);
        }
        
        return redirect()->route('home');
        
    } catch (\Exception $e) {
        return redirect()->route('login')
            ->withErrors(['email' => 'Google authentication failed. Please try again.']);
    }
}
```

**Username Generation for OAuth Users:**
```php
private function generateUniqueUsername($name)
{
    $username = Str::slug($name);
    $original = $username;
    $counter = 1;
    
    while (User::where('username', $username)->exists()) {
        $username = $original . $counter;
        $counter++;
    }
    
    return $username;
}
```

---

### 3. Password Reset

#### Request Reset Link

**Endpoint:** `POST /forgot-password`

**Flow:**
1. User enters email on forgot password page
2. System validates email exists
3. Generate reset token
4. Send email with reset link
5. Token expires after 1 hour

**Controller:** `PasswordResetLinkController`

```php
public function store(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);
    
    $user = User::where('email', $request->email)->first();
    
    if ($user) {
        // Create reset token
        $token = Str::random(60);
        
        // Store token hash
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );
        
        // Send email
        Mail::to($user->email)->send(new ResetPasswordMail($token));
    }
    
    // Always show success to prevent email enumeration
    return back()->with('success', 'Password reset link sent if email exists.');
}
```

---

#### Reset Password

**Endpoint:** `POST /reset-password`

**Validation:**
```php
[
    'token' => ['required'],
    'email' => ['required', 'email'],
    'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
]
```

**Controller:** `ResetPasswordController`

```php
public function store(Request $request)
{
    $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
    ]);
    
    $resetToken = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();
    
    if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
        return back()->withErrors([
            'email' => 'Invalid reset token.',
        ]);
    }
    
    // Check token age (1 hour)
    if (now()->diffInMinutes($resetToken->created_at) > 60) {
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        return back()->withErrors([
            'email' => 'Reset token has expired.',
        ]);
    }
    
    $user = User::where('email', $request->email)->first();
    
    if ($user) {
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        return redirect()->route('login')
            ->with('success', 'Password reset successfully. Please login.');
    }
    
    return back()->withErrors([
        'email' => 'User not found.',
    ]);
}
```

---

### 4. Change Password (Authenticated)

**Endpoint:** `POST /password/change`

**Auth:** Required

**Validation:**
```php
[
    'current_password' => ['required'],
    'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
]
```

**Controller:** `PasswordController@change`

```php
public function change(Request $request)
{
    $request->validate([
        'current_password' => ['required'],
        'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
    ]);
    
    $user = Auth::user();
    
    // Verify current password
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.',
        ]);
    }
    
    // Update password
    $user->update([
        'password' => Hash::make($request->password),
    ]);
    
    return back()->with('success', 'Password changed successfully.');
}
```

---

## Authorization

### Middleware

#### AdminMiddleware

**Location:** `app/Http/Middleware/AdminMiddleware.php`

**Purpose:** Restricts routes to admin users only.

```php
public function handle(Request $request, Closure $next)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    if (!Auth::user()->is_admin) {
        abort(403, 'Unauthorized action.');
    }
    
    return $next($request);
}
```

**Applied Routes:**
- All `/admin/*` routes
- Admin dashboard
- User management
- Content moderation

---

#### CheckEmailVerified

**Location:** `app/Http/Middleware/CheckEmailVerified.php`

**Purpose:** Ensures user has verified email before accessing app.

```php
public function handle(Request $request, Closure $next)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    $user = Auth::user();
    
    // Skip for admins
    if ($user->is_admin) {
        return $next($request);
    }
    
    // Check verification
    if (!$user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }
    
    return $next($request);
}
```

**Applied Routes:**
- All authenticated routes (except verification routes)

---

#### CheckUserSuspended

**Location:** `app/Http/Middleware/CheckUserSuspended.php`

**Purpose:** Prevents suspended users from accessing the app.

```php
public function handle(Request $request, Closure $next)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    if (Auth::user()->is_suspended) {
        Auth::logout();
        return redirect()->route('auth.suspended')
            ->with('error', 'Your account has been suspended.');
    }
    
    return $next($request);
}
```

**Applied Routes:**
- All authenticated routes

---

### Route Protection

#### Web Routes (routes/web.php)

```php
// Guest routes (only for non-authenticated users)
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login.view');
    Route::post('/login', [LoginController::class, 'store'])->name('login');
    Route::get('/register', fn() => view('auth.register'))->name('register.view');
    Route::post('/register', [RegisterController::class, 'store'])->name('register');
    // ... password reset routes
});

// Authenticated routes
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
    Route::post('/logout', fn(Request $request) => tap(Auth::logout(), fn() => $request->session()->invalidate()))
        ->name('logout');
    
    Route::get('/', [PostController::class, 'index'])->name('home');
    
    // ... all other authenticated routes
});

// Admin routes
Route::middleware(['auth', 'admin', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    // ... admin routes
});
```

---

### User Roles & Permissions

#### Admin Role

**Capabilities:**
- Access admin panel
- View all users
- Edit/delete any user
- Suspend/unsuspend accounts
- Delete any post, comment, or story
- Create new admin accounts
- View analytics and statistics

**Implementation:**
```php
// Check if admin
if (Auth::user()->is_admin) {
    // Admin actions
}

// In controller
public function deleteUser(User $user)
{
    // Only admins can delete users
    abort_unless(Auth::user()->is_admin, 403);
    
    $user->delete();
}
```

---

#### Regular User

**Capabilities:**
- Create posts, comments, stories
- Follow/unfollow users
- Like/save posts
- Send messages
- Join groups
- Edit own profile
- Delete own content

**Restrictions:**
- Cannot access admin panel
- Cannot modify other users' content
- Cannot block admins
- Username change cooldown (3 days)

---

### Account Suspension

**Admin Action:** Suspend user account

**Effects:**
- User is logged out immediately
- Cannot login again
- Redirected to suspended page
- Profile still visible but marked as suspended
- Content remains visible

**Implementation:**
```php
// In AdminController@updateUser
public function updateUser(Request $request, User $user)
{
    $validated = $request->validate([
        'is_suspended' => ['boolean'],
        // ... other fields
    ]);
    
    if (isset($validated['is_suspended'])) {
        $user->update(['is_suspended' => $validated['is_suspended']]);
        
        if ($validated['is_suspended']) {
            // Logout user if suspended
            if ($user->id === Auth::id()) {
                Auth::logout();
            }
        }
    }
    
    return redirect()->route('admin.users.show', $user)
        ->with('success', 'User updated successfully.');
}
```

---

### Content Authorization

#### Post Authorization

```php
// In PostController@update
public function update(Request $request, Post $post)
{
    // Only owner or admin can update
    abort_unless(
        Auth::user()->is_admin || Auth::user()->id === $post->user_id,
        403,
        'Unauthorized action.'
    );
    
    // Update logic...
}

// In PostController@destroy
public function destroy(Post $post)
{
    // Only owner or admin can delete
    abort_unless(
        Auth::user()->is_admin || Auth::user()->id === $post->user_id,
        403,
        'Unauthorized action.'
    );
    
    $post->delete();
}
```

---

#### Comment Authorization

```php
// In CommentController@destroy
public function destroy(Comment $comment)
{
    // Owner, post owner, or admin can delete
    $canDelete = Auth::user()->is_admin 
        || Auth::user()->id === $comment->user_id
        || Auth::user()->id === $comment->post->user_id;
    
    abort_unless($canDelete, 403);
    
    $comment->delete();
}
```

---

#### Story Authorization

```php
// In StoryController@destroy
public function destroy(User $user, Story $story)
{
    // Only owner or admin can delete
    abort_unless(
        Auth::user()->is_admin || Auth::user()->id === $story->user_id,
        403
    );
    
    $story->delete();
}
```

---

#### Group Authorization

```php
// In GroupController@update
public function update(Request $request, Group $group)
{
    // Only admins can update group
    abort_unless($group->isAdmin(Auth::user()), 403);
    
    // Update logic...
}

// In GroupController@addMembers
public function addMembers(Request $request, Group $group)
{
    // Only admins can add members
    abort_unless($group->isAdmin(Auth::user()), 403);
    
    // Add members logic...
}
```

---

## Privacy Controls

### Private Accounts

**Feature:** Users can set their account to private.

**Effects:**
- Posts only visible to followers
- Follow requests require approval (future feature)
- Stories only visible to followers

**Implementation:**
```php
// In PostController@index
public function index(Request $request)
{
    $user = Auth::user();
    
    $posts = Post::with(['user.profile', 'media', 'likes', 'comments'])
        ->whereHas('user', function ($query) use ($user) {
            $query->where('id', $user->id)  // Own posts
                  ->orWhere('is_private', false)  // Public accounts
                  ->orWhereHas('followers', function ($q) use ($user) {
                      $q->where('follower_id', $user->id);  // Followed users
                  });
        })
        ->latest()
        ->paginate(15);
    
    return inertia('Posts/Index', compact('posts'));
}
```

---

### Private Posts

**Feature:** Individual posts can be marked as private.

**Effects:**
- Only visible to the user
- Not shown in feed to others
- Not searchable

**Implementation:**
```php
// In PostController@show
public function show(Post $post)
{
    // Check privacy
    if ($post->is_private && $post->user_id !== Auth::id()) {
        abort(403, 'This post is private.');
    }
    
    return inertia('Posts/Show', compact('post'));
}
```

---

### Blocking Users

**Feature:** Users can block other users.

**Effects:**
- Blocked user cannot follow you
- Blocked user cannot see your posts in feed
- Blocked user cannot message you
- You cannot see blocked user's content

**Implementation:**
```php
// In UserController@block
public function block(User $user)
{
    $authUser = Auth::user();
    
    // Cannot block admins
    abort_if($user->is_admin, 403, 'Cannot block admin users.');
    
    // Cannot block self
    abort_if($user->id === $authUser->id, 403);
    
    // Toggle block
    $block = Block::where('blocker_id', $authUser->id)
        ->where('blocked_id', $user->id)
        ->first();
    
    if ($block) {
        $block->delete();
        return back()->with('success', 'User unblocked.');
    }
    
    Block::create([
        'blocker_id' => $authUser->id,
        'blocked_id' => $user->id,
    ]);
    
    return back()->with('success', 'User blocked.');
}
```

**Check if Blocked:**
```php
// In Post feed query
->whereHas('user', function ($query) use ($user) {
    $query->whereDoesntHave('blockedBy', function ($q) use ($user) {
        $q->where('blocker_id', $user->id);
    });
})
```

---

## Session Management

### Session Configuration

**Location:** `config/session.php`

```php
return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => 'sessions',
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'nexus_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
];
```

---

### Remember Me

**Implementation:**
```php
// In LoginController
if (Auth::attempt($credentials, $request->boolean('remember'))) {
    // 'remember' parameter sets remember token
    // Session persists for 5 years (default)
}
```

**Remember Token:**
- Stored in `remember_token` column
- Hashed and stored in cookie
- Valid for 5 years by default

---

### Session Regeneration

**Security Measure:** Regenerate session ID after login to prevent session fixation.

```php
public function store(Request $request)
{
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        // ...
    }
}
```

---

## Online Status

### Status Tracking

**Fields:**
- `last_active`: Timestamp of last activity
- `is_online`: Boolean flag

**Update Logic:**
```php
// In UserController@updateOnlineStatus
public function updateOnlineStatus()
{
    $user = Auth::user();
    
    $user->update([
        'last_active' => now(),
        'is_online' => true,
    ]);
    
    return response()->json(['success' => true]);
}
```

**Polling:**
```javascript
// Update every 30 seconds
setInterval(() => {
    fetch('/user/update-online-status', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf_token,
        },
    });
}, 30000);
```

**Set Offline:**
```php
// In UserController@setOfflineStatus
public function setOfflineStatus()
{
    $user = Auth::user();
    
    $user->update([
        'is_online' => false,
    ]);
}
```

**Check Status:**
```php
// In UserController@getOnlineStatus
public function getOnlineStatus(User $user)
{
    $isOnline = $user->is_online && 
                $user->last_active->diffMinutes(now()) <= 2;
    
    return response()->json([
        'is_online' => $isOnline,
        'last_active' => $user->last_active,
    ]);
}
```

---

## Username Management

### Username Change Cooldown

**Rule:** Regular users can only change username once every 3 days.

**Implementation:**
```php
// In User model
public function canChangeUsername(): bool
{
    if ($this->is_admin) {
        return true;
    }
    
    if (!$this->username_changed_at) {
        return true;
    }
    
    return $this->username_changed_at->diffInDays(now()) >= 3;
}

public function getUsernameChangeCooldownRemaining(): ?int
{
    if ($this->canChangeUsername()) {
        return 0;
    }
    
    return 3 - $this->username_changed_at->diffInDays(now());
}

public function updateUsername(string $newUsername): bool
{
    if (!$this->canChangeUsername()) {
        return false;
    }
    
    // Check uniqueness
    if (User::where('username', $newUsername)->where('id', '!=', $this->id)->exists()) {
        return false;
    }
    
    $this->update([
        'username' => $newUsername,
        'username_changed_at' => now(),
    ]);
    
    return true;
}
```

---

## Account Deletion

**Endpoint:** `DELETE /profile/delete-account`

**Auth:** Required

**Validation:**
```php
[
    'password' => ['required', 'current_password'],
]
```

**Implementation:**
```php
// In UserController@deleteAccount
public function deleteAccount(Request $request)
{
    $request->validate([
        'password' => ['required', 'current_password'],
    ]);
    
    $user = Auth::user();
    
    DB::transaction(function () use ($user) {
        // Delete related data
        $user->posts()->delete();
        $user->comments()->delete();
        $user->stories()->delete();
        $user->notifications()->delete();
        $user->savedPosts()->delete();
        $user->likes()->delete();
        $user->profile()->delete();
        
        // Remove from group memberships
        $user->groupMemberships()->delete();
        
        // Delete conversations (soft)
        $user->conversations()->update(['deleted_by_user' => true]);
        
        // Finally delete user
        $user->delete();
    });
    
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect()->route('login')
        ->with('success', 'Account deleted successfully.');
}
```

---

## Security Features

### Password Hashing

**Algorithm:** Bcrypt (default)

```php
// Hash password
Hash::make($password);

// Verify password
Hash::check($password, $hash);

// Check if needs rehash
Hash::needsRehash($hash);
```

---

### CSRF Protection

**Automatic for all POST, PUT, DELETE requests.**

**Meta Tag:**
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

**Axios Setup:**
```javascript
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
```

---

### XSS Prevention

**Blade Templates:**
```blade
{{-- Escaped by default --}}
{{ $userInput }}

{{-- Raw output (use carefully) --}}
{!! $trustedHtml !!}
```

**Vue Templates:**
```vue
<!-- Escaped by default -->
{{ userInput }}

<!-- Raw HTML (use carefully) -->
<div v-html="trustedHtml"></div>
```

---

### SQL Injection Prevention

**Eloquent ORM (Safe):**
```php
// Parameterized query
User::where('email', $email)->first();
```

**Raw Query (Unsafe - Don't Do):**
```php
// Vulnerable to SQL injection
DB::select("SELECT * FROM users WHERE email = '$email'");

// Safe alternative
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
```

---

### Rate Limiting

**Configuration:**
```php
// In RouteServiceProvider
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->email);
});

RateLimiter::for('password-reset', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});
```

**Usage:**
```php
public function store(Request $request)
{
    if (RateLimiter::tooManyAttempts('login:'.$request->email, 5)) {
        return back()->withErrors([
            'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn('login:'.$request->email).' seconds.',
        ]);
    }
    
    // ...
}
```

---

### Disposable Email Blocking

**Implementation:**
```php
private function isDisposableEmail(string $email): bool
{
    $domain = Str::after($email, '@');
    
    $disposableDomains = [
        'tempmail.com',
        '10minutemail.com',
        'guerrillamail.com',
        // ... more domains
    ];
    
    return in_array($domain, $disposableDomains);
}
```

---

## Testing Authentication

### Feature Tests

```php
// Test registration
public function test_user_can_register()
{
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);
    
    $response->assertRedirect(route('verification.notice'));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
}

// Test login
public function test_user_can_login()
{
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();
}

// Test email verification
public function test_email_verification()
{
    $user = User::factory()->create([
        'verification_code' => '123456',
        'verification_code_expires_at' => now()->addMinutes(15),
    ]);
    
    $response = $this->actingAs($user)
        ->post('/email/verify-code', [
            'code' => '123456',
        ]);
    
    $response->assertRedirect(route('home'));
    $this->assertNotNull($user->fresh()->email_verified_at);
}

// Test admin authorization
public function test_admin_can_access_admin_panel()
{
    $admin = User::factory()->create(['is_admin' => true]);
    
    $response = $this->actingAs($admin)
        ->get('/admin');
    
    $response->assertOk();
}

public function test_regular_user_cannot_access_admin_panel()
{
    $user = User::factory()->create(['is_admin' => false]);
    
    $response = $this->actingAs($user)
        ->get('/admin');
    
    $response->assertForbidden();
}
```

---

**Last Updated**: March 2026
