<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('already_verified', __('messages.email_already_verified'));
        }

        // Generate and send new verification code
        $verificationCode = $request->user()->generateVerificationCode();

        // Send verification code via email using the professional template
        \Illuminate\Support\Facades\Mail::to($request->user()->email)->send(new \App\Mail\VerificationCodeMail($request->user(), $verificationCode));

        return back()->with('message', __('messages.verification_code_sent'));
    }

    /**
     * Verify the email using the provided code.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->withErrors(['code' => __('errors.user_not_found')]);
        }

        if ($user->verifyCode($request->code)) {
            // Log the user in
            Auth::login($user);

            return redirect('/')->with('message', __('messages.email_verified_success'));
        }

        return back()->withErrors(['code' => __('auth.invalid_verification_code')]);
    }
}
