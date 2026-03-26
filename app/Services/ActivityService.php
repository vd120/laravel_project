<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ActivityService
{
    /**
     * Log user activity - ONE method does EVERYTHING
     */
    public function logActivity(string $action, ?int $userId = null, ?string $sessionId = null): ActivityLog
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required');
        }

        $request = request();
        $ipAddress = $this->getIpAddress($request);
        $sessionId = $sessionId ?? $request->session()->getId();
        
        // Get ALL data from API (one call gets everything)
        $locationData = $this->getIpLocation($ipAddress);

        return ActivityLog::create([
            'user_id' => $userId,
            'session_id' => $sessionId, // Save session ID
            'action' => $action,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent() ?? '',
            'device_type' => $this->getDeviceType($request),
            'browser' => $this->getBrowser($request),
            'os' => $this->getOS($request),
            'country' => $locationData['country'] ?? null,
            'city' => $locationData['city'] ?? null,
            'region' => $locationData['region'] ?? null,
            'isp' => $locationData['isp'] ?? null,
            'timezone' => $locationData['timezone'] ?? null,
            'latitude' => $locationData['latitude'] ?? null,
            'longitude' => $locationData['longitude'] ?? null,
            'logged_at' => now(),
        ]);
    }

    /**
     * Get IP address from request (works with Cloudflare, proxies, and load balancers)
     */
    private function getIpAddress(Request $request): string
    {
        // Priority 1: Cloudflare specific headers (most reliable for Cloudflare tunnel)
        if ($request->header('CF-Connecting-IP')) {
            return $request->header('CF-Connecting-IP');
        }
        
        // Priority 2: Cloudflare alternative header
        if ($request->header('CF-IPCountry')) {
            // If Cloudflare is adding country, also check for their IP header
            $cfConnectingIp = $request->header('X-Forwarded-For');
            if ($cfConnectingIp) {
                $ips = explode(',', $cfConnectingIp);
                // Cloudflare appends the real IP at the end of X-Forwarded-For
                return trim(end($ips));
            }
        }

        // Priority 3: Standard X-Forwarded-For header (proxy/load balancer)
        if ($request->header('X-Forwarded-For')) {
            $ips = explode(',', $request->header('X-Forwarded-For'));
            // Get the first IP (original client IP)
            return trim($ips[0]);
        }

        // Priority 4: X-Real-IP header (nginx)
        if ($request->header('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }
        
        // Priority 5: True-Client-IP (some CDNs and load balancers)
        if ($request->header('True-Client-IP')) {
            return $request->header('True-Client-IP');
        }

        // Priority 6: Fall back to Laravel's IP detection
        return $request->ip() ?? 'unknown';
    }

    /**
     * Get location data from IP address using HTTPS IP geolocation APIs
     */
    private function getIpLocation(string $ipAddress): array
    {
        // Skip localhost and private IPs
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost', 'unknown']) ||
            $this->isPrivateIp($ipAddress)) {
            return [
                'country' => 'Local Network',
                'city' => 'Localhost',
                'region' => null,
                'isp' => null,
                'timezone' => null,
                'latitude' => null,
                'longitude' => null,
            ];
        }

        // Try HTTPS APIs only (HTTP APIs are blocked)
        $apis = [
            'ipapi.co',      // HTTPS - works
            'ipwhois.app',   // HTTPS - fallback
        ];

        foreach ($apis as $api) {
            try {
                $locationData = $this->fetchFromApi($api, $ipAddress);

                if ($locationData && isset($locationData['country'])) {
                    return $locationData;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch from ' . $api . ' for IP ' . $ipAddress . ': ' . $e->getMessage());
                // Continue to next API
            }
        }

        // Fallback: Use Cloudflare country header
        $cfCountry = request()->header('CF-IPCountry');
        if ($cfCountry) {
            return [
                'country' => $this->getCountryName($cfCountry),
                'countryCode' => $cfCountry,
                'city' => null,
                'region' => null,
                'isp' => null,
                'timezone' => null,
                'latitude' => null,
                'longitude' => null,
            ];
        }

        // All failed
        return [
            'country' => null,
            'city' => null,
            'region' => null,
            'isp' => null,
            'timezone' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Map country code to country name
     */
    private function getCountryName(string $code): string
    {
        $countries = [
            'EG' => 'Egypt',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'DE' => 'Germany',
            'FR' => 'France',
            'IN' => 'India',
            'CN' => 'China',
            'BR' => 'Brazil',
            'RU' => 'Russia',
            'JP' => 'Japan',
            'AU' => 'Australia',
            'CA' => 'Canada',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'PL' => 'Poland',
            'TR' => 'Turkey',
        ];
        
        return $countries[$code] ?? $code;
    }

    /**
     * Fetch location data from specific API
     */
    private function fetchFromApi(string $api, string $ipAddress): ?array
    {
        switch ($api) {
            case 'ip-api.com':
                return $this->fetchFromIpApi($ipAddress);

            case 'ipapi.co':
                return $this->fetchFromIpApiCo($ipAddress);

            case 'ipwhois.app':
                return $this->fetchFromIpWhois($ipAddress);

            default:
                return null;
        }
    }

    /**
     * Fetch from ip-api.com (primary - free, no API key required)
     */
    private function fetchFromIpApi(string $ipAddress): ?array
    {
        $response = Http::timeout(8)->get("http://ip-api.com/json/{$ipAddress}?fields=status,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,asname,query,mobile,proxy,hosting");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? null,
                    'countryCode' => $data['countryCode'] ?? null,
                    'region' => $data['regionName'] ?? $data['region'] ?? null,
                    'regionCode' => $data['region'] ?? null,
                    'city' => $data['city'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'zip' => $data['zip'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Fetch from ipapi.co (primary - HTTPS, free tier available)
     */
    private function fetchFromIpApiCo(string $ipAddress): ?array
    {
        $response = Http::timeout(8)->get("https://ipapi.co/{$ipAddress}/json/");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['country_name'])) {
                return [
                    'country' => $data['country_name'] ?? null,
                    'countryCode' => $data['country_code'] ?? null,
                    'region' => $data['region'] ?? null,
                    'regionCode' => $data['region_code'] ?? null,
                    'city' => $data['city'] ?? null,
                    'isp' => $data['org'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'zip' => $data['postal'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Fetch from ipwhois.app (fallback - HTTPS, free)
     */
    private function fetchFromIpWhois(string $ipAddress): ?array
    {
        $response = Http::timeout(8)->get("https://ipwhois.app/json/{$ipAddress}");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['country'])) {
                return [
                    'country' => $data['country'] ?? null,
                    'countryCode' => $data['country_code'] ?? null,
                    'region' => $data['region'] ?? null,
                    'regionCode' => null,
                    'city' => $data['city'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'zip' => $data['zip'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Check if IP is a private/local address
     */
    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * Detect device type from user agent
     */
    private function getDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';

        // Mobile detection
        if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent)) {
            return 'mobile';
        }

        // Tablet detection
        if (preg_match('/(tablet|ipad|playbook)/i', $userAgent) || 
            (preg_match('/(android)/i', $userAgent) && !preg_match('/(mobile)/i', $userAgent))) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Detect browser from user agent
     */
    private function getBrowser(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';

        if (preg_match('/Edg\/(\d+)/i', $userAgent, $matches)) {
            return 'Edge ' . ($matches[1] ?? '');
        }

        if (preg_match('/Chrome\/(\d+)/i', $userAgent, $matches)) {
            return 'Chrome ' . ($matches[1] ?? '');
        }

        if (preg_match('/Firefox\/(\d+)/i', $userAgent, $matches)) {
            return 'Firefox ' . ($matches[1] ?? '');
        }

        if (preg_match('/Safari\/(\d+)/i', $userAgent, $matches) && !preg_match('/Chrome/i', $userAgent)) {
            return 'Safari ' . ($matches[1] ?? '');
        }

        if (preg_match('/MSIE (\d+)/i', $userAgent, $matches) || preg_match('/Trident\/.*rv:(\d+)/i', $userAgent, $matches)) {
            return 'IE ' . ($matches[1] ?? '');
        }

        if (preg_match('/Opera|OPR\//i', $userAgent)) {
            return 'Opera';
        }

        return 'Other';
    }

    /**
     * Detect operating system from user agent
     */
    private function getOS(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';

        if (preg_match('/Windows NT 10\.0/i', $userAgent)) {
            return 'Windows 10/11';
        }

        if (preg_match('/Windows NT 6\.3/i', $userAgent)) {
            return 'Windows 8.1';
        }

        if (preg_match('/Windows NT 6\.2/i', $userAgent)) {
            return 'Windows 8';
        }

        if (preg_match('/Windows NT 6\.1/i', $userAgent)) {
            return 'Windows 7';
        }

        if (preg_match('/Mac OS X (\d+[._]\d+)/i', $userAgent, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1] ?? '');
        }

        if (preg_match('/Android (\d+)/i', $userAgent, $matches)) {
            return 'Android ' . ($matches[1] ?? '');
        }

        if (preg_match('/iPhone OS (\d+)/i', $userAgent, $matches)) {
            return 'iOS ' . ($matches[1] ?? '');
        }

        if (preg_match('/iPad.*OS (\d+)/i', $userAgent, $matches)) {
            return 'iPadOS ' . ($matches[1] ?? '');
        }

        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }

        if (preg_match('/Ubuntu/i', $userAgent)) {
            return 'Ubuntu';
        }

        return 'Unknown';
    }

    /**
     * Get user's recent activity
     */
    public function getUserActivity(int $userId, int $limit = 50, int $days = 30)
    {
        return ActivityLog::forUser($userId)
            ->recent($days)
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's login history
     */
    public function getUserLoginHistory(int $userId, int $limit = 20)
    {
        return ActivityLog::forUser($userId)
            ->action('login')
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's active sessions - FAST & ACCURATE
     */
    public function getActiveSessions(int $userId)
    {
        // Get ALL recent logins (last 7 days)
        $logins = ActivityLog::where('user_id', $userId)
            ->action('login')
            ->where('logged_at', '>=', now()->subDays(7))
            ->orderBy('logged_at', 'desc')
            ->get();

        // Get all active sessions from database with their last_activity
        $activeSessions = \DB::table('sessions')
            ->where('user_id', $userId)
            ->get()
            ->keyBy('id');

        // Filter logins to only those with active sessions
        return $logins->filter(function($login) use ($activeSessions) {
            // If has session_id, check if it's in active sessions
            if ($login->session_id) {
                // Session must exist in sessions table
                if (!$activeSessions->has($login->session_id)) {
                    return false; // Session was terminated or expired
                }
                
                // Get the session data
                $session = $activeSessions->get($login->session_id);
                
                // Session is active if last_activity was within 24 hours
                $sessionAge = now()->timestamp - $session->last_activity;
                return $sessionAge < 86400; // 24 hours in seconds
            }
            // Old logins without session_id - consider active only if very recent (< 30 minutes)
            return $login->logged_at->diffInMinutes(now()) < 30;
        });
    }

    /**
     * Get detailed active sessions with metadata for display
     */
    public function getActiveSessionsWithDetails(int $userId)
    {
        $sessions = $this->getActiveSessions($userId);

        return $sessions->map(function($session) {
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'device_type' => $session->device_type,
                'browser' => $session->browser,
                'os' => $session->os,
                'country' => $session->country,
                'city' => $session->city,
                'last_active' => $session->logged_at->diffForHumans(),
                'login_time' => $session->logged_at->format('M d, Y h:i A'),
                'is_current' => $this->isCurrentSession($session),
            ];
        });
    }

    /**
     * Check if this session is the current one
     */
    private function isCurrentSession($session)
    {
        $request = request();
        $currentSessionId = $request->session()->getId();
        
        // Primary check: Compare session IDs (most accurate)
        if ($session->session_id && $session->session_id === $currentSessionId) {
            return true;
        }
        
        // Fallback: Check if IP matches and session is recent (within 2 hours)
        $currentIp = $this->getIpAddress($request);
        $isSameIp = $session->ip_address === $currentIp;
        $isRecent = $session->logged_at->diffInMinutes(now()) < 120;
        
        // Also check user agent matches
        $currentUA = $request->userAgent() ?? '';
        $isSameUA = $session->user_agent === $currentUA;
        
        return $isSameIp && $isRecent && $isSameUA;
    }

    /**
     * Log login activity
     */
    public function logLogin(int $userId): ActivityLog
    {
        return $this->logActivity('login', $userId);
    }

    /**
     * Log logout activity
     */
    public function logLogout(int $userId): ActivityLog
    {
        return $this->logActivity('logout', $userId);
    }

    /**
     * Log password change
     */
    public function logPasswordChange(int $userId): ActivityLog
    {
        return $this->logActivity('password_change', $userId);
    }

    /**
     * Log profile update
     */
    public function logProfileUpdate(int $userId): ActivityLog
    {
        return $this->logActivity('profile_update', $userId);
    }

    /**
     * Log username change
     */
    public function logUsernameChange(int $userId): ActivityLog
    {
        return $this->logActivity('username_change', $userId);
    }

    /**
     * Log email verification
     */
    public function logEmailVerification(int $userId): ActivityLog
    {
        return $this->logActivity('email_verification', $userId);
    }

    /**
     * Log failed login attempt (for non-existent users or wrong credentials)
     */
    public function logFailedLogin(string $email): ActivityLog
    {
        // Create activity log without user_id for failed attempts
        $request = request();
        $ipAddress = $this->getIpAddress($request);
        $locationData = $this->getIpLocation($ipAddress);
        
        // Capture session ID if available
        $sessionId = null;
        try {
            $sessionId = $request->session()->getId();
        } catch (\Exception $e) {
            // Session not available in some contexts
        }

        return ActivityLog::create([
            'user_id' => null,
            'session_id' => $sessionId,
            'action' => 'failed_login',
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($request),
            'browser' => $this->getBrowser($request),
            'os' => $this->getOS($request),
            'country' => $locationData['country'] ?? null,
            'city' => $locationData['city'] ?? null,
            'region' => $locationData['region'] ?? null,
            'isp' => $locationData['isp'] ?? null,
            'timezone' => $locationData['timezone'] ?? null,
            'latitude' => $locationData['latitude'] ?? null,
            'longitude' => $locationData['longitude'] ?? null,
            'logged_at' => now(),
            // Store attempted email in a separate field if needed
            // You can add 'attempted_email' column to the table for this
        ]);
    }
}
