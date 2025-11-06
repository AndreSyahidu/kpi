<?php
class KPI_Dashboard_Approvals_API extends KPI_Dashboard_API_Base {
    public function register_routes() {
        register_rest_route($this->namespace, '/approvals', [
            'methods' => 'GET', 'callback' => [$this, 'get_pending'], 'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);
        register_rest_route($this->namespace, '/approvals/(?P<id>[\d]+)/approve', [
            'methods' => 'POST', 'callback' => [$this, 'approve'], 'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);
        register_rest_route($this->namespace, '/approvals/(?P<id>[\d]+)/reject', [
            'methods' => 'POST', 'callback' => [$this, 'reject'], 'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);
        register_rest_route($this->namespace, '/approvals/bulk-approve', [
            'methods' => 'POST', 'callback' => [$this, 'bulk_approve'], 'permission_callback' => [$this, 'permission_check_dept_head'],
        ]);
    }

    public function get_pending($request) {
        $queue = KPI_Dashboard_Approval_Service::get_pending_queue($this->get_current_user($request));
        return $this->success($queue);
    }

    public function approve($request) {
        $result = KPI_Dashboard_Approval_Service::approve($request->get_param('id'), $this->get_current_user($request)->id, $request->get_param('review_notes'));
        if (is_wp_error($result)) return $this->handle_error($result);
        return $this->success($result, __('Entry approved', 'kpi-dashboard'));
    }

    public function reject($request) {
        $result = KPI_Dashboard_Approval_Service::reject($request->get_param('id'), $this->get_current_user($request)->id, $request->get_param('review_notes'));
        if (is_wp_error($result)) return $this->handle_error($result);
        return $this->success($result, __('Entry rejected', 'kpi-dashboard'));
    }

    public function bulk_approve($request) {
        $result = KPI_Dashboard_Approval_Service::bulk_approve($request->get_param('entry_ids'), $this->get_current_user($request)->id, $request->get_param('review_notes'));
        return $this->success($result, __('Bulk approval completed', 'kpi-dashboard'));
    }
}
