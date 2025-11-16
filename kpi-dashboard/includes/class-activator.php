<?php
/**
 * Fired during plugin activation
 */
class KPI_Dashboard_Activator {

    /**
     * Plugin activation logic
     */
    public static function activate() {
        try {
            // Check PHP version
            if (version_compare(PHP_VERSION, '8.1', '<')) {
                throw new Exception('KPI Dashboard requires PHP 8.1 or higher. Current version: ' . PHP_VERSION);
            }

            // Check WordPress version
            if (version_compare(get_bloginfo('version'), '6.4', '<')) {
                throw new Exception('KPI Dashboard requires WordPress 6.4 or higher. Current version: ' . get_bloginfo('version'));
            }

            // Check required PHP extensions
            $required_extensions = ['mysqli', 'json', 'mbstring'];
            $missing = [];
            foreach ($required_extensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missing[] = $ext;
                }
            }
            if (!empty($missing)) {
                throw new Exception('Missing required PHP extensions: ' . implode(', ', $missing));
            }

            // Create database tables
            self::create_database_tables();

            // Add performance optimization indexes
            KPI_Dashboard_DB_Indexes::add_optimization_indexes();

            // Create upload directories
            self::create_upload_directories();

            // Set default options
            self::set_default_options();

            // Flush rewrite rules for custom routing
            flush_rewrite_rules();

            // Set activation timestamp
            update_option('kpi_dashboard_activated', time());
            update_option('kpi_dashboard_version', KPI_DASHBOARD_VERSION);

        } catch (Exception $e) {
            // Log error
            error_log('KPI Dashboard Activation Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            // Deactivate plugin
            deactivate_plugins(KPI_DASHBOARD_PLUGIN_BASENAME);

            // Show user-friendly error
            $error_html = '
                <div style="max-width: 800px; margin: 50px auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
                    <h1 style="color: #d63638;">❌ KPI Dashboard Activation Failed</h1>

                    <div style="background: #fff; border-left: 4px solid #d63638; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0;">Error Details:</h3>
                        <p style="font-family: monospace; background: #f6f7f7; padding: 15px; border-radius: 3px;">' . esc_html($e->getMessage()) . '</p>
                    </div>

                    <div style="background: #fff; border-left: 4px solid #72aee6; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0;">🔍 How to Debug:</h3>
                        <ol>
                            <li>Run the debug script: <code style="background: #f6f7f7; padding: 2px 6px;">http://yoursite.com/wp-content/plugins/kpi-dashboard/debug-activation.php</code></li>
                            <li>Check WordPress debug log: <code style="background: #f6f7f7; padding: 2px 6px;">wp-content/debug.log</code></li>
                            <li>See full troubleshooting guide: <code style="background: #f6f7f7; padding: 2px 6px;">FIX_ACTIVATION_ERROR.md</code></li>
                        </ol>
                    </div>

                    <div style="background: #fff; border-left: 4px solid #00a32a; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0;">✅ Quick Checklist:</h3>
                        <ul>
                            <li>PHP Version ≥ 8.1: <strong>' . PHP_VERSION . '</strong></li>
                            <li>WordPress Version ≥ 6.4: <strong>' . get_bloginfo('version') . '</strong></li>
                            <li>MySQL/MariaDB running and accessible</li>
                            <li>Required PHP extensions installed (mysqli, json, mbstring)</li>
                        </ul>
                    </div>

                    <p><a href="' . admin_url('plugins.php') . '" class="button button-primary">« Back to Plugins</a></p>
                </div>
            ';

            wp_die(
                $error_html,
                'KPI Dashboard Activation Error',
                ['back_link' => false]
            );
        }
    }

    /**
     * Create custom database tables
     */
    private static function create_database_tables() {
        $schema_file = KPI_DASHBOARD_PLUGIN_DIR . 'includes/database/class-db-schema.php';

        if (!file_exists($schema_file)) {
            throw new Exception('Database schema file not found: ' . $schema_file);
        }

        require_once $schema_file;

        if (!class_exists('KPI_Dashboard_DB_Schema')) {
            throw new Exception('Database schema class not found');
        }

        // Test database connection
        global $wpdb;
        if ($wpdb->last_error) {
            throw new Exception('Database connection error: ' . $wpdb->last_error);
        }

        // Create tables
        KPI_Dashboard_DB_Schema::create_tables();

        // Verify tables were created
        $required_tables = ['kpi_users', 'kpi_departments', 'kpi_positions'];
        foreach ($required_tables as $table) {
            $full_table = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
            if (!$exists) {
                throw new Exception('Failed to create table: ' . $table);
            }
        }
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
