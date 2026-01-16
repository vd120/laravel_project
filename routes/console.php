<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule story cleanup every hour
Artisan::command('stories:cleanup', function () {
    $this->call('app:delete-expired-stories');
})->purpose('Clean up expired stories')->hourly();

// Schedule cleanup of unverified users daily
Artisan::command('users:cleanup-unverified', function () {
    $this->call('users:delete-unverified');
})->purpose('Delete users who haven\'t verified their email within 24 hours')->daily();
