<?php
/**
 * Approval Service
 */
class KPI_Dashboard_Approval_Service {

    public static function approve($entry_id, $reviewed_by, $review_notes = '') {
        $entry = KPI_Dashboard_KPI_Data_Model::get($entry_id);

        if (!$entry) {
            return new WP_Error('not_found', __('Data entry not found', 'kpi-dashboard'));
        }

        if ($entry->status !== 'pending') {
            return new WP_Error('invalid_status', __('Only pending entries can be approved', 'kpi-dashboard'));
        }

        // Get reviewer
        $reviewer = KPI_Dashboard_User_Model::get($reviewed_by);
        if (!$reviewer) {
            return new WP_Error('reviewer_not_found', __('Reviewer not found', 'kpi-dashboard'));
        }

        // Permission check
        if (!KPI_Dashboard_Permissions::can_approve_data($reviewer, $entry)) {
            return new WP_Error('permission_denied', __('You do not have permission to approve this entry', 'kpi-dashboard'));
        }

        $result = KPI_Dashboard_KPI_Data_Model::approve($entry_id, $reviewed_by, $review_notes);

        if (!$result) {
            return new WP_Error('approve_failed', __('Failed to approve entry', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('approve', 'kpi_data', $entry_id, ['status' => 'pending'], ['status' => 'approved', 'review_notes' => $review_notes]);

        // Notify submitter
        KPI_Dashboard_Notification_Service::create([
            'user_id' => $entry->submitted_by,
            'type' => 'success',
            'severity' => 'success',
            'title' => __('Data Entry Approved', 'kpi-dashboard'),
            'message' => __('Your KPI data entry has been approved', 'kpi-dashboard'),
            'related_entity_type' => 'kpi_data',
            'related_entity_id' => $entry_id,
        ]);

        return KPI_Dashboard_KPI_Data_Model::get($entry_id);
    }

    public static function reject($entry_id, $reviewed_by, $review_notes) {
        if (empty($review_notes)) {
            return new WP_Error('notes_required', __('Review notes are required for rejection', 'kpi-dashboard'));
        }

        $entry = KPI_Dashboard_KPI_Data_Model::get($entry_id);

        if (!$entry) {
            return new WP_Error('not_found', __('Data entry not found', 'kpi-dashboard'));
        }

        if ($entry->status !== 'pending') {
            return new WP_Error('invalid_status', __('Only pending entries can be rejected', 'kpi-dashboard'));
        }

        $reviewer = KPI_Dashboard_User_Model::get($reviewed_by);
        if (!$reviewer) {
            return new WP_Error('reviewer_not_found', __('Reviewer not found', 'kpi-dashboard'));
        }

        if (!KPI_Dashboard_Permissions::can_approve_data($reviewer, $entry)) {
            return new WP_Error('permission_denied', __('You do not have permission to reject this entry', 'kpi-dashboard'));
        }

        $result = KPI_Dashboard_KPI_Data_Model::reject($entry_id, $reviewed_by, $review_notes);

        if (!$result) {
            return new WP_Error('reject_failed', __('Failed to reject entry', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('reject', 'kpi_data', $entry_id, ['status' => 'pending'], ['status' => 'rejected', 'review_notes' => $review_notes]);

        // Notify submitter
        KPI_Dashboard_Notification_Service::create([
            'user_id' => $entry->submitted_by,
            'type' => 'warning',
            'severity' => 'warning',
            'title' => __('Data Entry Rejected', 'kpi-dashboard'),
            'message' => sprintf(__('Your KPI data entry has been rejected. Reason: %s', 'kpi-dashboard'), $review_notes),
            'related_entity_type' => 'kpi_data',
            'related_entity_id' => $entry_id,
        ]);

        return KPI_Dashboard_KPI_Data_Model::get($entry_id);
    }

    public static function bulk_approve($entry_ids, $reviewed_by, $review_notes = '') {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($entry_ids as $entry_id) {
            $result = self::approve($entry_id, $reviewed_by, $review_notes);

            if (is_wp_error($result)) {
                $results['failed'][] = [
                    'id' => $entry_id,
                    'error' => $result->get_error_message(),
                ];
            } else {
                $results['success'][] = $result;
            }
        }

        return $results;
    }

    public static function get_pending_queue($user) {
        if ($user->role === 'super_admin') {
            // Super admin can see all pending approvals
            return KPI_Dashboard_KPI_Data_Model::get_pending_approvals();
        } elseif ($user->role === 'dept_head') {
            // Dept head can see pending approvals for their departments
            $accessible_depts = KPI_Dashboard_Permissions::get_accessible_departments($user);
            return KPI_Dashboard_KPI_Data_Model::get_pending_approvals($accessible_depts);
        }

        return [];
    }

    public static function get_pending_count($user) {
        $queue = self::get_pending_queue($user);
        return count($queue);
    }
}
