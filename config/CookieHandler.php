<?php
/**
 * Cookie Management Class
 * Provides secure cookie handling with consistent settings across the application
 */
class CookieHandler {
    // Cookie configuration constants
    private const COOKIE_EXPIRY = 30 * 24 * 60 * 60; // 30 days default
    private const COOKIE_PATH = '/';                  // Available across entire domain
    private const COOKIE_DOMAIN = '';                 // Current domain only
    private const SECURE = true;                      // Only transmit over HTTPS
    private const HTTP_ONLY = true;                   // Prevent JavaScript access
    
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
                'samesite' => 'Strict'
            ]
        );
    }
    
    public static function get($name) {
        return $_COOKIE[$name] ?? null;
    }
    
    public static function delete($name) {
        if (isset($_COOKIE[$name])) {
            setcookie(
                $name,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => self::COOKIE_PATH,
                    'domain' => self::COOKIE_DOMAIN,
                    'secure' => self::SECURE,
                    'httponly' => self::HTTP_ONLY,
                    'samesite' => 'Strict'
                ]
            );
            unset($_COOKIE[$name]);
        }
    }
    
    public static function exists($name) {
        return isset($_COOKIE[$name]);
    }
}
