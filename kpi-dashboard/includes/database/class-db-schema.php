<?php
/**
 * Database Schema Manager
 * Creates and manages all custom database tables for KPI Dashboard
 */
class KPI_Dashboard_DB_Schema {

    /**
     * Create all database tables
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Users table
        $table_users = $wpdb->prefix . 'kpi_users';
        $sql_users = "CREATE TABLE $table_users (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) UNSIGNED NULL,
            username varchar(60) NOT NULL,
            email varchar(100) NOT NULL,
            password_hash varchar(255) NOT NULL,
            full_name varchar(200) NOT NULL,
            avatar_url varchar(500) NULL,
            role enum('super_admin','dept_head','manager','staff') NOT NULL DEFAULT 'staff',
            department_id bigint(20) UNSIGNED NULL,
            position_id bigint(20) UNSIGNED NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            last_login datetime NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY username (username),
            UNIQUE KEY email (email),
            KEY wp_user_id (wp_user_id),
            KEY department_id (department_id),
            KEY position_id (position_id),
            KEY is_active (is_active),
            KEY role (role)
        ) $charset_collate;";

        // Departments table
        $table_departments = $wpdb->prefix . 'kpi_departments';
        $sql_departments = "CREATE TABLE $table_departments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            description text NULL,
            parent_id bigint(20) UNSIGNED NULL,
            color_code varchar(7) NOT NULL DEFAULT '#1565C0',
            icon_class varchar(100) NULL,
            logo_url varchar(500) NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY parent_id (parent_id),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        // Positions table
        $table_positions = $wpdb->prefix . 'kpi_positions';
        $sql_positions = "CREATE TABLE $table_positions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(200) NOT NULL,
            department_id bigint(20) UNSIGNED NOT NULL,
            level enum('junior','senior','lead','manager','director') NOT NULL DEFAULT 'junior',
            description text NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY department_id (department_id),
            KEY is_active (is_active),
            KEY level (level)
        ) $charset_collate;";

        // Department Heads assignment table
        $table_dept_heads = $wpdb->prefix . 'kpi_department_heads';
        $sql_dept_heads = "CREATE TABLE $table_dept_heads (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            department_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            assigned_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            assigned_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY dept_user (department_id, user_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // KPI Definitions table
        $table_kpi_defs = $wpdb->prefix . 'kpi_definitions';
        $sql_kpi_defs = "CREATE TABLE $table_kpi_defs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            description text NULL,
            category varchar(100) NOT NULL DEFAULT 'custom',
            type enum('department','position','personal') NOT NULL DEFAULT 'department',
            metric_type enum('number','percentage','ratio','custom','boolean') NOT NULL DEFAULT 'number',
            unit varchar(50) NULL,
            input_frequency enum('daily','weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
            calculation_method enum('sum','average','count','formula','auto') NOT NULL DEFAULT 'sum',
            formula text NULL,
            target_value decimal(20,4) NULL,
            target_type enum('minimum','maximum','exact','range') NOT NULL DEFAULT 'minimum',
            target_range_min decimal(20,4) NULL,
            target_range_max decimal(20,4) NULL,
            weight int(11) NOT NULL DEFAULT 1,
            chart_type varchar(50) NOT NULL DEFAULT 'line',
            color_scheme text NULL,
            decimal_places tinyint(2) NOT NULL DEFAULT 2,
            validation_rules text NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY category (category),
            KEY type (type),
            KEY is_active (is_active)
        ) $charset_collate;";

        // KPI Assignments table
        $table_kpi_assignments = $wpdb->prefix . 'kpi_assignments';
        $sql_kpi_assignments = "CREATE TABLE $table_kpi_assignments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            kpi_id bigint(20) UNSIGNED NOT NULL,
            assigned_to_type enum('department','position','user') NOT NULL,
            assigned_to_id bigint(20) UNSIGNED NOT NULL,
            target_override decimal(20,4) NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            assigned_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            assigned_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY kpi_id (kpi_id),
            KEY assigned_to (assigned_to_type, assigned_to_id),
            KEY is_active (is_active)
        ) $charset_collate;";

        // KPI Data entries table
        $table_kpi_data = $wpdb->prefix . 'kpi_data';
        $sql_kpi_data = "CREATE TABLE $table_kpi_data (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            kpi_id bigint(20) UNSIGNED NOT NULL,
            department_id bigint(20) UNSIGNED NOT NULL,
            position_id bigint(20) UNSIGNED NULL,
            user_id bigint(20) UNSIGNED NULL,
            period_start date NOT NULL,
            period_end date NOT NULL,
            value decimal(20,4) NOT NULL,
            unit varchar(50) NULL,
            status enum('draft','pending','approved','rejected') NOT NULL DEFAULT 'pending',
            notes text NULL,
            attachments text NULL,
            submitted_by bigint(20) UNSIGNED NOT NULL,
            submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_by bigint(20) UNSIGNED NULL,
            reviewed_at datetime NULL,
            review_notes text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY kpi_id (kpi_id),
            KEY department_id (department_id),
            KEY position_id (position_id),
            KEY user_id (user_id),
            KEY period_dates (period_start, period_end),
            KEY status (status),
            KEY composite_idx (kpi_id, department_id, period_start),
            KEY is_deleted (is_deleted)
        ) $charset_collate;";

        // Audit Logs table
        $table_audit_logs = $wpdb->prefix . 'kpi_audit_logs';
        $sql_audit_logs = "CREATE TABLE $table_audit_logs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NULL,
            action varchar(50) NOT NULL,
            entity_type varchar(50) NOT NULL,
            entity_id bigint(20) UNSIGNED NULL,
            before_value longtext NULL,
            after_value longtext NULL,
            reason text NULL,
            ip_address varchar(45) NULL,
            user_agent varchar(500) NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY entity (entity_type, entity_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Notifications table
        $table_notifications = $wpdb->prefix . 'kpi_notifications';
        $sql_notifications = "CREATE TABLE $table_notifications (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            type enum('reminder','approval','alert','info','success','warning','error') NOT NULL DEFAULT 'info',
            severity enum('info','warning','critical','success') NOT NULL DEFAULT 'info',
            title varchar(200) NOT NULL,
            message text NOT NULL,
            action_url varchar(500) NULL,
            related_entity_type varchar(50) NULL,
            related_entity_id bigint(20) UNSIGNED NULL,
            is_read tinyint(1) NOT NULL DEFAULT 0,
            read_at datetime NULL,
            sent_via_email tinyint(1) NOT NULL DEFAULT 0,
            email_sent_at datetime NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_read (user_id, is_read),
            KEY type (type),
            KEY severity (severity),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Alert Rules table
        $table_alert_rules = $wpdb->prefix . 'kpi_alert_rules';
        $sql_alert_rules = "CREATE TABLE $table_alert_rules (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            kpi_id bigint(20) UNSIGNED NULL,
            condition_type enum('below_target','above_target','declining_trend','no_data','custom') NOT NULL,
            threshold_value decimal(20,4) NULL,
            threshold_operator varchar(10) NULL,
            consecutive_periods int(11) NULL,
            severity enum('warning','critical') NOT NULL DEFAULT 'warning',
            recipients text NOT NULL,
            notification_channels text NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY kpi_id (kpi_id),
            KEY is_active (is_active)
        ) $charset_collate;";

        // User Notification Preferences table
        $table_notif_prefs = $wpdb->prefix . 'kpi_user_notification_prefs';
        $sql_notif_prefs = "CREATE TABLE $table_notif_prefs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            notification_type varchar(50) NOT NULL,
            enabled tinyint(1) NOT NULL DEFAULT 1,
            channel enum('email','in_app','push') NOT NULL DEFAULT 'in_app',
            frequency enum('realtime','hourly','daily','weekly') NOT NULL DEFAULT 'realtime',
            quiet_hours_start time NULL,
            quiet_hours_end time NULL,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_type_channel (user_id, notification_type, channel)
        ) $charset_collate;";

        // Scheduled Reports table
        $table_scheduled_reports = $wpdb->prefix . 'kpi_scheduled_reports';
        $sql_scheduled_reports = "CREATE TABLE $table_scheduled_reports (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            template_type varchar(50) NOT NULL,
            config longtext NOT NULL,
            frequency enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
            schedule_day int(11) NULL,
            schedule_time time NULL,
            recipients text NOT NULL,
            format enum('pdf','excel','csv') NOT NULL DEFAULT 'pdf',
            is_active tinyint(1) NOT NULL DEFAULT 1,
            last_run_at datetime NULL,
            next_run_at datetime NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED NULL,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_active (is_active),
            KEY next_run_at (next_run_at)
        ) $charset_collate;";

        // Report History table
        $table_report_history = $wpdb->prefix . 'kpi_report_history';
        $sql_report_history = "CREATE TABLE $table_report_history (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            scheduled_report_id bigint(20) UNSIGNED NULL,
            generated_by bigint(20) UNSIGNED NOT NULL,
            report_type varchar(50) NOT NULL,
            config longtext NULL,
            file_path varchar(500) NOT NULL,
            file_size bigint(20) UNSIGNED NOT NULL,
            generated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY scheduled_report_id (scheduled_report_id),
            KEY generated_by (generated_by),
            KEY generated_at (generated_at)
        ) $charset_collate;";

        // Comments table
        $table_comments = $wpdb->prefix . 'kpi_comments';
        $sql_comments = "CREATE TABLE $table_comments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_id bigint(20) UNSIGNED NULL,
            entity_type varchar(50) NOT NULL,
            entity_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            comment_text text NOT NULL,
            mentions text NULL,
            attachments text NULL,
            status enum('open','resolved') NOT NULL DEFAULT 'open',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY parent_id (parent_id),
            KEY entity (entity_type, entity_id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        // Settings table
        $table_settings = $wpdb->prefix . 'kpi_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext NOT NULL,
            setting_type varchar(50) NOT NULL DEFAULT 'general',
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by bigint(20) UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY setting_type (setting_type)
        ) $charset_collate;";

        // Sessions table
        $table_sessions = $wpdb->prefix . 'kpi_sessions';
        $sql_sessions = "CREATE TABLE $table_sessions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            token_hash varchar(255) NOT NULL,
            refresh_token_hash varchar(255) NULL,
            ip_address varchar(45) NULL,
            user_agent varchar(500) NULL,
            expires_at datetime NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY token_hash (token_hash),
            KEY user_id (user_id),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        // Execute all table creation queries
        dbDelta($sql_users);
        dbDelta($sql_departments);
        dbDelta($sql_positions);
        dbDelta($sql_dept_heads);
        dbDelta($sql_kpi_defs);
        dbDelta($sql_kpi_assignments);
        dbDelta($sql_kpi_data);
        dbDelta($sql_audit_logs);
        dbDelta($sql_notifications);
        dbDelta($sql_alert_rules);
        dbDelta($sql_notif_prefs);
        dbDelta($sql_scheduled_reports);
        dbDelta($sql_report_history);
        dbDelta($sql_comments);
        dbDelta($sql_settings);
        dbDelta($sql_sessions);

        // Insert default super admin user
        self::create_default_admin();

        // Insert default KPI categories
        self::insert_default_data();

        update_option('kpi_dashboard_db_version', KPI_DASHBOARD_VERSION);
    }

    /**
     * Create default super admin user
     */
    private static function create_default_admin() {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $exists = $wpdb->get_var("SELECT id FROM $table WHERE username = 'admin' LIMIT 1");

        if (!$exists) {
            $wpdb->insert($table, [
                'username' => 'admin',
                'email' => get_option('admin_email'),
                'password_hash' => password_hash('admin', PASSWORD_BCRYPT),
                'full_name' => 'Super Administrator',
                'role' => 'super_admin',
                'is_active' => 1,
                'created_at' => current_time('mysql'),
            ]);
        }
    }

    /**
     * Insert default data (departments, KPI templates, etc.)
     */
    private static function insert_default_data() {
        global $wpdb;

        // Default departments
        $dept_table = $wpdb->prefix . 'kpi_departments';
        $dept_exists = $wpdb->get_var("SELECT COUNT(*) FROM $dept_table");

        if ($dept_exists == 0) {
            $default_departments = [
                ['name' => 'Sales', 'slug' => 'sales', 'color_code' => '#4CAF50', 'description' => 'Sales and Revenue Department'],
                ['name' => 'Marketing', 'slug' => 'marketing', 'color_code' => '#2196F3', 'description' => 'Marketing and Leads Department'],
                ['name' => 'Operations', 'slug' => 'operations', 'color_code' => '#FF9800', 'description' => 'Operations and Production Department'],
                ['name' => 'Human Resources', 'slug' => 'hr', 'color_code' => '#9C27B0', 'description' => 'Human Resources Department'],
                ['name' => 'Finance', 'slug' => 'finance', 'color_code' => '#F44336', 'description' => 'Finance and Accounting Department'],
                ['name' => 'IT', 'slug' => 'it', 'color_code' => '#607D8B', 'description' => 'Information Technology Department'],
            ];

            foreach ($default_departments as $index => $dept) {
                $wpdb->insert($dept_table, array_merge($dept, [
                    'sort_order' => $index + 1,
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                ]));
            }
        }
    }

    /**
     * Drop all custom tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = [
            'kpi_sessions',
            'kpi_settings',
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
            'kpi_departments',
            'kpi_users',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
    }
}
