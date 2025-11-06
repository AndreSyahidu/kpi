<?php
/**
 * KPI Data Model
 */
class KPI_Dashboard_KPI_Data_Model {

    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_data';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d AND is_deleted = 0", $id));
    }

    public static function get_all($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_data';

        $defaults = [
            'kpi_id' => null,
            'department_id' => null,
            'position_id' => null,
            'user_id' => null,
            'status' => null,
            'period_start' => null,
            'period_end' => null,
            'orderby' => 'period_start',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['is_deleted = 0'];
        $params = [];

        if ($args['kpi_id']) {
            $where[] = 'kpi_id = %d';
            $params[] = $args['kpi_id'];
        }

        if ($args['department_id']) {
            $where[] = 'department_id = %d';
            $params[] = $args['department_id'];
        }

        if ($args['position_id']) {
            $where[] = 'position_id = %d';
            $params[] = $args['position_id'];
        }

        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $params[] = $args['user_id'];
        }

        if ($args['status']) {
            $where[] = 'status = %s';
            $params[] = $args['status'];
        }

        if ($args['period_start']) {
            $where[] = 'period_start >= %s';
            $params[] = $args['period_start'];
        }

        if ($args['period_end']) {
            $where[] = 'period_end <= %s';
            $params[] = $args['period_end'];
        }

        $where_clause = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

        $params[] = $args['limit'];
        $params[] = $args['offset'];

        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby LIMIT %d OFFSET %d",
            $params
        );

        return $wpdb->get_results($query);
    }

    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_data';

        $defaults = [
            'kpi_id' => null,
            'department_id' => null,
            'position_id' => null,
            'user_id' => null,
            'period_start' => null,
            'period_end' => null,
            'value' => 0,
            'unit' => null,
            'status' => 'pending',
            'notes' => '',
            'attachments' => null,
            'submitted_by' => null,
            'submitted_at' => current_time('mysql'),
            'created_at' => current_time('mysql'),
        ];

        $data = wp_parse_args($data, $defaults);

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $data['attachments'] = json_encode($data['attachments']);
        }

        $result = $wpdb->insert($table, $data);
        return $result ? $wpdb->insert_id : false;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_data';

        $data['updated_at'] = current_time('mysql');

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $data['attachments'] = json_encode($data['attachments']);
        }

        return $wpdb->update($table, $data, ['id' => $id]);
    }

    public static function approve($id, $reviewed_by, $review_notes = '') {
        return self::update($id, [
            'status' => 'approved',
            'reviewed_by' => $reviewed_by,
            'reviewed_at' => current_time('mysql'),
            'review_notes' => $review_notes,
        ]);
    }

    public static function reject($id, $reviewed_by, $review_notes) {
        return self::update($id, [
            'status' => 'rejected',
            'reviewed_by' => $reviewed_by,
            'reviewed_at' => current_time('mysql'),
            'review_notes' => $review_notes,
        ]);
    }

    public static function delete($id) {
        return self::update($id, ['is_deleted' => 1]);
    }

    public static function get_pending_approvals($department_ids = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_data';

        $where = "status = 'pending' AND is_deleted = 0";

        if (!empty($department_ids)) {
            $ids = implode(',', array_map('intval', $department_ids));
            $where .= " AND department_id IN ($ids)";
        }

        return $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY submitted_at ASC");
    }

    public static function get_statistics($kpi_id, $department_id, $start_date, $end_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_data';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as count,
                SUM(value) as total,
                AVG(value) as average,
                MIN(value) as minimum,
                MAX(value) as maximum
            FROM $table
            WHERE kpi_id = %d
            AND department_id = %d
            AND period_start >= %s
            AND period_end <= %s
            AND status = 'approved'
            AND is_deleted = 0",
            $kpi_id,
            $department_id,
            $start_date,
            $end_date
        ));
    }
}
