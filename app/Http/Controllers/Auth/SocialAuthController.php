<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            // Try stateless first, fall back to regular method
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Exception $e) {
                // Fallback to regular OAuth flow
                $googleUser = Socialite::driver('google')->user();
            }

            // Find existing user by email
            $user = User::where('email', $googleUser->email)->first();

            // Case 1: New user - email doesn't exist in database
            if (!$user) {
                // Generate a unique username from email or name
                $username = Str::slug($googleUser->name);
                $originalUsername = $username;
                $counter = 1;
                
                while (User::where('username', $username)->exists()) {
                    $username = $originalUsername . $counter;
                    $counter++;
                }

                // Create new user WITHOUT email verification (needs verification)
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'username' => $username,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => null, // NOT verified - needs verification code
                ]);

                // Store user ID in session for pending verification
                session(['pending_verification_user_id' => $user->id]);

                // Redirect to verification page
                return redirect()->route('verification.notice')->with('message', 'Please verify your email to continue.');
            }

            // Case 2: User exists but email is NOT verified (email_verified_at is null)
            if (is_null($user->email_verified_at)) {
                // Store user ID in session for pending verification
                session(['pending_verification_user_id' => $user->id]);

                // Redirect to verification page
                return redirect()->route('verification.notice')->with('message', 'Please verify your email to continue.');
            }

            // Case 3: User exists and email IS verified - log in and redirect to home
            
            // Check if user is suspended
            if ($user->is_suspended) {
                return redirect()->route('login')->with('suspended', true);
            }
            
            Auth::login($user);

            // Regenerate session
            request()->session()->regenerate();

            return redirect()->intended('/')->with('message', 'Welcome back, ' . $user->name . '!');
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Unable to login with Google. Please try again.']);
        }
    }
}
