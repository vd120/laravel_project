<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ActivityController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display user's activity logs
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get filter parameters
        $action = $request->get('action', 'all');
        $days = (int) $request->get('days', 30);

        // Build query
        $query = ActivityLog::forUser($user->id)
            ->recent($days)
            ->orderBy('logged_at', 'desc');

        // Filter by action if specified
        if ($action !== 'all') {
            $query->action($action);
        }

        $activities = $query->paginate(20);

        // Get statistics
        $totalLogins = ActivityLog::forUser($user->id)->action('login')->count();
        $failedLogins = ActivityLog::where('action', 'failed_login')
            ->where('ip_address', request()->ip())
            ->recent($days)
            ->count();
        $activeSessions = $this->activityService->getActiveSessions($user->id)->count();
        $activeSessionsList = $this->activityService->getActiveSessionsWithDetails($user->id);

        // Get available actions for filter
        $actions = [
            'all' => __('activity.all_activities'),
            'login' => __('activity.login'),
            'failed_login' => __('activity.failed_login'),
            'logout' => __('activity.logout'),
            'password_change' => __('activity.password_change'),
            'profile_update' => __('activity.profile_update'),
        ];

        return view('activity.index', compact('activities', 'actions', 'action', 'days', 'totalLogins', 'failedLogins', 'activeSessions', 'activeSessionsList'));
    }

    /**
     * Export activity logs to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $days = (int) $request->get('days', 30);
        $action = $request->get('action', 'all');

        // Build query
        $query = ActivityLog::forUser($user->id)
            ->recent($days)
            ->orderBy('logged_at', 'desc');

        // Filter by action if specified
        if ($action !== 'all') {
            $query->action($action);
        }

        $activities = $query->get();

        // CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-logs-' . now()->format('Y-m-d') . '.csv"',
        ];

        // Create callback for streaming CSV
        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 support in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'ID',
                'Date/Time',
                'Action',
                'IP Address',
                'Device Type',
                'Browser',
                'OS',
                'Country',
                'City',
                'ISP',
                'Coordinates',
            ]);

            // Data rows
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->logged_at->format('Y-m-d H:i:s'),
                    $activity->action_name,
                    $activity->ip_address,
                    $activity->device_type,
                    $activity->browser,
                    $activity->os,
                    $activity->country ?? 'N/A',
                    $activity->city ?? 'N/A',
                    $activity->isp ?? 'N/A',
                    $activity->latitude && $activity->longitude ? "{$activity->latitude},{$activity->longitude}" : 'N/A',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Terminate all other sessions (logout from all devices)
     */
    public function terminateAllSessions(Request $request)
    {
        $user = auth()->user();
        $currentSessionId = $request->session()->getId();

        // Get all sessions for this user except current
        $sessionsToDelete = \DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->get();

        $deletedCount = 0;
        foreach ($sessionsToDelete as $session) {
            $deleted = \DB::table('sessions')->where('id', $session->id)->delete();
            if ($deleted) {
                $deletedCount++;
            }
        }

        // Log the action with proper request data (captured BEFORE logActivity)
        $this->activityService->logActivity('all_sessions_terminated', $user->id);

        return redirect()->back()->with('success', __('activity.all_sessions_terminated'));
    }

    /**
     * Terminate a specific session (logout from specific device)
     * Uses session_id for EXACT matching - 100% accurate!
     */
    public function terminateSession($id)
    {
        $user = auth()->user();
        $currentSessionId = request()->session()->getId();

        $activity = ActivityLog::where('id', $id)
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->first();

        if (!$activity) {
            return redirect()->back()->with('error', __('activity.session_not_found'));
        }

        // Check if session_id exists
        if (!$activity->session_id) {
            return redirect()->back()->with('error', __('activity.session_no_longer_active'));
        }

        // SAFETY: Don't allow terminating current session
        if ($activity->session_id === $currentSessionId) {
            return redirect()->back()->with('error', __('activity.cannot_terminate_current_session'));
        }

        // SAFETY: Don't allow terminating if this is a recent login (within 1 minute)
        if ($activity->logged_at->diffInMinutes(now()) < 1) {
            return redirect()->back()->with('error', __('activity.cannot_terminate_recent_session'));
        }

        // Check if session still exists
        $sessionExists = \DB::table('sessions')->where('id', $activity->session_id)->exists();
        if (!$sessionExists) {
            // Session already terminated/deleted
            return redirect()->back()->with('error', __('activity.session_no_longer_active'));
        }

        // TERMINATE BY SESSION ID - 100% accurate!
        $deleted = \DB::table('sessions')->where('id', $activity->session_id)->delete();
        
        if ($deleted > 0) {
            // Log the termination
            $this->activityService->logActivity('session_terminate_attempt', $user->id);
            return redirect()->back()->with('success', __('activity.session_terminated'));
        }

        // Delete failed
        return redirect()->back()->with('error', __('activity.session_not_found'));
    }

    /**
     * Clear all activity logs
     */
    public function clearOld(Request $request)
    {
        $user = auth()->user();
        $ipAddress = request()->ip();
        $sessionId = $request->session()->getId();

        // Delete user's activity logs
        $deleted = ActivityLog::where('user_id', $user->id)->delete();

        // Also delete failed login attempts from this IP or session
        $deletedFailed = ActivityLog::where('action', 'failed_login')
            ->where(function($q) use ($ipAddress, $sessionId) {
                $q->where('ip_address', $ipAddress)
                  ->orWhere('session_id', $sessionId);
            })
            ->delete();

        $totalDeleted = $deleted + $deletedFailed;

        return redirect()->back()->with('success', __('activity.logs_cleared', ['count' => $totalDeleted]));
    }

    /**
     * Delete a specific activity log
     */
    public function deleteLog($id)
    {
        $user = auth()->user();
        $ipAddress = request()->ip();
        $sessionId = request()->session()->getId();

        // Try to find activity log by user_id
        $activity = ActivityLog::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        // If not found, check if it's a failed login from this IP or session
        if (!$activity) {
            $activity = ActivityLog::where('id', $id)
                ->where('action', 'failed_login')
                ->where(function($q) use ($ipAddress, $sessionId) {
                    $q->where('ip_address', $ipAddress)
                      ->orWhere('session_id', $sessionId);
                })
                ->first();
        }

        if (!$activity) {
            return redirect()->back()->with('error', __('activity.log_not_found'));
        }

        $activity->delete();

        return redirect()->back()->with('success', __('activity.log_deleted'));
    }
}
