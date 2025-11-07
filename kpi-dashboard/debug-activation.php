<?php
/**
 * KPI Dashboard - Activation Debug Script
 *
 * Run this file directly to see detailed activation errors
 * Access via: http://yoursite.com/wp-content/plugins/kpi-dashboard/debug-activation.php
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../../../wp-load.php',
    __DIR__ . '/../../../../../wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $wp_load) {
    if (file_exists($wp_load)) {
        require_once $wp_load;
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('Error: Could not find WordPress. Please run this from wp-content/plugins/kpi-dashboard/');
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo '<pre>';
echo "KPI Dashboard - Activation Debug\n";
echo "=================================\n\n";

// Check PHP version
echo "PHP Version: " . PHP_VERSION;
if (version_compare(PHP_VERSION, '8.1', '<')) {
    echo " ❌ (Requires 8.1+)\n";
} else {
    echo " ✅\n";
}

// Check WordPress version
echo "WordPress Version: " . get_bloginfo('version');
if (version_compare(get_bloginfo('version'), '6.4', '<')) {
    echo " ❌ (Requires 6.4+)\n";
} else {
    echo " ✅\n";
}

// Check required extensions
echo "\nRequired PHP Extensions:\n";
$required_extensions = ['mysqli', 'json', 'mbstring', 'openssl'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "  - $ext: " . ($loaded ? "✅" : "❌") . "\n";
}

// Check file existence
echo "\nChecking files:\n";
$required_files = [
    'kpi-dashboard.php',
    'includes/class-activator.php',
    'includes/class-kpi-dashboard.php',
    'includes/database/class-db-schema.php',
];

foreach ($required_files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "  - $file: " . ($exists ? "✅" : "❌") . "\n";
}

// Try to load main plugin file
echo "\nLoading plugin files...\n";

try {
    if (!defined('KPI_DASHBOARD_VERSION')) {
        require_once __DIR__ . '/kpi-dashboard.php';
        echo "  ✅ Main plugin file loaded\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error loading main file: " . $e->getMessage() . "\n";
    echo "  Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

// Try activation
echo "\nTesting activation...\n";

try {
    require_once __DIR__ . '/includes/class-activator.php';

    // Check if we can call the activation
    echo "  - Activator class loaded ✅\n";

    // Test database connection
    global $wpdb;
    $test_query = $wpdb->query("SELECT 1");
    if ($test_query !== false) {
        echo "  - Database connection ✅\n";
    } else {
        echo "  - Database connection ❌\n";
        echo "    Error: " . $wpdb->last_error . "\n";
    }

    // Try to activate
    echo "\n  Attempting activation...\n";
    KPI_Dashboard_Activator::activate();
    echo "  ✅ Activation successful!\n";

} catch (Throwable $e) {
    echo "  ❌ Activation failed!\n";
    echo "\nError Details:\n";
    echo "  Type: " . get_class($e) . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

// Check database tables
echo "\n\nChecking Database Tables:\n";
global $wpdb;

$tables = [
    'kpi_users',
    'kpi_departments',
    'kpi_positions',
    'kpi_definitions',
    'kpi_data',
    'kpi_notifications',
];

foreach ($tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
    echo "  - $table: " . ($exists ? "✅" : "❌") . "\n";
}

// Check for WordPress errors
if (is_wp_error($wpdb->last_error)) {
    echo "\nWordPress Errors:\n";
    echo $wpdb->last_error . "\n";
}

echo "\n=================================\n";
echo "Debug complete.\n";
echo "\nIf activation failed, please:\n";
echo "1. Copy the error message above\n";
echo "2. Check WordPress debug.log file\n";
echo "3. Ensure all PHP extensions are installed\n";
echo "4. Verify database permissions\n";

// Enable WordPress debug if not already
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    echo "\nRecommendation: Enable WordPress debug mode\n";
    echo "Add to wp-config.php:\n";
    echo "  define('WP_DEBUG', true);\n";
    echo "  define('WP_DEBUG_LOG', true);\n";
    echo "  define('WP_DEBUG_DISPLAY', false);\n";
}

echo '</pre>';
