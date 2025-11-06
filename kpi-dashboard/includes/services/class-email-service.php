<?php
/**
 * Email Service
 */
class KPI_Dashboard_Email_Service {

    private static function get_email_settings() {
        $settings = get_option('kpi_dashboard_email', []);

        return wp_parse_args($settings, [
            'from_name' => get_bloginfo('name'),
            'from_email' => get_option('admin_email'),
            'smtp_enabled' => false,
        ]);
    }

    private static function send($to, $subject, $message, $headers = []) {
        $settings = self::get_email_settings();

        $default_headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $settings['from_name'] . ' <' . $settings['from_email'] . '>',
        ];

        $headers = array_merge($default_headers, $headers);

        return wp_mail($to, $subject, $message, $headers);
    }

    public static function send_welcome_email($user) {
        $subject = sprintf(__('Welcome to %s KPI Dashboard', 'kpi-dashboard'), get_bloginfo('name'));

        $message = self::get_email_template('welcome', [
            'user_name' => $user->full_name,
            'username' => $user->username,
            'login_url' => get_site_url() . '/kpi',
        ]);

        return self::send($user->email, $subject, $message);
    }

    public static function send_password_reset($email, $name, $reset_link) {
        $subject = __('Password Reset Request', 'kpi-dashboard');

        $message = self::get_email_template('password-reset', [
            'name' => $name,
            'reset_link' => $reset_link,
        ]);

        return self::send($email, $subject, $message);
    }

    public static function send_notification($email, $notification) {
        $subject = $notification->title;

        $message = self::get_email_template('notification', [
            'title' => $notification->title,
            'message' => $notification->message,
            'action_url' => $notification->action_url ? get_site_url() . $notification->action_url : null,
            'severity' => $notification->severity,
        ]);

        return self::send($email, $subject, $message);
    }

    public static function send_weekly_digest($user, $stats) {
        $subject = sprintf(__('Weekly KPI Digest - %s', 'kpi-dashboard'), date('F d, Y'));

        $message = self::get_email_template('weekly-digest', [
            'user_name' => $user->full_name,
            'stats' => $stats,
        ]);

        return self::send($user->email, $subject, $message);
    }

    private static function get_email_template($template, $data) {
        $settings = self::get_email_settings();

        // Extract variables
        extract($data);

        // Start output buffering
        ob_start();

        // Common header
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1565C0; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .button { display: inline-block; padding: 12px 24px; background: #1565C0; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html($settings['from_name']); ?></h1>
                    <p>KPI Dashboard</p>
                </div>
                <div class="content">
        <?php

        // Template-specific content
        switch ($template) {
            case 'welcome':
                ?>
                <h2>Welcome, <?php echo esc_html($user_name); ?>!</h2>
                <p>Your account has been created successfully.</p>
                <p><strong>Username:</strong> <?php echo esc_html($username); ?></p>
                <p>You can now access the KPI Dashboard:</p>
                <p><a href="<?php echo esc_url($login_url); ?>" class="button">Access Dashboard</a></p>
                <?php
                break;

            case 'password-reset':
                ?>
                <h2>Password Reset Request</h2>
                <p>Hello <?php echo esc_html($name); ?>,</p>
                <p>We received a request to reset your password. Click the button below to reset it:</p>
                <p><a href="<?php echo esc_url($reset_link); ?>" class="button">Reset Password</a></p>
                <p>This link will expire in 1 hour. If you didn't request this, please ignore this email.</p>
                <?php
                break;

            case 'notification':
                ?>
                <h2><?php echo esc_html($title); ?></h2>
                <p><?php echo esc_html($message); ?></p>
                <?php if ($action_url): ?>
                <p><a href="<?php echo esc_url($action_url); ?>" class="button">View Details</a></p>
                <?php endif; ?>
                <?php
                break;

            case 'weekly-digest':
                ?>
                <h2>Weekly KPI Summary</h2>
                <p>Hello <?php echo esc_html($user_name); ?>,</p>
                <p>Here's your weekly KPI summary:</p>
                <ul>
                    <li>Pending Approvals: <?php echo esc_html($stats['pending_approvals'] ?? 0); ?></li>
                    <li>Data Entries This Week: <?php echo esc_html($stats['entries_this_week'] ?? 0); ?></li>
                    <li>KPIs Below Target: <?php echo esc_html($stats['below_target'] ?? 0); ?></li>
                </ul>
                <p><a href="<?php echo esc_url(get_site_url() . '/kpi'); ?>" class="button">View Dashboard</a></p>
                <?php
                break;
        }

        // Common footer
        ?>
                </div>
                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($settings['from_name']); ?>. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        <?php

        return ob_get_clean();
    }
}
