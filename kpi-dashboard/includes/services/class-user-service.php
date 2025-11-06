<?php
/**
 * User Service - Business logic for user operations
 */
class KPI_Dashboard_User_Service {

    /**
     * Create new user
     */
    public static function create($data, $created_by = null) {
        // Validate
        $validation = KPI_Dashboard_Validator::validate_user($data, false);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        // Sanitize
        $data = KPI_Dashboard_Sanitizer::user_data($data);

        // Check username exists
        if (KPI_Dashboard_User_Model::username_exists($data['username'])) {
            return new WP_Error('username_exists', __('Username already exists', 'kpi-dashboard'));
        }

        // Check email exists
        if (KPI_Dashboard_User_Model::email_exists($data['email'])) {
            return new WP_Error('email_exists', __('Email already exists', 'kpi-dashboard'));
        }

        // Add created_by
        $data['created_by'] = $created_by;

        // Create user
        $user_id = KPI_Dashboard_User_Model::create($data);

        if (!$user_id) {
            return new WP_Error('create_failed', __('Failed to create user', 'kpi-dashboard'));
        }

        // Audit log
        KPI_Dashboard_Audit_Log::log('create', 'user', $user_id, null, $data);

        // Send welcome email
        $user = KPI_Dashboard_User_Model::get($user_id);
        KPI_Dashboard_Email_Service::send_welcome_email($user);

        // Clear cache
        KPI_Dashboard_Cache::delete_pattern('users_');

        return $user;
    }

    /**
     * Update user
     */
    public static function update($user_id, $data, $updated_by = null) {
        $user = KPI_Dashboard_User_Model::get($user_id);

        if (!$user) {
            return new WP_Error('user_not_found', __('User not found', 'kpi-dashboard'));
        }

        // Validate
        $validation = KPI_Dashboard_Validator::validate_user($data, true);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        // Sanitize
        $data = KPI_Dashboard_Sanitizer::user_data($data);

        // Check username exists (excluding current user)
        if (isset($data['username']) && KPI_Dashboard_User_Model::username_exists($data['username'], $user_id)) {
            return new WP_Error('username_exists', __('Username already exists', 'kpi-dashboard'));
        }

        // Check email exists (excluding current user)
        if (isset($data['email']) && KPI_Dashboard_User_Model::email_exists($data['email'], $user_id)) {
            return new WP_Error('email_exists', __('Email already exists', 'kpi-dashboard'));
        }

        // Store old values for audit
        $before = clone $user;

        // Update user
        $result = KPI_Dashboard_User_Model::update($user_id, $data);

        if ($result === false) {
            return new WP_Error('update_failed', __('Failed to update user', 'kpi-dashboard'));
        }

        // Audit log
        KPI_Dashboard_Audit_Log::log('update', 'user', $user_id, $before, $data);

        // Clear cache
        KPI_Dashboard_Cache::delete_pattern('users_');

        return KPI_Dashboard_User_Model::get($user_id);
    }

    /**
     * Delete user (soft delete)
     */
    public static function delete($user_id, $deleted_by = null) {
        $user = KPI_Dashboard_User_Model::get($user_id);

        if (!$user) {
            return new WP_Error('user_not_found', __('User not found', 'kpi-dashboard'));
        }

        // Don't allow deleting last super admin
        if ($user->role === 'super_admin') {
            $super_admin_count = KPI_Dashboard_User_Model::count(['role' => 'super_admin', 'is_active' => 1]);
            if ($super_admin_count <= 1) {
                return new WP_Error('last_admin', __('Cannot delete the last super admin', 'kpi-dashboard'));
            }
        }

        // Deactivate user
        $result = KPI_Dashboard_User_Model::delete($user_id);

        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete user', 'kpi-dashboard'));
        }

        // Destroy all sessions
        KPI_Dashboard_Session::destroy_all($user_id);

        // Audit log
        KPI_Dashboard_Audit_Log::log('delete', 'user', $user_id, $user, null);

        // Clear cache
        KPI_Dashboard_Cache::delete_pattern('users_');

        return true;
    }

    /**
     * Get user with department and position info
     */
    public static function get_with_relations($user_id) {
        $user = KPI_Dashboard_User_Model::get($user_id);

        if (!$user) {
            return null;
        }

        // Get department
        if ($user->department_id) {
            $user->department = KPI_Dashboard_Department_Model::get($user->department_id);
        }

        // Get position
        if ($user->position_id) {
            $user->position = KPI_Dashboard_Position_Model::get($user->position_id);
        }

        // Check if department head
        $user->is_department_head = false;
        if ($user->role === 'dept_head' && $user->department_id) {
            $user->is_department_head = KPI_Dashboard_Permissions::is_department_head($user_id, $user->department_id);
        }

        // Get permissions
        $user->permissions = KPI_Dashboard_Permissions::get_user_permissions($user);

        return $user;
    }

    /**
     * Get users list with filters and pagination
     */
    public static function get_list($args = [], $current_user = null) {
        // Filter based on user permissions
        if ($current_user) {
            if ($current_user->role === 'dept_head') {
                // Dept heads can only see users in their departments
                $accessible_depts = KPI_Dashboard_Permissions::get_accessible_departments($current_user);
                if (empty($accessible_depts)) {
                    return ['items' => [], 'total' => 0, 'pages' => 0];
                }
                // Filter by accessible departments
                if (!isset($args['department_id'])) {
                    // Would need to modify model to support IN clause for multiple departments
                }
            } elseif (in_array($current_user->role, ['manager', 'staff'])) {
                // Managers and staff can only see users in their department
                $args['department_id'] = $current_user->department_id;
            }
        }

        $defaults = [
            'limit' => 50,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        $users = KPI_Dashboard_User_Model::get_all($args);
        $total = KPI_Dashboard_User_Model::count($args);

        // Sanitize for response
        $items = array_map([KPI_Dashboard_User_Model::class, 'sanitize_for_response'], $users);

        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $args['limit']),
            'current_page' => floor($args['offset'] / $args['limit']) + 1,
        ];
    }

    /**
     * Bulk import users from CSV
     */
    public static function bulk_import($csv_data, $created_by = null) {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($csv_data as $row) {
            $user = self::create($row, $created_by);

            if (is_wp_error($user)) {
                $results['failed'][] = [
                    'data' => $row,
                    'error' => $user->get_error_message(),
                ];
            } else {
                $results['success'][] = $user;
            }
        }

        return $results;
    }

    /**
     * Assign user to department
     */
    public static function assign_to_department($user_id, $department_id, $position_id = null) {
        $user = KPI_Dashboard_User_Model::get($user_id);

        if (!$user) {
            return new WP_Error('user_not_found', __('User not found', 'kpi-dashboard'));
        }

        $department = KPI_Dashboard_Department_Model::get($department_id);

        if (!$department) {
            return new WP_Error('department_not_found', __('Department not found', 'kpi-dashboard'));
        }

        $data = ['department_id' => $department_id];

        if ($position_id) {
            $position = KPI_Dashboard_Position_Model::get($position_id);
            if (!$position || $position->department_id != $department_id) {
                return new WP_Error('invalid_position', __('Position does not belong to this department', 'kpi-dashboard'));
            }
            $data['position_id'] = $position_id;
        }

        $result = KPI_Dashboard_User_Model::update($user_id, $data);

        if (!$result) {
            return new WP_Error('update_failed', __('Failed to assign user', 'kpi-dashboard'));
        }

        // Audit log
        KPI_Dashboard_Audit_Log::log('assign_department', 'user', $user_id, null, $data);

        return KPI_Dashboard_User_Model::get($user_id);
    }
}
