{{ config('app.name') }} - Email Verification

========================================

Welcome, {{ $user->name }}!

Thank you for joining {{ config('app.name') }}! 

YOUR VERIFICATION CODE: {{ $verificationCode }}

This code expires in 10 minutes.

How to verify:
1. Enter the 6-digit code above
2. Click verify to complete registration
3. Start using {{ config('app.name') }}!

========================================
You're receiving this because you registered for {{ config('app.name') }}.
&copy; {{ date('Y') }} {{ config('app.name') }}
