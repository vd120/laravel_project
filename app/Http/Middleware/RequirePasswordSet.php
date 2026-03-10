<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordSet
{
    /**
     * Handle an incoming request.
     *
     * Redirect users with null password (Google OAuth) to set password page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user is logged in but has no password (Google OAuth), redirect to set password
        if ($user && $user->password === null) {
            // Allow access to set-password routes and logout
            if ($request->routeIs('password.set-password*') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('password.set-password');
        }

        return $next($request);
    }
}
