<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityService;
use Illuminate\Http\Request;

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
        $activeSessions = $this->activityService->getActiveSessions($user->id)->count();

        // Get available actions for filter
        $actions = [
            'all' => __('activity.all_activities'),
            'login' => __('activity.login'),
            'logout' => __('activity.logout'),
            'password_change' => __('activity.password_change'),
            'profile_update' => __('activity.profile_update'),
        ];

        return view('activity.index', compact('activities', 'actions', 'action', 'days', 'totalLogins', 'activeSessions'));
    }

    /**
     * Terminate a specific session (logout from specific device)
     */
    public function terminateSession($id)
    {
        $user = auth()->user();
        
        $activity = ActivityLog::where('id', $id)
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->first();

        if (!$activity) {
            return redirect()->back()->with('error', __('activity.session_not_found'));
        }

        // Note: We can't actually terminate sessions without a session management system
        // This is just a placeholder for future implementation
        // For now, we'll just log the attempt
        $this->activityService->logActivity('session_terminate_attempt', $user->id);

        return redirect()->back()->with('success', __('activity.session_terminate_info'));
    }

    /**
     * Clear old activity logs
     */
    public function clearOld(Request $request)
    {
        $user = auth()->user();
        $days = (int) $request->get('days', 90);

        $deleted = ActivityLog::forUser($user->id)
            ->where('logged_at', '<', now()->subDays($days))
            ->delete();

        return redirect()->back()->with('success', __('activity.logs_cleared', ['count' => $deleted]));
    }
}
