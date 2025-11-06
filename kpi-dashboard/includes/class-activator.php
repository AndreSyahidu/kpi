<?php
/**
 * Fired during plugin activation
 */
class KPI_Dashboard_Activator {

    /**
     * Plugin activation logic
     */
    public static function activate() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            deactivate_plugins(KPI_DASHBOARD_PLUGIN_BASENAME);
            wp_die(
                __('KPI Dashboard requires PHP 8.1 or higher. Please upgrade PHP.', 'kpi-dashboard'),
                __('Plugin Activation Error', 'kpi-dashboard'),
                ['back_link' => true]
            );
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '6.4', '<')) {
            deactivate_plugins(KPI_DASHBOARD_PLUGIN_BASENAME);
            wp_die(
                __('KPI Dashboard requires WordPress 6.4 or higher. Please upgrade WordPress.', 'kpi-dashboard'),
                __('Plugin Activation Error', 'kpi-dashboard'),
                ['back_link' => true]
            );
        }

        // Create database tables
        self::create_database_tables();

        // Create upload directories
        self::create_upload_directories();

        // Set default options
        self::set_default_options();

        // Flush rewrite rules for custom routing
        flush_rewrite_rules();

        // Set activation timestamp
        update_option('kpi_dashboard_activated', time());
        update_option('kpi_dashboard_version', KPI_DASHBOARD_VERSION);
    }

    /**
     * Create custom database tables
     */
    private static function create_database_tables() {
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/database/class-db-schema.php';
        KPI_Dashboard_DB_Schema::create_tables();
    }

    /**
     * Create upload directories for KPI dashboard
     */
    private static function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $kpi_dir = $upload_dir['basedir'] . '/kpi-dashboard';

        $directories = [
            $kpi_dir,
            $kpi_dir . '/attachments',
            $kpi_dir . '/reports',
            $kpi_dir . '/exports',
            $kpi_dir . '/temp',
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);

                // Create .htaccess for security
                $htaccess = $dir . '/.htaccess';
                if (!file_exists($htaccess)) {
                    file_put_contents($htaccess, 'Options -Indexes' . PHP_EOL . 'deny from all');
                }

                // Create index.php for security
                $index = $dir . '/index.php';
                if (!file_exists($index)) {
                    file_put_contents($index, '<?php // Silence is golden');
                }
            }
        }
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $defaults = [
            'kpi_dashboard_settings' => [
                'company_name' => get_bloginfo('name'),
                'company_logo' => 'https://www.mbdcorp.id/wp-content/uploads/2022/08/logo-favicon-mbd-corp-150x150-1.webp',
                'primary_color' => '#1565C0',
                'secondary_color' => '#FF6B35',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
                'timezone' => get_option('timezone_string', 'UTC'),
                'data_retention_years' => 3,
                'session_timeout' => 480, // 8 hours in minutes
                'enable_2fa' => false,
                'enable_audit_log' => true,
            ],
            'kpi_dashboard_notifications' => [
                'enable_email' => true,
                'enable_in_app' => true,
                'reminder_day' => 1, // Monday
                'reminder_time' => '09:00',
                'digest_frequency' => 'daily',
            ],
            'kpi_dashboard_email' => [
                'from_name' => get_bloginfo('name'),
                'from_email' => get_option('admin_email'),
                'smtp_enabled' => false,
            ],
        ];

        foreach ($defaults as $option_name => $option_value) {
            if (!get_option($option_name)) {
                add_option($option_name, $option_value);
            }
        }
    }
}
