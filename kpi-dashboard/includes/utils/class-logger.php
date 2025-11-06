<?php
/**
 * Logging Utility
 */
class KPI_Dashboard_Logger {

    private static $log_file = null;

    /**
     * Initialize logger
     */
    private static function init() {
        if (!self::$log_file) {
            $upload_dir = wp_upload_dir();
            $log_dir = $upload_dir['basedir'] . '/kpi-dashboard/logs';

            if (!file_exists($log_dir)) {
                wp_mkdir_p($log_dir);
            }

            self::$log_file = $log_dir . '/kpi-dashboard-' . date('Y-m-d') . '.log';
        }
    }

    /**
     * Log error
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }

    /**
     * Log warning
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log info
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }

    /**
     * Log debug
     */
    public static function debug($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }

    /**
     * Main log function
     */
    private static function log($level, $message, $context = []) {
        self::init();

        $timestamp = current_time('Y-m-d H:i:s');
        $user = KPI_Dashboard_Auth::get_current_user();
        $user_id = $user ? $user->id : 0;

        $log_entry = sprintf(
            "[%s] [%s] [User:%d] %s\n",
            $timestamp,
            $level,
            $user_id,
            $message
        );

        if (!empty($context)) {
            $log_entry .= "Context: " . json_encode($context) . "\n";
        }

        error_log($log_entry, 3, self::$log_file);

        // Also log to WordPress debug.log if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log("[KPI Dashboard] [$level] $message");
        }
    }

    /**
     * Get recent logs
     */
    public static function get_recent_logs($lines = 100) {
        self::init();

        if (!file_exists(self::$log_file)) {
            return [];
        }

        $file = file(self::$log_file);
        return array_slice($file, -$lines);
    }

    /**
     * Clear old log files (keep last 30 days)
     */
    public static function cleanup_old_logs() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/kpi-dashboard/logs';

        if (!is_dir($log_dir)) {
            return;
        }

        $files = glob($log_dir . '/kpi-dashboard-*.log');
        $cutoff = strtotime('-30 days');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
