<?php
/**
 * Debug REST API - KPI Dashboard
 * Check if REST API endpoints are working
 */

// Load WordPress
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 KPI Dashboard - REST API Debug</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
    h1 { color: #4fc3f7; }
    h2 { color: #81c784; margin-top: 30px; }
    .success { color: #81c784; }
    .error { color: #e57373; }
    .warning { color: #ffb74d; }
    pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
    .box { background: #2d2d2d; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 3px solid #4fc3f7; }
</style>";

// 1. Check if REST API is enabled
echo "<h2>1. WordPress REST API Status</h2>";
echo "<div class='box'>";
$rest_enabled = function_exists('rest_get_server');
if ($rest_enabled) {
    echo "<span class='success'>✓ REST API is enabled</span><br>";
    echo "REST URL: <code>" . rest_url() . "</code>";
} else {
    echo "<span class='error'>✗ REST API is NOT enabled</span>";
}
echo "</div>";

// 2. Check KPI REST API namespace
echo "<h2>2. KPI REST API Namespace</h2>";
echo "<div class='box'>";
$kpi_rest_url = rest_url('kpi/v1');
echo "KPI REST URL: <code>" . $kpi_rest_url . "</code><br><br>";

// Try to get REST routes
$wp_rest_server = rest_get_server();
$routes = $wp_rest_server->get_routes();
$kpi_routes = [];

foreach ($routes as $route => $handlers) {
    if (strpos($route, '/kpi/v1') === 0) {
        $kpi_routes[] = $route;
    }
}

if (!empty($kpi_routes)) {
    echo "<span class='success'>✓ Found " . count($kpi_routes) . " KPI API routes</span><br>";
    echo "<pre>";
    foreach ($kpi_routes as $route) {
        echo $route . "\n";
    }
    echo "</pre>";
} else {
    echo "<span class='error'>✗ No KPI API routes found!</span><br>";
    echo "<span class='warning'>⚠ Plugin may not be properly initialized</span>";
}
echo "</div>";

// 3. Test Auth Endpoint
echo "<h2>3. Test Authentication Endpoint</h2>";
echo "<div class='box'>";

$auth_url = rest_url('kpi/v1/auth/login');
echo "Testing: <code>POST {$auth_url}</code><br><br>";

$response = wp_remote_post($auth_url, [
    'headers' => ['Content-Type' => 'application/json'],
    'body' => json_encode([
        'username' => 'admin',
        'password' => 'admin'
    ])
]);

if (is_wp_error($response)) {
    echo "<span class='error'>✗ Request failed: " . $response->get_error_message() . "</span>";
} else {
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    echo "Status Code: <code>" . $status_code . "</code><br>";

    if ($status_code == 200) {
        echo "<span class='success'>✓ Auth endpoint is working!</span><br>";
    } else {
        echo "<span class='error'>✗ Auth endpoint returned error</span><br>";
    }

    echo "<br>Response:<br><pre>";
    $json = json_decode($body, true);
    echo json_encode($json, JSON_PRETTY_PRINT);
    echo "</pre>";
}
echo "</div>";

// 4. Check Database Tables
echo "<h2>4. Database Tables</h2>";
echo "<div class='box'>";
global $wpdb;

$tables = [
    'kpi_categories' => $wpdb->prefix . 'kpi_categories',
    'kpi_indicators' => $wpdb->prefix . 'kpi_indicators',
    'kpi_indicator_data' => $wpdb->prefix . 'kpi_indicator_data',
    'kpi_settings' => $wpdb->prefix . 'kpi_settings',
    'kpi_audit_logs' => $wpdb->prefix . 'kpi_audit_logs',
];

$all_exist = true;
foreach ($tables as $name => $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;

    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo "<span class='success'>✓ $table</span> - $count rows<br>";
    } else {
        echo "<span class='error'>✗ $table - NOT FOUND</span><br>";
        $all_exist = false;
    }
}

if (!$all_exist) {
    echo "<br><span class='warning'>⚠ Some tables are missing! Try deactivating and reactivating the plugin.</span>";
}
echo "</div>";

// 5. Check Plugin Classes
echo "<h2>5. Plugin Classes Loaded</h2>";
echo "<div class='box'>";

$classes = [
    'KPI_Dashboard' => 'Main plugin class',
    'KPI_Dashboard_Router' => 'Router class',
    'KPI_Dashboard_API_Auth' => 'Auth API',
    'KPI_Dashboard_API_Categories' => 'Categories API',
    'KPI_Dashboard_API_Indicators' => 'Indicators API',
];

foreach ($classes as $class => $desc) {
    if (class_exists($class)) {
        echo "<span class='success'>✓ $class</span> - $desc<br>";
    } else {
        echo "<span class='error'>✗ $class</span> - $desc - NOT LOADED<br>";
    }
}
echo "</div>";

// 6. Test Direct API Call
echo "<h2>6. Test Categories Endpoint (requires auth)</h2>";
echo "<div class='box'>";
echo "This will show if the API can return data after authentication.<br><br>";

// Get current user token (if logged in)
$current_user = wp_get_current_user();
if ($current_user->ID > 0) {
    echo "Current User: <code>" . $current_user->user_login . "</code> (ID: {$current_user->ID})<br>";

    // Try to get categories
    $categories_url = rest_url('kpi/v1/categories');
    echo "Testing: <code>GET {$categories_url}</code><br><br>";

    $cat_response = wp_remote_get($categories_url, [
        'headers' => [
            'X-WP-Nonce' => wp_create_nonce('wp_rest')
        ],
        'cookies' => $_COOKIE
    ]);

    if (!is_wp_error($cat_response)) {
        $cat_status = wp_remote_retrieve_response_code($cat_response);
        $cat_body = wp_remote_retrieve_body($cat_response);

        echo "Status Code: <code>$cat_status</code><br>";
        echo "Response:<br><pre>";
        $cat_json = json_decode($cat_body, true);
        echo json_encode($cat_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "</pre>";
    }
} else {
    echo "<span class='warning'>⚠ Not logged in to WordPress. Login to test authenticated endpoints.</span>";
}
echo "</div>";

// 7. JavaScript Config Check
echo "<h2>7. Frontend JavaScript Config</h2>";
echo "<div class='box'>";
echo "The frontend should receive this config:<br><br>";
echo "<pre>";
$config = [
    'apiUrl' => rest_url('kpi/v1'),
    'siteUrl' => get_site_url(),
    'baseUrl' => get_site_url() . '/kpi',
    'nonce' => wp_create_nonce('wp_rest'),
    'version' => defined('KPI_DASHBOARD_VERSION') ? KPI_DASHBOARD_VERSION : 'unknown',
    'companyName' => get_option('blogname', 'MBD Corp'),
];
echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "</pre>";
echo "</div>";

// 8. Browser Console Test
echo "<h2>8. Browser Console Test</h2>";
echo "<div class='box'>";
echo "Copy and paste this into your browser console when on /kpi page:<br><br>";
echo "<pre style='background: #1e1e1e; color: #4fc3f7;'>";
echo "// Check if config is loaded\n";
echo "console.log('KPI Config:', window.KPI_DASHBOARD_CONFIG);\n\n";
echo "// Test API call\n";
echo "fetch('" . rest_url('kpi/v1/auth/status') . "', {\n";
echo "  headers: {\n";
echo "    'X-WP-Nonce': window.KPI_DASHBOARD_CONFIG.nonce\n";
echo "  }\n";
echo "})\n";
echo ".then(r => r.json())\n";
echo ".then(data => console.log('API Response:', data))\n";
echo ".catch(err => console.error('API Error:', err));";
echo "</pre>";
echo "</div>";

echo "<hr style='margin: 40px 0; border-color: #4fc3f7;'>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If API routes are missing → Deactivate and reactivate plugin</li>";
echo "<li>If database tables are missing → Reactivate plugin</li>";
echo "<li>If API returns errors → Check browser console on /kpi page</li>";
echo "<li>Run the browser console test above to see actual frontend errors</li>";
echo "</ol>";
