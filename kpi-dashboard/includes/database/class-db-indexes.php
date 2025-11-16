<?php
/**
 * Database Index Optimization
 * Adds additional indexes for better query performance
 */
class KPI_Dashboard_DB_Indexes {

    /**
     * Add performance optimization indexes
     * These are additional indexes beyond the base schema for common query patterns
     */
    public static function add_optimization_indexes() {
        global $wpdb;

        $indexes_added = [];
        $indexes_failed = [];

        // Suppress errors temporarily
        $wpdb->hide_errors();

        try {
            // KPI Data table - Optimize approval queue queries
            $table_kpi_data = $wpdb->prefix . 'kpi_data';

            // Index for: Find pending entries by department (approval queue)
            $result = $wpdb->query("
                CREATE INDEX idx_status_dept_submitted
                ON $table_kpi_data (status, department_id, submitted_at)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_status_dept_submitted on kpi_data';
            }

            // Index for: Find user's submitted entries
            $result = $wpdb->query("
                CREATE INDEX idx_submitted_by_status
                ON $table_kpi_data (submitted_by, status, submitted_at DESC)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_submitted_by_status on kpi_data';
            }

            // Index for: Period-based reporting with department filter
            $result = $wpdb->query("
                CREATE INDEX idx_dept_period_status
                ON $table_kpi_data (department_id, period_start, period_end, status)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_dept_period_status on kpi_data';
            }

            // Users table - Optimize role + department queries
            $table_users = $wpdb->prefix . 'kpi_users';

            // Index for: Find active users by role and department
            $result = $wpdb->query("
                CREATE INDEX idx_role_dept_active
                ON $table_users (role, department_id, is_active)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_role_dept_active on kpi_users';
            }

            // Notifications table - Optimize unread notifications query
            $table_notifications = $wpdb->prefix . 'kpi_notifications';

            // Index for: Get unread notifications ordered by date
            $result = $wpdb->query("
                CREATE INDEX idx_user_unread_date
                ON $table_notifications (user_id, is_read, created_at DESC)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_user_unread_date on kpi_notifications';
            }

            // Audit logs - Optimize entity lookup with date range
            $table_audit_logs = $wpdb->prefix . 'kpi_audit_logs';

            // Index for: Get entity history with date filtering
            $result = $wpdb->query("
                CREATE INDEX idx_entity_action_date
                ON $table_audit_logs (entity_type, entity_id, action, created_at DESC)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_entity_action_date on kpi_audit_logs';
            }

            // KPI Definitions - Optimize category + type filtering
            $table_kpi_defs = $wpdb->prefix . 'kpi_definitions';

            // Index for: Filter KPIs by category, type, and active status
            $result = $wpdb->query("
                CREATE INDEX idx_category_type_active
                ON $table_kpi_defs (category, type, is_active)
            ");
            if ($result !== false) {
                $indexes_added[] = 'idx_category_type_active on kpi_definitions';
            }

            // Analyze tables for query optimization (MySQL only)
            if (self::is_mysql()) {
                $tables = [
                    $table_kpi_data,
                    $table_users,
                    $table_notifications,
                    $table_audit_logs,
                    $table_kpi_defs,
                ];

                foreach ($tables as $table) {
                    $wpdb->query("ANALYZE TABLE $table");
                }
            }

        } catch (Exception $e) {
            $indexes_failed[] = $e->getMessage();
        }

        // Re-enable error display
        $wpdb->show_errors();

        return [
            'success' => !empty($indexes_added),
            'indexes_added' => $indexes_added,
            'indexes_failed' => $indexes_failed,
            'count' => count($indexes_added),
        ];
    }

    /**
     * Check if database is MySQL
     */
    private static function is_mysql() {
        global $wpdb;
        return strpos($wpdb->db_version(), 'MariaDB') !== false ||
               strpos($wpdb->db_version(), 'MySQL') !== false;
    }

    /**
     * Remove optimization indexes (for rollback)
     */
    public static function remove_optimization_indexes() {
        global $wpdb;
        $wpdb->hide_errors();

        $indexes_to_drop = [
            $wpdb->prefix . 'kpi_data' => [
                'idx_status_dept_submitted',
                'idx_submitted_by_status',
                'idx_dept_period_status',
            ],
            $wpdb->prefix . 'kpi_users' => [
                'idx_role_dept_active',
            ],
            $wpdb->prefix . 'kpi_notifications' => [
                'idx_user_unread_date',
            ],
            $wpdb->prefix . 'kpi_audit_logs' => [
                'idx_entity_action_date',
            ],
            $wpdb->prefix . 'kpi_definitions' => [
                'idx_category_type_active',
            ],
        ];

        $dropped = [];

        foreach ($indexes_to_drop as $table => $indexes) {
            foreach ($indexes as $index) {
                $wpdb->query("DROP INDEX $index ON $table");
                $dropped[] = "$index on $table";
            }
        }

        $wpdb->show_errors();

        return [
            'success' => true,
            'indexes_dropped' => $dropped,
        ];
    }

    /**
     * Check if optimization indexes exist
     */
    public static function check_indexes() {
        global $wpdb;

        $table_kpi_data = $wpdb->prefix . 'kpi_data';

        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_kpi_data");

        $existing = [];
        foreach ($indexes as $index) {
            $existing[] = $index->Key_name;
        }

        $expected = [
            'idx_status_dept_submitted',
            'idx_submitted_by_status',
            'idx_dept_period_status',
        ];

        $missing = array_diff($expected, $existing);

        return [
            'all_present' => empty($missing),
            'existing' => $existing,
            'missing' => $missing,
        ];
    }
}
