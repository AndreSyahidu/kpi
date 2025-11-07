<?php
/**
 * Debug API Script
 * Upload ke: /wp-content/plugins/kpi-dashboard/debug-api.php
 * Buka: https://www.mbdcorp.id/wp-content/plugins/kpi-dashboard/debug-api.php
 */

// Load WordPress
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>KPI Dashboard - API Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .success { border-left-color: #4CAF50; }
        .error { border-left-color: #f44336; }
        .warning { border-left-color: #FF9800; }
        h2 { margin-top: 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
        .status.ok { background: #4CAF50; color: white; }
        .status.fail { background: #f44336; color: white; }
    </style>
</head>
<body>
    <h1>🔍 KPI Dashboard - API Diagnostic</h1>

    <?php
    // 1. Check Plugin Active
    echo '<div class="section">';
    echo '<h2>1. Plugin Status</h2>';
    $plugin_active = is_plugin_active('kpi-dashboard/kpi-dashboard.php');
    if ($plugin_active) {
        echo '<span class="status ok">✓ ACTIVE</span>';
    } else {
        echo '<span class="status fail">✗ INACTIVE</span>';
        echo '<p>Plugin harus diaktifkan dulu!</p>';
    }
    echo '</div>';

    // 2. Check Database Tables
    echo '<div class="section">';
    echo '<h2>2. Database Tables</h2>';
    global $wpdb;
    $tables = [
        'kpi_users',
        'kpi_departments',
        'kpi_positions',
        'kpi_definitions',
        'kpi_data',
        'kpi_notifications',
        'kpi_settings'
    ];
    
    $all_exist = true;
    foreach ($tables as $table) {
        $full_table = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table;
        
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
            echo "<div>✓ $table <small>($count rows)</small></div>";
        } else {
            echo "<div style='color: red;'>✗ $table <strong>NOT FOUND</strong></div>";
            $all_exist = false;
        }
    }
    
    if (!$all_exist) {
        echo '<p style="color: red;"><strong>Database tables belum dibuat! Deactivate dan Activate ulang plugin.</strong></p>';
    }
    echo '</div>';

    // 3. Check Frontend Build
    echo '<div class="section">';
    echo '<h2>3. Frontend Build Files</h2>';
    $plugin_dir = WP_PLUGIN_DIR . '/kpi-dashboard/';
    $manifest = $plugin_dir . 'assets/dist/.vite/manifest.json';
    $main_js = $plugin_dir . 'assets/dist/assets/main-Cn-9-PkT.js';
    
    if (file_exists($manifest)) {
        echo '<div>✓ manifest.json EXISTS</div>';
        $manifest_content = json_decode(file_get_contents($manifest), true);
        if ($manifest_content) {
            echo '<pre>' . json_encode($manifest_content, JSON_PRETTY_PRINT) . '</pre>';
        }
    } else {
        echo '<div style="color: red;">✗ manifest.json NOT FOUND</div>';
        echo '<p>File harus ada di: ' . $manifest . '</p>';
    }
    
    if (file_exists($main_js)) {
        $size = filesize($main_js);
        echo '<div>✓ main-Cn-9-PkT.js EXISTS (' . number_format($size / 1024, 2) . ' KB)</div>';
    } else {
        echo '<div style="color: red;">✗ main-Cn-9-PkT.js NOT FOUND</div>';
        
        // Check if old build exists
        $dist_dir = $plugin_dir . 'assets/dist/assets/';
        if (is_dir($dist_dir)) {
            $files = scandir($dist_dir);
            echo '<p>Files in dist/assets/:</p><pre>';
            print_r(array_diff($files, ['.', '..']));
            echo '</pre>';
        }
    }
    echo '</div>';

    // 4. Check API Endpoints
    echo '<div class="section">';
    echo '<h2>4. REST API Endpoints</h2>';
    $endpoints = [
        '/kpi/v1/auth/login',
        '/kpi/v1/users',
        '/kpi/v1/departments',
        '/kpi/v1/kpis',
        '/kpi/v1/data',
        '/kpi/v1/approvals',
        '/kpi/v1/reports',
        '/kpi/v1/notifications',
        '/kpi/v1/settings',
        '/kpi/v1/analytics'
    ];
    
    $rest_url = rest_url();
    echo '<p>REST API Base URL: ' . $rest_url . '</p>';
    
    foreach ($endpoints as $endpoint) {
        $url = $rest_url . $endpoint;
        echo '<div>' . $endpoint . '</div>';
    }
    echo '</div>';

    // 5. Check Current User
    echo '<div class="section">';
    echo '<h2>5. Current User</h2>';
    $current_user = wp_get_current_user();
    if ($current_user->ID) {
        echo '<div>✓ Logged in as: ' . $current_user->user_login . ' (ID: ' . $current_user->ID . ')</div>';
        echo '<div>Roles: ' . implode(', ', $current_user->roles) . '</div>';
    } else {
        echo '<div style="color: orange;">⚠ Not logged in</div>';
        echo '<p>Login dulu ke WordPress untuk test API!</p>';
    }
    echo '</div>';

    // 6. Test Sample API Call
    echo '<div class="section">';
    echo '<h2>6. Test API Call - Departments</h2>';
    
    if ($current_user->ID) {
        $request = new WP_REST_Request('GET', '/kpi/v1/departments');
        $response = rest_do_request($request);
        
        if ($response->is_error()) {
            echo '<div style="color: red;">✗ API Error</div>';
            echo '<pre>' . print_r($response->get_error_message(), true) . '</pre>';
        } else {
            echo '<div style="color: green;">✓ API Working</div>';
            echo '<p>Response:</p>';
            echo '<pre>' . json_encode($response->get_data(), JSON_PRETTY_PRINT) . '</pre>';
        }
    } else {
        echo '<p style="color: orange;">Tidak bisa test API karena belum login</p>';
    }
    echo '</div>';

    // 7. Check Page Files
    echo '<div class="section">';
    echo '<h2>7. React Page Files</h2>';
    $page_files = [
        'assets/src/pages/users/UsersPage.tsx',
        'assets/src/pages/departments/DepartmentsPage.tsx',
        'assets/src/pages/kpis/KPIsPage.tsx',
        'assets/src/pages/data-entry/DataEntryPage.tsx',
        'assets/src/pages/approvals/ApprovalsPage.tsx',
        'assets/src/pages/reports/ReportsPage.tsx',
        'assets/src/pages/analytics/AnalyticsPage.tsx',
        'assets/src/pages/notifications/NotificationsPage.tsx',
        'assets/src/pages/settings/SettingsPage.tsx',
    ];
    
    foreach ($page_files as $file) {
        $full_path = $plugin_dir . $file;
        if (file_exists($full_path)) {
            $lines = count(file($full_path));
            
            // Check if it's a placeholder
            $content = file_get_contents($full_path);
            $is_placeholder = strpos($content, 'Backend ready') !== false;
            
            if ($is_placeholder) {
                echo '<div style="color: red;">✗ ' . basename(dirname($file)) . ' - PLACEHOLDER (' . $lines . ' lines)</div>';
            } else {
                echo '<div style="color: green;">✓ ' . basename(dirname($file)) . ' - IMPLEMENTED (' . $lines . ' lines)</div>';
            }
        } else {
            echo '<div style="color: red;">✗ ' . basename(dirname($file)) . ' - NOT FOUND</div>';
        }
    }
    echo '</div>';

    // 8. Instructions
    echo '<div class="section warning">';
    echo '<h2>📋 Next Steps</h2>';
    echo '<ol>';
    echo '<li>Pastikan semua file di atas ✓ (hijau)</li>';
    echo '<li>Jika ada yang merah, upload file yang kurang</li>';
    echo '<li>Jika database tables tidak ada: Deactivate & Activate plugin</li>';
    echo '<li>Jika build files tidak ada: Upload folder assets/dist/</li>';
    echo '<li>Jika page files masih placeholder: Upload folder assets/src/pages/</li>';
    echo '<li>Clear cache WordPress</li>';
    echo '<li>Hard reload browser (Ctrl+F5)</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <div class="section">
        <h2>🔗 Useful Links</h2>
        <ul>
            <li><a href="<?php echo admin_url('plugins.php'); ?>">WordPress Plugins</a></li>
            <li><a href="<?php echo admin_url('options-permalink.php'); ?>">Permalinks Settings</a></li>
            <li><a href="<?php echo site_url('/kpi'); ?>">KPI Dashboard</a></li>
            <li><a href="<?php echo rest_url('kpi/v1/departments'); ?>">Test API: Departments</a></li>
        </ul>
    </div>

</body>
</html>
