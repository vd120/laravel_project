<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

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
        
        // Set locale from cookie EARLY (before middleware runs - for error pages!)
        $this->setLocaleFromCookie();
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
