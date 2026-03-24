<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register rate limiters
        $this->registerRateLimiters();

        // Set locale from cookie EARLY (before middleware runs - for error pages!)
        $this->setLocaleFromCookie();
    }

    /**
     * Register rate limiters for throttle middleware
     */
    private function registerRateLimiters(): void
    {
        // Auth throttle - 5 attempts per minute
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Email verification throttle - 3 attempts per minute
        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Posts throttle - 20 posts per minute
        RateLimiter::for('posts', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?? $request->ip());
        });

        // Comments throttle - 30 comments per minute
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?? $request->ip());
        });
    }

    /**
     * Set locale from cookie (runs before middleware)
     */
    private function setLocaleFromCookie(): void
    {
        $request = Request::capture();
        $cookieLocale = $request->cookie('locale');

        $supportedLocales = ['en', 'ar'];

        if ($cookieLocale && in_array($cookieLocale, $supportedLocales)) {
            App::setLocale($cookieLocale);
            \Carbon\Carbon::setLocale($cookieLocale);
        }
    }
}
