<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class LogUserLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (!$event->user) {
            return;
        }

        // Update the session with user_id
        $sessionId = request()->session()->getId();
        
        DB::table('sessions')
            ->where('id', $sessionId)
            ->update(['user_id' => $event->user->id]);
    }
}
