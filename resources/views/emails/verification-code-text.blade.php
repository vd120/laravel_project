{{ config('app.name', 'Laravel') }} - Email Verification Code

Welcome, {{ $user->name }}! 🎉

Thank you for joining {{ config('app.name') }}! To complete your registration and secure your account, please use the verification code below.

YOUR VERIFICATION CODE: {{ $verificationCode }}

IMPORTANT: This code expires in 10 minutes. Unverified accounts will be automatically deleted after 24 hours.

HOW TO VERIFY YOUR ACCOUNT:
1. Return to the verification page in your browser
2. Enter the 6-digit code shown above
3. Click "Verify Account" to complete registration
4. Start exploring {{ config('app.name') }}!

Didn't request this? You can safely ignore this email.

Need help? Contact our support team.

---
{{ config('app.name') }}, Secure & Modern Platform
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.

You're receiving this email because you registered for {{ config('app.name') }}.
