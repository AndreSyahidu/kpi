<?php
class KPI_Dashboard_Settings_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/settings', [
            'methods' => 'GET', 'callback' => [$this, 'get_settings'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/settings', [
            'methods' => 'PUT', 'callback' => [$this, 'update_settings'], 'permission_callback' => [$this, 'permission_check_super_admin'],
        ]);
    }

    public function get_settings($request) {
        $settings = [
            'general' => get_option('kpi_dashboard_settings', []),
            'notifications' => get_option('kpi_dashboard_notifications', []),
            'email' => get_option('kpi_dashboard_email', []),
        ];
        return $this->success($settings);
    }

    public function update_settings($request) {
        $data = $request->get_json_params();

        if (isset($data['general'])) {
            update_option('kpi_dashboard_settings', $data['general']);
        }
        if (isset($data['notifications'])) {
            update_option('kpi_dashboard_notifications', $data['notifications']);
        }
        if (isset($data['email'])) {
            update_option('kpi_dashboard_email', $data['email']);
        }

        KPI_Dashboard_Audit_Log::log('update_settings', 'settings', null, null, $data);
        return $this->success(null, __('Settings updated', 'kpi-dashboard'));
    }
}
