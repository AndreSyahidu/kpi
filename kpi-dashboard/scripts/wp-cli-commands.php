<?php
/**
 * WP-CLI Commands for KPI Dashboard
 *
 * Usage:
 *   wp kpi-dashboard <command> [options]
 *
 * Available commands:
 *   wp kpi-dashboard install        - Install/reinstall database tables
 *   wp kpi-dashboard generate-data  - Generate dummy test data
 *   wp kpi-dashboard clear-data     - Clear all dummy data
 *   wp kpi-dashboard create-user    - Create a new user
 *   wp kpi-dashboard reset          - Reset plugin to initial state
 *   wp kpi-dashboard info           - Show plugin information
 */

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * KPI Dashboard WP-CLI Commands
 */
class KPI_Dashboard_CLI_Commands {

    /**
     * Install or reinstall database tables
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard install
     *     wp kpi-dashboard install --force
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function install($args, $assoc_args) {
        WP_CLI::log('Installing KPI Dashboard database tables...');

        require_once KPI_DASHBOARD_PATH . 'includes/database/class-db-schema.php';

        try {
            KPI_Dashboard_DB_Schema::create_tables();
            WP_CLI::success('Database tables created successfully!');

            if (isset($assoc_args['force']) || WP_CLI::confirm('Create default data (admin user, departments)?')) {
                KPI_Dashboard_DB_Schema::insert_default_data();
                WP_CLI::success('Default data created!');
                WP_CLI::log('');
                WP_CLI::log('Default credentials:');
                WP_CLI::log('  Username: admin');
                WP_CLI::log('  Password: admin');
                WP_CLI::log('');
                WP_CLI::warning('Please change the default password immediately!');
            }
        } catch (Exception $e) {
            WP_CLI::error('Installation failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate dummy test data
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard generate-data
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function generate_data($args, $assoc_args) {
        WP_CLI::log('Generating dummy test data...');

        require_once KPI_DASHBOARD_PATH . 'generate-dummy-data.php';

        try {
            KPI_Dashboard_Dummy_Data::generate_all();
            WP_CLI::success('Dummy data generated successfully!');
            WP_CLI::log('');
            WP_CLI::log('Test accounts created:');
            WP_CLI::log('  john.doe / password123 (Super Admin)');
            WP_CLI::log('  sarah.sales / password123 (Dept Head - Sales)');
            WP_CLI::log('  mike.marketing / password123 (Dept Head - Marketing)');
            WP_CLI::log('  ... and 20 more users');
            WP_CLI::log('');
            WP_CLI::log('Access dashboard at: ' . get_site_url() . '/kpi');
        } catch (Exception $e) {
            WP_CLI::error('Failed to generate data: ' . $e->getMessage());
        }
    }

    /**
     * Clear all dummy test data
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard clear-data
     *     wp kpi-dashboard clear-data --yes
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function clear_data($args, $assoc_args) {
        if (!isset($assoc_args['yes'])) {
            WP_CLI::confirm('This will delete all test data. Are you sure?');
        }

        WP_CLI::log('Clearing dummy data...');

        require_once KPI_DASHBOARD_PATH . 'generate-dummy-data.php';

        try {
            KPI_Dashboard_Dummy_Data::clear_all();
            WP_CLI::success('Dummy data cleared successfully!');
        } catch (Exception $e) {
            WP_CLI::error('Failed to clear data: ' . $e->getMessage());
        }
    }

    /**
     * Create a new user
     *
     * ## OPTIONS
     *
     * [--username=<username>]
     * : Username for the new user
     *
     * [--email=<email>]
     * : Email address
     *
     * [--password=<password>]
     * : Password (will be prompted if not provided)
     *
     * [--name=<name>]
     * : Full name
     *
     * [--role=<role>]
     * : User role (super_admin, dept_head, manager, staff)
     *
     * [--department=<id>]
     * : Department ID
     *
     * [--position=<id>]
     * : Position ID
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard create-user --username=johndoe --email=john@example.com --role=staff
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function create_user($args, $assoc_args) {
        // Get parameters
        $username = WP_CLI\Utils\get_flag_value($assoc_args, 'username', '');
        $email = WP_CLI\Utils\get_flag_value($assoc_args, 'email', '');
        $password = WP_CLI\Utils\get_flag_value($assoc_args, 'password', '');
        $name = WP_CLI\Utils\get_flag_value($assoc_args, 'name', '');
        $role = WP_CLI\Utils\get_flag_value($assoc_args, 'role', 'staff');
        $department_id = WP_CLI\Utils\get_flag_value($assoc_args, 'department', null);
        $position_id = WP_CLI\Utils\get_flag_value($assoc_args, 'position', null);

        // Prompt for required fields
        if (empty($username)) {
            $username = cli\prompt('Username');
        }

        if (empty($email)) {
            $email = cli\prompt('Email');
        }

        if (empty($name)) {
            $name = cli\prompt('Full Name');
        }

        if (empty($password)) {
            $password = cli\prompt('Password', false, '', true);
        }

        // Validate role
        $valid_roles = ['super_admin', 'dept_head', 'manager', 'staff'];
        if (!in_array($role, $valid_roles)) {
            WP_CLI::error('Invalid role. Must be one of: ' . implode(', ', $valid_roles));
        }

        // Create user
        $user_data = [
            'username' => $username,
            'full_name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'department_id' => $department_id,
            'position_id' => $position_id,
        ];

        try {
            require_once KPI_DASHBOARD_PATH . 'includes/models/class-user.php';
            $user_id = KPI_Dashboard_User_Model::create($user_data);

            WP_CLI::success('User created successfully!');
            WP_CLI::log('');
            WP_CLI::log('User ID: ' . $user_id);
            WP_CLI::log('Username: ' . $username);
            WP_CLI::log('Email: ' . $email);
            WP_CLI::log('Role: ' . $role);
        } catch (Exception $e) {
            WP_CLI::error('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Reset plugin to initial state
     *
     * ## OPTIONS
     *
     * [--yes]
     * : Skip confirmation
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard reset --yes
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function reset($args, $assoc_args) {
        if (!isset($assoc_args['yes'])) {
            WP_CLI::confirm('This will delete ALL data and reset to initial state. Are you sure?', $assoc_args);
        }

        WP_CLI::log('Resetting KPI Dashboard...');

        global $wpdb;

        // Drop all tables
        $tables = [
            'kpi_users', 'kpi_departments', 'kpi_positions', 'kpi_department_heads',
            'kpi_definitions', 'kpi_assignments', 'kpi_data', 'kpi_audit_logs',
            'kpi_notifications', 'kpi_alert_rules', 'kpi_user_notification_prefs',
            'kpi_scheduled_reports', 'kpi_report_history', 'kpi_comments',
            'kpi_settings', 'kpi_sessions',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }

        WP_CLI::log('Dropped all tables');

        // Recreate tables
        require_once KPI_DASHBOARD_PATH . 'includes/database/class-db-schema.php';
        KPI_Dashboard_DB_Schema::create_tables();
        KPI_Dashboard_DB_Schema::insert_default_data();

        WP_CLI::success('Plugin reset successfully!');
        WP_CLI::log('');
        WP_CLI::log('Default credentials:');
        WP_CLI::log('  Username: admin');
        WP_CLI::log('  Password: admin');
    }

    /**
     * Show plugin information
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard info
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function info($args, $assoc_args) {
        global $wpdb;

        WP_CLI::log('KPI Dashboard Information');
        WP_CLI::log('=========================');
        WP_CLI::log('');

        // Plugin version
        WP_CLI::log('Version: ' . KPI_DASHBOARD_VERSION);
        WP_CLI::log('Path: ' . KPI_DASHBOARD_PATH);
        WP_CLI::log('URL: ' . get_site_url() . '/kpi');
        WP_CLI::log('');

        // Database stats
        WP_CLI::log('Database Statistics:');
        WP_CLI::log('-------------------');

        $stats = [
            'Users' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_users WHERE is_active = 1"),
            'Departments' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_departments WHERE is_active = 1"),
            'Positions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_positions WHERE is_active = 1"),
            'KPI Definitions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_definitions WHERE is_active = 1"),
            'Data Entries' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_data"),
            'Pending Approvals' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_data WHERE status = 'pending'"),
            'Notifications' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_notifications WHERE is_read = 0"),
            'Audit Logs' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_audit_logs"),
        ];

        foreach ($stats as $label => $count) {
            WP_CLI::log(sprintf('  %-20s %s', $label . ':', number_format($count)));
        }

        WP_CLI::log('');

        // Check tables
        WP_CLI::log('Database Tables:');
        WP_CLI::log('---------------');

        $tables = [
            'kpi_users', 'kpi_departments', 'kpi_positions', 'kpi_definitions',
            'kpi_data', 'kpi_notifications', 'kpi_audit_logs', 'kpi_sessions',
        ];

        $all_exist = true;
        foreach ($tables as $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
            if ($exists) {
                WP_CLI::log('  ✓ ' . $table);
            } else {
                WP_CLI::log('  ✗ ' . $table . ' (missing)');
                $all_exist = false;
            }
        }

        WP_CLI::log('');

        if (!$all_exist) {
            WP_CLI::warning('Some tables are missing. Run: wp kpi-dashboard install');
        }

        // System info
        WP_CLI::log('System:');
        WP_CLI::log('-------');
        WP_CLI::log('  WordPress: ' . get_bloginfo('version'));
        WP_CLI::log('  PHP: ' . PHP_VERSION);
        WP_CLI::log('  MySQL: ' . $wpdb->db_version());
        WP_CLI::log('');
    }

    /**
     * Export data to CSV
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Export type (users, departments, kpis, data)
     *
     * [--output=<file>]
     * : Output file path
     *
     * ## EXAMPLES
     *
     *     wp kpi-dashboard export --type=users --output=users.csv
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function export($args, $assoc_args) {
        $type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', 'users');
        $output = WP_CLI\Utils\get_flag_value($assoc_args, 'output', $type . '.csv');

        WP_CLI::log("Exporting $type to $output...");

        global $wpdb;

        $data = [];

        switch ($type) {
            case 'users':
                $data = $wpdb->get_results(
                    "SELECT u.*, d.name as department, p.title as position
                     FROM {$wpdb->prefix}kpi_users u
                     LEFT JOIN {$wpdb->prefix}kpi_departments d ON u.department_id = d.id
                     LEFT JOIN {$wpdb->prefix}kpi_positions p ON u.position_id = p.id
                     ORDER BY u.id",
                    ARRAY_A
                );
                break;

            case 'departments':
                $data = $wpdb->get_results(
                    "SELECT * FROM {$wpdb->prefix}kpi_departments ORDER BY id",
                    ARRAY_A
                );
                break;

            case 'kpis':
                $data = $wpdb->get_results(
                    "SELECT * FROM {$wpdb->prefix}kpi_definitions ORDER BY id",
                    ARRAY_A
                );
                break;

            case 'data':
                $data = $wpdb->get_results(
                    "SELECT d.*, k.name as kpi_name, u.full_name as user_name
                     FROM {$wpdb->prefix}kpi_data d
                     LEFT JOIN {$wpdb->prefix}kpi_definitions k ON d.kpi_id = k.id
                     LEFT JOIN {$wpdb->prefix}kpi_users u ON d.user_id = u.id
                     ORDER BY d.created_at DESC",
                    ARRAY_A
                );
                break;

            default:
                WP_CLI::error('Invalid type. Use: users, departments, kpis, or data');
        }

        if (empty($data)) {
            WP_CLI::warning('No data to export');
            return;
        }

        // Write CSV
        $fp = fopen($output, 'w');

        // Header
        fputcsv($fp, array_keys($data[0]));

        // Data
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);

        WP_CLI::success("Exported " . count($data) . " rows to $output");
    }
}

// Register commands
WP_CLI::add_command('kpi-dashboard', 'KPI_Dashboard_CLI_Commands');
