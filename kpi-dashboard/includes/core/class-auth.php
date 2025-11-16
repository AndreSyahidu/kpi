<?php
/**
 * Authentication System
 * Handles login, logout, JWT token generation and validation
 */
class KPI_Dashboard_Auth {

    private static $secret_key = null;

    /**
     * Initialize authentication
     */
    public static function init() {
        self::$secret_key = self::get_secret_key();
    }

    /**
     * Get or generate secret key for JWT
     */
    private static function get_secret_key() {
        $key = get_option('kpi_dashboard_jwt_secret');

        if (!$key) {
            $key = bin2hex(random_bytes(32));
            update_option('kpi_dashboard_jwt_secret', $key);
        }

        return $key;
    }

    /**
     * Authenticate user with username/email and password
     *
     * @param string $username Username or email
     * @param string $password Password
     * @return array|WP_Error User data with tokens or error
     */
    public static function authenticate($username, $password) {
        global $wpdb;

        // SECURITY: Check rate limit BEFORE any database queries to prevent timing attacks
        $rate_limit = KPI_Dashboard_Rate_Limiter::check_combined('login', $username, 5, 900); // 5 attempts per 15 minutes

        if (!$rate_limit['allowed']) {
            $minutes = ceil($rate_limit['retry_after'] / 60);
            return new WP_Error(
                'too_many_attempts',
                sprintf(
                    __('Too many login attempts. Please try again in %d minutes.', 'kpi-dashboard'),
                    $minutes
                ),
                ['retry_after' => $rate_limit['retry_after']]
            );
        }

        $table = $wpdb->prefix . 'kpi_users';

        // Find user by username or email
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE (username = %s OR email = %s) AND is_active = 1",
            $username,
            $username
        ));

        if (!$user) {
            self::log_failed_attempt($username);
            return new WP_Error('invalid_credentials', __('Invalid username or password', 'kpi-dashboard'));
        }

        // Verify password
        if (!password_verify($password, $user->password_hash)) {
            self::log_failed_attempt($username);
            return new WP_Error('invalid_credentials', __('Invalid username or password', 'kpi-dashboard'));
        }

        // Update last login
        $wpdb->update($table, [
            'last_login' => current_time('mysql'),
        ], ['id' => $user->id]);

        // Generate tokens
        $access_token = self::generate_access_token($user);
        $refresh_token = self::generate_refresh_token($user);

        // Store session
        KPI_Dashboard_Session::create($user->id, $access_token, $refresh_token);

        // Log successful login
        KPI_Dashboard_Audit_Log::log('login', 'user', $user->id, null, [
            'username' => $user->username,
            'success' => true,
        ]);

        // Clear failed attempts
        self::clear_failed_attempts($username);

        // Prepare user data (remove sensitive info)
        $user_data = self::prepare_user_data($user);

        // Return WITHOUT 'success' field to avoid double nesting when wrapped by API success()
        return [
            'user' => $user_data,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in' => 900, // 15 minutes
        ];
    }

    /**
     * Logout user
     *
     * @param string $token Access token
     * @return bool
     */
    public static function logout($token) {
        KPI_Dashboard_Session::destroy($token);

        // Log logout
        $user = self::get_current_user();
        if ($user) {
            KPI_Dashboard_Audit_Log::log('logout', 'user', $user->id);
        }

        return true;
    }

    /**
     * Refresh access token using refresh token
     *
     * @param string $refresh_token
     * @return array|WP_Error
     */
    public static function refresh_token($refresh_token) {
        global $wpdb;

        $session = KPI_Dashboard_Session::get_by_refresh_token($refresh_token);

        if (!$session) {
            return new WP_Error('invalid_token', __('Invalid refresh token', 'kpi-dashboard'));
        }

        // Get user
        $table = $wpdb->prefix . 'kpi_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $session->user_id
        ));

        if (!$user) {
            return new WP_Error('user_not_found', __('User not found', 'kpi-dashboard'));
        }

        // Generate new tokens
        $new_access_token = self::generate_access_token($user);
        $new_refresh_token = self::generate_refresh_token($user);

        // Update session
        KPI_Dashboard_Session::update($session->id, $new_access_token, $new_refresh_token);

        // Return WITHOUT 'success' field to avoid double nesting when wrapped by API success()
        return [
            'access_token' => $new_access_token,
            'refresh_token' => $new_refresh_token,
            'expires_in' => 900,
        ];
    }

    /**
     * Generate JWT access token
     *
     * @param object $user
     * @return string
     */
    private static function generate_access_token($user) {
        $payload = [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'department_id' => $user->department_id,
            'iat' => time(),
            'exp' => time() + 900, // 15 minutes
        ];

        return self::encode_jwt($payload);
    }

    /**
     * Generate refresh token
     *
     * @param object $user
     * @return string
     */
    private static function generate_refresh_token($user) {
        $payload = [
            'user_id' => $user->id,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60), // 7 days
        ];

        return self::encode_jwt($payload);
    }

    /**
     * Simple JWT encoding
     */
    private static function encode_jwt($payload) {
        self::init();

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);

        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64url_encode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Simple JWT decoding
     */
    private static function decode_jwt($jwt) {
        self::init();

        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return false;
        }

        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        // Verify signature
        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64url_encode($signature);

        if ($base64UrlSignature !== $signatureProvided) {
            return false;
        }

        $payload = json_decode($payload);

        // Check expiration
        if (isset($payload->exp) && $payload->exp < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Base64 URL encode
     */
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Validate access token and return user
     *
     * @param string $token
     * @return object|false
     */
    public static function validate_token($token) {
        $payload = self::decode_jwt($token);

        if (!$payload) {
            return false;
        }

        // Verify session exists
        if (!KPI_Dashboard_Session::exists($token)) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $payload->user_id
        ));

        return $user ?: false;
    }

    /**
     * Get current authenticated user
     *
     * @return object|null
     */
    public static function get_current_user() {
        $token = self::get_token_from_request();

        if (!$token) {
            return null;
        }

        return self::validate_token($token);
    }

    /**
     * Get token from request headers
     *
     * @return string|null
     */
    private static function get_token_from_request() {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Fallback to $_SERVER
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Prepare user data for response (remove sensitive fields)
     */
    private static function prepare_user_data($user) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
            'last_login' => $user->last_login,
        ];
    }

    /**
     * Log failed login attempt
     */
    private static function log_failed_attempt($username) {
        // Record attempt using the new rate limiter
        KPI_Dashboard_Rate_Limiter::record_combined('login', $username);

        // Get current attempt count for audit log
        $rate_limit = KPI_Dashboard_Rate_Limiter::check_combined('login', $username, 5, 900);

        // Log to audit
        KPI_Dashboard_Audit_Log::log('login_failed', 'user', null, null, [
            'username' => $username,
            'attempts' => $rate_limit['count'],
            'ip' => KPI_Dashboard_Rate_Limiter::get_client_ip(),
        ]);
    }

    /**
     * Clear failed login attempts (called after successful login)
     */
    private static function clear_failed_attempts($username) {
        KPI_Dashboard_Rate_Limiter::clear_combined('login', $username);
    }

    /**
     * Change password
     */
    public static function change_password($user_id, $old_password, $new_password) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $user_id));

        if (!$user) {
            return new WP_Error('user_not_found', __('User not found', 'kpi-dashboard'));
        }

        if (!password_verify($old_password, $user->password_hash)) {
            return new WP_Error('invalid_password', __('Current password is incorrect', 'kpi-dashboard'));
        }

        $wpdb->update($table, [
            'password_hash' => password_hash($new_password, PASSWORD_BCRYPT),
            'updated_at' => current_time('mysql'),
        ], ['id' => $user_id]);

        KPI_Dashboard_Audit_Log::log('password_changed', 'user', $user_id);

        return true;
    }

    /**
     * Reset password (for forgot password)
     */
    public static function reset_password($email) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email = %s AND is_active = 1", $email));

        if (!$user) {
            // Don't reveal if email exists
            return true;
        }

        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_hash = password_hash($reset_token, PASSWORD_BCRYPT);

        // Store token (valid for 1 hour)
        set_transient('kpi_password_reset_' . $user->id, $reset_hash, HOUR_IN_SECONDS);

        // Send email
        $reset_link = get_site_url() . '/kpi/reset-password?token=' . $reset_token . '&user=' . $user->id;

        KPI_Dashboard_Email_Service::send_password_reset($user->email, $user->full_name, $reset_link);

        KPI_Dashboard_Audit_Log::log('password_reset_requested', 'user', $user->id);

        return true;
    }

    /**
     * Verify reset token and set new password
     */
    public static function verify_reset_token($user_id, $token, $new_password) {
        $stored_hash = get_transient('kpi_password_reset_' . $user_id);

        if (!$stored_hash || !password_verify($token, $stored_hash)) {
            return new WP_Error('invalid_token', __('Invalid or expired reset token', 'kpi-dashboard'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kpi_users';

        $wpdb->update($table, [
            'password_hash' => password_hash($new_password, PASSWORD_BCRYPT),
            'updated_at' => current_time('mysql'),
        ], ['id' => $user_id]);

        delete_transient('kpi_password_reset_' . $user_id);

        KPI_Dashboard_Audit_Log::log('password_reset_completed', 'user', $user_id);

        return true;
    }
}
