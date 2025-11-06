<?php
/**
 * Plugin Name: KPI Dashboard
 * Plugin URI: https://www.mbdcorp.id
 * Description: Comprehensive KPI Management System with standalone dashboard, user management, department tracking, and advanced analytics.
 * Version: 1.0.0
 * Author: MBD Corp
 * Author URI: https://www.mbdcorp.id
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: kpi-dashboard
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('KPI_DASHBOARD_VERSION', '1.0.0');
define('KPI_DASHBOARD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KPI_DASHBOARD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KPI_DASHBOARD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_kpi_dashboard() {
    require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/class-activator.php';
    KPI_Dashboard_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_kpi_dashboard() {
    require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/class-deactivator.php';
    KPI_Dashboard_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_kpi_dashboard');
register_deactivation_hook(__FILE__, 'deactivate_kpi_dashboard');

/**
 * The core plugin class.
 */
require KPI_DASHBOARD_PLUGIN_DIR . 'includes/class-kpi-dashboard.php';

/**
 * Begins execution of the plugin.
 */
function run_kpi_dashboard() {
    $plugin = new KPI_Dashboard();
    $plugin->run();
}

run_kpi_dashboard();
