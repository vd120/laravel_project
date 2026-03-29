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

- **Mailable Classes**: 2
- **Email Templates**: 4
- **Email Types**: 3
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

