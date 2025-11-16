<?php
/**
 * Permissions System
 * Role-based access control (RBAC)
 */
class KPI_Dashboard_Permissions {

    /**
     * Permission matrix
     */
    private static $permissions = [
        'super_admin' => [
            'users' => ['create', 'read', 'update', 'delete', 'manage_all'],
            'departments' => ['create', 'read', 'update', 'delete'],
            'positions' => ['create', 'read', 'update', 'delete'],
            'kpis' => ['create', 'read', 'update', 'delete'],
            'data' => ['create', 'read', 'update', 'delete', 'approve', 'reject'],
            'reports' => ['create', 'read', 'export', 'schedule'],
            'analytics' => ['read', 'all_departments'],
            'notifications' => ['create', 'read', 'manage'],
            'settings' => ['read', 'update'],
            'audit_logs' => ['read', 'all'],
        ],
        'dept_head' => [
            'users' => ['create', 'read', 'update', 'own_department'],
            'departments' => ['read', 'update_own'],
            'positions' => ['create', 'read', 'update', 'own_department'],
            'kpis' => ['create', 'read', 'update', 'own_department'],
            'data' => ['create', 'read', 'update', 'approve', 'reject', 'own_department'],
            'reports' => ['create', 'read', 'export', 'own_department'],
            'analytics' => ['read', 'own_department'],
            'notifications' => ['read', 'own'],
            'audit_logs' => ['read', 'own_department'],
        ],
        'manager' => [
            'users' => ['read', 'own_department'],
            'departments' => ['read'],
            'positions' => ['read'],
            'kpis' => ['read', 'own_department'],
            'data' => ['create', 'read', 'update_pending', 'own_department'],
            'reports' => ['read', 'export', 'own_department'],
            'analytics' => ['read', 'own_department'],
            'notifications' => ['read', 'own'],
        ],
        'staff' => [
            'users' => ['read', 'own'],
            'departments' => ['read', 'own'],
            'positions' => ['read', 'own'],
            'kpis' => ['read', 'assigned'],
            'data' => ['read', 'own'],
            'reports' => ['read', 'own'],
            'analytics' => ['read', 'own'],
            'notifications' => ['read', 'own'],
        ],
    ];

    /**
     * Check if user has permission
     *
     * @param object $user User object
     * @param string $resource Resource name (e.g., 'users', 'kpis')
     * @param string $action Action name (e.g., 'create', 'update')
     * @return bool
     */
    public static function can($user, $resource, $action) {
        if (!$user || !isset($user->role)) {
            return false;
        }

        $role = $user->role;

        if (!isset(self::$permissions[$role])) {
            return false;
        }

        $role_permissions = self::$permissions[$role];

        if (!isset($role_permissions[$resource])) {
            return false;
        }

        return in_array($action, $role_permissions[$resource]);
    }

    /**
     * Check if user can access department data
     *
     * @param object $user
     * @param int $department_id
     * @return bool
     */
    public static function can_access_department($user, $department_id) {
        if (!$user) {
            return false;
        }

        // Super admin can access all departments
        if ($user->role === 'super_admin') {
            return true;
        }

        // Dept head can access assigned departments
        if ($user->role === 'dept_head') {
            return self::is_department_head($user->id, $department_id);
        }

        // Manager and staff can only access their own department
        if (in_array($user->role, ['manager', 'staff'])) {
            return $user->department_id == $department_id;
        }

        return false;
    }

    /**
     * Check if user is department head
     */
    public static function is_department_head($user_id, $department_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_department_heads';

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND department_id = %d",
            $user_id,
            $department_id
        ));

        return $exists > 0;
    }

    /**
     * Get accessible departments for user
     *
     * @param object $user
     * @return array Department IDs
     */
    public static function get_accessible_departments($user) {
        global $wpdb;

        if (!$user) {
            return [];
        }

        // Super admin can access all
        if ($user->role === 'super_admin') {
            $table = $wpdb->prefix . 'kpi_departments';
            $results = $wpdb->get_col("SELECT id FROM $table WHERE is_active = 1");
            return array_map('intval', $results);
        }

        // Dept head can access assigned departments
        if ($user->role === 'dept_head') {
            $table = $wpdb->prefix . 'kpi_department_heads';
            $results = $wpdb->get_col($wpdb->prepare(
                "SELECT department_id FROM $table WHERE user_id = %d",
                $user->id
            ));
            return array_map('intval', $results);
        }

        // Manager and staff can only access their own department
        if (in_array($user->role, ['manager', 'staff']) && $user->department_id) {
            return [(int)$user->department_id];
        }

        return [];
    }

    /**
     * Check if user can approve data
     */
    public static function can_approve_data($user, $data) {
        if (!$user) {
            return false;
        }

        // Super admin can approve all
        if ($user->role === 'super_admin') {
            return true;
        }

        // Dept head can approve data from their departments
        if ($user->role === 'dept_head') {
            return self::is_department_head($user->id, $data->department_id);
        }

        return false;
    }

    /**
     * Check if user can view data entry
     */
    public static function can_view_data($user, $data) {
        if (!$user || !$data) {
            return false;
        }

        // Super admin can view all
        if ($user->role === 'super_admin') {
            return true;
        }

        // Dept head can view data from their departments
        if ($user->role === 'dept_head') {
            return self::can_access_department($user, $data->department_id);
        }

        // Manager and staff can view data from their department
        if (in_array($user->role, ['manager', 'staff'])) {
            return $user->department_id == $data->department_id;
        }

        return false;
    }

    /**
     * Check if user can edit data
     */
    public static function can_edit_data($user, $data) {
        if (!$user) {
            return false;
        }

        // Super admin can edit all
        if ($user->role === 'super_admin') {
            return true;
        }

        // Dept head can edit data from their departments
        if ($user->role === 'dept_head' && self::is_department_head($user->id, $data->department_id)) {
            return true;
        }

        // Manager can edit own pending data
        if ($user->role === 'manager' && $data->status === 'pending' && $data->submitted_by == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete data
     */
    public static function can_delete_data($user, $data) {
        if (!$user) {
            return false;
        }

        // Only super admin can delete
        return $user->role === 'super_admin';
    }

    /**
     * Filter departments query based on user permissions
     */
    public static function filter_departments_query($user, $query) {
        if (!$user) {
            return $query . " AND 1=0"; // No access
        }

        if ($user->role === 'super_admin') {
            return $query; // Access all
        }

        $accessible = self::get_accessible_departments($user);

        if (empty($accessible)) {
            return $query . " AND 1=0";
        }

        // Sanitize IDs to prevent SQL injection
        $ids = array_map('intval', $accessible);
        $ids = implode(',', $ids);
        return $query . " AND id IN ($ids)";
    }

    /**
     * Check if user can view another user's profile
     */
    public static function can_view_user($user, $target_user) {
        if (!$user || !$target_user) {
            return false;
        }

        // Can always view own profile
        if ($user->id == $target_user->id) {
            return true;
        }

        // Super admin can view all users
        if ($user->role === 'super_admin') {
            return true;
        }

        // Dept head and manager can view users in their departments
        if (in_array($user->role, ['dept_head', 'manager'])) {
            return self::can_access_department($user, $target_user->department_id);
        }

        // Staff can only view their own profile
        return false;
    }

    /**
     * Check if user can manage users
     */
    public static function can_manage_users($user, $target_user = null) {
        if (!$user) {
            return false;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role === 'dept_head' && $target_user) {
            // Dept head can manage users in their departments
            return self::can_access_department($user, $target_user->department_id);
        }

        return false;
    }

    /**
     * Check if user can view KPI
     */
    public static function can_view_kpi($user, $kpi) {
        if (!$user || !$kpi) {
            return false;
        }

        // Super admin can view all KPIs
        if ($user->role === 'super_admin') {
            return true;
        }

        // Dept head can view KPIs from their departments
        if ($user->role === 'dept_head' && isset($kpi->department_id)) {
            return self::can_access_department($user, $kpi->department_id);
        }

        // Manager and staff can view KPIs from their department or assigned to them
        if (in_array($user->role, ['manager', 'staff'])) {
            // Check if KPI is from their department
            if (isset($kpi->department_id) && $user->department_id == $kpi->department_id) {
                return true;
            }
            // KPIs without department restriction are viewable by all
            if (!isset($kpi->department_id) || empty($kpi->department_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can view department
     */
    public static function can_view_department($user, $department) {
        if (!$user || !$department) {
            return false;
        }

        // Super admin can view all departments
        if ($user->role === 'super_admin') {
            return true;
        }

        // All authenticated users can view basic department info
        // But sensitive stats should be filtered separately
        return true;
    }

    /**
     * Get permission summary for user (for frontend)
     */
    public static function get_user_permissions($user) {
        if (!$user || !isset($user->role)) {
            return [];
        }

        return self::$permissions[$user->role] ?? [];
    }
}
