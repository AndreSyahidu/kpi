<?php
/**
 * Session Management
 * Handles user sessions and token storage
 */
class KPI_Dashboard_Session {

    /**
     * Create new session
     */
    public static function create($user_id, $access_token, $refresh_token) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        $token_hash = hash('sha256', $access_token);
        $refresh_hash = hash('sha256', $refresh_token);

        $wpdb->insert($table, [
            'user_id' => $user_id,
            'token_hash' => $token_hash,
            'refresh_token_hash' => $refresh_hash,
            'ip_address' => self::get_client_ip(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'expires_at' => date('Y-m-d H:i:s', time() + 900), // 15 minutes
            'created_at' => current_time('mysql'),
        ]);

        return $wpdb->insert_id;
    }

    /**
     * Get session by access token
     */
    public static function get($token) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        $token_hash = hash('sha256', $token);

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE token_hash = %s AND expires_at > NOW()",
            $token_hash
        ));
    }

    /**
     * Get session by refresh token
     */
    public static function get_by_refresh_token($refresh_token) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        $refresh_hash = hash('sha256', $refresh_token);

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE refresh_token_hash = %s",
            $refresh_hash
        ));
    }

    /**
     * Check if session exists and is valid
     */
    public static function exists($token) {
        return self::get($token) !== null;
    }

    /**
     * Update session with new tokens
     */
    public static function update($session_id, $new_access_token, $new_refresh_token) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        $token_hash = hash('sha256', $new_access_token);
        $refresh_hash = hash('sha256', $new_refresh_token);

        return $wpdb->update($table, [
            'token_hash' => $token_hash,
            'refresh_token_hash' => $refresh_hash,
            'expires_at' => date('Y-m-d H:i:s', time() + 900),
        ], ['id' => $session_id]);
    }

    /**
     * Destroy session
     */
    public static function destroy($token) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        $token_hash = hash('sha256', $token);

        return $wpdb->delete($table, ['token_hash' => $token_hash]);
    }

    /**
     * Destroy all sessions for a user
     */
    public static function destroy_all($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        return $wpdb->delete($table, ['user_id' => $user_id]);
    }

    /**
     * Clean up expired sessions (called by cron)
     */
    public static function cleanup_expired() {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        return $wpdb->query("DELETE FROM $table WHERE expires_at < NOW()");
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get all active sessions for a user
     */
    public static function get_user_sessions($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_sessions';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT id, ip_address, user_agent, created_at, expires_at
            FROM $table
            WHERE user_id = %d AND expires_at > NOW()
            ORDER BY created_at DESC",
            $user_id
        ));
    }
}
