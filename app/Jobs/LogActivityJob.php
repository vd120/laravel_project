<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $action;
    public string $sessionId;

    public $tries = 3;
    public $backoff = 10;

    /**
     * Create new job
     */
    public function __construct(User $user, string $action = 'login')
    {
        $this->userId = $user->id;
        $this->action = $action;
        try {
            $this->sessionId = request()->session()->getId();
        } catch (\Exception $e) {
            $this->sessionId = null;
        }
    }

    /**
     * Execute job
     */
    public function handle(ActivityService $activityService): void
    {
        if (empty($this->sessionId)) {
            \Log::warning('LogActivityJob: sessionId is empty, skipping for user ' . $this->userId);
            return;
        }

        $activityService->logActivity($this->action, $this->userId, $this->sessionId);
        \Log::debug('LogActivityJob: Logged activity for user ' . $this->userId);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('LogActivityJob failed: ' . $exception->getMessage());
    }
}
