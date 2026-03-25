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

    /**
     * The number of times the queued job may be attempted before failing.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $action = 'login')
    {
        $this->userId = $user->id;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(ActivityService $activityService): void
    {
        try {
            $activityService->logActivity($this->action, $this->userId);
        } catch (\Exception $e) {
            \Log::error('Failed to log activity for user ' . $this->userId . ': ' . $e->getMessage());
            throw $e; // Will trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('LogActivityJob failed after ' . $this->tries . ' attempts for user ' . $this->userId);
        // Don't rethrow - just log the failure
    }
}
