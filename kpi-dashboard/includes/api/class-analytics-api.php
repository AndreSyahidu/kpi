<?php
class KPI_Dashboard_Analytics_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/analytics/overview', [
            'methods' => 'GET', 'callback' => [$this, 'overview'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/analytics/best-performers', [
            'methods' => 'GET', 'callback' => [$this, 'best_performers'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/analytics/low-performers', [
            'methods' => 'GET', 'callback' => [$this, 'low_performers'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/analytics/trend', [
            'methods' => 'GET', 'callback' => [$this, 'trend'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/analytics/comparison', [
            'methods' => 'GET', 'callback' => [$this, 'comparison'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/analytics/insights', [
            'methods' => 'GET', 'callback' => [$this, 'insights'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
    }

    public function overview($request) {
        $start = $request->get_param('period_start') ?: date('Y-m-01');
        $end = $request->get_param('period_end') ?: date('Y-m-t');
        return $this->success(KPI_Dashboard_Analytics_Service::get_company_overview($start, $end));
    }

    public function best_performers($request) {
        $start = $request->get_param('period_start') ?: date('Y-m-01');
        $end = $request->get_param('period_end') ?: date('Y-m-t');
        $limit = $request->get_param('limit') ?: 5;
        return $this->success(KPI_Dashboard_Analytics_Service::get_best_performers($start, $end, $limit));
    }

    public function low_performers($request) {
        $start = $request->get_param('period_start') ?: date('Y-m-01');
        $end = $request->get_param('period_end') ?: date('Y-m-t');
        $limit = $request->get_param('limit') ?: 5;
        return $this->success(KPI_Dashboard_Analytics_Service::get_low_performers($start, $end, $limit));
    }

    public function trend($request) {
        $kpi_id = $request->get_param('kpi_id');
        $dept_id = $request->get_param('department_id');
        $months = $request->get_param('months') ?: 6;
        return $this->success(KPI_Dashboard_Analytics_Service::get_trend_analysis($kpi_id, $dept_id, $months));
    }

    public function comparison($request) {
        $kpi_id = $request->get_param('kpi_id');
        $start = $request->get_param('period_start') ?: date('Y-m-01');
        $end = $request->get_param('period_end') ?: date('Y-m-t');
        return $this->success(KPI_Dashboard_Analytics_Service::get_department_comparison($kpi_id, $start, $end));
    }

    public function insights($request) {
        $dept_id = $request->get_param('department_id');
        $start = $request->get_param('period_start') ?: date('Y-m-01');
        $end = $request->get_param('period_end') ?: date('Y-m-t');
        return $this->success(KPI_Dashboard_Analytics_Service::get_insights($dept_id, $start, $end));
    }
}
