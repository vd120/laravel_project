# Nexus - Complete Email System Documentation

Comprehensive documentation of all email types, templates, sending logic, and configuration.

---

## Table of Contents

1. [Email System Overview](#email-system-overview)
2. [Email Configuration](#email-configuration)
3. [Email Types](#email-types)
4. [Mailable Classes](#mailable-classes)
5. [Email Templates](#email-templates)
6. [Email Sending Logic](#email-sending-logic)
7. [Email Jobs & Queues](#email-jobs--queues)
8. [Email Localization](#email-localization)
9. [Email Testing](#email-testing)
10. [Email Best Practices](#email-best-practices)

---

## 1. Email System Overview

Nexus uses Laravel's Mail system with support for:
-  HTML emails with responsive design
-  Plain text fallback emails
-  Queued email sending
-  Multi-language support (EN/AR)
-  Email localization
-  Security alerts with location data

### Email Statistics

- **Mailable Classes**: 3
- **Email Templates**: 5
- **Email Types**: 4
- **Languages Supported**: 2 (EN, AR)

---

## 2. Email Configuration

### Environment Variables

```env
# Mail Configuration
MAIL_MAILER=log                    # Driver: log, smtp, mailgun, etc.
MAIL_HOST=127.0.0.1                # SMTP host
MAIL_PORT=2525                     # SMTP port
MAIL_USERNAME=null                 # SMTP username
MAIL_PASSWORD=null                 # SMTP password
MAIL_ENCRYPTION=null               # TLS/SSL
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Production SMTP Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io         # Or your SMTP provider
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Mail Configuration File

**File:** `config/mail.php`

```php
return [
    'default' => env('MAIL_MAILER', 'log'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Nexus'),
    ],
];
```

---

## 3. Email Types

### 3.1 Verification Code Email

**Purpose:** Email verification during registration  
**Trigger:** User registration  
**Mailable:** `VerificationCodeMail`  
**Template:** `emails.verification-code` (HTML), `emails.verification-code-text` (Plain)

**Content:**
- 6-digit verification code
- 10-minute expiry notice
- Verification instructions

**Sent When:**
- User registers with email/password
- User requests verification code resend

**Code Location:**
```php
// app/Http/Controllers/Auth/RegisterController.php
$verificationCode = $user->generateVerificationCode();

\Mail::raw(
    "Welcome to " . config('app.name') . "!\n\n" .
    "Your verification code is: {$verificationCode}\n\n" .
    "Please enter this code to verify your account.",
    function ($message) use ($user) {
        $message->to($user->email)
                ->subject(config('app.name') . ' - Verification Code');
    }
);
```

---

### 3.2 Welcome Email

**Purpose:** Welcome new users  
**Trigger:** After registration  
**Mailable:** `WelcomeMail` (Queued)  
**Template:** `emails.welcome`

**Content:**
- Welcome message
- Verify email button
- Feature highlights (Stories, Chat, AI, Community)
- Security note
- Social links

**Features:**
-  Queued for performance (`implements ShouldQueue`)
-  Generates verification URL
-  Responsive design
-  Multi-language support (EN/AR)
-  Gradient styling

**Code Location:**
```php
// app/Http/Controllers/Auth/RegisterController.php
\Mail::to($user->email)->queue(new \App\Mail\WelcomeMail($user));
```

---

### 3.3 Login Security Alert

**Purpose:** Notify user of new login  
**Trigger:** New login detected  
**Mailable:** `LoginSecurityAlert`  
**Template:** `emails.login-security-alert`

**Content:**
- Login notification
- IP address
- Location (country, city, coordinates)
- Device information (browser, OS)
- Login timestamp
- Timezone
- Map link to location
- Security recommendations (if suspicious)

**Features:**
-  Suspicious login detection
-  Google Maps link to location
-  Security tips for suspicious logins
-  Activity log link
-  Responsive design

**Code Location:**
```php
// app/Jobs/SendLoginEmailJob.php
if ($activity->is_suspicious) {
    Mail::to($user->email)->send(new LoginSecurityAlert($activity));
}
```

---

### 3.4 Password Reset Email

**Purpose:** Password reset request  
**Trigger:** User requests password reset  
**Mailable:** Laravel default  
**Template:** `emails.password-reset`

**Content:**
- Password reset link
- 60-minute expiry notice
- Reset instructions

**Code Location:**
```php
// app/Models/User.php
public function sendPasswordResetNotification($token)
{
    $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $this->email,
    ], false));

    \Mail::raw(
        "Hello,\n\n" .
        "You requested a password reset for your " . config('app.name') . " account.\n\n" .
        "Click the link below to reset your password:\n" .
        $resetUrl . "\n\n" .
        "This link expires in 60 minutes.\n\n" .
        "If you didn't request this, you can safely ignore this email.",
        function ($message) {
            $message->to($this->email)
                    ->subject(config('app.name') . ' - Password Reset Request');
        }
    );
}
```

---

## 4. Mailable Classes

### 4.1 VerificationCodeMail

**File:** `app/Mail/VerificationCodeMail.php`

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationCode;

    public function __construct($user, $verificationCode)
    {
        $this->user = $user;
        $this->verificationCode = $verificationCode;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Email Verification Code - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.verification-code',
            text: 'emails.verification-code-text',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

**Properties:**
- `$user` (User): User model
- `$verificationCode` (string): 6-digit code

---

### 4.2 WelcomeMail

**File:** `app/Mail/WelcomeMail.php`

```php
<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = $user->verificationUrl();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.welcome_subject', ['app_name' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

**Properties:**
- `$user` (User): User model
- `$verificationUrl` (string): Verification URL

**Features:**
-  Implements `ShouldQueue` for async sending
-  Auto-generates verification URL

---

### 4.3 LoginSecurityAlert

**File:** `app/Mail/LoginSecurityAlert.php`

```php
<?php

namespace App\Mail;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginSecurityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public ActivityLog $activity;
    public string $userName;
    public string $userEmail;

    public function __construct(ActivityLog $activity)
    {
        $this->activity = $activity;
        $this->userName = $activity->user->name;
        $this->userEmail = $activity->user->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.login_notification_subject', ['app_name' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-security-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

**Properties:**
- `$activity` (ActivityLog): Activity log model
- `$userName` (string): User's name
- `$userEmail` (string): User's email

**Features:**
-  Includes suspicious login detection (red/green theme)
-  Shows location data with Google Maps link
-  Device and browser information
-  Security tips for suspicious logins

---

## 5. Email Templates

### 5.1 Verification Code (HTML)

**File:** `resources/views/emails/verification-code.blade.php`

**Features:**
- Simple HTML design
- 6-digit code display
- Expiry notice (10 minutes)
- Verification instructions

**Content:**
```blade
Welcome to {{ config('app.name') }}!

Your verification code is: {{ $verificationCode }}

Please enter this code to verify your account.
```

---

### 5.2 Verification Code (Plain Text)

**File:** `resources/views/emails/verification-code-text.blade.php`

**Features:**
- Plain text fallback
- Same content as HTML version

---

### 5.3 Welcome Email

**File:** `resources/views/emails/welcome.blade.php`

**Features:**
-  Responsive design
-  Gradient header (purple theme)
-  Welcome box with branding
-  Verify email CTA button
-  Feature highlights (4 features)
-  Security note
-  Social links
-  Multi-language support (EN/AR RTL)
-  Mobile responsive

**Sections:**
1. **Header** - Welcome title with gradient background
2. **Welcome Box** - Branded welcome message
3. **Greeting** - Personal greeting with user name
4. **CTA Button** - Verify email button
5. **Features** - 4 feature highlights:
   - Stories
   - Chat
   - AI Assistant
   - Community
6. **Security Note** - Security reminder
7. **Footer** - Links and social icons

**Styling:**
- Gradient: `#5e60ce` to `#7400b8`
- Responsive breakpoints: 600px
- RTL support for Arabic

---

### 5.4 Login Security Alert

**File:** `resources/views/emails/login-security-alert.blade.php`

**Features:**
-  Suspicious login detection (red theme)
-  Normal login (green theme)
-  Location data with Google Maps link
-  Device information
-  Security tips for suspicious logins
-  Activity log CTA button
-  Responsive design
-  Multi-language support

**Information Displayed:**
- IP address
- ISP (if available)
- Location (city, region, country)
- GPS coordinates with map link
- Device type
- Browser
- Operating system
- Login time
- Timezone

**Conditional Features:**
- Red alert box for suspicious logins
- Security tips section for suspicious logins

---

### 5.5 Password Reset

**File:** `resources/views/emails/password-reset.blade.php`

**Features:**
- Plain text format
- Reset link
- 60-minute expiry notice
- Reset instructions

---

## 6. Email Sending Logic

### 6.1 Registration Flow

**File:** `app/Http/Controllers/Auth/RegisterController.php`

```php
public function store(Request $request)
{
    // ... validation and user creation ...
    
    $user = User::create([...]);
    
    // Generate verification code
    $verificationCode = $user->generateVerificationCode();
    
    // Queue welcome email
    try {
        \Mail::to($user->email)->queue(new \App\Mail\WelcomeMail($user));
        \Log::info('Welcome email queued for user: ' . $user->email);
    } catch (\Exception $e) {
        \Log::error('Failed to queue welcome email: ' . $e->getMessage());
    }
    
    // Send verification code immediately
    \Mail::raw(
        "Welcome to " . config('app.name') . "!\n\n" .
        "Your verification code is: {$verificationCode}\n\n" .
        "Please enter this code to verify your account.",
        function ($message) use ($user) {
            $message->to($user->email)
                    ->subject(config('app.name') . ' - Verification Code');
        }
    );
    
    return redirect()->route('verification.notice');
}
```

---

### 6.2 Login Security Alert

**File:** `app/Jobs/SendLoginEmailJob.php`

```php
public function handle(): void
{
    try {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        // Don't send if email is not verified
        if (!$user->hasVerifiedEmail()) {
            return;
        }

        // Get the most recent login activity for this user
        $activity = ActivityLog::where('user_id', $this->userId)
            ->where('action', 'login')
            ->latest()
            ->first();

        if (!$activity) {
            return;
        }

        // Check if this is a suspicious login
        $isSuspicious = $activity->is_suspicious;

        // Only send email if it's suspicious
        if (!$isSuspicious) {
            return;
        }

        // Send email
        Mail::to($user->email)->send(new LoginSecurityAlert($activity));

    } catch (\Exception $e) {
        \Log::error('Failed to send login email: ' . $e->getMessage());
        throw $e; // Will trigger retry
    }
}
```

**Triggered When:**
- Suspicious login detected (new IP, location, device)
- User has verified email
- Activity log created with `is_suspicious = true`

---

### 6.3 Password Reset

**File:** `app/Models/User.php`

```php
public function sendPasswordResetNotification($token)
{
    $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $this->email,
    ], false));

    \Mail::raw(
        "Hello,\n\n" .
        "You requested a password reset for your " . config('app.name') . " account.\n\n" .
        "Click the link below to reset your password:\n" .
        $resetUrl . "\n\n" .
        "This link expires in 60 minutes.\n\n" .
        "If you didn't request this, you can safely ignore this email.\n\n" .
        "© " . date('Y') . " " . config('app.name'),
        function ($message) {
            $message->to($this->email)
                    ->subject(config('app.name') . ' - Password Reset Request');
        }
    );
}
```

---

### 6.4 Verification Code Resend

**File:** `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`

```php
public function store(Request $request)
{
    $user = $request->user();
    
    if ($user->hasVerifiedEmail()) {
        return back();
    }
    
    $verificationCode = $user->generateVerificationCode();
    
    \Mail::to($user->email)->send(new \App\Mail\VerificationCodeMail($user, $verificationCode));
    
    return back()->with('message', __('messages.verification_code_sent'));
}
```

---

## 7. Email Jobs & Queues

### 7.1 WelcomeMail (Queued)

The `WelcomeMail` class implements `ShouldQueue` for async sending:

```php
class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    // ...
}
```

**Queue Configuration:**
```env
QUEUE_CONNECTION=database
```

**Start Queue Worker:**
```bash
php artisan queue:work --sleep=3 --tries=3
```

---

### 7.2 SendLoginEmailJob

**File:** `app/Jobs/SendLoginEmailJob.php`

```php
<?php

namespace App\Jobs;

use App\Mail\LoginSecurityAlert;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoginEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public $tries = 2;
    public $backoff = 5;

    public function __construct(User $user)
    {
        $this->userId = $user->id;
    }

    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            if (!$user) {
                return;
            }

            // Don't send if email is not verified
            if (!$user->hasVerifiedEmail()) {
                return;
            }

            // Get the most recent login activity for this user
            $activity = ActivityLog::where('user_id', $this->userId)
                ->where('action', 'login')
                ->latest()
                ->first();

            if (!$activity) {
                return;
            }

            // Check if this is a suspicious login
            $isSuspicious = $activity->is_suspicious;

            // Only send email if it's suspicious
            if (!$isSuspicious) {
                return;
            }

            // Send email
            Mail::to($user->email)->send(new LoginSecurityAlert($activity));

        } catch (\Exception $e) {
            \Log::error('Failed to send login email to user ' . $this->userId . ': ' . $e->getMessage());
            throw $e; // Will trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SendLoginEmailJob failed after ' . $this->tries . ' attempts for user ' . $this->userId);
    }
}
```

**Features:**
-  Queued job with retry logic (2 tries)
-  5-second backoff between retries
-  Only sends for suspicious logins
-  Checks email verification status
-  Fetches most recent login activity

---

## 8. Email Localization

### Supported Languages

- **English** (en): No RTL
- **Arabic** (ar): Yes RTL

### Localization in Templates

```blade
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

{{-- Translated subject --}}
subject: __('emails.welcome_subject', ['app_name' => config('app.name')])

{{-- Translated content --}}
<h1>{{ __('emails.welcome_title') }}</h1>
<p>{{ __('emails.welcome_subtitle') }}</p>
```

### RTL Support

```css
[dir="rtl"] {
    text-align: right;
}

[dir="rtl"] .feature-item {
    flex-direction: row-reverse;
}

[dir="rtl"] .info-item {
    text-align: right;
}
```

---

## 9. Email Testing

### Test Email Command

**File:** `app/Console/Commands/SendTestEmail.php`

```php
public function handle()
{
    $email = $this->argument('email');
    
    $user = User::first();
    $verificationCode = '123456';
    
    \Mail::send('emails.verification-code', 
        ['user' => $user, 'verificationCode' => $verificationCode], 
        function ($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email - ' . config('app.name'));
        }
    );
    
    $this->info('Test email sent to ' . $email);
}
```

**Usage:**
```bash
php artisan send-test-email your-email@example.com
```

---

### Development Testing (Mailtrap)

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

---

## 10. Email Best Practices

### 10.1 Security

-  Never include passwords in emails
-  Use secure tokens for reset links
-  Set expiry times on verification codes
-  Use HTTPS for all email links
-  Sanitize user input in emails

### 10.2 Performance

-  Queue heavy emails (WelcomeMail)
-  Use plain text fallbacks
-  Compress images if used
-  Minimize inline CSS

### 10.3 Deliverability

-  Use SPF records
-  Use DKIM signing
-  Use dedicated sending domain
-  Include unsubscribe link
-  Include physical address (GDPR)

### 10.4 Mobile Optimization

-  Responsive design (max-width: 600px)
-  Large touch targets (buttons)
-  Readable font sizes (14px+)
-  Single column layout

---

## Email Summary

**Email Types:**
- **Verification Code**: Mailable: VerificationCodeMail, Template: emails.verification-code, Queue: No, Trigger: Registration
- **Welcome**: Mailable: WelcomeMail, Template: emails.welcome, Queue: Yes, Trigger: Registration
- **Login Alert**: Mailable: LoginSecurityAlert, Template: emails.login-security-alert, Queue: Yes, Trigger: Suspicious Login
- **Password Reset**: Mailable: Laravel Default, Template: emails.password-reset, Queue: No, Trigger: Password Reset Request

---

## Email Troubleshooting

### Issue: Emails Not Sending

**Solution:**
1. Check mail configuration in `.env`
2. Verify SMTP credentials
3. Check queue worker is running
4. Check logs: `storage/logs/laravel.log`

### Issue: Emails Going to Spam

**Solution:**
1. Set up SPF records
2. Set up DKIM signing
3. Use dedicated sending domain
4. Include unsubscribe link
5. Avoid spam trigger words

### Issue: Queue Not Processing

**Solution:**
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Restart queue worker
php artisan queue:restart
php artisan queue:work --sleep=3 --tries=3
```

---

<div align="center">

**Nexus - Email System Documentation**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
