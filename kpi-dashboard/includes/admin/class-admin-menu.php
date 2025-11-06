<?php
/**
 * Admin Menu Integration
 * Minimal WordPress admin integration
 */
class KPI_Dashboard_Admin_Menu {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Add menu items to WordPress admin
     */
    public function add_menu_items() {
        add_menu_page(
            __('KPI Dashboard', 'kpi-dashboard'),
            __('KPI Dashboard', 'kpi-dashboard'),
            'manage_options',
            'kpi-dashboard',
            [$this, 'render_admin_page'],
            'dashicons-chart-line',
            30
        );

        add_submenu_page(
            'kpi-dashboard',
            __('Dashboard', 'kpi-dashboard'),
            __('Dashboard', 'kpi-dashboard'),
            'manage_options',
            'kpi-dashboard',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'kpi-dashboard',
            __('Settings', 'kpi-dashboard'),
            __('Settings', 'kpi-dashboard'),
            'manage_options',
            'kpi-dashboard-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'kpi-dashboard',
            __('System Info', 'kpi-dashboard'),
            __('System Info', 'kpi-dashboard'),
            'manage_options',
            'kpi-dashboard-info',
            [$this, 'render_info_page']
        );
    }

    /**
     * Render main admin page
     */
    public function render_admin_page() {
        $kpi_url = get_site_url() . '/kpi';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2><?php _e('Access KPI Dashboard', 'kpi-dashboard'); ?></h2>
                <p><?php _e('The KPI Dashboard is a standalone application accessible at:', 'kpi-dashboard'); ?></p>
                <p>
                    <a href="<?php echo esc_url($kpi_url); ?>" class="button button-primary button-hero" target="_blank">
                        <?php _e('Open KPI Dashboard', 'kpi-dashboard'); ?>
                    </a>
                </p>
                <p>
                    <strong><?php _e('URL:', 'kpi-dashboard'); ?></strong>
                    <code><?php echo esc_url($kpi_url); ?></code>
                </p>

                <hr>

                <h3><?php _e('Default Login Credentials', 'kpi-dashboard'); ?></h3>
                <p class="description">
                    <?php _e('For security, please change these credentials immediately after first login.', 'kpi-dashboard'); ?>
                </p>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Username:', 'kpi-dashboard'); ?></th>
                        <td><code>admin</code></td>
                    </tr>
                    <tr>
                        <th><?php _e('Password:', 'kpi-dashboard'); ?></th>
                        <td><code>admin</code></td>
                    </tr>
                </table>

                <hr>

                <h3><?php _e('Quick Stats', 'kpi-dashboard'); ?></h3>
                <?php $this->display_quick_stats(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Display quick statistics
     */
    private function display_quick_stats() {
        $total_users = KPI_Dashboard_User_Model::count(['is_active' => 1]);
        $total_departments = count(KPI_Dashboard_Department_Model::get_all(['is_active' => 1]));
        $total_kpis = count(KPI_Dashboard_KPI_Model::get_all(['is_active' => 1]));
        $pending_approvals = count(KPI_Dashboard_KPI_Data_Model::get_pending_approvals());

        ?>
        <table class="wp-list-table widefat fixed striped">
            <tbody>
                <tr>
                    <td><strong><?php _e('Active Users', 'kpi-dashboard'); ?></strong></td>
                    <td><?php echo esc_html($total_users); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Departments', 'kpi-dashboard'); ?></strong></td>
                    <td><?php echo esc_html($total_departments); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Active KPIs', 'kpi-dashboard'); ?></strong></td>
                    <td><?php echo esc_html($total_kpis); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Pending Approvals', 'kpi-dashboard'); ?></strong></td>
                    <td><?php echo esc_html($pending_approvals); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['kpi_settings_submit'])) {
            check_admin_referer('kpi_settings_save');
            $this->save_settings();
        }

        $settings = get_option('kpi_dashboard_settings', []);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form method="post" action="">
                <?php wp_nonce_field('kpi_settings_save'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="company_name"><?php _e('Company Name', 'kpi-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="company_name" name="settings[company_name]"
                                   value="<?php echo esc_attr($settings['company_name'] ?? ''); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_logo"><?php _e('Company Logo URL', 'kpi-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="company_logo" name="settings[company_logo]"
                                   value="<?php echo esc_url($settings['company_logo'] ?? ''); ?>"
                                   class="regular-text">
                            <p class="description"><?php _e('Full URL to company logo image', 'kpi-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="primary_color"><?php _e('Primary Color', 'kpi-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="primary_color" name="settings[primary_color]"
                                   value="<?php echo esc_attr($settings['primary_color'] ?? '#1565C0'); ?>"
                                   class="color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="secondary_color"><?php _e('Secondary Color', 'kpi-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="secondary_color" name="settings[secondary_color]"
                                   value="<?php echo esc_attr($settings['secondary_color'] ?? '#FF6B35'); ?>"
                                   class="color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="data_retention_years"><?php _e('Data Retention (Years)', 'kpi-dashboard'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="data_retention_years" name="settings[data_retention_years]"
                                   value="<?php echo esc_attr($settings['data_retention_years'] ?? 3); ?>"
                                   min="1" max="10">
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'kpi-dashboard'), 'primary', 'kpi_settings_submit'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save settings
     */
    private function save_settings() {
        if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
            return;
        }

        $settings = [];
        $allowed_keys = ['company_name', 'company_logo', 'primary_color', 'secondary_color', 'data_retention_years'];

        foreach ($allowed_keys as $key) {
            if (isset($_POST['settings'][$key])) {
                $settings[$key] = sanitize_text_field($_POST['settings'][$key]);
            }
        }

        update_option('kpi_dashboard_settings', $settings);

        add_settings_error(
            'kpi_dashboard_settings',
            'settings_updated',
            __('Settings saved successfully.', 'kpi-dashboard'),
            'success'
        );
    }

    /**
     * Render system info page
     */
    public function render_info_page() {
        global $wpdb;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="card">
                <h2><?php _e('System Information', 'kpi-dashboard'); ?></h2>

                <table class="wp-list-table widefat fixed striped">
                    <tbody>
                        <tr>
                            <td><strong><?php _e('Plugin Version', 'kpi-dashboard'); ?></strong></td>
                            <td><?php echo esc_html(KPI_DASHBOARD_VERSION); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('WordPress Version', 'kpi-dashboard'); ?></strong></td>
                            <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('PHP Version', 'kpi-dashboard'); ?></strong></td>
                            <td><?php echo esc_html(PHP_VERSION); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('MySQL Version', 'kpi-dashboard'); ?></strong></td>
                            <td><?php echo esc_html($wpdb->db_version()); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Database Tables', 'kpi-dashboard'); ?></strong></td>
                            <td><?php $this->check_database_tables(); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Upload Directory Writable', 'kpi-dashboard'); ?></strong></td>
                            <td><?php echo is_writable(wp_upload_dir()['basedir']) ? '✅ Yes' : '❌ No'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2><?php _e('Database Tables Status', 'kpi-dashboard'); ?></h2>
                <?php $this->show_table_status(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Check database tables
     */
    private function check_database_tables() {
        global $wpdb;

        $tables = [
            'kpi_users', 'kpi_departments', 'kpi_positions', 'kpi_department_heads',
            'kpi_definitions', 'kpi_assignments', 'kpi_data', 'kpi_audit_logs',
            'kpi_notifications', 'kpi_alert_rules', 'kpi_user_notification_prefs',
            'kpi_scheduled_reports', 'kpi_report_history', 'kpi_comments',
            'kpi_settings', 'kpi_sessions',
        ];

        $missing = [];
        foreach ($tables as $table) {
            $full_table = $wpdb->prefix . $table;
            if ($wpdb->get_var("SHOW TABLES LIKE '$full_table'") != $full_table) {
                $missing[] = $table;
            }
        }

        if (empty($missing)) {
            echo '✅ ' . __('All tables exist', 'kpi-dashboard');
        } else {
            echo '❌ ' . sprintf(__('%d tables missing', 'kpi-dashboard'), count($missing));
        }
    }

    /**
     * Show table status with row counts
     */
    private function show_table_status() {
        global $wpdb;

        $tables = [
            'kpi_users' => 'Users',
            'kpi_departments' => 'Departments',
            'kpi_positions' => 'Positions',
            'kpi_definitions' => 'KPI Definitions',
            'kpi_data' => 'KPI Data Entries',
            'kpi_notifications' => 'Notifications',
            'kpi_audit_logs' => 'Audit Logs',
        ];

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Table</th><th>Row Count</th></tr></thead>';
        echo '<tbody>';

        foreach ($tables as $table => $label) {
            $full_table = $wpdb->prefix . $table;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
            echo '<tr>';
            echo '<td><strong>' . esc_html($label) . '</strong></td>';
            echo '<td>' . esc_html(number_format($count)) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_styles() {
        $screen = get_current_screen();

        if (strpos($screen->id, 'kpi-dashboard') !== false) {
            wp_enqueue_style('wp-color-picker');
        }
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();

        if (strpos($screen->id, 'kpi-dashboard') !== false) {
            wp_enqueue_script('wp-color-picker');

            wp_add_inline_script('wp-color-picker', '
                jQuery(document).ready(function($) {
                    $(".color-picker").wpColorPicker();
                });
            ');
        }
    }
}
