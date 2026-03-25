<?php

namespace App\Listeners;

use App\Jobs\LogActivityJob;
use App\Jobs\SendLoginEmailJob;
use Illuminate\Auth\Events\Login;

class LogUserLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if ($event->user) {
            // Dispatch jobs to queue (non-blocking)
            LogActivityJob::dispatch($event->user, 'login');
            SendLoginEmailJob::dispatch($event->user);
        }
    }
}
