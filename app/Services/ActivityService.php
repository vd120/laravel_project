<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ActivityService
{
    /**
     * Log user activity
     */
    public function logActivity(string $action, ?int $userId = null): ActivityLog
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required');
        }

        $request = request();
        $ipAddress = $this->getIpAddress($request);
        $locationData = $this->getIpLocation($ipAddress);

        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
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
     * Get location data from IP address using multiple IP geolocation APIs with fallback
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

        // Try multiple APIs in order of preference
        $apis = [
            'ip-api.com',
            'ipapi.co',
            'ipwhois.app',
        ];

        foreach ($apis as $api) {
            try {
                $locationData = $this->fetchFromApi($api, $ipAddress);
                
                if ($locationData && isset($locationData['country'])) {
                    \Log::info('Successfully fetched location from ' . $api . ' for IP: ' . $ipAddress);
                    return $locationData;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch from ' . $api . ' for IP ' . $ipAddress . ': ' . $e->getMessage());
                // Continue to next API
            }
        }

        // All APIs failed
        \Log::error('All IP geolocation APIs failed for IP: ' . $ipAddress);

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
     * Fetch from ipapi.co (fallback 1 - free tier available)
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
     * Fetch from ipwhois.app (fallback 2 - free, no API key required)
     */
    private function fetchFromIpWhois(string $ipAddress): ?array
    {
        $response = Http::timeout(8)->get("http://ipwhois.app/json/{$ipAddress}");

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
     * Get user's active sessions (recent logins without logout)
     */
    public function getActiveSessions(int $userId)
    {
        // Get recent logins
        $logins = ActivityLog::forUser($userId)
            ->action('login')
            ->orderBy('logged_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent logouts
        $logouts = ActivityLog::forUser($userId)
            ->action('logout')
            ->orderBy('logged_at', 'desc')
            ->limit(10)
            ->get()
            ->pluck('ip_address')
            ->toArray();

        // Filter out logins that have corresponding logouts
        return $logins->filter(function($login) use ($logouts) {
            return !in_array($login->ip_address, $logouts);
        });
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
}
