<?php
/**
 * Rate Limiter
 * Prevents brute-force attacks by limiting request rates
 */
class KPI_Dashboard_Rate_Limiter {

    /**
     * Check if action is rate limited
     *
     * @param string $action Action identifier (e.g., 'login', 'reset_password')
     * @param string $identifier User identifier (e.g., username, IP, email)
     * @param int $max_attempts Maximum allowed attempts
     * @param int $window_seconds Time window in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => int]
     */
    public static function check($action, $identifier, $max_attempts = 5, $window_seconds = 900) {
        $key = self::get_cache_key($action, $identifier);
        $attempts = self::get_attempts($key);

        $now = time();
        $window_start = $now - $window_seconds;

        // Filter attempts within the time window
        $recent_attempts = array_filter($attempts, function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        });

        $count = count($recent_attempts);
        $is_allowed = $count < $max_attempts;

        // Calculate when the limit resets
        $reset_at = !empty($recent_attempts) ? min($recent_attempts) + $window_seconds : $now;

        return [
            'allowed' => $is_allowed,
            'remaining' => max(0, $max_attempts - $count),
            'count' => $count,
            'max_attempts' => $max_attempts,
            'reset_at' => $reset_at,
            'retry_after' => $is_allowed ? 0 : ($reset_at - $now),
        ];
    }

    /**
     * Record an attempt
     *
     * @param string $action Action identifier
     * @param string $identifier User identifier
     * @return void
     */
    public static function record($action, $identifier) {
        $key = self::get_cache_key($action, $identifier);
        $attempts = self::get_attempts($key);
        $attempts[] = time();

        // Store for 24 hours (86400 seconds)
        set_transient($key, $attempts, 86400);
    }

    /**
     * Clear attempts (e.g., after successful login)
     *
     * @param string $action Action identifier
     * @param string $identifier User identifier
     * @return void
     */
    public static function clear($action, $identifier) {
        $key = self::get_cache_key($action, $identifier);
        delete_transient($key);
    }

    /**
     * Get cache key for rate limiting
     *
     * @param string $action
     * @param string $identifier
     * @return string
     */
    private static function get_cache_key($action, $identifier) {
        // Sanitize identifier to prevent cache key manipulation
        $safe_identifier = sanitize_key($identifier);
        return "kpi_rate_limit_{$action}_{$safe_identifier}";
    }

    /**
     * Get stored attempts from cache
     *
     * @param string $key
     * @return array
     */
    private static function get_attempts($key) {
        $attempts = get_transient($key);
        return is_array($attempts) ? $attempts : [];
    }

    /**
     * Get client IP address (handles proxies)
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip = '';

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Get first IP if multiple are present (comma-separated)
        if (strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }

        return sanitize_text_field($ip);
    }

    /**
     * Combined check using both username and IP
     *
     * @param string $action
     * @param string $username
     * @param int $max_attempts
     * @param int $window_seconds
     * @return array
     */
    public static function check_combined($action, $username, $max_attempts = 5, $window_seconds = 900) {
        $ip = self::get_client_ip();

        // Check both username and IP
        $username_check = self::check($action . '_user', $username, $max_attempts, $window_seconds);
        $ip_check = self::check($action . '_ip', $ip, $max_attempts * 3, $window_seconds); // Allow 3x attempts per IP

        // If either is blocked, return blocked status with the stricter limit
        if (!$username_check['allowed'] || !$ip_check['allowed']) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'count' => max($username_check['count'], $ip_check['count']),
                'max_attempts' => $max_attempts,
                'reset_at' => max($username_check['reset_at'], $ip_check['reset_at']),
                'retry_after' => max($username_check['retry_after'], $ip_check['retry_after']),
                'reason' => !$username_check['allowed'] ? 'username' : 'ip',
            ];
        }

        return $username_check;
    }

    /**
     * Record combined attempt (username + IP)
     *
     * @param string $action
     * @param string $username
     * @return void
     */
    public static function record_combined($action, $username) {
        $ip = self::get_client_ip();
        self::record($action . '_user', $username);
        self::record($action . '_ip', $ip);
    }

    /**
     * Clear combined attempts (username + IP)
     *
     * @param string $action
     * @param string $username
     * @return void
     */
    public static function clear_combined($action, $username) {
        $ip = self::get_client_ip();
        self::clear($action . '_user', $username);
        self::clear($action . '_ip', $ip);
    }
}
