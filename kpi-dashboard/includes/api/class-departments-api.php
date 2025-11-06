<?php
class KPI_Dashboard_Departments_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/departments', [
            ['methods' => 'GET', 'callback' => [$this, 'get_items'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'POST', 'callback' => [$this, 'create_item'], 'permission_callback' => [$this, 'permission_check_super_admin']],
        ]);
        register_rest_route($this->namespace, '/departments/(?P<id>[\d]+)', [
            ['methods' => 'GET', 'callback' => [$this, 'get_item'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'PUT', 'callback' => [$this, 'update_item'], 'permission_callback' => [$this, 'permission_check_super_admin']],
            ['methods' => 'DELETE', 'callback' => [$this, 'delete_item'], 'permission_callback' => [$this, 'permission_check_super_admin']],
        ]);
        register_rest_route($this->namespace, '/departments/(?P<id>[\d]+)/heads', [
            'methods' => 'POST', 'callback' => [$this, 'assign_head'], 'permission_callback' => [$this, 'permission_check_super_admin'],
        ]);
        register_rest_route($this->namespace, '/departments/hierarchy', [
            'methods' => 'GET', 'callback' => [$this, 'get_hierarchy'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
    }

    public function get_items($request) {
        $args = ['is_active' => $request->get_param('is_active'), 'search' => $request->get_param('search')];
        $departments = KPI_Dashboard_Department_Model::get_all($args);
        return $this->success($departments);
    }

    public function get_item($request) {
        $dept = KPI_Dashboard_Department_Service::get_with_stats($request->get_param('id'));
        if (!$dept) return $this->error(__('Department not found', 'kpi-dashboard'), 'not_found', 404);
        return $this->success($dept);
    }

    public function create_item($request) {
        $dept = KPI_Dashboard_Department_Service::create($request->get_json_params(), $this->get_current_user($request)->id);
        if (is_wp_error($dept)) return $this->handle_error($dept);
        return $this->success($dept, __('Department created', 'kpi-dashboard'), 201);
    }

    public function update_item($request) {
        $dept = KPI_Dashboard_Department_Service::update($request->get_param('id'), $request->get_json_params());
        if (is_wp_error($dept)) return $this->handle_error($dept);
        return $this->success($dept, __('Department updated', 'kpi-dashboard'));
    }

    public function delete_item($request) {
        $result = KPI_Dashboard_Department_Service::delete($request->get_param('id'));
        if (is_wp_error($result)) return $this->handle_error($result);
        return $this->success(null, __('Department deleted', 'kpi-dashboard'));
    }

    public function assign_head($request) {
        $result = KPI_Dashboard_Department_Service::assign_head(
            $request->get_param('id'),
            $request->get_param('user_id'),
            $this->get_current_user($request)->id
        );
        if (is_wp_error($result)) return $this->handle_error($result);
        return $this->success(null, __('Department head assigned', 'kpi-dashboard'));
    }

    public function get_hierarchy($request) {
        return $this->success(KPI_Dashboard_Department_Model::get_hierarchy());
    }
}
