<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Change the user's password.
     */
    public function change(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', __('messages.password_changed_success'));
    }

    /**
     * Show set password page for Google OAuth users.
     */
    public function showSetPassword()
    {
        $user = auth()->user();
        
        // Only allow users who registered via Google (no password set)
        if (!$user || $user->password !== null) {
            return redirect()->route('home');
        }

        return view('auth.set-password');
    }

    /**
     * Set password for Google OAuth users.
     */
    public function setPassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Only allow users who registered via Google
        if (!$user || $user->password !== null) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Clear any pending verification session
        session()->forget('pending_verification_user_id');

        return redirect()->route('home')->with('success', __('messages.password_set_success'));
    }
}
