<?php
class KPI_Dashboard_Notifications_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/notifications', [
            'methods' => 'GET', 'callback' => [$this, 'get_items'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/notifications/(?P<id>[\d]+)/read', [
            'methods' => 'POST', 'callback' => [$this, 'mark_read'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/notifications/mark-all-read', [
            'methods' => 'POST', 'callback' => [$this, 'mark_all_read'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/notifications/unread-count', [
            'methods' => 'GET', 'callback' => [$this, 'unread_count'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/notifications/preferences', [
            'methods' => 'GET', 'callback' => [$this, 'get_preferences'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
        register_rest_route($this->namespace, '/notifications/preferences', [
            'methods' => 'PUT', 'callback' => [$this, 'update_preferences'], 'permission_callback' => [$this, 'permission_check_authenticated'],
        ]);
    }

    public function get_items($request) {
        $user = $this->get_current_user($request);
        $unread_only = $request->get_param('unread_only') ?: false;
        $limit = $request->get_param('limit') ?: 50;
        return $this->success(KPI_Dashboard_Notification_Model::get_user_notifications($user->id, $unread_only, $limit));
    }

    public function mark_read($request) {
        KPI_Dashboard_Notification_Model::mark_as_read($request->get_param('id'));
        return $this->success(null, __('Marked as read', 'kpi-dashboard'));
    }

    public function mark_all_read($request) {
        KPI_Dashboard_Notification_Model::mark_all_as_read($this->get_current_user($request)->id);
        return $this->success(null, __('All marked as read', 'kpi-dashboard'));
    }

    public function unread_count($request) {
        $count = KPI_Dashboard_Notification_Model::get_unread_count($this->get_current_user($request)->id);
        return $this->success(['count' => $count]);
    }

    public function get_preferences($request) {
        $prefs = KPI_Dashboard_Notification_Service::get_user_preferences($this->get_current_user($request)->id);
        return $this->success($prefs);
    }

    public function update_preferences($request) {
        KPI_Dashboard_Notification_Service::update_user_preferences($this->get_current_user($request)->id, $request->get_json_params());
        return $this->success(null, __('Preferences updated', 'kpi-dashboard'));
    }
}
