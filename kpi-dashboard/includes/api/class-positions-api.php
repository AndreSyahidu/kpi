<?php
class KPI_Dashboard_Positions_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/positions', [
            ['methods' => 'GET', 'callback' => [$this, 'get_items'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'POST', 'callback' => [$this, 'create_item'], 'permission_callback' => [$this, 'permission_check_dept_head']],
        ]);
        register_rest_route($this->namespace, '/positions/(?P<id>[\d]+)', [
            ['methods' => 'GET', 'callback' => [$this, 'get_item'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'PUT', 'callback' => [$this, 'update_item'], 'permission_callback' => [$this, 'permission_check_dept_head']],
            ['methods' => 'DELETE', 'callback' => [$this, 'delete_item'], 'permission_callback' => [$this, 'permission_check_dept_head']],
        ]);
    }

    public function get_items($request) {
        $args = ['department_id' => $request->get_param('department_id'), 'is_active' => $request->get_param('is_active')];
        return $this->success(KPI_Dashboard_Position_Model::get_all($args));
    }

    public function get_item($request) {
        $position = KPI_Dashboard_Position_Model::get($request->get_param('id'));
        if (!$position) return $this->error(__('Position not found', 'kpi-dashboard'), 'not_found', 404);
        return $this->success($position);
    }

    public function create_item($request) {
        $data = $request->get_json_params();
        $data['created_by'] = $this->get_current_user($request)->id;
        $id = KPI_Dashboard_Position_Model::create($data);
        if (!$id) return $this->error(__('Failed to create position', 'kpi-dashboard'));
        return $this->success(KPI_Dashboard_Position_Model::get($id), __('Position created', 'kpi-dashboard'), 201);
    }

    public function update_item($request) {
        $result = KPI_Dashboard_Position_Model::update($request->get_param('id'), $request->get_json_params());
        if (!$result) return $this->error(__('Failed to update position', 'kpi-dashboard'));
        return $this->success(KPI_Dashboard_Position_Model::get($request->get_param('id')), __('Position updated', 'kpi-dashboard'));
    }

    public function delete_item($request) {
        $result = KPI_Dashboard_Position_Model::delete($request->get_param('id'));
        if (!$result) return $this->error(__('Failed to delete position', 'kpi-dashboard'));
        return $this->success(null, __('Position deleted', 'kpi-dashboard'));
    }
}
