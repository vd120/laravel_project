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
    public string $ipAddress;
    public string $userAgent;
    public ?string $countryCode;
    public string $sessionId; // NEW: Store current session ID

    public $tries = 3;
    public $backoff = 10;

    /**
     * Create new job - capture session ID + essentials
     */
    public function __construct(User $user, string $action = 'login')
    {
        $this->userId = $user->id;
        $this->action = $action;
        
        // Capture session ID immediately - this must happen during HTTP request
        try {
            $this->sessionId = request()->session()->getId();
        } catch (\Exception $e) {
            $this->sessionId = null;
        }

        // Capture ONLY what can't be detected later
        $this->ipAddress = request()->header('CF-Connecting-IP')
            ?? (request()->header('X-Forwarded-For') ? explode(',', request()->header('X-Forwarded-For'))[0] : null)
            ?? request()->ip() ?? 'unknown';

        $this->userAgent = request()->userAgent() ?? '';
        $this->countryCode = request()->header('CF-IPCountry');
    }

    /**
     * Execute job - ActivityService does EVERYTHING (API + detection)
     */
    public function handle(ActivityService $activityService): void
    {
        try {
            // If sessionId is null, we're running in a non-HTTP context - skip logging
            if (empty($this->sessionId)) {
                \Log::warning('LogActivityJob: sessionId is empty, skipping activity log for user ' . $this->userId);
                return;
            }

            $mockRequest = new \Illuminate\Http\Request();
            $mockRequest->server->set('REMOTE_ADDR', $this->ipAddress);
            $mockRequest->headers->set('HTTP_USER_AGENT', $this->userAgent);
            $mockRequest->headers->set('CF-Connecting-IP', $this->ipAddress);
            $mockRequest->headers->set('CF-IPCountry', $this->countryCode);

            // Create a mock session with the captured session ID
            $mockSession = new \Illuminate\Session\Store('mock', new \Illuminate\Session\NullSessionHandler());
            $mockSession->setId($this->sessionId);
            $mockRequest->setSession($mockSession);

            app()->instance('request', $mockRequest);

            // ActivityService does ALL the work (API + detection)
            // Pass the session ID explicitly to ensure it's saved
            $activity = $activityService->logActivity($this->action, $this->userId, $this->sessionId);

            app()->forgetInstance('request');
            
            \Log::debug('LogActivityJob: Logged activity for user ' . $this->userId . ' with session ' . substr($this->sessionId, 0, 10));
        } catch (\Exception $e) {
            \Log::error('LogActivityJob failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('LogActivityJob failed: ' . $exception->getMessage());
    }
}
