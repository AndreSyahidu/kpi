<?php
/**
 * Fired during plugin deactivation
 */
class KPI_Dashboard_Deactivator {

    /**
     * Plugin deactivation logic
     */
    public static function deactivate() {
        // Clear scheduled cron jobs
        self::clear_scheduled_events();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Clean up temporary files
        self::cleanup_temp_files();

        // Note: We don't delete database tables or user data
        // This allows reactivation without data loss
        // For complete removal, use uninstall.php
    }

    /**
     * Clear all scheduled cron events
     */
    private static function clear_scheduled_events() {
        $cron_hooks = [
            'kpi_dashboard_weekly_reminder',
            'kpi_dashboard_daily_digest',
            'kpi_dashboard_cleanup_sessions',
            'kpi_dashboard_scheduled_reports',
            'kpi_dashboard_check_alerts',
        ];

        foreach ($cron_hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    /**
     * Clean up temporary files
     */
    private static function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/kpi-dashboard/temp';

        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
