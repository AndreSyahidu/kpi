<?php
/**
 * Report Generator Service
 */
class KPI_Dashboard_Report_Generator {

    /**
     * Generate department performance report
     */
    public static function generate_department_report($department_id, $period_start, $period_end, $format = 'csv') {
        $dept = KPI_Dashboard_Department_Model::get($department_id);
        if (!$dept) {
            return new WP_Error('dept_not_found', __('Department not found', 'kpi-dashboard'));
        }

        // Get KPIs for this department
        $kpis = KPI_Dashboard_KPI_Model::get_kpis_for_department($department_id);

        $report_data = [];
        $report_data[] = ['Department', $dept->name];
        $report_data[] = ['Period', $period_start . ' to ' . $period_end];
        $report_data[] = ['Generated', current_time('Y-m-d H:i:s')];
        $report_data[] = []; // Empty row

        // Headers
        $report_data[] = ['KPI Name', 'Target', 'Actual', 'Achievement %', 'Status'];

        foreach ($kpis as $kpi) {
            $stats = KPI_Dashboard_KPI_Data_Model::get_statistics(
                $kpi->id,
                $department_id,
                $period_start,
                $period_end
            );

            $actual = $stats->average ?? 0;
            $target = $kpi->target_value ?? 0;
            $achievement = $target > 0 ? ($actual / $target) * 100 : 0;
            $status = $achievement >= 100 ? 'Achieved' : ($achievement >= 90 ? 'On Track' : 'Below Target');

            $report_data[] = [
                $kpi->name,
                $target,
                round($actual, 2),
                round($achievement, 2) . '%',
                $status,
            ];
        }

        // Generate file
        $filename = KPI_Dashboard_Export::generate_filename(
            'department-report-' . sanitize_title($dept->name),
            $format
        );

        if ($format === 'csv') {
            return KPI_Dashboard_Export::to_csv($report_data, $filename);
        } elseif ($format === 'json') {
            return KPI_Dashboard_Export::to_json([
                'department' => $dept->name,
                'period' => ['start' => $period_start, 'end' => $period_end],
                'kpis' => $report_data,
            ], $filename);
        }

        return new WP_Error('invalid_format', __('Invalid report format', 'kpi-dashboard'));
    }

    /**
     * Generate executive summary report
     */
    public static function generate_executive_summary($period_start, $period_end, $format = 'csv') {
        $overview = KPI_Dashboard_Analytics_Service::get_company_overview($period_start, $period_end);
        $best_performers = KPI_Dashboard_Analytics_Service::get_best_performers($period_start, $period_end);
        $low_performers = KPI_Dashboard_Analytics_Service::get_low_performers($period_start, $period_end);

        $report_data = [];
        $report_data[] = ['Executive Summary'];
        $report_data[] = ['Period', $period_start . ' to ' . $period_end];
        $report_data[] = ['Generated', current_time('Y-m-d H:i:s')];
        $report_data[] = [];

        $report_data[] = ['Overview'];
        $report_data[] = ['Total KPIs Tracked', $overview['total_kpis']];
        $report_data[] = ['Total Departments', $overview['total_departments']];
        $report_data[] = ['Total Data Entries', $overview['total_entries']];
        $report_data[] = ['Approval Rate', $overview['approval_rate'] . '%'];
        $report_data[] = [];

        $report_data[] = ['Top Performing Departments'];
        $report_data[] = ['Department', 'Achievement %'];
        foreach ($best_performers as $dept) {
            $report_data[] = [$dept['department_name'], $dept['achievement_percentage'] . '%'];
        }
        $report_data[] = [];

        $report_data[] = ['Areas Needing Attention'];
        $report_data[] = ['Department', 'Achievement %'];
        foreach ($low_performers as $dept) {
            $report_data[] = [$dept['department_name'], $dept['achievement_percentage'] . '%'];
        }

        $filename = KPI_Dashboard_Export::generate_filename('executive-summary', $format);

        if ($format === 'csv') {
            return KPI_Dashboard_Export::to_csv($report_data, $filename);
        } elseif ($format === 'json') {
            return KPI_Dashboard_Export::to_json([
                'period' => ['start' => $period_start, 'end' => $period_end],
                'overview' => $overview,
                'best_performers' => $best_performers,
                'low_performers' => $low_performers,
            ], $filename);
        }

        return new WP_Error('invalid_format', __('Invalid report format', 'kpi-dashboard'));
    }

    /**
     * Generate KPI detail report
     */
    public static function generate_kpi_report($kpi_id, $period_start, $period_end, $format = 'csv') {
        $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);
        if (!$kpi) {
            return new WP_Error('kpi_not_found', __('KPI not found', 'kpi-dashboard'));
        }

        $data_entries = KPI_Dashboard_KPI_Data_Model::get_all([
            'kpi_id' => $kpi_id,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'status' => 'approved',
            'limit' => 1000,
        ]);

        $report_data = [];
        $report_data[] = ['KPI Report'];
        $report_data[] = ['KPI Name', $kpi->name];
        $report_data[] = ['Target', $kpi->target_value ?? 'N/A'];
        $report_data[] = ['Period', $period_start . ' to ' . $period_end];
        $report_data[] = [];

        $report_data[] = ['Department', 'Period Start', 'Period End', 'Value', 'Unit', 'Submitted By', 'Submitted At'];

        foreach ($data_entries as $entry) {
            $dept = KPI_Dashboard_Department_Model::get($entry->department_id);
            $user = KPI_Dashboard_User_Model::get($entry->submitted_by);

            $report_data[] = [
                $dept->name ?? 'N/A',
                $entry->period_start,
                $entry->period_end,
                $entry->value,
                $entry->unit,
                $user->full_name ?? 'N/A',
                $entry->submitted_at,
            ];
        }

        $filename = KPI_Dashboard_Export::generate_filename(
            'kpi-report-' . sanitize_title($kpi->name),
            $format
        );

        if ($format === 'csv') {
            return KPI_Dashboard_Export::to_csv($report_data, $filename);
        } elseif ($format === 'json') {
            return KPI_Dashboard_Export::to_json([
                'kpi' => [
                    'id' => $kpi->id,
                    'name' => $kpi->name,
                    'target' => $kpi->target_value,
                ],
                'period' => ['start' => $period_start, 'end' => $period_end],
                'entries' => $data_entries,
            ], $filename);
        }

        return new WP_Error('invalid_format', __('Invalid report format', 'kpi-dashboard'));
    }

    /**
     * Store generated report in history
     */
    public static function save_to_history($report_type, $config, $file_info, $generated_by) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_report_history';

        return $wpdb->insert($table, [
            'generated_by' => $generated_by,
            'report_type' => $report_type,
            'config' => json_encode($config),
            'file_path' => $file_info['filepath'],
            'file_size' => $file_info['size'],
            'generated_at' => current_time('mysql'),
        ]);
    }
}
