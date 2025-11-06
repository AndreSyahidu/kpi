<?php
class KPI_Dashboard_KPIs_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/kpis', [
            ['methods' => 'GET', 'callback' => [$this, 'get_items'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'POST', 'callback' => [$this, 'create_item'], 'permission_callback' => [$this, 'permission_check_dept_head']],
        ]);
        register_rest_route($this->namespace, '/kpis/(?P<id>[\d]+)', [
            ['methods' => 'GET', 'callback' => [$this, 'get_item'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'PUT', 'callback' => [$this, 'update_item'], 'permission_callback' => [$this, 'permission_check_dept_head']],
            ['methods' => 'DELETE', 'callback' => [$this, 'delete_item'], 'permission_callback' => [$this, 'permission_check_super_admin']],
        ]);
        register_rest_route($this->namespace, '/kpis/(?P<id>[\d]+)/assign', [
            'methods' => 'POST', 'callback' => [$this, 'assign'], 'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);
    }

    public function get_items($request) {
        $pagination = $this->get_pagination_params($request);
        $args = array_merge($pagination, ['category' => $request->get_param('category'), 'type' => $request->get_param('type'), 'search' => $request->get_param('search')]);
        return $this->success(KPI_Dashboard_KPI_Model::get_all($args));
    }

    public function get_item($request) {
        $kpi = KPI_Dashboard_KPI_Service::get_with_assignments($request->get_param('id'));
        if (!$kpi) return $this->error(__('KPI not found', 'kpi-dashboard'), 'not_found', 404);
        return $this->success($kpi);
    }

    public function create_item($request) {
        $kpi = KPI_Dashboard_KPI_Service::create($request->get_json_params(), $this->get_current_user($request)->id);
        if (is_wp_error($kpi)) return $this->handle_error($kpi);
        return $this->success($kpi, __('KPI created', 'kpi-dashboard'), 201);
    }

    public function update_item($request) {
        $kpi = KPI_Dashboard_KPI_Service::update($request->get_param('id'), $request->get_json_params());
        if (is_wp_error($kpi)) return $this->handle_error($kpi);
        return $this->success($kpi, __('KPI updated', 'kpi-dashboard'));
    }

    public function delete_item($request) {
        $result = KPI_Dashboard_KPI_Service::delete($request->get_param('id'));
        if (is_wp_error($result)) return $this->handle_error($result);
        return $this->success(null, __('KPI deleted', 'kpi-dashboard'));
    }

    public function assign($request) {
        $kpi_id = $request->get_param('id');
        $type = $request->get_param('assigned_to_type');
        $id = $request->get_param('assigned_to_id');
        $target = $request->get_param('target_override');
        $user = $this->get_current_user($request);

        $result = $type === 'department'
            ? KPI_Dashboard_KPI_Service::assign_to_department($kpi_id, $id, $target, $user->id)
            : KPI_Dashboard_KPI_Service::assign_to_position($kpi_id, $id, $target, $user->id);

        if (is_wp_error($result)) return $this->handle_error($result);
        return $this->success(null, __('KPI assigned', 'kpi-dashboard'));
    }
}
