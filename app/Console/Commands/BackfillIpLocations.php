<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BackfillIpLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:backfill-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill location data for existing activity logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting location backfill...');
        
        // Get activity logs without location data
        $logs = ActivityLog::whereNull('country')
            ->orWhereNull('city')
            ->limit(100)
            ->get();
        
        $this->info("Found {$logs->count()} logs to process");
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($logs as $log) {
            // Skip private/local IPs
            if (in_array($log->ip_address, ['127.0.0.1', '::1', 'localhost', 'unknown']) || 
                $this->isPrivateIp($log->ip_address)) {
                $log->update([
                    'country' => 'Local Network',
                    'city' => 'Localhost',
                ]);
                $this->line("✓ {$log->ip_address} - Local Network (skipped)");
                $skipped++;
                continue;
            }
            
            // Fetch location from IP-API
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$log->ip_address}?fields=status,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,asname,query,mobile,proxy,hosting)");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['status']) && $data['status'] === 'success') {
                        $log->update([
                            'country' => $data['country'] ?? null,
                            'city' => $data['city'] ?? null,
                            'region' => $data['regionName'] ?? $data['region'] ?? null,
                            'isp' => $data['isp'] ?? null,
                            'timezone' => $data['timezone'] ?? null,
                            'latitude' => $data['lat'] ?? null,
                            'longitude' => $data['lon'] ?? null,
                        ]);
                        $city = $data['city'] ?? 'Unknown';
                        $region = $data['regionName'] ?? '';
                        $country = $data['country'] ?? 'Unknown';
                        $isp = $data['isp'] ?? '';
                        $location = $city . ($region ? ', ' . $region : '') . ', ' . $country;
                        $this->line("✓ {$log->ip_address} - {$location} | ISP: {$isp}");
                        $updated++;
                    } else {
                        $message = $data['message'] ?? 'Unknown';
                        $this->warn("✗ {$log->ip_address} - API error: {$message}");
                        $skipped++;
                    }
                } else {
                    $this->warn("✗ {$log->ip_address} - HTTP error: {$response->status()}");
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->warn("✗ {$log->ip_address} - Exception: {$e->getMessage()}");
                $skipped++;
            }
            
            // Rate limit: IP-API allows 45 requests per minute
            usleep(1500000); // 1.5 second delay
        }
        
        $this->newLine();
        $this->info("Backfill complete!");
        $this->table(['Status', 'Count'], [
            ['Updated', $updated],
            ['Skipped', $skipped],
            ['Total', $updated + $skipped],
        ]);
    }
    
    /**
     * Check if IP is private
     */
    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
