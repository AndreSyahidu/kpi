<?php
/**
 * KPI Data Service
 */
class KPI_Dashboard_Data_Service {

    public static function create_entry($data, $submitted_by) {
        // Get KPI definition for validation
        $kpi = KPI_Dashboard_KPI_Model::get($data['kpi_id']);
        if (!$kpi) {
            return new WP_Error('kpi_not_found', __('KPI not found', 'kpi-dashboard'));
        }

        // Validate
        $validation = KPI_Dashboard_Validator::validate_kpi_data($data, $kpi);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        // Sanitize
        $data = KPI_Dashboard_Sanitizer::kpi_entry_data($data);
        $data['submitted_by'] = $submitted_by;
        $data['status'] = $data['status'] ?? 'pending';

        // Check for duplicate entry (same KPI, department, period)
        $existing = KPI_Dashboard_KPI_Data_Model::get_all([
            'kpi_id' => $data['kpi_id'],
            'department_id' => $data['department_id'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'limit' => 1,
        ]);

        if (!empty($existing)) {
            return new WP_Error('duplicate_entry', __('A data entry already exists for this period', 'kpi-dashboard'));
        }

        $entry_id = KPI_Dashboard_KPI_Data_Model::create($data);

        if (!$entry_id) {
            return new WP_Error('create_failed', __('Failed to create data entry', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('create', 'kpi_data', $entry_id, null, $data);

        // Notify department heads for approval
        self::notify_for_approval($entry_id);

        // Check alerts
        self::check_alerts($entry_id);

        return KPI_Dashboard_KPI_Data_Model::get($entry_id);
    }

    public static function update_entry($entry_id, $data, $user) {
        $entry = KPI_Dashboard_KPI_Data_Model::get($entry_id);

        if (!$entry) {
            return new WP_Error('not_found', __('Data entry not found', 'kpi-dashboard'));
        }

        // Permission check
        if (!KPI_Dashboard_Permissions::can_edit_data($user, $entry)) {
            return new WP_Error('permission_denied', __('You do not have permission to edit this entry', 'kpi-dashboard'));
        }

        // Get KPI definition for validation
        $kpi = KPI_Dashboard_KPI_Model::get($entry->kpi_id);

        // Validate
        if (isset($data['value'])) {
            $validation = KPI_Dashboard_Validator::validate_kpi_data(array_merge((array)$entry, $data), $kpi);
            if ($validation !== true) {
                return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
            }
        }

        $data = KPI_Dashboard_Sanitizer::kpi_entry_data($data);
        $before = clone $entry;

        $result = KPI_Dashboard_KPI_Data_Model::update($entry_id, $data);

        if ($result === false) {
            return new WP_Error('update_failed', __('Failed to update entry', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('update', 'kpi_data', $entry_id, $before, $data, $data['reason'] ?? null);

        return KPI_Dashboard_KPI_Data_Model::get($entry_id);
    }

    public static function bulk_create($entries, $submitted_by) {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($entries as $data) {
            $entry = self::create_entry($data, $submitted_by);

            if (is_wp_error($entry)) {
                $results['failed'][] = [
                    'data' => $data,
                    'error' => $entry->get_error_message(),
                ];
            } else {
                $results['success'][] = $entry;
            }
        }

        return $results;
    }

    private static function notify_for_approval($entry_id) {
        $entry = KPI_Dashboard_KPI_Data_Model::get($entry_id);
        if (!$entry) {
            return;
        }

        // Get department heads
        $heads = KPI_Dashboard_Department_Model::get_heads($entry->department_id);

        foreach ($heads as $head) {
            KPI_Dashboard_Notification_Service::create([
                'user_id' => $head->id,
                'type' => 'approval',
                'severity' => 'info',
                'title' => __('New Data Entry Awaiting Approval', 'kpi-dashboard'),
                'message' => sprintf(__('A new KPI data entry has been submitted and requires your approval'), ''),
                'action_url' => '/kpi/approvals',
                'related_entity_type' => 'kpi_data',
                'related_entity_id' => $entry_id,
            ]);
        }
    }

    private static function check_alerts($entry_id) {
        $entry = KPI_Dashboard_KPI_Data_Model::get($entry_id);
        if (!$entry || $entry->status !== 'approved') {
            return;
        }

        $kpi = KPI_Dashboard_KPI_Model::get($entry->kpi_id);
        if (!$kpi) {
            return;
        }

        // Check if value is below target
        if ($kpi->target_value && $kpi->target_type === 'minimum') {
            $achievement_pct = ($entry->value / $kpi->target_value) * 100;

            if ($achievement_pct < 70) {
                // Critical alert
                KPI_Dashboard_Notification_Service::notify_by_alert_rules($kpi->id, $entry, 'below_target', 'critical');
            } elseif ($achievement_pct < 90) {
                // Warning alert
                KPI_Dashboard_Notification_Service::notify_by_alert_rules($kpi->id, $entry, 'below_target', 'warning');
            }
        }
    }

    public static function get_department_data($department_id, $args = []) {
        $defaults = [
            'period_start' => date('Y-m-01'), // Start of current month
            'period_end' => date('Y-m-t'),     // End of current month
            'status' => 'approved',
            'limit' => 100,
        ];

        $args = wp_parse_args($args, $defaults);
        $args['department_id'] = $department_id;

        return KPI_Dashboard_KPI_Data_Model::get_all($args);
    }

    public static function get_kpi_data_with_comparison($kpi_id, $department_id, $period_start, $period_end) {
        // Current period data
        $current = KPI_Dashboard_KPI_Data_Model::get_all([
            'kpi_id' => $kpi_id,
            'department_id' => $department_id,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'status' => 'approved',
        ]);

        // Calculate previous period
        $start = new DateTime($period_start);
        $end = new DateTime($period_end);
        $interval = $start->diff($end);

        $prev_end = clone $start;
        $prev_end->modify('-1 day');
        $prev_start = clone $prev_end;
        $prev_start->sub($interval);

        // Previous period data
        $previous = KPI_Dashboard_KPI_Data_Model::get_all([
            'kpi_id' => $kpi_id,
            'department_id' => $department_id,
            'period_start' => $prev_start->format('Y-m-d'),
            'period_end' => $prev_end->format('Y-m-d'),
            'status' => 'approved',
        ]);

        // Calculate stats
        $current_total = array_sum(array_column($current, 'value'));
        $previous_total = array_sum(array_column($previous, 'value'));

        $change = 0;
        $change_pct = 0;
        if ($previous_total > 0) {
            $change = $current_total - $previous_total;
            $change_pct = ($change / $previous_total) * 100;
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'current_total' => $current_total,
            'previous_total' => $previous_total,
            'change' => $change,
            'change_percentage' => $change_pct,
            'trend' => $change >= 0 ? 'up' : 'down',
        ];
    }
}
