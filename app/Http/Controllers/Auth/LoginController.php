<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // Regenerate session immediately to prevent session fixation
            $request->session()->regenerate();

            $user = Auth::user();

            // Log login activity AFTER session regeneration to capture correct session ID
            try {
                $this->activityService->logActivity('login', $user->id);
            } catch (\Exception $e) {
                \Log::error('Failed to log login activity: ' . $e->getMessage());
            }

            // Check if user is suspended
            if ($user->is_suspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login.view')->with('suspended', true);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('message', __('messages.please_verify_email'));
            }

            // Store session tracking for concurrent login detection
            session([
                'session_id' => $request->session()->getId(),
                'last_activity' => now()->timestamp,
                'login_time' => now()->toDateTimeString()
            ]);

            return redirect()->intended('/');
        }

        // Log failed login attempt
        $this->activityService->logFailedLogin($request->input('email'));

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
