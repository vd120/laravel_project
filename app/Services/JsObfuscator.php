<?php

namespace App\Services;

class JsObfuscator
{
    /**
     * Obfuscate inline JavaScript code
     * This is a lightweight obfuscation for inline scripts
     */
    public function obfuscate(string $jsCode): string
    {
        if (empty(trim($jsCode))) {
            return $jsCode;
        }

        // Only obfuscate in production
        if (!app()->environment('production')) {
            return $jsCode;
        }

        // Simple but effective obfuscation techniques:
        
        // 1. Remove comments
        $jsCode = preg_replace('/\/\/.*$/m', '', $jsCode);
        $jsCode = preg_replace('/\/\*.*?\*\//s', '', $jsCode);
        
        // 2. Remove extra whitespace
        $jsCode = preg_replace('/\s+/m', ' ', $jsCode);
        $jsCode = trim($jsCode);
        
        // 3. Encode strings using atob (base64 decode at runtime)
        $jsCode = preg_replace_callback('/(["\'])(.*?)\1/', function($matches) {
            // Don't encode very short strings or those with special chars
            if (strlen($matches[2]) < 3 || strpos($matches[2], "\n") !== false) {
                return $matches[0];
            }
            $encoded = base64_encode($matches[2]);
            return "atob('{$encoded}')";
        }, $jsCode);

        return $jsCode;
    }

    /**
     * Obfuscate and wrap in eval (heavy obfuscation)
     */
    public function obfuscateHeavy(string $jsCode): string
    {
        if (empty(trim($jsCode)) || !app()->environment('production')) {
            return $jsCode;
        }

        // Remove comments and whitespace
        $jsCode = preg_replace('/\/\/.*$/m', '', $jsCode);
        $jsCode = preg_replace('/\/\*.*?\*\//s', '', $jsCode);
        $jsCode = preg_replace('/\s+/m', ' ', $jsCode);
        $jsCode = trim($jsCode);

        // Base64 encode and wrap in eval
        $encoded = base64_encode($jsCode);
        return "<script>eval(atob('{$encoded}'))</script>";
    }
}
