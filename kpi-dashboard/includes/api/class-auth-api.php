<?php
/**
 * Authentication API Endpoints
 */
class KPI_Dashboard_Auth_API extends KPI_Dashboard_API_Base {

    public function register_routes() {
        // Login
        register_rest_route($this->namespace, '/auth/login', [
            'methods' => 'POST',
            'callback' => [$this, 'login'],
            'permission_callback' => '__return_true',
            'args' => [
                'username' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'password' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'remember' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        // Logout
        register_rest_route($this->namespace, '/auth/logout', [
            'methods' => 'POST',
            'callback' => [$this, 'logout'],
            'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);

        // Refresh token
        register_rest_route($this->namespace, '/auth/refresh', [
            'methods' => 'POST',
            'callback' => [$this, 'refresh_token'],
            'permission_callback' => '__return_true',
            'args' => [
                'refresh_token' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);

        // Get current user
        register_rest_route($this->namespace, '/auth/me', [
            'methods' => 'GET',
            'callback' => [$this, 'get_me'],
            'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);

        // Change password
        register_rest_route($this->namespace, '/auth/change-password', [
            'methods' => 'POST',
            'callback' => [$this, 'change_password'],
            'permission_callback' => [$this, 'permission_check_authenticated'],
            'args' => [
                'old_password' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'new_password' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);

        // Forgot password
        register_rest_route($this->namespace, '/auth/forgot-password', [
            'methods' => 'POST',
            'callback' => [$this, 'forgot_password'],
            'permission_callback' => '__return_true',
            'args' => [
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
        ]);

        // Reset password
        register_rest_route($this->namespace, '/auth/reset-password', [
            'methods' => 'POST',
            'callback' => [$this, 'reset_password'],
            'permission_callback' => '__return_true',
            'args' => [
                'user_id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'token' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'new_password' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);
    }

    /**
     * Login endpoint
     */
    public function login($request) {
        $username = $request->get_param('username');
        $password = $request->get_param('password');

        $result = KPI_Dashboard_Auth::authenticate($username, $password);

        if (is_wp_error($result)) {
            return $this->handle_error($result);
        }

        return $this->success($result, __('Login successful', 'kpi-dashboard'));
    }

    /**
     * Logout endpoint
     */
    public function logout($request) {
        $auth_header = $request->get_header('authorization');

        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $token = $matches[1];
            KPI_Dashboard_Auth::logout($token);
        }

        return $this->success(null, __('Logout successful', 'kpi-dashboard'));
    }

    /**
     * Refresh token endpoint
     */
    public function refresh_token($request) {
        $refresh_token = $request->get_param('refresh_token');

        $result = KPI_Dashboard_Auth::refresh_token($refresh_token);

        if (is_wp_error($result)) {
            return $this->handle_error($result);
        }

        return $this->success($result, __('Token refreshed', 'kpi-dashboard'));
    }

    /**
     * Get current user info
     */
    public function get_me($request) {
        $user = $this->get_current_user($request);

        if (!$user) {
            return $this->error(__('Not authenticated', 'kpi-dashboard'), 'not_authenticated', 401);
        }

        $user_data = KPI_Dashboard_User_Service::get_with_relations($user->id);

        // Sanitize
        $user_data = KPI_Dashboard_User_Model::sanitize_for_response($user_data);

        return $this->success($user_data);
    }

    /**
     * Change password endpoint
     */
    public function change_password($request) {
        $user = $this->get_current_user($request);

        $old_password = $request->get_param('old_password');
        $new_password = $request->get_param('new_password');

        $result = KPI_Dashboard_Auth::change_password($user->id, $old_password, $new_password);

        if (is_wp_error($result)) {
            return $this->handle_error($result);
        }

        return $this->success(null, __('Password changed successfully', 'kpi-dashboard'));
    }

    /**
     * Forgot password endpoint
     */
    public function forgot_password($request) {
        $email = $request->get_param('email');

        $result = KPI_Dashboard_Auth::reset_password($email);

        if (is_wp_error($result)) {
            return $this->handle_error($result);
        }

        return $this->success(null, __('Password reset email sent', 'kpi-dashboard'));
    }

    /**
     * Reset password endpoint
     */
    public function reset_password($request) {
        $user_id = $request->get_param('user_id');
        $token = $request->get_param('token');
        $new_password = $request->get_param('new_password');

        $result = KPI_Dashboard_Auth::verify_reset_token($user_id, $token, $new_password);

        if (is_wp_error($result)) {
            return $this->handle_error($result);
        }

        return $this->success(null, __('Password reset successful', 'kpi-dashboard'));
    }
}
