<?php
class KPI_Dashboard_Reports_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/reports/department', [
            'methods' => 'POST', 'callback' => [$this, 'department_report'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/reports/executive', [
            'methods' => 'POST', 'callback' => [$this, 'executive_report'], 'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);
        register_rest_route($this->namespace, '/reports/kpi', [
            'methods' => 'POST', 'callback' => [$this, 'kpi_report'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
    }

    public function department_report($request) {
        $dept_id = $request->get_param('department_id');
        $start = $request->get_param('period_start');
        $end = $request->get_param('period_end');
        $format = $request->get_param('format') ?: 'csv';

        $result = KPI_Dashboard_Report_Generator::generate_department_report($dept_id, $start, $end, $format);
        if (is_wp_error($result)) return $this->handle_error($result);

        KPI_Dashboard_Report_Generator::save_to_history('department', compact('dept_id', 'start', 'end', 'format'), $result, $this->get_current_user($request)->id);
        return $this->success($result, __('Report generated', 'kpi-dashboard'));
    }

    public function executive_report($request) {
        $start = $request->get_param('period_start');
        $end = $request->get_param('period_end');
        $format = $request->get_param('format') ?: 'csv';

        $result = KPI_Dashboard_Report_Generator::generate_executive_summary($start, $end, $format);
        if (is_wp_error($result)) return $this->handle_error($result);

        KPI_Dashboard_Report_Generator::save_to_history('executive', compact('start', 'end', 'format'), $result, $this->get_current_user($request)->id);
        return $this->success($result, __('Report generated', 'kpi-dashboard'));
    }

    public function kpi_report($request) {
        $kpi_id = $request->get_param('kpi_id');
        $start = $request->get_param('period_start');
        $end = $request->get_param('period_end');
        $format = $request->get_param('format') ?: 'csv';

        $result = KPI_Dashboard_Report_Generator::generate_kpi_report($kpi_id, $start, $end, $format);
        if (is_wp_error($result)) return $this->handle_error($result);

        KPI_Dashboard_Report_Generator::save_to_history('kpi', compact('kpi_id', 'start', 'end', 'format'), $result, $this->get_current_user($request)->id);
        return $this->success($result, __('Report generated', 'kpi-dashboard'));
    }
}
