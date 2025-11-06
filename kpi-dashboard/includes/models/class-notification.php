<?php
/**
 * Notification Model
 */
class KPI_Dashboard_Notification_Model {

    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_user_notifications($user_id, $unread_only = false, $limit = 50) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';

        $where = "user_id = %d";
        $params = [$user_id];

        if ($unread_only) {
            $where .= " AND is_read = 0";
        }

        $params[] = $limit;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE $where ORDER BY created_at DESC LIMIT %d",
            $params
        ));
    }

    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';

        $defaults = [
            'user_id' => null,
            'type' => 'info',
            'severity' => 'info',
            'title' => '',
            'message' => '',
            'action_url' => null,
            'related_entity_type' => null,
            'related_entity_id' => null,
            'is_read' => 0,
            'sent_via_email' => 0,
            'created_at' => current_time('mysql'),
        ];

        $data = wp_parse_args($data, $defaults);
        $result = $wpdb->insert($table, $data);
        return $result ? $wpdb->insert_id : false;
    }

    public static function mark_as_read($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';

        return $wpdb->update($table, [
            'is_read' => 1,
            'read_at' => current_time('mysql'),
        ], ['id' => $id]);
    }

    public static function mark_all_as_read($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';

        return $wpdb->update($table, [
            'is_read' => 1,
            'read_at' => current_time('mysql'),
        ], [
            'user_id' => $user_id,
            'is_read' => 0,
        ]);
    }

    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';
        return $wpdb->delete($table, ['id' => $id]);
    }

    public static function get_unread_count($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND is_read = 0",
            $user_id
        ));
    }

    public static function create_bulk($user_ids, $data) {
        $results = [];
        foreach ($user_ids as $user_id) {
            $notification_data = array_merge($data, ['user_id' => $user_id]);
            $results[] = self::create($notification_data);
        }
        return $results;
    }

    public static function cleanup_old($days = 90) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_notifications';

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE is_read = 1 AND read_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
}
