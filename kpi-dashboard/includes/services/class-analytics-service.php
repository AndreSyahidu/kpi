<?php
/**
 * Analytics Service
 */
class KPI_Dashboard_Analytics_Service {

    /**
     * Get company overview stats
     */
    public static function get_company_overview($period_start, $period_end) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(DISTINCT kpi_id) as total_kpis,
                COUNT(DISTINCT department_id) as total_departments,
                COUNT(*) as total_entries,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_entries,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_entries
            FROM $data_table
            WHERE period_start >= %s
            AND period_end <= %s
            AND is_deleted = 0",
            $period_start,
            $period_end
        ));

        return [
            'period' => [
                'start' => $period_start,
                'end' => $period_end,
            ],
            'total_kpis' => (int) $stats->total_kpis,
            'total_departments' => (int) $stats->total_departments,
            'total_entries' => (int) $stats->total_entries,
            'approved_entries' => (int) $stats->approved_entries,
            'pending_entries' => (int) $stats->pending_entries,
            'approval_rate' => $stats->total_entries > 0 ? round(($stats->approved_entries / $stats->total_entries) * 100, 2) : 0,
        ];
    }

    /**
     * Get best performing departments
     */
    public static function get_best_performers($period_start, $period_end, $limit = 5) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';
        $dept_table = $wpdb->prefix . 'kpi_departments';
        $kpi_table = $wpdb->prefix . 'kpi_definitions';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                d.id,
                d.name,
                d.color_code,
                COUNT(DISTINCT kd.kpi_id) as kpi_count,
                AVG(CASE
                    WHEN k.target_value > 0 THEN (kd.value / k.target_value) * 100
                    ELSE 100
                END) as achievement_percentage
            FROM $data_table kd
            INNER JOIN $dept_table d ON kd.department_id = d.id
            INNER JOIN $kpi_table k ON kd.kpi_id = k.id
            WHERE kd.period_start >= %s
            AND kd.period_end <= %s
            AND kd.status = 'approved'
            AND kd.is_deleted = 0
            AND d.is_active = 1
            GROUP BY d.id, d.name, d.color_code
            HAVING achievement_percentage >= 90
            ORDER BY achievement_percentage DESC
            LIMIT %d",
            $period_start,
            $period_end,
            $limit
        ));

        return array_map(function($row) {
            return [
                'department_id' => (int) $row->id,
                'department_name' => $row->name,
                'color_code' => $row->color_code,
                'kpi_count' => (int) $row->kpi_count,
                'achievement_percentage' => round((float) $row->achievement_percentage, 2),
            ];
        }, $results);
    }

    /**
     * Get low performing departments
     */
    public static function get_low_performers($period_start, $period_end, $limit = 5) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';
        $dept_table = $wpdb->prefix . 'kpi_departments';
        $kpi_table = $wpdb->prefix . 'kpi_definitions';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                d.id,
                d.name,
                d.color_code,
                COUNT(DISTINCT kd.kpi_id) as kpi_count,
                AVG(CASE
                    WHEN k.target_value > 0 THEN (kd.value / k.target_value) * 100
                    ELSE 100
                END) as achievement_percentage
            FROM $data_table kd
            INNER JOIN $dept_table d ON kd.department_id = d.id
            INNER JOIN $kpi_table k ON kd.kpi_id = k.id
            WHERE kd.period_start >= %s
            AND kd.period_end <= %s
            AND kd.status = 'approved'
            AND kd.is_deleted = 0
            AND d.is_active = 1
            GROUP BY d.id, d.name, d.color_code
            HAVING achievement_percentage < 70
            ORDER BY achievement_percentage ASC
            LIMIT %d",
            $period_start,
            $period_end,
            $limit
        ));

        return array_map(function($row) {
            return [
                'department_id' => (int) $row->id,
                'department_name' => $row->name,
                'color_code' => $row->color_code,
                'kpi_count' => (int) $row->kpi_count,
                'achievement_percentage' => round((float) $row->achievement_percentage, 2),
            ];
        }, $results);
    }

    /**
     * Get trend analysis
     */
    public static function get_trend_analysis($kpi_id, $department_id, $months = 6) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';

        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-$months months"));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                DATE_FORMAT(period_start, '%%Y-%%m') as period,
                AVG(value) as average_value,
                MIN(value) as min_value,
                MAX(value) as max_value,
                COUNT(*) as entry_count
            FROM $data_table
            WHERE kpi_id = %d
            AND department_id = %d
            AND period_start >= %s
            AND period_end <= %s
            AND status = 'approved'
            AND is_deleted = 0
            GROUP BY DATE_FORMAT(period_start, '%%Y-%%m')
            ORDER BY period ASC",
            $kpi_id,
            $department_id,
            $start_date,
            $end_date
        ));

        $trend_data = array_map(function($row) {
            return [
                'period' => $row->period,
                'average' => round((float) $row->average_value, 2),
                'min' => round((float) $row->min_value, 2),
                'max' => round((float) $row->max_value, 2),
                'count' => (int) $row->entry_count,
            ];
        }, $results);

        // Calculate trend direction
        $trend = 'stable';
        if (count($trend_data) >= 2) {
            $first_half = array_slice($trend_data, 0, ceil(count($trend_data) / 2));
            $second_half = array_slice($trend_data, ceil(count($trend_data) / 2));

            $first_avg = array_sum(array_column($first_half, 'average')) / count($first_half);
            $second_avg = array_sum(array_column($second_half, 'average')) / count($second_half);

            if ($second_avg > $first_avg * 1.1) {
                $trend = 'increasing';
            } elseif ($second_avg < $first_avg * 0.9) {
                $trend = 'decreasing';
            }
        }

        return [
            'data' => $trend_data,
            'trend' => $trend,
            'periods' => count($trend_data),
        ];
    }

    /**
     * Get department comparison
     */
    public static function get_department_comparison($kpi_id, $period_start, $period_end) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';
        $dept_table = $wpdb->prefix . 'kpi_departments';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT
                d.id as department_id,
                d.name as department_name,
                d.color_code,
                AVG(kd.value) as average_value,
                SUM(kd.value) as total_value,
                COUNT(*) as entry_count
            FROM $data_table kd
            INNER JOIN $dept_table d ON kd.department_id = d.id
            WHERE kd.kpi_id = %d
            AND kd.period_start >= %s
            AND kd.period_end <= %s
            AND kd.status = 'approved'
            AND kd.is_deleted = 0
            AND d.is_active = 1
            GROUP BY d.id, d.name, d.color_code
            ORDER BY average_value DESC",
            $kpi_id,
            $period_start,
            $period_end
        ));
    }

    /**
     * Get insights and recommendations
     */
    public static function get_insights($department_id, $period_start, $period_end) {
        $insights = [];

        // Find KPIs consistently below target
        $below_target = self::get_kpis_below_target($department_id, $period_start, $period_end);
        if (!empty($below_target)) {
            $insights[] = [
                'type' => 'warning',
                'title' => __('KPIs Below Target', 'kpi-dashboard'),
                'message' => sprintf(__('%d KPIs are consistently below target', 'kpi-dashboard'), count($below_target)),
                'data' => $below_target,
            ];
        }

        // Find improving KPIs
        $improving = self::get_improving_kpis($department_id, $period_start, $period_end);
        if (!empty($improving)) {
            $insights[] = [
                'type' => 'success',
                'title' => __('Improving Performance', 'kpi-dashboard'),
                'message' => sprintf(__('%d KPIs show improvement', 'kpi-dashboard'), count($improving)),
                'data' => $improving,
            ];
        }

        // Find declining KPIs
        $declining = self::get_declining_kpis($department_id, $period_start, $period_end);
        if (!empty($declining)) {
            $insights[] = [
                'type' => 'error',
                'title' => __('Declining Performance', 'kpi-dashboard'),
                'message' => sprintf(__('%d KPIs show decline', 'kpi-dashboard'), count($declining)),
                'data' => $declining,
            ];
        }

        return $insights;
    }

    private static function get_kpis_below_target($department_id, $period_start, $period_end) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';
        $kpi_table = $wpdb->prefix . 'kpi_definitions';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT
                k.id,
                k.name,
                AVG(kd.value) as avg_value,
                k.target_value,
                (AVG(kd.value) / k.target_value * 100) as achievement_pct
            FROM $data_table kd
            INNER JOIN $kpi_table k ON kd.kpi_id = k.id
            WHERE kd.department_id = %d
            AND kd.period_start >= %s
            AND kd.period_end <= %s
            AND kd.status = 'approved'
            AND k.target_value > 0
            GROUP BY k.id, k.name, k.target_value
            HAVING achievement_pct < 90
            ORDER BY achievement_pct ASC",
            $department_id,
            $period_start,
            $period_end
        ));
    }

    private static function get_improving_kpis($department_id, $period_start, $period_end) {
        // TODO: Implement trend detection for improving KPIs
        return [];
    }

    private static function get_declining_kpis($department_id, $period_start, $period_end) {
        // TODO: Implement trend detection for declining KPIs
        return [];
    }

    /**
     * Get top performers (individuals)
     */
    public static function get_top_individuals($period_start, $period_end, $limit = 10) {
        global $wpdb;
        $data_table = $wpdb->prefix . 'kpi_data';
        $user_table = $wpdb->prefix . 'kpi_users';
        $kpi_table = $wpdb->prefix . 'kpi_definitions';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT
                u.id as user_id,
                u.full_name,
                u.department_id,
                COUNT(DISTINCT kd.kpi_id) as kpi_count,
                AVG(CASE
                    WHEN k.target_value > 0 THEN (kd.value / k.target_value) * 100
                    ELSE 100
                END) as achievement_percentage
            FROM $data_table kd
            INNER JOIN $user_table u ON kd.user_id = u.id
            INNER JOIN $kpi_table k ON kd.kpi_id = k.id
            WHERE kd.period_start >= %s
            AND kd.period_end <= %s
            AND kd.status = 'approved'
            AND kd.user_id IS NOT NULL
            GROUP BY u.id, u.full_name, u.department_id
            HAVING achievement_percentage >= 100
            ORDER BY achievement_percentage DESC
            LIMIT %d",
            $period_start,
            $period_end,
            $limit
        ));
    }
}
