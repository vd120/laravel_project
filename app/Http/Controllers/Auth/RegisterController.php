<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // Define reserved usernames that cannot be used
        $reservedUsernames = [
            // Admin and system related
            'admin', 'administrator', 'root', 'system', 'sysadmin',
            'moderator', 'mod', 'staff', 'support', 'help',
            'bot', 'robot', 'api', 'service',

            // Laravel/social platform related
            'laravel', 'social', 'twitter', 'x', 'meta', 'facebook',
            'instagram', 'linkedin', 'youtube', 'tiktok',

            // Common variations
            'admin1', 'admin123', 'administrator1', 'root1',
            'mod1', 'moderator1', 'staff1', 'support1',

            // Application specific
            'app', 'application', 'platform', 'site', 'website',
            'company', 'official', 'team', 'dev', 'developer',

            // Common admin variations
            'superuser', 'superadmin', 'master', 'owner',
            'ceo', 'founder', 'manager', 'director'
        ];

        // Define disposable email domains to block
        $disposableEmailDomains = [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com', 'temp-mail.org',
            'throwaway.email', 'yopmail.com', 'maildrop.cc', 'tempail.com',
            'fakeinbox.com', 'mailcatch.com', 'tempinbox.com', 'dispostable.com',
            '0-mail.com', '20minutemail.com', '33mail.com', 'anonbox.net'
        ];

        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:users,name',
                'regex:/^[a-zA-Z0-9_-]+$/',
                function ($attribute, $value, $fail) use ($reservedUsernames) {
                    if (in_array(strtolower($value), $reservedUsernames)) {
                        $fail('This username is reserved and cannot be used.');
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($disposableEmailDomains) {
                    $domain = strtolower(substr(strrchr($value, "@"), 1));
                    if (in_array($domain, $disposableEmailDomains)) {
                        $fail('Disposable email addresses are not allowed. Please use a valid email address.');
                    }
                },
                function ($attribute, $value, $fail) {
                    // Check for existing unverified users with this email and delete them
                    $existingUnverifiedUser = User::where('email', $value)
                        ->whereNull('email_verified_at')
                        ->first();

                    if ($existingUnverifiedUser) {
                        // Delete the unverified user immediately
                        $existingUnverifiedUser->delete();
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // Password strength validation (same as JavaScript checker)
                    $strength = 0;

                    if (strlen($value) >= 8) {
                        $strength += 1;
                    }

                    if (preg_match('/[a-z]/', $value)) {
                        $strength += 1;
                    }

                    if (preg_match('/[A-Z]/', $value)) {
                        $strength += 1;
                    }

                    if (preg_match('/\d/', $value)) {
                        $strength += 1;
                    }

                    if (preg_match('/[^A-Za-z0-9]/', $value)) {
                        $strength += 1;
                    }

                    // Require at least "Medium" strength (3 criteria met)
                    if ($strength < 3) {
                        $fail('Password is too weak. Please use a stronger password with uppercase, lowercase, numbers, and/or special characters.');
                    }
                },
            ],
        ], [
            'username.min' => 'Username must be at least 3 characters long.',
            'username.regex' => 'Username can only contain letters, numbers, underscores, and hyphens.',
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => null, // Explicitly set to null for verification
        ]);

        // Create a basic profile for the user
        $user->profile()->create([]);

        // Generate and send verification code
        $verificationCode = $user->generateVerificationCode();

        // Send simple verification code via email
        \Illuminate\Support\Facades\Mail::raw(
            "Welcome to " . config('app.name') . "!\n\n" .
            "Your verification code is: {$verificationCode}\n\n" .
            "Please enter this code to verify your account.",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject(config('app.name') . ' - Verification Code');
            }
        );

        // Store user ID in session for verification process
        session(['pending_verification_user_id' => $user->id]);

        return redirect()->route('verification.notice')->with('message', 'Registration successful! Please check your email for a 6-digit verification code.');
    }
}
