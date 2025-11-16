<?php
class KPI_Dashboard_Data_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/data', [
            ['methods' => 'GET', 'callback' => [$this, 'get_items'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'POST', 'callback' => [$this, 'create_item'], 'permission_callback' => [$this, 'permission_check_authenticated']],
        ]);
        register_rest_route($this->namespace, '/data/(?P<id>[\d]+)', [
            ['methods' => 'GET', 'callback' => [$this, 'get_item'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'PUT', 'callback' => [$this, 'update_item'], 'permission_callback' => [$this, 'permission_check_authenticated']],
            ['methods' => 'DELETE', 'callback' => [$this, 'delete_item'], 'permission_callback' => [$this, 'permission_check_super_admin']],
        ]);
        register_rest_route($this->namespace, '/data/bulk', [
            'methods' => 'POST', 'callback' => [$this, 'bulk_create'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
    }

    public function get_items($request) {
        $pagination = $this->get_pagination_params($request);
        $args = array_merge($pagination, [
            'kpi_id' => $request->get_param('kpi_id'),
            'department_id' => $request->get_param('department_id'),
            'status' => $request->get_param('status'),
            'period_start' => $request->get_param('period_start'),
            'period_end' => $request->get_param('period_end'),
        ]);
        return $this->success(KPI_Dashboard_KPI_Data_Model::get_all($args));
    }

    public function get_item($request) {
        $current_user = $this->get_current_user($request);
        $entry = KPI_Dashboard_KPI_Data_Model::get($request->get_param('id'));

        if (!$entry) {
            return $this->error(__('Entry not found', 'kpi-dashboard'), 'not_found', 404);
        }

        // SECURITY: Check if current user has permission to view this data entry
        if (!KPI_Dashboard_Permissions::can_view_data($current_user, $entry)) {
            return $this->error(__('You do not have permission to view this data entry', 'kpi-dashboard'), 'forbidden', 403);
        }

        return $this->success($entry);
    }

    public function create_item($request) {
        $entry = KPI_Dashboard_Data_Service::create_entry($request->get_json_params(), $this->get_current_user($request)->id);
        if (is_wp_error($entry)) return $this->handle_error($entry);
        return $this->success($entry, __('Data entry created', 'kpi-dashboard'), 201);
    }

    public function update_item($request) {
        $entry = KPI_Dashboard_Data_Service::update_entry($request->get_param('id'), $request->get_json_params(), $this->get_current_user($request));
        if (is_wp_error($entry)) return $this->handle_error($entry);
        return $this->success($entry, __('Data entry updated', 'kpi-dashboard'));
    }

    public function delete_item($request) {
        $result = KPI_Dashboard_KPI_Data_Model::delete($request->get_param('id'));
        if (!$result) return $this->error(__('Failed to delete entry', 'kpi-dashboard'));
        return $this->success(null, __('Data entry deleted', 'kpi-dashboard'));
    }

    public function bulk_create($request) {
        $result = KPI_Dashboard_Data_Service::bulk_create($request->get_param('entries'), $this->get_current_user($request)->id);
        return $this->success($result, __('Bulk import completed', 'kpi-dashboard'));
    }
}
