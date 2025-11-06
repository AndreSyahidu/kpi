<?php
/**
 * Notification Service
 */
class KPI_Dashboard_Notification_Service {

    public static function create($data) {
        $notification_id = KPI_Dashboard_Notification_Model::create($data);

        if (!$notification_id) {
            return false;
        }

        // Send email if configured
        if ($data['sent_via_email'] ?? false) {
            self::send_email_notification($notification_id);
        }

        return $notification_id;
    }

    public static function create_bulk($user_ids, $data) {
        return KPI_Dashboard_Notification_Model::create_bulk($user_ids, $data);
    }

    public static function send_email_notification($notification_id) {
        $notification = KPI_Dashboard_Notification_Model::get($notification_id);
        if (!$notification) {
            return false;
        }

        $user = KPI_Dashboard_User_Model::get($notification->user_id);
        if (!$user) {
            return false;
        }

        // Check user preferences
        $prefs = self::get_user_preferences($user->id);
        if (!$prefs['email_enabled']) {
            return false;
        }

        // Send email
        $result = KPI_Dashboard_Email_Service::send_notification($user->email, $notification);

        if ($result) {
            global $wpdb;
            $table = $wpdb->prefix . 'kpi_notifications';
            $wpdb->update($table, [
                'email_sent_at' => current_time('mysql'),
            ], ['id' => $notification_id]);
        }

        return $result;
    }

    public static function get_user_preferences($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_user_notification_prefs';

        $prefs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d",
            $user_id
        ), OBJECT_K);

        // Default preferences
        $defaults = [
            'email_enabled' => true,
            'in_app_enabled' => true,
            'reminder_enabled' => true,
            'approval_enabled' => true,
            'alert_enabled' => true,
            'frequency' => 'realtime',
        ];

        // Merge with stored preferences
        foreach ($prefs as $pref) {
            $key = $pref->notification_type . '_' . $pref->channel . '_enabled';
            $defaults[$key] = (bool) $pref->enabled;
        }

        return $defaults;
    }

    public static function update_user_preferences($user_id, $preferences) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_user_notification_prefs';

        // Delete existing preferences
        $wpdb->delete($table, ['user_id' => $user_id]);

        // Insert new preferences
        foreach ($preferences as $type => $settings) {
            foreach ($settings as $channel => $enabled) {
                $wpdb->insert($table, [
                    'user_id' => $user_id,
                    'notification_type' => $type,
                    'channel' => $channel,
                    'enabled' => $enabled ? 1 : 0,
                    'frequency' => $settings['frequency'] ?? 'realtime',
                ]);
            }
        }

        return true;
    }

    public static function notify_by_alert_rules($kpi_id, $entry, $condition_type, $severity) {
        global $wpdb;
        $table = $wpdb->prefix . 'kpi_alert_rules';

        $rules = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
            WHERE (kpi_id = %d OR kpi_id IS NULL)
            AND condition_type = %s
            AND severity = %s
            AND is_active = 1",
            $kpi_id,
            $condition_type,
            $severity
        ));

        foreach ($rules as $rule) {
            $recipients = json_decode($rule->recipients, true);
            $channels = json_decode($rule->notification_channels, true);

            $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);

            $notification_data = [
                'type' => 'alert',
                'severity' => $severity,
                'title' => sprintf(__('KPI Alert: %s', 'kpi-dashboard'), $kpi->name),
                'message' => sprintf(
                    __('KPI "%s" is %s threshold (%s)', 'kpi-dashboard'),
                    $kpi->name,
                    $condition_type === 'below_target' ? 'below' : 'above',
                    $entry->value . ' ' . $entry->unit
                ),
                'related_entity_type' => 'kpi_data',
                'related_entity_id' => $entry->id,
                'sent_via_email' => in_array('email', $channels),
            ];

            // Send to recipients
            foreach ($recipients as $recipient) {
                if (is_numeric($recipient)) {
                    // User ID
                    $notification_data['user_id'] = $recipient;
                    self::create($notification_data);
                } elseif ($recipient === 'dept_head') {
                    // All department heads of the department
                    $heads = KPI_Dashboard_Department_Model::get_heads($entry->department_id);
                    foreach ($heads as $head) {
                        $notification_data['user_id'] = $head->id;
                        self::create($notification_data);
                    }
                } elseif ($recipient === 'super_admin') {
                    // All super admins
                    $admins = KPI_Dashboard_User_Model::get_by_role('super_admin');
                    foreach ($admins as $admin) {
                        $notification_data['user_id'] = $admin->id;
                        self::create($notification_data);
                    }
                }
            }
        }
    }

    public static function send_weekly_reminder() {
        // Get all managers and dept heads
        $users = array_merge(
            KPI_Dashboard_User_Model::get_by_role('manager'),
            KPI_Dashboard_User_Model::get_by_role('dept_head')
        );

        foreach ($users as $user) {
            // Check if they have pending data entry
            $kpis = KPI_Dashboard_KPI_Service::get_kpis_for_user($user);

            if (!empty($kpis)) {
                self::create([
                    'user_id' => $user->id,
                    'type' => 'reminder',
                    'severity' => 'info',
                    'title' => __('Weekly KPI Data Entry Reminder', 'kpi-dashboard'),
                    'message' => sprintf(__('You have %d KPIs that require data entry this week', 'kpi-dashboard'), count($kpis)),
                    'action_url' => '/kpi/data-entry',
                    'sent_via_email' => true,
                ]);
            }
        }
    }
}
