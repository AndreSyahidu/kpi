<?php
/**
 * KPI Definition Model
 */
class KPI_Dashboard_KPI_Model {

    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_definitions';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_all($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_definitions';

        $defaults = [
            'category' => null,
            'type' => null,
            'is_active' => 1,
            'search' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => 100,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $params = [];

        if ($args['category']) {
            $where[] = 'category = %s';
            $params[] = $args['category'];
        }

        if ($args['type']) {
            $where[] = 'type = %s';
            $params[] = $args['type'];
        }

        if ($args['is_active'] !== null) {
            $where[] = 'is_active = %d';
            $params[] = $args['is_active'];
        }

        if ($args['search']) {
            $where[] = '(name LIKE %s OR description LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search;
            $params[] = $search;
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
        $table = $wpdb->prefix . 'kpi_definitions';

        $defaults = [
            'name' => '',
            'slug' => '',
            'description' => '',
            'category' => 'custom',
            'type' => 'department',
            'metric_type' => 'number',
            'unit' => null,
            'input_frequency' => 'monthly',
            'calculation_method' => 'sum',
            'formula' => null,
            'target_value' => null,
            'target_type' => 'minimum',
            'target_range_min' => null,
            'target_range_max' => null,
            'weight' => 1,
            'chart_type' => 'line',
            'color_scheme' => null,
            'decimal_places' => 2,
            'validation_rules' => null,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'created_by' => null,
        ];

        $data = wp_parse_args($data, $defaults);

        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        }

        // Serialize JSON fields
        if (isset($data['formula']) && is_array($data['formula'])) {
            $data['formula'] = json_encode($data['formula']);
        }
        if (isset($data['color_scheme']) && is_array($data['color_scheme'])) {
            $data['color_scheme'] = json_encode($data['color_scheme']);
        }
        if (isset($data['validation_rules']) && is_array($data['validation_rules'])) {
            $data['validation_rules'] = json_encode($data['validation_rules']);
        }

        $result = $wpdb->insert($table, $data);
        return $result ? $wpdb->insert_id : false;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_definitions';

        $data['updated_at'] = current_time('mysql');

        // Serialize JSON fields
        if (isset($data['formula']) && is_array($data['formula'])) {
            $data['formula'] = json_encode($data['formula']);
        }
        if (isset($data['color_scheme']) && is_array($data['color_scheme'])) {
            $data['color_scheme'] = json_encode($data['color_scheme']);
        }
        if (isset($data['validation_rules']) && is_array($data['validation_rules'])) {
            $data['validation_rules'] = json_encode($data['validation_rules']);
        }

        return $wpdb->update($table, $data, ['id' => $id]);
    }

    public static function delete($id) {
        return self::update($id, ['is_active' => 0]);
    }

    public static function assign($kpi_id, $assigned_to_type, $assigned_to_id, $target_override = null, $assigned_by = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_assignments';

        return $wpdb->insert($table, [
            'kpi_id' => $kpi_id,
            'assigned_to_type' => $assigned_to_type,
            'assigned_to_id' => $assigned_to_id,
            'target_override' => $target_override,
            'is_active' => 1,
            'assigned_at' => current_time('mysql'),
            'assigned_by' => $assigned_by,
        ]);
    }

    public static function unassign($kpi_id, $assigned_to_type, $assigned_to_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_assignments';

        return $wpdb->delete($table, [
            'kpi_id' => $kpi_id,
            'assigned_to_type' => $assigned_to_type,
            'assigned_to_id' => $assigned_to_id,
        ]);
    }

    public static function get_assignments($kpi_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_assignments';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE kpi_id = %d AND is_active = 1",
            $kpi_id
        ));
    }

    public static function get_kpis_for_department($department_id) {
        global $wpdb;
        $kpis_table = $wpdb->prefix . 'kpi_definitions';
        $assignments_table = $wpdb->prefix . 'kpi_assignments';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT k.* FROM $kpis_table k
            INNER JOIN $assignments_table a ON k.id = a.kpi_id
            WHERE a.assigned_to_type = 'department'
            AND a.assigned_to_id = %d
            AND k.is_active = 1
            AND a.is_active = 1
            ORDER BY k.name",
            $department_id
        ));
    }
}
