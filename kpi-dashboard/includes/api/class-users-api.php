<?php
/**
 * Users API Endpoints
 */
class KPI_Dashboard_Users_API extends KPI_Dashboard_API_Base {

    public function register_routes() {
        // Get all users
        register_rest_route($this->namespace, '/users', [
            'methods' => 'GET',
            'callback' => [$this, 'get_items'],
            'permission_callback' => [$this, 'permission_check_authenticated'],
            'args' => $this->get_collection_params(),
        ]);

        // Get single user
        register_rest_route($this->namespace, '/users/(?P<id>[\d]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_item'],
            'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);

        // Create user
        register_rest_route($this->namespace, '/users', [
            'methods' => 'POST',
            'callback' => [$this, 'create_item'],
            'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);

        // Update user
        register_rest_route($this->namespace, '/users/(?P<id>[\d]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_item'],
            'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);

        // Delete user
        register_rest_route($this->namespace, '/users/(?P<id>[\d]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_item'],
            'permission_callback' => [$this, 'permission_check_super_admin'],
        ]);
    }

    public function get_items($request) {
        $current_user = $this->get_current_user($request);
        $pagination = $this->get_pagination_params($request);

        $args = array_merge($pagination, [
            'role' => $request->get_param('role'),
            'department_id' => $request->get_param('department_id'),
            'is_active' => $request->get_param('is_active'),
            'search' => $request->get_param('search'),
            'orderby' => $request->get_param('orderby') ?: 'created_at',
            'order' => strtoupper($request->get_param('order') ?: 'DESC'),
        ]);

        $result = KPI_Dashboard_User_Service::get_list($args, $current_user);

        return $this->success($result);
    }

    public function get_item($request) {
        $user_id = $request->get_param('id');
        $user = KPI_Dashboard_User_Service::get_with_relations($user_id);

        if (!$user) {
            return $this->error(__('User not found', 'kpi-dashboard'), 'not_found', 404);
        }

        $user = KPI_Dashboard_User_Model::sanitize_for_response($user);

        return $this->success($user);
    }

    public function create_item($request) {
        $current_user = $this->get_current_user($request);
        $data = $request->get_json_params();

        $user = KPI_Dashboard_User_Service::create($data, $current_user->id);

        if (is_wp_error($user)) {
            return $this->handle_error($user);
        }

        return $this->success($user, __('User created successfully', 'kpi-dashboard'), 201);
    }

    public function update_item($request) {
        $user_id = $request->get_param('id');
        $current_user = $this->get_current_user($request);
        $data = $request->get_json_params();

        $user = KPI_Dashboard_User_Service::update($user_id, $data, $current_user->id);

        if (is_wp_error($user)) {
            return $this->handle_error($user);
        }

        return $this->success($user, __('User updated successfully', 'kpi-dashboard'));
    }

    public function delete_item($request) {
        $user_id = $request->get_param('id');
        $current_user = $this->get_current_user($request);

        $result = KPI_Dashboard_User_Service::delete($user_id, $current_user->id);

        if (is_wp_error($result)) {
            return $this->handle_error($result);
        }

        return $this->success(null, __('User deleted successfully', 'kpi-dashboard'));
    }
}
