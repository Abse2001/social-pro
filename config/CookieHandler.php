<?php
/**
 * CookieHandler Class
 * Provides secure cookie management functionality with consistent security settings
 */
class CookieHandler {
    // Cookie defaults
    private const COOKIE_EXPIRY = 30 * 24 * 60 * 60; // 30 days in seconds
    private const COOKIE_PATH = '/';                 // Cookie available for entire domain
    private const COOKIE_DOMAIN = '';                // Current domain only
    private const SECURE = true;                     // Only send over HTTPS
    private const HTTP_ONLY = true;                  // Prevent JavaScript access
    
    /**
     * Set a new cookie with secure defaults
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int|null $expiry Optional expiry timestamp
     */
    public static function set($name, $value, $expiry = null) {
        $expiry = $expiry ?? time() + self::COOKIE_EXPIRY;
        setcookie(
            $name,
            $value,
            [
                'expires' => $expiry,
                'path' => self::COOKIE_PATH,
                'domain' => self::COOKIE_DOMAIN,
                'secure' => self::SECURE,
                'httponly' => self::HTTP_ONLY,
                'samesite' => 'Strict' // Prevent CSRF attacks
            ]
        );
    }
    
    /**
     * Get a cookie value by name
     * 
     * @param string $name Cookie name
     * @return string|null Cookie value or null if not found
     */
    public static function get($name) {
        return $_COOKIE[$name] ?? null;
    }
    
    /**
     * Delete a cookie and unset from $_COOKIE array
     * 
     * @param string $name Cookie name
     */
    public static function delete($name) {
        if (isset($_COOKIE[$name])) {
            // Set cookie with past expiry to trigger browser deletion
            setcookie(
                $name,
                '',
                [
                    'expires' => time() - 3600, // Set expiry to past
                    'path' => self::COOKIE_PATH,
                    'domain' => self::COOKIE_DOMAIN,
                    'secure' => self::SECURE,
                    'httponly' => self::HTTP_ONLY,
                    'samesite' => 'Strict'
                ]
            );
            unset($_COOKIE[$name]); // Remove from $_COOKIE array
        }
    }
    
    /**
     * Check if a cookie exists
     * 
     * @param string $name Cookie name
     * @return bool True if cookie exists
     */
    public static function exists($name) {
        return isset($_COOKIE[$name]);
    }
}
