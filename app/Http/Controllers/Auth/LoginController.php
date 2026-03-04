<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user is suspended
            if ($user->is_suspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login.view')->with('suspended', true);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // Store session tracking for concurrent login detection
            $request->session()->regenerate();
            session([
                'session_id' => $request->session()->getId(),
                'last_activity' => now()->timestamp,
                'login_time' => now()->toDateTimeString()
            ]);

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Handle logout and set message for concurrent login or deleted account
     */
    public function logoutWithMessage(Request $request, $type = 'concurrent')
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        $messageKey = $type === 'deleted' ? 'account_deleted' : 'concurrent_login';
        
        return redirect()->route('login.view')->with($messageKey, true);
    }
}
