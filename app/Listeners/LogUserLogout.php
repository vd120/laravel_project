<?php

namespace App\Listeners;

use App\Jobs\LogActivityJob;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            // Dispatch job to queue (non-blocking)
            LogActivityJob::dispatch($event->user, 'logout');
        }
    }
}
