<?php

namespace App\Listeners;

use App\Services\ActivityService;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            // Log activity SYNCHRONOUSLY to preserve session context
            try {
                $activityService = app(ActivityService::class);
                $activityService->logActivity('logout', $event->user->id);
            } catch (\Exception $e) {
                \Log::error('Failed to log logout activity: ' . $e->getMessage());
            }
        }
    }
}
