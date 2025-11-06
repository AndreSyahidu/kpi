<?php
/**
 * Base API Class
 * Common functionality for all API endpoints
 */
class KPI_Dashboard_API_Base extends WP_REST_Controller {

    protected $namespace = 'kpi/v1';

    /**
     * Get current authenticated user
     */
    protected function get_current_user($request) {
        $auth_header = $request->get_header('authorization');

        if (!$auth_header) {
            return null;
        }

        // Extract token
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $token = $matches[1];
            return KPI_Dashboard_Auth::validate_token($token);
        }

        return null;
    }

    /**
     * Check if user is authenticated
     */
    protected function is_authenticated($request) {
        return $this->get_current_user($request) !== null;
    }

    /**
     * Permission callback for authenticated users
     */
    public function permission_check_authenticated($request) {
        if (!$this->is_authenticated($request)) {
            return new WP_Error(
                'not_authenticated',
                __('You must be logged in to access this resource', 'kpi-dashboard'),
                ['status' => 401]
            );
        }

        return true;
    }

    /**
     * Permission callback for super admins only
     */
    public function permission_check_super_admin($request) {
        $user = $this->get_current_user($request);

        if (!$user) {
            return new WP_Error('not_authenticated', __('Not authenticated', 'kpi-dashboard'), ['status' => 401]);
        }

        if ($user->role !== 'super_admin') {
            return new WP_Error('forbidden', __('Access denied', 'kpi-dashboard'), ['status' => 403]);
        }

        return true;
    }

    /**
     * Permission callback for dept heads and admins
     */
    public function permission_check_dept_head($request) {
        $user = $this->get_current_user($request);

        if (!$user) {
            return new WP_Error('not_authenticated', __('Not authenticated', 'kpi-dashboard'), ['status' => 401]);
        }

        if (!in_array($user->role, ['super_admin', 'dept_head'])) {
            return new WP_Error('forbidden', __('Access denied', 'kpi-dashboard'), ['status' => 403]);
        }

        return true;
    }

    /**
     * Send success response
     */
    protected function success($data = null, $message = '', $status = 200) {
        return new WP_REST_Response([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Send error response
     */
    protected function error($message, $code = 'error', $status = 400, $data = null) {
        return new WP_REST_Response([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Handle WP_Error
     */
    protected function handle_error($error) {
        if (!is_wp_error($error)) {
            return $error;
        }

        $status = 400;
        if (isset($error->error_data[$error->get_error_code()]['status'])) {
            $status = $error->error_data[$error->get_error_code()]['status'];
        }

        return $this->error(
            $error->get_error_message(),
            $error->get_error_code(),
            $status,
            $error->get_error_data()
        );
    }

    /**
     * Get pagination params from request
     */
    protected function get_pagination_params($request) {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 50;

        $validation = KPI_Dashboard_Validation_Service::validate_pagination($page, $per_page);

        return $validation;
    }

    /**
     * Get common query params
     */
    protected function get_collection_params() {
        return [
            'page' => [
                'description' => __('Current page', 'kpi-dashboard'),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1,
            ],
            'per_page' => [
                'description' => __('Items per page', 'kpi-dashboard'),
                'type' => 'integer',
                'default' => 50,
                'minimum' => 1,
                'maximum' => 100,
            ],
            'search' => [
                'description' => __('Search query', 'kpi-dashboard'),
                'type' => 'string',
            ],
            'orderby' => [
                'description' => __('Order by field', 'kpi-dashboard'),
                'type' => 'string',
                'default' => 'created_at',
            ],
            'order' => [
                'description' => __('Order direction', 'kpi-dashboard'),
                'type' => 'string',
                'default' => 'desc',
                'enum' => ['asc', 'desc'],
            ],
        ];
    }

    /**
     * Log API request
     */
    protected function log_request($request, $response = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            KPI_Dashboard_Logger::debug(sprintf(
                'API Request: %s %s',
                $request->get_method(),
                $request->get_route()
            ), [
                'params' => $request->get_params(),
                'user' => $this->get_current_user($request)->id ?? null,
            ]);
        }
    }
}
