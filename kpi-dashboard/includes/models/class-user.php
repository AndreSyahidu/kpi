<?php
/**
 * User Model
 */
class KPI_Dashboard_User_Model {

    /**
     * Get user by ID
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Get all users with filters
     */
    public static function get_all($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $defaults = [
            'role' => null,
            'department_id' => null,
            'is_active' => null,
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['1=1'];
        $params = [];

        if ($args['role']) {
            $where[] = 'role = %s';
            $params[] = $args['role'];
        }

        if ($args['department_id']) {
            $where[] = 'department_id = %d';
            $params[] = $args['department_id'];
        }

        if ($args['is_active'] !== null) {
            $where[] = 'is_active = %d';
            $params[] = $args['is_active'];
        }

        if ($args['search']) {
            $where[] = '(username LIKE %s OR email LIKE %s OR full_name LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_clause = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

        if ($params) {
            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby LIMIT %d OFFSET %d",
                array_merge($params, [$args['limit'], $args['offset']])
            );
        } else {
            $query = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby LIMIT {$args['limit']} OFFSET {$args['offset']}";
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get total count with filters
     */
    public static function count($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $where = ['1=1'];
        $params = [];

        if (isset($args['role'])) {
            $where[] = 'role = %s';
            $params[] = $args['role'];
        }

        if (isset($args['department_id'])) {
            $where[] = 'department_id = %d';
            $params[] = $args['department_id'];
        }

        if (isset($args['is_active'])) {
            $where[] = 'is_active = %d';
            $params[] = $args['is_active'];
        }

        if (isset($args['search']) && $args['search']) {
            $where[] = '(username LIKE %s OR email LIKE %s OR full_name LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_clause = implode(' AND ', $where);

        if ($params) {
            return (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE $where_clause",
                $params
            ));
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_clause");
    }

    /**
     * Create new user
     */
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $defaults = [
            'wp_user_id' => null,
            'username' => '',
            'email' => '',
            'password_hash' => '',
            'full_name' => '',
            'avatar_url' => null,
            'role' => 'staff',
            'department_id' => null,
            'position_id' => null,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'created_by' => null,
        ];

        $data = wp_parse_args($data, $defaults);

        // Hash password if provided as plain text
        if (isset($data['password']) && !isset($data['password_hash'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        $result = $wpdb->insert($table, $data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update user
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $data['updated_at'] = current_time('mysql');

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        return $wpdb->update($table, $data, ['id' => $id]);
    }

    /**
     * Delete user (soft delete - deactivate)
     */
    public static function delete($id) {
        return self::update($id, ['is_active' => 0]);
    }

    /**
     * Hard delete user
     */
    public static function hard_delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        return $wpdb->delete($table, ['id' => $id]);
    }

    /**
     * Check if username exists
     */
    public static function username_exists($username, $exclude_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        if ($exclude_id) {
            return (bool) $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE username = %s AND id != %d",
                $username,
                $exclude_id
            ));
        }

        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE username = %s",
            $username
        ));
    }

    /**
     * Check if email exists
     */
    public static function email_exists($email, $exclude_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        if ($exclude_id) {
            return (bool) $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE email = %s AND id != %d",
                $email,
                $exclude_id
            ));
        }

        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE email = %s",
            $email
        ));
    }

    /**
     * Get users by department
     */
    public static function get_by_department($department_id, $active_only = true) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE department_id = %d",
            $department_id
        );

        if ($active_only) {
            $query .= " AND is_active = 1";
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get users by role
     */
    public static function get_by_role($role, $active_only = true) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE role = %s",
            $role
        );

        if ($active_only) {
            $query .= " AND is_active = 1";
        }

        return $wpdb->get_results($query);
    }

    /**
     * Sanitize user data for response (remove sensitive fields)
     */
    public static function sanitize_for_response($user) {
        if (!$user) {
            return null;
        }

        $safe_fields = [
            'id', 'username', 'email', 'full_name', 'avatar_url',
            'role', 'department_id', 'position_id', 'is_active',
            'last_login', 'created_at', 'updated_at'
        ];

        $sanitized = new stdClass();
        foreach ($safe_fields as $field) {
            if (isset($user->$field)) {
                $sanitized->$field = $user->$field;
            }
        }

        return $sanitized;
    }
}
