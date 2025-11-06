<?php
/**
 * Dummy Data Generator for KPI Dashboard
 * Run via WordPress admin or WP-CLI to populate database with test data
 *
 * Usage:
 * 1. Via WordPress admin: Dashboard → KPI Dashboard → System Info → Generate Dummy Data button
 * 2. Via WP-CLI: wp eval-file wp-content/plugins/kpi-dashboard/generate-dummy-data.php
 */

if (!defined('ABSPATH')) {
    // Allow direct execution for WP-CLI
    require_once dirname(__FILE__) . '/../../../wp-load.php';
}

class KPI_Dashboard_Dummy_Data_Generator {

    /**
     * Generate complete dummy data
     */
    public static function generate_all() {
        global $wpdb;

        echo "🚀 Starting dummy data generation...\n\n";

        // Check if already has data
        $user_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kpi_users WHERE id != 1");
        if ($user_count > 0) {
            echo "⚠️  Warning: Database already contains data. Continuing will add more data.\n";
            echo "To start fresh, deactivate and reactivate the plugin first.\n\n";
        }

        $stats = [
            'users' => 0,
            'positions' => 0,
            'kpis' => 0,
            'data_entries' => 0,
            'notifications' => 0,
        ];

        // Generate data
        $stats['users'] = self::generate_users();
        $stats['positions'] = self::generate_positions();
        $stats['kpis'] = self::generate_kpis();
        $stats['data_entries'] = self::generate_kpi_data();
        $stats['notifications'] = self::generate_notifications();

        echo "\n✅ Dummy data generation complete!\n\n";
        echo "📊 Summary:\n";
        echo "   Users: {$stats['users']}\n";
        echo "   Positions: {$stats['positions']}\n";
        echo "   KPIs: {$stats['kpis']}\n";
        echo "   Data Entries: {$stats['data_entries']}\n";
        echo "   Notifications: {$stats['notifications']}\n";
        echo "\n🎉 You can now login and explore the system!\n";
        echo "   URL: " . get_site_url() . "/kpi\n";
        echo "   Username: admin / Password: admin\n\n";

        return $stats;
    }

    /**
     * Generate dummy users
     */
    private static function generate_users() {
        echo "👥 Generating users...\n";

        $departments = KPI_Dashboard_Department_Model::get_all(['is_active' => 1]);
        if (empty($departments)) {
            echo "   ⚠️  No departments found. Skipping users.\n";
            return 0;
        }

        $users = [
            // Super Admins
            ['username' => 'john.doe', 'email' => 'john@mbdcorp.id', 'full_name' => 'John Doe', 'role' => 'super_admin', 'dept' => null],

            // Department Heads
            ['username' => 'sarah.sales', 'email' => 'sarah@mbdcorp.id', 'full_name' => 'Sarah Johnson', 'role' => 'dept_head', 'dept' => 'sales'],
            ['username' => 'mike.marketing', 'email' => 'mike@mbdcorp.id', 'full_name' => 'Mike Anderson', 'role' => 'dept_head', 'dept' => 'marketing'],
            ['username' => 'lisa.operations', 'email' => 'lisa@mbdcorp.id', 'full_name' => 'Lisa Williams', 'role' => 'dept_head', 'dept' => 'operations'],
            ['username' => 'david.hr', 'email' => 'david@mbdcorp.id', 'full_name' => 'David Brown', 'role' => 'dept_head', 'dept' => 'hr'],
            ['username' => 'emily.finance', 'email' => 'emily@mbdcorp.id', 'full_name' => 'Emily Davis', 'role' => 'dept_head', 'dept' => 'finance'],
            ['username' => 'alex.it', 'email' => 'alex@mbdcorp.id', 'full_name' => 'Alex Martinez', 'role' => 'dept_head', 'dept' => 'it'],

            // Managers
            ['username' => 'tom.sales', 'email' => 'tom@mbdcorp.id', 'full_name' => 'Tom Wilson', 'role' => 'manager', 'dept' => 'sales'],
            ['username' => 'anna.sales', 'email' => 'anna@mbdcorp.id', 'full_name' => 'Anna Taylor', 'role' => 'manager', 'dept' => 'sales'],
            ['username' => 'chris.marketing', 'email' => 'chris@mbdcorp.id', 'full_name' => 'Chris Moore', 'role' => 'manager', 'dept' => 'marketing'],
            ['username' => 'jessica.marketing', 'email' => 'jessica@mbdcorp.id', 'full_name' => 'Jessica Lee', 'role' => 'manager', 'dept' => 'marketing'],
            ['username' => 'robert.operations', 'email' => 'robert@mbdcorp.id', 'full_name' => 'Robert Garcia', 'role' => 'manager', 'dept' => 'operations'],
            ['username' => 'maria.hr', 'email' => 'maria@mbdcorp.id', 'full_name' => 'Maria Rodriguez', 'role' => 'manager', 'dept' => 'hr'],
            ['username' => 'james.finance', 'email' => 'james@mbdcorp.id', 'full_name' => 'James Thomas', 'role' => 'manager', 'dept' => 'finance'],
            ['username' => 'sophia.it', 'email' => 'sophia@mbdcorp.id', 'full_name' => 'Sophia Jackson', 'role' => 'manager', 'dept' => 'it'],

            // Staff
            ['username' => 'kevin.sales', 'email' => 'kevin@mbdcorp.id', 'full_name' => 'Kevin White', 'role' => 'staff', 'dept' => 'sales'],
            ['username' => 'laura.sales', 'email' => 'laura@mbdcorp.id', 'full_name' => 'Laura Harris', 'role' => 'staff', 'dept' => 'sales'],
            ['username' => 'daniel.marketing', 'email' => 'daniel@mbdcorp.id', 'full_name' => 'Daniel Martin', 'role' => 'staff', 'dept' => 'marketing'],
            ['username' => 'olivia.marketing', 'email' => 'olivia@mbdcorp.id', 'full_name' => 'Olivia Thompson', 'role' => 'staff', 'dept' => 'marketing'],
            ['username' => 'william.operations', 'email' => 'william@mbdcorp.id', 'full_name' => 'William Martinez', 'role' => 'staff', 'dept' => 'operations'],
            ['username' => 'emma.hr', 'email' => 'emma@mbdcorp.id', 'full_name' => 'Emma Robinson', 'role' => 'staff', 'dept' => 'hr'],
            ['username' => 'noah.finance', 'email' => 'noah@mbdcorp.id', 'full_name' => 'Noah Clark', 'role' => 'staff', 'dept' => 'finance'],
            ['username' => 'ava.it', 'email' => 'ava@mbdcorp.id', 'full_name' => 'Ava Lewis', 'role' => 'staff', 'dept' => 'it'],
        ];

        $dept_map = [];
        foreach ($departments as $dept) {
            $dept_map[$dept->slug] = $dept->id;
        }

        $count = 0;
        foreach ($users as $user_data) {
            $dept_id = $user_data['dept'] ? ($dept_map[$user_data['dept']] ?? null) : null;

            $user_id = KPI_Dashboard_User_Model::create([
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'password' => 'password123', // Default password for all test users
                'full_name' => $user_data['full_name'],
                'role' => $user_data['role'],
                'department_id' => $dept_id,
                'is_active' => 1,
            ]);

            if ($user_id) {
                $count++;

                // Assign department heads
                if ($user_data['role'] === 'dept_head' && $dept_id) {
                    KPI_Dashboard_Department_Model::assign_head($dept_id, $user_id, 1);
                }
            }
        }

        echo "   ✓ Created $count users\n";
        return $count;
    }

    /**
     * Generate positions
     */
    private static function generate_positions() {
        echo "💼 Generating positions...\n";

        $departments = KPI_Dashboard_Department_Model::get_all(['is_active' => 1]);

        $position_templates = [
            'sales' => [
                ['title' => 'Sales Director', 'level' => 'director'],
                ['title' => 'Sales Manager', 'level' => 'manager'],
                ['title' => 'Senior Sales Executive', 'level' => 'senior'],
                ['title' => 'Sales Executive', 'level' => 'junior'],
            ],
            'marketing' => [
                ['title' => 'Marketing Director', 'level' => 'director'],
                ['title' => 'Marketing Manager', 'level' => 'manager'],
                ['title' => 'Digital Marketing Specialist', 'level' => 'senior'],
                ['title' => 'Content Creator', 'level' => 'junior'],
            ],
            'operations' => [
                ['title' => 'Operations Director', 'level' => 'director'],
                ['title' => 'Operations Manager', 'level' => 'manager'],
                ['title' => 'Operations Coordinator', 'level' => 'senior'],
            ],
            'hr' => [
                ['title' => 'HR Director', 'level' => 'director'],
                ['title' => 'HR Manager', 'level' => 'manager'],
                ['title' => 'HR Specialist', 'level' => 'senior'],
                ['title' => 'HR Assistant', 'level' => 'junior'],
            ],
            'finance' => [
                ['title' => 'Finance Director', 'level' => 'director'],
                ['title' => 'Finance Manager', 'level' => 'manager'],
                ['title' => 'Accountant', 'level' => 'senior'],
            ],
            'it' => [
                ['title' => 'IT Director', 'level' => 'director'],
                ['title' => 'IT Manager', 'level' => 'manager'],
                ['title' => 'Senior Developer', 'level' => 'senior'],
                ['title' => 'Junior Developer', 'level' => 'junior'],
            ],
        ];

        $count = 0;
        foreach ($departments as $dept) {
            if (isset($position_templates[$dept->slug])) {
                foreach ($position_templates[$dept->slug] as $pos) {
                    $position_id = KPI_Dashboard_Position_Model::create([
                        'title' => $pos['title'],
                        'department_id' => $dept->id,
                        'level' => $pos['level'],
                        'is_active' => 1,
                    ]);

                    if ($position_id) {
                        $count++;
                    }
                }
            }
        }

        echo "   ✓ Created $count positions\n";
        return $count;
    }

    /**
     * Generate KPIs
     */
    private static function generate_kpis() {
        echo "📊 Generating KPIs...\n";

        $departments = KPI_Dashboard_Department_Model::get_all(['is_active' => 1]);

        $kpi_templates = [
            'sales' => [
                ['name' => 'Monthly Sales Revenue', 'metric' => 'number', 'unit' => 'IDR', 'target' => 1000000000, 'freq' => 'monthly'],
                ['name' => 'Conversion Rate', 'metric' => 'percentage', 'unit' => '%', 'target' => 15, 'freq' => 'monthly'],
                ['name' => 'New Customers Acquired', 'metric' => 'number', 'unit' => 'customers', 'target' => 50, 'freq' => 'monthly'],
                ['name' => 'Customer Satisfaction', 'metric' => 'ratio', 'unit' => '/5', 'target' => 4.5, 'freq' => 'monthly'],
            ],
            'marketing' => [
                ['name' => 'Website Traffic', 'metric' => 'number', 'unit' => 'visits', 'target' => 100000, 'freq' => 'monthly'],
                ['name' => 'Lead Generation', 'metric' => 'number', 'unit' => 'leads', 'target' => 500, 'freq' => 'monthly'],
                ['name' => 'Social Media Engagement', 'metric' => 'percentage', 'unit' => '%', 'target' => 10, 'freq' => 'monthly'],
                ['name' => 'Email Open Rate', 'metric' => 'percentage', 'unit' => '%', 'target' => 25, 'freq' => 'monthly'],
            ],
            'operations' => [
                ['name' => 'Production Output', 'metric' => 'number', 'unit' => 'units', 'target' => 10000, 'freq' => 'monthly'],
                ['name' => 'Quality Defect Rate', 'metric' => 'percentage', 'unit' => '%', 'target' => 2, 'freq' => 'monthly'],
                ['name' => 'On-Time Delivery', 'metric' => 'percentage', 'unit' => '%', 'target' => 95, 'freq' => 'monthly'],
            ],
            'hr' => [
                ['name' => 'Employee Satisfaction', 'metric' => 'ratio', 'unit' => '/5', 'target' => 4.0, 'freq' => 'quarterly'],
                ['name' => 'Employee Turnover Rate', 'metric' => 'percentage', 'unit' => '%', 'target' => 5, 'freq' => 'monthly'],
                ['name' => 'Training Hours per Employee', 'metric' => 'number', 'unit' => 'hours', 'target' => 8, 'freq' => 'monthly'],
            ],
            'finance' => [
                ['name' => 'Monthly Revenue', 'metric' => 'number', 'unit' => 'IDR', 'target' => 5000000000, 'freq' => 'monthly'],
                ['name' => 'Profit Margin', 'metric' => 'percentage', 'unit' => '%', 'target' => 20, 'freq' => 'monthly'],
                ['name' => 'Operating Expenses', 'metric' => 'number', 'unit' => 'IDR', 'target' => 3000000000, 'freq' => 'monthly'],
            ],
            'it' => [
                ['name' => 'System Uptime', 'metric' => 'percentage', 'unit' => '%', 'target' => 99.9, 'freq' => 'monthly'],
                ['name' => 'Bug Resolution Time', 'metric' => 'number', 'unit' => 'hours', 'target' => 24, 'freq' => 'monthly'],
                ['name' => 'User Support Tickets Resolved', 'metric' => 'percentage', 'unit' => '%', 'target' => 90, 'freq' => 'monthly'],
            ],
        ];

        $dept_map = [];
        foreach ($departments as $dept) {
            $dept_map[$dept->slug] = $dept->id;
        }

        $count = 0;
        foreach ($kpi_templates as $dept_slug => $kpis) {
            if (!isset($dept_map[$dept_slug])) continue;

            $dept_id = $dept_map[$dept_slug];

            foreach ($kpis as $kpi_data) {
                $kpi_id = KPI_Dashboard_KPI_Model::create([
                    'name' => $kpi_data['name'],
                    'slug' => sanitize_title($kpi_data['name']),
                    'category' => $dept_slug,
                    'type' => 'department',
                    'metric_type' => $kpi_data['metric'],
                    'unit' => $kpi_data['unit'],
                    'input_frequency' => $kpi_data['freq'],
                    'target_value' => $kpi_data['target'],
                    'target_type' => 'minimum',
                    'chart_type' => 'line',
                    'is_active' => 1,
                    'created_by' => 1,
                ]);

                if ($kpi_id) {
                    // Assign to department
                    KPI_Dashboard_KPI_Model::assign($kpi_id, 'department', $dept_id, null, 1);
                    $count++;
                }
            }
        }

        echo "   ✓ Created $count KPIs\n";
        return $count;
    }

    /**
     * Generate KPI data entries
     */
    private static function generate_kpi_data() {
        echo "📈 Generating KPI data entries...\n";

        $kpis = KPI_Dashboard_KPI_Model::get_all(['is_active' => 1, 'limit' => 100]);
        $departments = KPI_Dashboard_Department_Model::get_all(['is_active' => 1]);

        if (empty($kpis)) {
            echo "   ⚠️  No KPIs found. Skipping data entries.\n";
            return 0;
        }

        $count = 0;
        $months = 6; // Generate data for last 6 months

        foreach ($kpis as $kpi) {
            // Get KPI assignments
            $assignments = KPI_Dashboard_KPI_Model::get_assignments($kpi->id);

            foreach ($assignments as $assignment) {
                if ($assignment->assigned_to_type !== 'department') continue;

                $dept_id = $assignment->assigned_to_id;

                // Generate data for last N months
                for ($i = $months - 1; $i >= 0; $i--) {
                    $period_start = date('Y-m-01', strtotime("-$i months"));
                    $period_end = date('Y-m-t', strtotime("-$i months"));

                    // Generate realistic value (80% to 120% of target)
                    $target = $kpi->target_value ?? 100;
                    $variance = ($target * 0.4); // 40% variance
                    $value = $target + (rand(-$variance, $variance));
                    $value = max(0, $value); // No negative values

                    // Round based on metric type
                    if ($kpi->metric_type === 'percentage' || $kpi->metric_type === 'ratio') {
                        $value = round($value, 2);
                    } else {
                        $value = round($value);
                    }

                    // Random status (most approved, some pending)
                    $status = rand(1, 10) > 2 ? 'approved' : 'pending';

                    $entry_id = KPI_Dashboard_KPI_Data_Model::create([
                        'kpi_id' => $kpi->id,
                        'department_id' => $dept_id,
                        'period_start' => $period_start,
                        'period_end' => $period_end,
                        'value' => $value,
                        'unit' => $kpi->unit,
                        'status' => $status,
                        'notes' => 'Auto-generated dummy data',
                        'submitted_by' => 1,
                        'submitted_at' => date('Y-m-d H:i:s', strtotime($period_end . ' +1 day')),
                    ]);

                    if ($entry_id && $status === 'approved') {
                        // Set reviewed info
                        global $wpdb;
                        $wpdb->update(
                            $wpdb->prefix . 'kpi_data',
                            [
                                'reviewed_by' => 1,
                                'reviewed_at' => date('Y-m-d H:i:s', strtotime($period_end . ' +2 days')),
                            ],
                            ['id' => $entry_id]
                        );
                    }

                    if ($entry_id) {
                        $count++;
                    }
                }
            }
        }

        echo "   ✓ Created $count data entries\n";
        return $count;
    }

    /**
     * Generate notifications
     */
    private static function generate_notifications() {
        echo "🔔 Generating notifications...\n";

        $users = KPI_Dashboard_User_Model::get_all(['is_active' => 1, 'limit' => 50]);

        if (empty($users)) {
            echo "   ⚠️  No users found. Skipping notifications.\n";
            return 0;
        }

        $notification_templates = [
            ['type' => 'info', 'severity' => 'info', 'title' => 'Welcome to KPI Dashboard', 'message' => 'Start tracking your KPIs today!'],
            ['type' => 'reminder', 'severity' => 'info', 'title' => 'Weekly Data Entry Reminder', 'message' => 'Please submit your KPI data for this week.'],
            ['type' => 'success', 'severity' => 'success', 'title' => 'Target Achieved!', 'message' => 'Congratulations! You achieved your monthly target.'],
            ['type' => 'warning', 'severity' => 'warning', 'title' => 'Below Target', 'message' => 'Your KPI is currently below the target threshold.'],
        ];

        $count = 0;
        foreach ($users as $user) {
            // Create 2-3 notifications per user
            $num_notifications = rand(2, 3);

            for ($i = 0; $i < $num_notifications; $i++) {
                $template = $notification_templates[array_rand($notification_templates)];

                $notif_id = KPI_Dashboard_Notification_Model::create([
                    'user_id' => $user->id,
                    'type' => $template['type'],
                    'severity' => $template['severity'],
                    'title' => $template['title'],
                    'message' => $template['message'],
                    'is_read' => rand(0, 1), // Random read/unread
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                ]);

                if ($notif_id) {
                    $count++;
                }
            }
        }

        echo "   ✓ Created $count notifications\n";
        return $count;
    }

    /**
     * Clear all dummy data (reset to fresh state)
     */
    public static function clear_all() {
        global $wpdb;

        echo "🗑️  Clearing all data (keeping structure)...\n";

        // Delete in reverse order to respect foreign keys
        $tables = [
            'kpi_comments',
            'kpi_report_history',
            'kpi_scheduled_reports',
            'kpi_user_notification_prefs',
            'kpi_alert_rules',
            'kpi_notifications',
            'kpi_audit_logs',
            'kpi_data',
            'kpi_assignments',
            'kpi_definitions',
            'kpi_department_heads',
            'kpi_positions',
            'kpi_sessions',
        ];

        foreach ($tables as $table) {
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$table}");
        }

        // Delete users except admin (id=1)
        $wpdb->query("DELETE FROM {$wpdb->prefix}kpi_users WHERE id != 1");

        // Keep default departments but clear stats
        // Departments are kept as they are created on plugin activation

        echo "   ✓ Data cleared successfully\n";
        echo "   ℹ️  Default admin user and departments preserved\n\n";
    }
}

// Auto-run if called directly (for WP-CLI)
if (php_sapi_name() === 'cli' || defined('WP_CLI')) {
    KPI_Dashboard_Dummy_Data_Generator::generate_all();
}
