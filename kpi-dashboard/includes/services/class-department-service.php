<?php
/**
 * Department Service
 */
class KPI_Dashboard_Department_Service {

    public static function create($data, $created_by = null) {
        $validation = KPI_Dashboard_Validator::validate_department($data);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        $data = KPI_Dashboard_Sanitizer::department_data($data);
        $data['created_by'] = $created_by;

        $dept_id = KPI_Dashboard_Department_Model::create($data);

        if (!$dept_id) {
            return new WP_Error('create_failed', __('Failed to create department', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('create', 'department', $dept_id, null, $data);
        KPI_Dashboard_Cache::clear_departments();

        return KPI_Dashboard_Department_Model::get($dept_id);
    }

    public static function update($dept_id, $data) {
        $dept = KPI_Dashboard_Department_Model::get($dept_id);

        if (!$dept) {
            return new WP_Error('not_found', __('Department not found', 'kpi-dashboard'));
        }

        $validation = KPI_Dashboard_Validator::validate_department($data);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        $data = KPI_Dashboard_Sanitizer::department_data($data);
        $before = clone $dept;

        $result = KPI_Dashboard_Department_Model::update($dept_id, $data);

        if ($result === false) {
            return new WP_Error('update_failed', __('Failed to update department', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('update', 'department', $dept_id, $before, $data);
        KPI_Dashboard_Cache::clear_departments();

        return KPI_Dashboard_Department_Model::get($dept_id);
    }

    public static function delete($dept_id) {
        $dept = KPI_Dashboard_Department_Model::get($dept_id);

        if (!$dept) {
            return new WP_Error('not_found', __('Department not found', 'kpi-dashboard'));
        }

        // Check if department has users
        $users = KPI_Dashboard_User_Model::get_by_department($dept_id, false);
        if (!empty($users)) {
            return new WP_Error('has_users', __('Cannot delete department with users. Please reassign users first.', 'kpi-dashboard'));
        }

        // Check for child departments
        $children = KPI_Dashboard_Department_Model::get_all(['parent_id' => $dept_id, 'is_active' => null]);
        if (!empty($children)) {
            return new WP_Error('has_children', __('Cannot delete department with sub-departments', 'kpi-dashboard'));
        }

        $result = KPI_Dashboard_Department_Model::delete($dept_id);

        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete department', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('delete', 'department', $dept_id, $dept, null);
        KPI_Dashboard_Cache::clear_departments();

        return true;
    }

    public static function get_with_stats($dept_id) {
        $dept = KPI_Dashboard_Department_Model::get($dept_id);

        if (!$dept) {
            return null;
        }

        // Get stats
        $dept->stats = [
            'total_users' => count(KPI_Dashboard_User_Model::get_by_department($dept_id, true)),
            'total_positions' => count(KPI_Dashboard_Position_Model::get_by_department($dept_id, true)),
            'total_kpis' => count(KPI_Dashboard_KPI_Model::get_kpis_for_department($dept_id)),
            'heads' => KPI_Dashboard_Department_Model::get_heads($dept_id),
        ];

        return $dept;
    }

    public static function assign_head($dept_id, $user_id, $assigned_by = null) {
        $dept = KPI_Dashboard_Department_Model::get($dept_id);
        if (!$dept) {
            return new WP_Error('dept_not_found', __('Department not found', 'kpi-dashboard'));
        }

        $user = KPI_Dashboard_User_Model::get($user_id);
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found', 'kpi-dashboard'));
        }

        // Update user role to dept_head if not already
        if ($user->role !== 'dept_head' && $user->role !== 'super_admin') {
            KPI_Dashboard_User_Model::update($user_id, ['role' => 'dept_head']);
        }

        $result = KPI_Dashboard_Department_Model::assign_head($dept_id, $user_id, $assigned_by);

        if (!$result) {
            return new WP_Error('assign_failed', __('Failed to assign department head', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('assign_head', 'department', $dept_id, null, ['user_id' => $user_id]);

        // Send notification
        KPI_Dashboard_Notification_Service::create([
            'user_id' => $user_id,
            'type' => 'info',
            'severity' => 'info',
            'title' => __('Assigned as Department Head', 'kpi-dashboard'),
            'message' => sprintf(__('You have been assigned as head of %s department', 'kpi-dashboard'), $dept->name),
        ]);

        return true;
    }

    public static function remove_head($dept_id, $user_id) {
        $result = KPI_Dashboard_Department_Model::remove_head($dept_id, $user_id);

        if (!$result) {
            return new WP_Error('remove_failed', __('Failed to remove department head', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('remove_head', 'department', $dept_id, null, ['user_id' => $user_id]);

        return true;
    }
}
