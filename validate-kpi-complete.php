<?php
/**
 * KPI Dashboard - Complete Validation Script
 *
 * Upload ke: /public_html/
 * Akses: https://www.mbdcorp.id/validate-kpi-complete.php?key=validate-2024
 *
 * Script ini akan validate SEMUA aspek plugin untuk memastikan semuanya benar
 */

$secret = isset($_GET['key']) ? $_GET['key'] : '';
if ($secret !== 'validate-2024') {
    die('Access denied. Use: ?key=validate-2024');
}

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/wp-load.php',
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/../../wp-load.php',
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
    die('Error: Could not find WordPress.');
}

// Try multiple possible plugin directory locations
$possible_paths = [
    $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/kpi-dashboard/',
    ABSPATH . 'wp-content/plugins/kpi-dashboard/',
    dirname(__FILE__) . '/kpi-dashboard/',
    dirname(__FILE__) . '/wp-content/plugins/kpi-dashboard/',
];

$plugin_dir = '';
foreach ($possible_paths as $path) {
    if (is_dir($path)) {
        $plugin_dir = $path;
        break;
    }
}

if (empty($plugin_dir)) {
    die('<div style="background: #f44336; color: white; padding: 20px; margin: 20px; border-radius: 8px;">
    <h2>❌ Plugin Directory Not Found</h2>
    <p>Tried the following paths:</p>
    <ul>' . implode('', array_map(function($p) { return '<li><code>' . $p . '</code></li>'; }, $possible_paths)) . '</ul>
    <p><strong>Please ensure the KPI Dashboard plugin is installed.</strong></p>
    </div>');
}
$all_checks_passed = true;
$issues = [];
$warnings = [];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI Dashboard - Complete Validation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .content {
            padding: 40px;
        }
        .check-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #667eea;
        }
        .check-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .check-item .icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
            text-align: center;
        }
        .check-item .label {
            flex: 1;
            font-weight: 500;
        }
        .check-item .value {
            color: #666;
            font-family: monospace;
            font-size: 13px;
        }
        .pass { border-left: 4px solid #4caf50; }
        .fail { border-left: 4px solid #f44336; background: #ffebee; }
        .warn { border-left: 4px solid #ff9800; background: #fff3e0; }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #4caf50; color: white; }
        .badge-error { background: #f44336; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .badge-info { background: #2196f3; color: white; }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }
        .summary h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .summary .score {
            font-size: 64px;
            font-weight: bold;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .code-block {
            background: #282c34;
            color: #abb2bf;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            margin: 15px 0;
        }
        .progress-ring {
            display: inline-block;
            margin: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            border: 2px solid white;
            transition: all 0.3s;
        }
        .btn:hover {
            background: transparent;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔍 KPI Dashboard - Complete Validation</h1>
        <p><?php echo $_SERVER['HTTP_HOST']; ?> • <?php echo date('Y-m-d H:i:s'); ?></p>
        <p style="font-size: 12px; opacity: 0.9;">Plugin Dir: <?php echo esc_html($plugin_dir); ?></p>
    </div>

    <div class="content">

<?php

// ============================================
// CHECK 1: Plugin Files Structure
// ============================================

echo '<div class="check-section">';
echo '<h2>📁 Check 1/10: Plugin Files Structure</h2>';

$required_files = [
    'Main Plugin' => $plugin_dir . 'kpi-dashboard.php',
    'Router Class' => $plugin_dir . 'includes/core/class-router.php',
    'Main Plugin Class' => $plugin_dir . 'includes/class-kpi-dashboard.php',
    'Loader Class' => $plugin_dir . 'includes/class-loader.php',
    'Auth API' => $plugin_dir . 'includes/api/class-auth-api.php',
    'Users API' => $plugin_dir . 'includes/api/class-users-api.php',
];

$files_ok = true;
foreach ($required_files as $name => $path) {
    $exists = file_exists($path);
    $files_ok = $files_ok && $exists;

    echo '<div class="check-item ' . ($exists ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($exists ? '✅' : '❌') . '</div>';
    echo '<div class="label">' . $name . '</div>';
    echo '<div class="value">' . ($exists ? 'Found' : 'MISSING') . '</div>';
    echo '</div>';

    if (!$exists) {
        $issues[] = "Missing file: $name";
        $all_checks_passed = false;
    }
}

echo '</div>';

// ============================================
// CHECK 2: Build Files & Manifest
// ============================================

echo '<div class="check-section">';
echo '<h2>📦 Check 2/10: Build Files & Manifest</h2>';

$manifest_path = $plugin_dir . 'assets/dist/.vite/manifest.json';
$manifest_exists = file_exists($manifest_path);

echo '<div class="check-item ' . ($manifest_exists ? 'pass' : 'fail') . '">';
echo '<div class="icon">' . ($manifest_exists ? '✅' : '❌') . '</div>';
echo '<div class="label">Vite Manifest</div>';
echo '<div class="value">' . ($manifest_exists ? 'Found' : 'MISSING') . '</div>';
echo '</div>';

if ($manifest_exists) {
    $manifest = json_decode(file_get_contents($manifest_path), true);

    if ($manifest && isset($manifest['assets/src/main.tsx'])) {
        $entry = $manifest['assets/src/main.tsx'];

        echo '<div class="check-item pass">';
        echo '<div class="icon">✅</div>';
        echo '<div class="label">Main Entry Point</div>';
        echo '<div class="value">assets/src/main.tsx</div>';
        echo '</div>';

        // Check JS file
        $js_file = $plugin_dir . 'assets/dist/' . $entry['file'];
        $js_exists = file_exists($js_file);

        echo '<div class="check-item ' . ($js_exists ? 'pass' : 'fail') . '">';
        echo '<div class="icon">' . ($js_exists ? '✅' : '❌') . '</div>';
        echo '<div class="label">Main JS Bundle</div>';
        echo '<div class="value">' . $entry['file'];
        if ($js_exists) {
            echo ' (' . round(filesize($js_file) / 1024, 2) . ' KB)';
        }
        echo '</div>';
        echo '</div>';

        // Check CSS file
        if (isset($entry['css'][0])) {
            $css_file = $plugin_dir . 'assets/dist/' . $entry['css'][0];
            $css_exists = file_exists($css_file);

            echo '<div class="check-item ' . ($css_exists ? 'pass' : 'fail') . '">';
            echo '<div class="icon">' . ($css_exists ? '✅' : '❌') . '</div>';
            echo '<div class="label">Main CSS Bundle</div>';
            echo '<div class="value">' . $entry['css'][0];
            if ($css_exists) {
                echo ' (' . round(filesize($css_file) / 1024, 2) . ' KB)';
            }
            echo '</div>';
            echo '</div>';

            if (!$css_exists) {
                $issues[] = "CSS file missing: " . $entry['css'][0];
                $all_checks_passed = false;
            }
        }

        if (!$js_exists) {
            $issues[] = "JS file missing: " . $entry['file'];
            $all_checks_passed = false;
        }
    } else {
        echo '<div class="check-item fail">';
        echo '<div class="icon">❌</div>';
        echo '<div class="label">Manifest Structure</div>';
        echo '<div class="value">Invalid - missing assets/src/main.tsx entry</div>';
        echo '</div>';
        $issues[] = "Invalid manifest structure";
        $all_checks_passed = false;
    }
} else {
    $issues[] = "Manifest file not found";
    $all_checks_passed = false;
}

echo '</div>';

// ============================================
// CHECK 3: Router Configuration
// ============================================

echo '<div class="check-section">';
echo '<h2>🔀 Check 3/10: Router Configuration</h2>';

$router_file = $plugin_dir . 'includes/core/class-router.php';
if (file_exists($router_file)) {
    $router_content = file_get_contents($router_file);

    // Check for correct HTML element
    $has_root_element = preg_match('/<div\s+id=["\']root["\']\s*>/', $router_content);

    echo '<div class="check-item ' . ($has_root_element ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_root_element ? '✅' : '❌') . '</div>';
    echo '<div class="label">React Root Element</div>';
    echo '<div class="value">' . ($has_root_element ? 'Correct: &lt;div id="root"&gt;' : 'WRONG or MISSING') . '</div>';
    echo '</div>';

    // Check for rewrite rules
    $has_rewrite_rules = strpos($router_content, 'add_rewrite_rule') !== false;

    echo '<div class="check-item ' . ($has_rewrite_rules ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_rewrite_rules ? '✅' : '❌') . '</div>';
    echo '<div class="label">Rewrite Rules Setup</div>';
    echo '<div class="value">' . ($has_rewrite_rules ? 'Configured' : 'MISSING') . '</div>';
    echo '</div>';

    if (!$has_root_element) {
        $issues[] = "Router using wrong root element ID";
        $all_checks_passed = false;
    }
    if (!$has_rewrite_rules) {
        $issues[] = "Rewrite rules not configured";
        $all_checks_passed = false;
    }
}

echo '</div>';

// ============================================
// CHECK 4: React App Configuration
// ============================================

echo '<div class="check-section">';
echo '<h2>⚛️ Check 4/10: React App Configuration</h2>';

$main_tsx = $plugin_dir . 'assets/src/main.tsx';
if (file_exists($main_tsx)) {
    $main_content = file_get_contents($main_tsx);

    // Check for correct mount point
    $has_correct_mount = preg_match('/getElementById\(["\']root["\']\)/', $main_content);

    echo '<div class="check-item ' . ($has_correct_mount ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_correct_mount ? '✅' : '❌') . '</div>';
    echo '<div class="label">React Mount Point</div>';
    echo '<div class="value">' . ($has_correct_mount ? 'Correct: getElementById("root")' : 'WRONG or MISSING') . '</div>';
    echo '</div>';

    // Check for React imports
    $has_react_import = strpos($main_content, 'import React') !== false;
    $has_reactdom_import = strpos($main_content, 'import ReactDOM') !== false;

    echo '<div class="check-item ' . ($has_react_import && $has_reactdom_import ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_react_import && $has_reactdom_import ? '✅' : '❌') . '</div>';
    echo '<div class="label">React Imports</div>';
    echo '<div class="value">' . ($has_react_import && $has_reactdom_import ? 'Correct' : 'MISSING') . '</div>';
    echo '</div>';

    if (!$has_correct_mount) {
        $issues[] = "React app mounting to wrong element";
        $all_checks_passed = false;
    }
} else {
    echo '<div class="check-item fail">';
    echo '<div class="icon">❌</div>';
    echo '<div class="label">main.tsx</div>';
    echo '<div class="value">NOT FOUND</div>';
    echo '</div>';
    $issues[] = "main.tsx not found";
    $all_checks_passed = false;
}

echo '</div>';

// ============================================
// CHECK 5: WordPress Integration
// ============================================

echo '<div class="check-section">';
echo '<h2>🔌 Check 5/10: WordPress Integration</h2>';

// Check if plugin is active
if (function_exists('is_plugin_active')) {
    $is_active = is_plugin_active('kpi-dashboard/kpi-dashboard.php');

    echo '<div class="check-item ' . ($is_active ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($is_active ? '✅' : '❌') . '</div>';
    echo '<div class="label">Plugin Status</div>';
    echo '<div class="value">' . ($is_active ? 'ACTIVE' : 'INACTIVE') . '</div>';
    echo '</div>';

    if (!$is_active) {
        $issues[] = "Plugin is not activated";
        $all_checks_passed = false;
    }
}

// Check rewrite rules
$rules = get_option('rewrite_rules');
$kpi_rules = 0;
foreach ($rules as $pattern => $rewrite) {
    if (strpos($pattern, 'kpi') !== false || strpos($rewrite, 'kpi') !== false) {
        $kpi_rules++;
    }
}

echo '<div class="check-item ' . ($kpi_rules > 0 ? 'pass' : 'fail') . '">';
echo '<div class="icon">' . ($kpi_rules > 0 ? '✅' : '❌') . '</div>';
echo '<div class="label">Rewrite Rules Registered</div>';
echo '<div class="value">' . ($kpi_rules > 0 ? "$kpi_rules rules found" : 'NO RULES') . '</div>';
echo '</div>';

if ($kpi_rules === 0) {
    $warnings[] = "No rewrite rules found - run 'flush_rewrite_rules()' or go to Settings > Permalinks > Save";
}

echo '</div>';

// ============================================
// CHECK 6: REST API Endpoints
// ============================================

echo '<div class="check-section">';
echo '<h2>🌐 Check 6/10: REST API Endpoints</h2>';

if (function_exists('rest_get_server')) {
    $rest_server = rest_get_server();
    $routes = $rest_server->get_routes();

    $kpi_routes = array_filter($routes, function($route) {
        return strpos($route, '/kpi/v1') === 0;
    }, ARRAY_FILTER_USE_KEY);

    $routes_count = count($kpi_routes);

    echo '<div class="check-item ' . ($routes_count > 0 ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($routes_count > 0 ? '✅' : '❌') . '</div>';
    echo '<div class="label">API Routes Registered</div>';
    echo '<div class="value">' . $routes_count . ' endpoints</div>';
    echo '</div>';

    if ($routes_count > 0) {
        $critical_endpoints = [
            '/kpi/v1/auth/login',
            '/kpi/v1/auth/me',
            '/kpi/v1/users',
            '/kpi/v1/departments',
        ];

        foreach ($critical_endpoints as $endpoint) {
            $exists = isset($kpi_routes[$endpoint]);
            echo '<div class="check-item ' . ($exists ? 'pass' : 'warn') . '">';
            echo '<div class="icon">' . ($exists ? '✅' : '⚠️') . '</div>';
            echo '<div class="label">' . $endpoint . '</div>';
            echo '<div class="value">' . ($exists ? 'Registered' : 'Missing') . '</div>';
            echo '</div>';
        }
    } else {
        $issues[] = "No API endpoints registered";
        $all_checks_passed = false;
    }
}

echo '</div>';

// ============================================
// CHECK 7: Database Tables
// ============================================

echo '<div class="check-section">';
echo '<h2>🗄️ Check 7/10: Database Tables</h2>';

global $wpdb;

$required_tables = [
    'kpi_users',
    'kpi_departments',
    'kpi_positions',
    'kpi_definitions',
    'kpi_data',
    'kpi_sessions',
];

$tables_ok = true;
foreach ($required_tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table;
    $tables_ok = $tables_ok && $exists;

    $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_table") : 0;

    echo '<div class="check-item ' . ($exists ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($exists ? '✅' : '❌') . '</div>';
    echo '<div class="label">' . $table . '</div>';
    echo '<div class="value">' . ($exists ? "$count rows" : 'MISSING') . '</div>';
    echo '</div>';

    if (!$exists) {
        $issues[] = "Database table missing: $table";
        $all_checks_passed = false;
    }
}

echo '</div>';

// ============================================
// CHECK 8: File Permissions
// ============================================

echo '<div class="check-section">';
echo '<h2>🔐 Check 8/10: File Permissions</h2>';

$check_paths = [
    'Plugin Directory' => $plugin_dir,
    'Assets Directory' => $plugin_dir . 'assets/',
    'Dist Directory' => $plugin_dir . 'assets/dist/',
];

foreach ($check_paths as $name => $path) {
    $readable = is_readable($path);

    echo '<div class="check-item ' . ($readable ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($readable ? '✅' : '❌') . '</div>';
    echo '<div class="label">' . $name . '</div>';
    echo '<div class="value">';
    if (file_exists($path)) {
        echo 'Readable: ' . ($readable ? 'Yes' : 'No');
        echo ' | Perms: ' . substr(sprintf('%o', fileperms($path)), -4);
    } else {
        echo 'NOT FOUND';
    }
    echo '</div>';
    echo '</div>';

    if (!$readable && file_exists($path)) {
        $warnings[] = "Permission issue: $name";
    }
}

echo '</div>';

// ============================================
// CHECK 9: Simulated Page Render
// ============================================

echo '<div class="check-section">';
echo '<h2>🎨 Check 9/10: Simulated Page Render</h2>';

$test_url = home_url('/kpi');
$response = wp_remote_get($test_url, [
    'timeout' => 15,
    'sslverify' => false,
]);

if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $status = wp_remote_retrieve_response_code($response);

    echo '<div class="check-item ' . ($status == 200 ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($status == 200 ? '✅' : '❌') . '</div>';
    echo '<div class="label">HTTP Status</div>';
    echo '<div class="value">' . $status . '</div>';
    echo '</div>';

    // Check for root element
    $has_root = preg_match('/<div[^>]*id=["\']root["\'][^>]*>/i', $body);

    echo '<div class="check-item ' . ($has_root ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_root ? '✅' : '❌') . '</div>';
    echo '<div class="label">React Root Element in HTML</div>';
    echo '<div class="value">' . ($has_root ? 'Found: &lt;div id="root"&gt;' : 'NOT FOUND') . '</div>';
    echo '</div>';

    // Check for script tag
    $has_script = preg_match('/<script[^>]*src=["\']([^"\']*main-[^"\']*\.js)["\'][^>]*>/i', $body, $script_match);

    echo '<div class="check-item ' . ($has_script ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_script ? '✅' : '❌') . '</div>';
    echo '<div class="label">Main Script Tag in HTML</div>';
    echo '<div class="value">' . ($has_script ? 'Found: ' . basename($script_match[1]) : 'NOT FOUND') . '</div>';
    echo '</div>';

    // Check for config
    $has_config = strpos($body, 'window.KPI_DASHBOARD_CONFIG') !== false;

    echo '<div class="check-item ' . ($has_config ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($has_config ? '✅' : '❌') . '</div>';
    echo '<div class="label">Global Config Object</div>';
    echo '<div class="value">' . ($has_config ? 'Found: window.KPI_DASHBOARD_CONFIG' : 'NOT FOUND') . '</div>';
    echo '</div>';

    if (!$has_root || !$has_script || !$has_config) {
        $issues[] = "HTML output missing critical elements";
        $all_checks_passed = false;
    }
} else {
    echo '<div class="check-item fail">';
    echo '<div class="icon">❌</div>';
    echo '<div class="label">Page Fetch Error</div>';
    echo '<div class="value">' . $response->get_error_message() . '</div>';
    echo '</div>';
    $issues[] = "Cannot fetch /kpi page";
    $all_checks_passed = false;
}

echo '</div>';

// ============================================
// CHECK 10: Final Integration Test
// ============================================

echo '<div class="check-section">';
echo '<h2>🧪 Check 10/10: Integration Test</h2>';

// Test API endpoint
$api_test_url = rest_url('kpi/v1/departments');
$api_response = wp_remote_get($api_test_url, [
    'timeout' => 10,
    'sslverify' => false,
]);

if (!is_wp_error($api_response)) {
    $api_status = wp_remote_retrieve_response_code($api_response);

    echo '<div class="check-item ' . ($api_status == 200 || $api_status == 401 ? 'pass' : 'fail') . '">';
    echo '<div class="icon">' . ($api_status == 200 || $api_status == 401 ? '✅' : '❌') . '</div>';
    echo '<div class="label">API Endpoint Response</div>';
    echo '<div class="value">Status ' . $api_status . ' (' . ($api_status == 401 ? 'Auth required - OK' : ($api_status == 200 ? 'Success' : 'Error')) . ')</div>';
    echo '</div>';
}

// Test static asset
if (isset($entry) && isset($entry['file'])) {
    $js_url = plugins_url('assets/dist/' . $entry['file'], $plugin_dir . 'kpi-dashboard.php');
    $js_test = wp_remote_head($js_url, ['timeout' => 10, 'sslverify' => false]);

    if (!is_wp_error($js_test)) {
        $js_status = wp_remote_retrieve_response_code($js_test);

        echo '<div class="check-item ' . ($js_status == 200 ? 'pass' : 'fail') . '">';
        echo '<div class="icon">' . ($js_status == 200 ? '✅' : '❌') . '</div>';
        echo '<div class="label">Static Asset Accessibility</div>';
        echo '<div class="value">Status ' . $js_status . '</div>';
        echo '</div>';

        if ($js_status != 200) {
            $issues[] = "JS bundle not accessible via HTTP";
            $all_checks_passed = false;
        }
    }
}

echo '</div>';

// ============================================
// SUMMARY
// ============================================

$score = $all_checks_passed ? 100 : round((1 - (count($issues) / 20)) * 100);

echo '<div class="summary">';
echo '<h2>📊 Validation Summary</h2>';
echo '<div class="score">' . $score . '%</div>';

if ($all_checks_passed) {
    echo '<h3>🎉 ALL CHECKS PASSED!</h3>';
    echo '<p>Plugin sudah terkonfigurasi dengan sempurna.</p>';
    echo '<a href="' . home_url('/kpi') . '" class="btn">🚀 Open KPI Dashboard</a>';
} else {
    echo '<h3>⚠️ Found ' . count($issues) . ' Issue(s)</h3>';
    echo '<p>Silakan perbaiki issue berikut:</p>';
}

echo '</div>';

// Issues list
if (count($issues) > 0) {
    echo '<div class="check-section">';
    echo '<h2>❌ Issues Found (' . count($issues) . ')</h2>';
    echo '<ol>';
    foreach ($issues as $issue) {
        echo '<li style="margin: 8px 0; padding: 8px; background: #ffebee; border-radius: 4px;">' . esc_html($issue) . '</li>';
    }
    echo '</ol>';
    echo '</div>';
}

// Warnings list
if (count($warnings) > 0) {
    echo '<div class="check-section">';
    echo '<h2>⚠️ Warnings (' . count($warnings) . ')</h2>';
    echo '<ul>';
    foreach ($warnings as $warning) {
        echo '<li style="margin: 8px 0; padding: 8px; background: #fff3e0; border-radius: 4px;">' . esc_html($warning) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}

// Quick actions
echo '<div class="check-section">';
echo '<h2>🔗 Quick Actions</h2>';
echo '<p>';
echo '<a href="' . home_url('/kpi') . '" class="btn" target="_blank">🎯 Test Dashboard</a>';
echo '<a href="' . home_url('/kpi/users') . '" class="btn" target="_blank">👥 Test Users Page</a>';
echo '<a href="' . rest_url('kpi/v1/departments') . '" class="btn" target="_blank">🔌 Test API</a>';
echo '<a href="' . admin_url('options-permalink.php') . '" class="btn" target="_blank">🔄 Flush Permalinks</a>';
echo '</p>';
echo '</div>';

?>

    </div>
</div>

<script>
// Auto scroll to first error
window.onload = function() {
    var firstFail = document.querySelector('.fail');
    if (firstFail) {
        firstFail.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};
</script>

</body>
</html>
