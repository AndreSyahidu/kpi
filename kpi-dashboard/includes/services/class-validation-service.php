<?php
/**
 * Validation Service - Additional validation helpers
 */
class KPI_Dashboard_Validation_Service {

    /**
     * Validate user has permission for action
     */
    public static function validate_permission($user, $action, $resource, $entity = null) {
        if (!$user) {
            return new WP_Error('not_authenticated', __('Not authenticated', 'kpi-dashboard'));
        }

        if (!KPI_Dashboard_Permissions::can($user, $resource, $action)) {
            return new WP_Error('permission_denied', __('You do not have permission to perform this action', 'kpi-dashboard'));
        }

        // Additional entity-level checks
        if ($entity) {
            if ($resource === 'data' && !KPI_Dashboard_Permissions::can_edit_data($user, $entity)) {
                return new WP_Error('permission_denied', __('You do not have permission to edit this data', 'kpi-dashboard'));
            }

            if (isset($entity->department_id) && !KPI_Dashboard_Permissions::can_access_department($user, $entity->department_id)) {
                return new WP_Error('permission_denied', __('You do not have access to this department', 'kpi-dashboard'));
            }
        }

        return true;
    }

    /**
     * Validate date period
     */
    public static function validate_period($start, $end) {
        if (!KPI_Dashboard_Validator::validate_date($start)) {
            return new WP_Error('invalid_start_date', __('Invalid start date format', 'kpi-dashboard'));
        }

        if (!KPI_Dashboard_Validator::validate_date($end)) {
            return new WP_Error('invalid_end_date', __('Invalid end date format', 'kpi-dashboard'));
        }

        if (!KPI_Dashboard_Validator::validate_date_range($start, $end)) {
            return new WP_Error('invalid_date_range', __('Start date must be before end date', 'kpi-dashboard'));
        }

        // Check if period is not too far in the future
        $end_date = new DateTime($end);
        $now = new DateTime();
        if ($end_date > $now->modify('+1 year')) {
            return new WP_Error('invalid_future_date', __('End date cannot be more than 1 year in the future', 'kpi-dashboard'));
        }

        return true;
    }

    /**
     * Validate pagination parameters
     */
    public static function validate_pagination($page, $per_page) {
        $page = (int) $page;
        $per_page = (int) $per_page;

        if ($page < 1) {
            $page = 1;
        }

        if ($per_page < 1 || $per_page > 100) {
            $per_page = 50;
        }

        return [
            'page' => $page,
            'per_page' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'limit' => $per_page,
        ];
    }

    /**
     * Validate search query
     */
    public static function validate_search($query) {
        if (empty($query)) {
            return '';
        }

        // Sanitize
        $query = sanitize_text_field($query);

        // Minimum length
        if (strlen($query) < 2) {
            return new WP_Error('search_too_short', __('Search query must be at least 2 characters', 'kpi-dashboard'));
        }

        // Maximum length
        if (strlen($query) > 100) {
            return new WP_Error('search_too_long', __('Search query cannot exceed 100 characters', 'kpi-dashboard'));
        }

        return $query;
    }

    /**
     * Validate sort parameters
     */
    public static function validate_sort($orderby, $order, $allowed_fields) {
        if (!in_array($orderby, $allowed_fields)) {
            return new WP_Error('invalid_orderby', __('Invalid sort field', 'kpi-dashboard'));
        }

        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        return ['orderby' => $orderby, 'order' => $order];
    }
}
