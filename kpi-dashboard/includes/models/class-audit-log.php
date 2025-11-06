<?php
/**
 * Audit Log Model
 */
class KPI_Dashboard_Audit_Log {

    public static function log($action, $entity_type, $entity_id, $before_value = null, $after_value = null, $reason = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_audit_logs';

        $user = KPI_Dashboard_Auth::get_current_user();
        $user_id = $user ? $user->id : null;

        // Convert arrays/objects to JSON
        if (is_array($before_value) || is_object($before_value)) {
            $before_value = json_encode($before_value);
        }
        if (is_array($after_value) || is_object($after_value)) {
            $after_value = json_encode($after_value);
        }

        return $wpdb->insert($table, [
            'user_id' => $user_id,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'before_value' => $before_value,
            'after_value' => $after_value,
            'reason' => $reason,
            'ip_address' => self::get_client_ip(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'created_at' => current_time('mysql'),
        ]);
    }

    public static function get_logs($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_audit_logs';

        $defaults = [
            'user_id' => null,
            'action' => null,
            'entity_type' => null,
            'entity_id' => null,
            'start_date' => null,
            'end_date' => null,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $params = [];

        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $params[] = $args['user_id'];
        }

        if ($args['action']) {
            $where[] = 'action = %s';
            $params[] = $args['action'];
        }

        if ($args['entity_type']) {
            $where[] = 'entity_type = %s';
            $params[] = $args['entity_type'];
        }

        if ($args['entity_id']) {
            $where[] = 'entity_id = %d';
            $params[] = $args['entity_id'];
        }

        if ($args['start_date']) {
            $where[] = 'created_at >= %s';
            $params[] = $args['start_date'];
        }

        if ($args['end_date']) {
            $where[] = 'created_at <= %s';
            $params[] = $args['end_date'];
        }

        $where_clause = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

        $params[] = $args['limit'];
        $params[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby LIMIT %d OFFSET %d",
            $params
        ));
    }

    public static function get_entity_history($entity_type, $entity_id, $limit = 50) {
        return self::get_logs([
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'limit' => $limit,
        ]);
    }

    public static function get_user_activity($user_id, $limit = 100) {
        return self::get_logs([
            'user_id' => $user_id,
            'limit' => $limit,
        ]);
    }

    private static function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }

        return '0.0.0.0';
    }

    public static function cleanup_old($days = 365) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_audit_logs';

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
}
