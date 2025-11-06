<?php
/**
 * Position Model
 */
class KPI_Dashboard_Position_Model {

    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_positions';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_all($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_positions';

        $defaults = [
            'department_id' => null,
            'level' => null,
            'is_active' => 1,
            'search' => '',
            'orderby' => 'sort_order',
            'order' => 'ASC',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $params = [];

        if ($args['department_id']) {
            $where[] = 'department_id = %d';
            $params[] = $args['department_id'];
        }

        if ($args['level']) {
            $where[] = 'level = %s';
            $params[] = $args['level'];
        }

        if ($args['is_active'] !== null) {
            $where[] = 'is_active = %d';
            $params[] = $args['is_active'];
        }

        if ($args['search']) {
            $where[] = '(title LIKE %s OR description LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $where_clause = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

        if ($params) {
            $query = $wpdb->prepare("SELECT * FROM $table WHERE $where_clause ORDER BY $orderby", $params);
        } else {
            $query = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby";
        }

        return $wpdb->get_results($query);
    }

    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_positions';

        $defaults = [
            'title' => '',
            'department_id' => null,
            'level' => 'junior',
            'description' => '',
            'sort_order' => 0,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'created_by' => null,
        ];

        $data = wp_parse_args($data, $defaults);
        $result = $wpdb->insert($table, $data);
        return $result ? $wpdb->insert_id : false;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_positions';
        $data['updated_at'] = current_time('mysql');
        return $wpdb->update($table, $data, ['id' => $id]);
    }

    public static function delete($id) {
        return self::update($id, ['is_active' => 0]);
    }

    public static function get_by_department($department_id, $active_only = true) {
        return self::get_all([
            'department_id' => $department_id,
            'is_active' => $active_only ? 1 : null,
        ]);
    }
}
