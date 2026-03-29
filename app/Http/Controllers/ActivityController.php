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
        
        // Get active sessions - improved to handle NULL user_id in sessions table
        $activeSessionsList = $this->activityService->getActiveSessionsWithDetails($user->id);
        $activeSessions = $activeSessionsList->count();

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

        // Get user's recent login IPs
        $userIps = ActivityLog::where('user_id', $user->id)
            ->action('login')
            ->where('logged_at', '>=', now()->subDays(7))
            ->pluck('ip_address')
            ->unique()
            ->toArray();

        // Delete sessions: by user_id OR by matching IPs (except current session)
        $deletedCount = \DB::table('sessions')
            ->where(function($q) use ($user, $userIps, $currentSessionId) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('ip_address', $userIps);
            })
            ->where('id', '!=', $currentSessionId)
            ->delete();

        // Log the action
        $this->activityService->logActivity('all_sessions_terminated', $user->id);

        return redirect()->back()->with('success', __('activity.all_sessions_terminated', ['count' => $deletedCount]));
    }

    /**
     * Terminate a specific session (logout from specific device)
     */
    public function terminateSession($id)
    {
        $user = auth()->user();
        $currentSessionId = request()->session()->getId();

        // Get the activity log entry
        $activity = ActivityLog::where('id', $id)
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->first();

        if (!$activity) {
            return redirect()->back()->with('error', __('activity.session_not_found'));
        }

        // SAFETY: Don't allow terminating current session
        if ($id === $currentSessionId) {
            return redirect()->back()->with('error', __('activity.cannot_terminate_current_session'));
        }

        // SAFETY: Don't allow terminating if this is a recent login (within 1 minute)
        if ($activity->logged_at->diffInMinutes(now()) < 1) {
            return redirect()->back()->with('error', __('activity.cannot_terminate_recent_session'));
        }

        // Check if session still exists
        $sessionExists = \DB::table('sessions')->where('id', $id)->exists();
        if (!$sessionExists) {
            // Session already terminated/deleted
            return redirect()->back()->with('error', __('activity.session_no_longer_active'));
        }

        // TERMINATE BY SESSION ID (the $id parameter is the session ID from sessions table)
        $deleted = \DB::table('sessions')->where('id', $id)->delete();

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

        // Also delete ALL failed login attempts (not just current IP)
        // Failed logins don't have user_id, so we delete all of them
        $deletedFailed = ActivityLog::where('action', 'failed_login')->delete();

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

        // If not found, check if it's a failed login (any failed login can be deleted)
        if (!$activity) {
            $activity = ActivityLog::where('id', $id)
                ->where('action', 'failed_login')
                ->first();
        }

        if (!$activity) {
            return redirect()->back()->with('error', __('activity.log_not_found'));
        }

        $activity->delete();

        return redirect()->back()->with('success', __('activity.log_deleted'));
    }
}
