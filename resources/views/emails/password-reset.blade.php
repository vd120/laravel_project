{{ config('app.name') }} - Password Reset Request

========================================

Hello,

You requested a password reset for your {{ config('app.name') }} account.

RESET LINK:
{{ $resetUrl }}

This link expires in 60 minutes.

How to reset your password:
1. Click the link above
2. Enter your new password
3. Confirm and save

========================================

If you didn't request this, you can safely ignore this email.

&copy; {{ date('Y') }} {{ config('app.name') }}
