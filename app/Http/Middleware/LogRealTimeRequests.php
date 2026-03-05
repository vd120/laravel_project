<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRealTimeRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get real IP address (behind Cloudflare/proxy)
        $realIp = $this->getRealIp($request);
        
        // Get local server IP
        $localIp = $request->server('SERVER_ADDR', $request->ip()) ?? 'unknown';
        
        // Get user agent
        $userAgent = $request->userAgent() ?? 'Unknown';
        
        // Get request details
        $method = $request->method();
        $path = $request->path();
        $timestamp = now()->format('Y-m-d H:i:s');
        $referer = $request->header('Referer', '-');
        $contentType = $request->header('Content-Type', '-');
        $contentLength = $request->header('Content-Length', '0');
        $xRequestedWith = $request->header('X-Requested-With', '-');
        $forwardedFor = $request->header('X-Forwarded-For', '-');
        $forwardedProto = $request->header('X-Forwarded-Proto', '-');
        $cfConnectingIP = $request->header('CF-Connecting-IP', '-');
        $cfIPCountry = $request->header('CF-IPCountry', '-');
        $cfRay = $request->header('CF-Ray', '-');
        
        // Get location from IP using Cloudflare headers (INSTANT - no API calls!)
        $location = $this->getLocationFromIp($request, $realIp);
        
        // Parse device info from user agent
        $deviceInfo = $this->parseDeviceInfo($userAgent);
        
        // Log asynchronously (don't block the request)
        if (config('app.debug')) {
            // Only log in debug/tunnel mode
            app()->terminating(function () use (
                $timestamp, $realIp, $localIp, $method, $path, $userAgent,
                $deviceInfo, $location, $referer, $contentType, $contentLength,
                $xRequestedWith, $forwardedFor, $forwardedProto,
                $cfConnectingIP, $cfIPCountry, $cfRay
            ) {
                $logEntry = json_encode([
                    'timestamp' => $timestamp,
                    'ip' => $realIp,
                    'local_ip' => $localIp,
                    'method' => $method,
                    'path' => $path,
                    'user_agent' => $userAgent,
                    'device' => $deviceInfo['device'],
                    'os' => $deviceInfo['os'],
                    'browser' => $deviceInfo['browser'],
                    'referer' => $referer,
                    'content_type' => $contentType,
                    'content_length' => $contentLength,
                    'x_requested_with' => $xRequestedWith,
                    'x_forwarded_for' => $forwardedFor,
                    'x_forwarded_proto' => $forwardedProto,
                    'cf_connecting_ip' => $cfConnectingIP,
                    'cf_ip_country' => $cfIPCountry,
                    'cf_ray' => $cfRay,
                    'city' => $location['city'] ?? 'Unknown',
                    'country' => $location['country'] ?? 'Unknown',
                    'region' => $location['region'] ?? 'Unknown',
                    'latitude' => $location['lat'] ?? null,
                    'longitude' => $location['lon'] ?? null,
                    'is_local' => $location['is_local'] ?? false,
                ]) . PHP_EOL;
                
                file_put_contents(
                    storage_path('logs/realtime-requests.log'),
                    $logEntry,
                    FILE_APPEND | LOCK_EX
                );
            });
        }
        
        return $next($request);
    }
    
    /**
     * Get the real client IP address, even behind Cloudflare/proxy.
     */
    private function getRealIp(Request $request): string
    {
        // Cloudflare headers (highest priority when using cloudflared)
        if ($request->header('CF-Connecting-IP')) {
            return $request->header('CF-Connecting-IP');
        }
        
        // Standard X-Forwarded-For header (may contain multiple IPs)
        if ($request->header('X-Forwarded-For')) {
            $ips = explode(',', $request->header('X-Forwarded-For'));
            // Get the first non-private IP (the original client)
            foreach ($ips as $ip) {
                $ip = trim($ip);
                // FILTER_FLAG_IS_PRIVATE = 8
                if (filter_var($ip, FILTER_VALIDATE_IP) && !filter_var($ip, FILTER_VALIDATE_IP, 8)) {
                    return $ip;
                }
            }
            // If all are private, return the first one
            return trim($ips[0]);
        }
        
        // X-Real-IP header
        if ($request->header('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }
        
        // Fallback to direct IP
        return $request->ip() ?? '0.0.0.0';
    }
    
    /**
     * Get location data from IP using Cloudflare headers (no external API calls).
     * Cloudflare provides geolocation automatically for free.
     */
    private function getLocationFromIp(Request $request, string $ip): array
    {
        $isLocal = false;
        
        // Check if it's a local/private IP
        if (filter_var($ip, FILTER_VALIDATE_IP, 8) ||
            filter_var($ip, FILTER_VALIDATE_IP, 16) ||
            $ip === '127.0.0.1' || 
            $ip === '::1') {
            $isLocal = true;
            if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
                return [
                    'city' => 'Localhost',
                    'country' => 'Local',
                    'region' => 'Local',
                    'lat' => null,
                    'lon' => null,
                    'is_local' => true,
                ];
            }
        }
        
        // Use Cloudflare's built-in geolocation headers (FREE & INSTANT!)
        // Cloudflare automatically provides these for all requests
        $cfCity = $request->header('CF-IPCity');
        $cfCountry = $request->header('CF-IPCountry');
        $cfRegion = $request->header('CF-Region');
        $cfLat = $request->header('CF-IPLatitude');
        $cfLon = $request->header('CF-IPLongitude');
        
        // If Cloudflare headers exist, use them (no API call needed!)
        if ($cfCountry && $cfCountry !== '-') {
            return [
                'city' => $cfCity ?? 'Unknown',
                'country' => $this->getCountryName($cfCountry),
                'region' => $cfRegion ?? 'Unknown',
                'lat' => $cfLat ?: null,
                'lon' => $cfLon ?: null,
                'is_local' => $isLocal,
            ];
        }
        
        // Fallback for non-Cloudflare requests (localhost testing)
        return [
            'city' => $isLocal ? 'Local Network' : 'Unknown',
            'country' => $isLocal ? 'Local' : 'Unknown',
            'region' => $isLocal ? 'Local' : 'Unknown',
            'lat' => null,
            'lon' => null,
            'is_local' => $isLocal,
        ];
    }
    
    /**
     * Convert country code to country name.
     */
    private function getCountryName(string $code): string
    {
        $countries = [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BR' => 'Brazil',
            'IN' => 'India',
            'CN' => 'China',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'RU' => 'Russia',
            'ZA' => 'South Africa',
            'EG' => 'Egypt',
            'SA' => 'Saudi Arabia',
            'AE' => 'UAE',
            'TR' => 'Turkey',
            'MX' => 'Mexico',
            'AR' => 'Argentina',
            'ID' => 'Indonesia',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
            'PH' => 'Philippines',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'PK' => 'Pakistan',
            'BD' => 'Bangladesh',
            'NG' => 'Nigeria',
            'KE' => 'Kenya',
            'GH' => 'Ghana',
        ];
        return $countries[$code] ?? $code;
    }
    
    /**
     * Parse device information from user agent string.
     */
    private function parseDeviceInfo(string $userAgent): array
    {
        $device = 'Desktop';
        $os = 'Unknown';
        $browser = 'Unknown';
        
        // Detect device type
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobi))/i', $userAgent)) {
            $device = 'Tablet';
        } elseif (preg_match('/(mobi|phone|iphone|ipod|android|blackberry|opera mini|iemobile|wpdesktop)/i', $userAgent)) {
            $device = 'Mobile';
        }
        
        // Detect OS
        if (preg_match('/Windows NT 10\.0/i', $userAgent)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.[23]/i', $userAgent)) {
            $os = 'Windows 8/8.1';
        } elseif (preg_match('/Windows NT 6\.1/i', $userAgent)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Mac OS X [\d_]+/i', $userAgent)) {
            preg_match('/Mac OS X ([\d_]+)/i', $userAgent, $matches);
            $os = 'macOS ' . (isset($matches[1]) ? str_replace('_', '.', $matches[1]) : '');
        } elseif (preg_match('/iPhone OS [\d_]+/i', $userAgent)) {
            preg_match('/iPhone OS ([\d_]+)/i', $userAgent, $matches);
            $os = 'iOS ' . (isset($matches[1]) ? str_replace('_', '.', $matches[1]) : '');
        } elseif (preg_match('/iPad; CPU OS [\d_]+/i', $userAgent)) {
            preg_match('/CPU OS ([\d_]+)/i', $userAgent, $matches);
            $os = 'iPadOS ' . (isset($matches[1]) ? str_replace('_', '.', $matches[1]) : '');
        } elseif (preg_match('/Android [\d.]+/i', $userAgent)) {
            preg_match('/Android ([\d.]+)/i', $userAgent, $matches);
            $os = 'Android ' . (isset($matches[1]) ? $matches[1] : '');
        } elseif (preg_match('/X11|Linux x86/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Ubuntu/i', $userAgent)) {
            $os = 'Ubuntu Linux';
        } elseif (preg_match('/CrOS/i', $userAgent)) {
            $os = 'Chrome OS';
        }
        
        // Detect browser
        if (preg_match('/Edg\/([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'Edge ' . ($matches[1] ?? '');
        } elseif (preg_match('/Chrome\/([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'Chrome ' . ($matches[1] ?? '');
        } elseif (preg_match('/Firefox\/([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'Firefox ' . ($matches[1] ?? '');
        } elseif (preg_match('/Safari\/([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'Safari ' . ($matches[1] ?? '');
        } elseif (preg_match('/MSIE ([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'IE ' . ($matches[1] ?? '');
        } elseif (preg_match('/Trident\/([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'IE 11';
        } elseif (preg_match('/Opera[\s\/]([\d.]+)/i', $userAgent, $matches)) {
            $browser = 'Opera ' . ($matches[1] ?? '');
        }
        
        return [
            'device' => $device,
            'os' => $os,
            'browser' => $browser,
        ];
    }
}
