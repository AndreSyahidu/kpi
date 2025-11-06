<?php
/**
 * Department Model
 */
class KPI_Dashboard_Department_Model {

    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_departments';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_all($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_departments';

        $defaults = [
            'is_active' => 1,
            'parent_id' => null,
            'search' => '',
            'orderby' => 'sort_order',
            'order' => 'ASC',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $params = [];

        if ($args['is_active'] !== null) {
            $where[] = 'is_active = %d';
            $params[] = $args['is_active'];
        }

        if ($args['parent_id'] !== null) {
            if ($args['parent_id'] === 0) {
                $where[] = 'parent_id IS NULL';
            } else {
                $where[] = 'parent_id = %d';
                $params[] = $args['parent_id'];
            }
        }

        if ($args['search']) {
            $where[] = '(name LIKE %s OR description LIKE %s)';
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
        $table = $wpdb->prefix . 'kpi_departments';

        $defaults = [
            'name' => '',
            'slug' => '',
            'description' => '',
            'parent_id' => null,
            'color_code' => '#1565C0',
            'icon_class' => null,
            'logo_url' => null,
            'sort_order' => 0,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'created_by' => null,
        ];

        $data = wp_parse_args($data, $defaults);

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        }

        $result = $wpdb->insert($table, $data);
        return $result ? $wpdb->insert_id : false;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_departments';
        $data['updated_at'] = current_time('mysql');
        return $wpdb->update($table, $data, ['id' => $id]);
    }

    public static function delete($id) {
        return self::update($id, ['is_active' => 0]);
    }

    public static function get_hierarchy() {
        $all_depts = self::get_all(['is_active' => 1]);
        return self::build_tree($all_depts);
    }

    private static function build_tree($elements, $parent_id = null) {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parent_id) {
                $children = self::build_tree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public static function assign_head($department_id, $user_id, $assigned_by = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_department_heads';

        return $wpdb->insert($table, [
            'department_id' => $department_id,
            'user_id' => $user_id,
            'assigned_at' => current_time('mysql'),
            'assigned_by' => $assigned_by,
        ]);
    }

    public static function remove_head($department_id, $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_department_heads';

        return $wpdb->delete($table, [
            'department_id' => $department_id,
            'user_id' => $user_id,
        ]);
    }

    public static function get_heads($department_id) {
        global $wpdb;
        $heads_table = $wpdb->prefix . 'kpi_department_heads';
        $users_table = $wpdb->prefix . 'kpi_users';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT u.* FROM $users_table u
            INNER JOIN $heads_table dh ON u.id = dh.user_id
            WHERE dh.department_id = %d AND u.is_active = 1",
            $department_id
        ));
    }
}
