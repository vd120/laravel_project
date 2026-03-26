<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate a QR code for a user profile.
     *
     * @param \App\Models\User $user
     * @param int $size QR code size in pixels
     * @return string SVG content
     */
    public function generateProfileQrCode($user, int $size = 300): string
    {
        $profileUrl = route('users.show', $user->username);
        
        return QrCode::size($size)
            ->format('svg')
            ->generate($profileUrl);
    }

    /**
     * Generate a QR code as PNG image data.
     *
     * @param \App\Models\User $user
     * @param int $size QR code size in pixels
     * @return string PNG image data (base64 encoded)
     */
    public function generateProfileQrCodePng($user, int $size = 300): string
    {
        $profileUrl = route('users.show', $user->username);
        
        // Generate SVG first
        $svg = QrCode::size($size)->generate($profileUrl);
        
        // Convert SVG to PNG using GD
        return $this->convertSvgToPng($svg, $size);
    }

    /**
     * Convert SVG to PNG using GD library.
     *
     * @param string $svg SVG content
     * @param int $size Image size
     * @return string PNG binary data
     */
    private function convertSvgToPng(string $svg, int $size): string
    {
        // Create a true color image
        $image = imagecreatetruecolor($size, $size);
        
        // Allocate colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fill background with white
        imagefill($image, 0, 0, $white);
        
        // Extract viewBox to calculate scale
        $viewBoxSize = 100;
        if (preg_match('/viewBox="[\d.]+ [\d.]+ ([\d.]+) ([\d.]+)"/', $svg, $vbMatches)) {
            $viewBoxSize = (float) $vbMatches[1];
        }
        
        // Calculate scale factor
        $scale = $size / $viewBoxSize;
        
        // Parse SVG paths and build polygon points
        if (preg_match_all('/<path[^>]*d="([^"]+)"/', $svg, $pathMatches, PREG_SET_ORDER)) {
            foreach ($pathMatches as $pathMatch) {
                $pathData = $pathMatch[1];
                
                // Parse all path commands
                if (preg_match_all('/([ML])([\d.]+)[\s,]+([\d.]+)/', $pathData, $cmdMatches, PREG_SET_ORDER)) {
                    $points = [];
                    foreach ($cmdMatches as $cmdMatch) {
                        $x = (int) round((float) $cmdMatch[2] * $scale);
                        $y = (int) round((float) $cmdMatch[3] * $scale);
                        $points[] = $x;
                        $points[] = $y;
                    }
                    
                    // Fill the polygon if we have enough points
                    if (count($points) >= 6) { // At least 3 points (x,y pairs)
                        imagesetthickness($image, 1);
                        imagefilledpolygon($image, $points, count($points) / 2, $black);
                    }
                }
            }
        }
        
        // Disable interlacing
        imageinterlace($image, false);
        
        // Output PNG to string
        ob_start();
        imagepng($image, null, 9);
        $pngData = ob_get_clean();
        
        // Free memory
        imagedestroy($image);
        
        return $pngData;
    }

    /**
     * Get the profile URL for QR code.
     *
     * @param \App\Models\User $user
     * @return string
     */
    public function getProfileUrl($user): string
    {
        return route('users.show', $user->username);
    }
}
