<?php
/**
 * KPI Dashboard - Auto Fix & Debug Script
 *
 * Upload file ini ke: /public_html/
 * Akses via: https://www.mbdcorp.id/auto-fix-kpi.php
 *
 * Script ini akan:
 * - Detect semua masalah
 * - Auto-fix yang bisa diperbaiki
 * - Clear cache
 * - Generate report detail
 */

// Prevent direct access without security
$secret_key = isset($_GET['key']) ? $_GET['key'] : '';
if ($secret_key !== 'fix-kpi-2024') {
    die('Access denied. Use: ?key=fix-kpi-2024');
}

// Start output
?>
<!DOCTYPE html>
<html>
<head>
    <title>KPI Dashboard - Auto Fix & Debug</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1976d2;
            border-bottom: 3px solid #1976d2;
            padding-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-top: 30px;
            background: #e3f2fd;
            padding: 10px;
            border-left: 4px solid #1976d2;
        }
        .success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .code {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-success { background: #4caf50; color: white; }
        .badge-error { background: #f44336; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .fixed {
            background: #c8e6c9;
            padding: 10px;
            border-radius: 4px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #1976d2;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #1565c0;
        }
        .progress {
            margin: 20px 0;
        }
        .step {
            padding: 10px;
            margin: 5px 0;
            background: #f5f5f5;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 KPI Dashboard - Auto Fix & Debug</h1>
    <p><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST']; ?> | <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

// ==========================================
// CONFIGURATION
// ==========================================

$plugin_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/kpi-dashboard/';
$auto_fix = isset($_GET['fix']) && $_GET['fix'] === 'yes';
$issues_found = [];
$fixes_applied = [];

echo "<h2>🚀 Starting Diagnosis...</h2>";

if ($auto_fix) {
    echo "<div class='warning'><strong>⚠️ AUTO-FIX MODE ENABLED</strong><br>Script akan otomatis memperbaiki masalah yang ditemukan.</div>";
} else {
    echo "<div class='info'><strong>ℹ️ SCAN MODE</strong><br>Script hanya akan scan masalah. Untuk auto-fix, tambahkan: <code>&fix=yes</code></div>";
}

// ==========================================
// 1. CHECK PLUGIN DIRECTORY
// ==========================================

echo "<div class='progress'>";
echo "<div class='step'>📁 Step 1/8: Checking plugin directory...</div>";

if (!is_dir($plugin_dir)) {
    $issues_found[] = "Plugin directory tidak ditemukan: $plugin_dir";
    echo "<div class='error'>❌ Plugin directory tidak ada!</div>";
} else {
    echo "<div class='success'>✅ Plugin directory found: $plugin_dir</div>";
}

// ==========================================
// 2. CHECK CRITICAL FILES
// ==========================================

echo "<div class='step'>📄 Step 2/8: Checking critical files...</div>";

$critical_files = [
    'Main plugin file' => $plugin_dir . 'kpi-dashboard.php',
    'Build manifest' => $plugin_dir . 'assets/dist/.vite/manifest.json',
    'Main JS bundle' => $plugin_dir . 'assets/dist/assets/main-Cn-9-PkT.js',
    'Main CSS' => $plugin_dir . 'assets/dist/assets/main-BIgbKKtm.css',
];

echo "<table>";
echo "<tr><th>File</th><th>Status</th><th>Size</th><th>Permissions</th></tr>";

foreach ($critical_files as $name => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';

    echo "<tr>";
    echo "<td>$name</td>";

    if ($exists) {
        echo "<td><span class='badge badge-success'>✅ EXISTS</span></td>";
        echo "<td>" . number_format($size) . " bytes (" . round($size/1024, 2) . " KB)</td>";
        echo "<td>$perms</td>";

        // Check if permissions are correct
        if (!is_readable($path)) {
            $issues_found[] = "File not readable: $name";
            echo "</tr><tr><td colspan='4'><div class='error'>⚠️ File not readable!</div>";

            if ($auto_fix && is_writable(dirname($path))) {
                if (chmod($path, 0644)) {
                    $fixes_applied[] = "Fixed permissions for: $name";
                    echo "<div class='fixed'>🔧 FIXED: Set permissions to 0644</div>";
                }
            }
            echo "</td></tr>";
        }
    } else {
        echo "<td><span class='badge badge-error'>❌ MISSING</span></td>";
        echo "<td>-</td><td>-</td>";
        $issues_found[] = "Missing file: $name ($path)";
        echo "</tr><tr><td colspan='4'><div class='error'>🚨 CRITICAL: File hilang!</div></td></tr>";
    }
    echo "</tr>";
}

echo "</table>";

// ==========================================
// 3. CHECK MANIFEST.JSON CONTENT
// ==========================================

echo "<div class='step'>📋 Step 3/8: Checking manifest.json content...</div>";

$manifest_path = $plugin_dir . 'assets/dist/.vite/manifest.json';

if (file_exists($manifest_path)) {
    $manifest_content = file_get_contents($manifest_path);
    $manifest = json_decode($manifest_content, true);

    if ($manifest) {
        echo "<div class='success'>✅ Manifest valid JSON</div>";
        echo "<div class='code'>" . htmlspecialchars(json_encode($manifest, JSON_PRETTY_PRINT)) . "</div>";

        // Check if manifest has correct structure
        if (isset($manifest['assets/main.tsx'])) {
            $entry = $manifest['assets/main.tsx'];
            echo "<div class='success'>✅ Entry point found: assets/main.tsx</div>";
            echo "<div class='info'>Main file: " . htmlspecialchars($entry['file']) . "</div>";

            // Verify the file referenced in manifest exists
            $main_file = $plugin_dir . 'assets/dist/' . $entry['file'];
            if (!file_exists($main_file)) {
                $issues_found[] = "Manifest references missing file: " . $entry['file'];
                echo "<div class='error'>❌ File referenced in manifest not found: {$entry['file']}</div>";
            }
        } else {
            $issues_found[] = "Manifest missing 'assets/main.tsx' entry";
            echo "<div class='error'>❌ Manifest tidak memiliki entry point yang benar</div>";
        }
    } else {
        $issues_found[] = "Manifest is not valid JSON";
        echo "<div class='error'>❌ Manifest bukan JSON valid</div>";
    }
} else {
    $issues_found[] = "Manifest.json not found";
    echo "<div class='error'>❌ Manifest.json tidak ditemukan</div>";
}

// ==========================================
// 4. CHECK PAGE FILES
// ==========================================

echo "<div class='step'>📄 Step 4/8: Checking page implementations...</div>";

$page_files = [
    'Dashboard' => 'pages/dashboard/DashboardPage.tsx',
    'Users' => 'pages/users/UsersPage.tsx',
    'Departments' => 'pages/departments/DepartmentsPage.tsx',
    'Positions' => 'pages/positions/PositionsPage.tsx',
    'KPIs' => 'pages/kpis/KPIsPage.tsx',
    'Data Entry' => 'pages/data-entry/DataEntryPage.tsx',
    'Approvals' => 'pages/approvals/ApprovalsPage.tsx',
    'Reports' => 'pages/reports/ReportsPage.tsx',
    'Analytics' => 'pages/analytics/AnalyticsPage.tsx',
    'Notifications' => 'pages/notifications/NotificationsPage.tsx',
    'Settings' => 'pages/settings/SettingsPage.tsx',
];

echo "<table>";
echo "<tr><th>Page</th><th>Status</th><th>Lines</th><th>Has Placeholder?</th></tr>";

foreach ($page_files as $name => $rel_path) {
    $path = $plugin_dir . 'assets/src/' . $rel_path;
    $exists = file_exists($path);

    echo "<tr>";
    echo "<td>$name</td>";

    if ($exists) {
        $content = file_get_contents($path);
        $lines = count(file($path));
        $has_placeholder = (strpos($content, 'Backend ready') !== false ||
                           strpos($content, 'Coming soon') !== false ||
                           strpos($content, 'Under construction') !== false);

        echo "<td><span class='badge badge-success'>✅ EXISTS</span></td>";
        echo "<td>$lines lines</td>";

        if ($has_placeholder) {
            echo "<td><span class='badge badge-warning'>⚠️ YES</span></td>";
            $issues_found[] = "Page '$name' masih ada placeholder text";
        } else {
            echo "<td><span class='badge badge-success'>✅ NO</span></td>";
        }
    } else {
        echo "<td><span class='badge badge-error'>❌ MISSING</span></td>";
        echo "<td>-</td><td>-</td>";
        $issues_found[] = "Page file missing: $name";
    }
    echo "</tr>";
}

echo "</table>";

// ==========================================
// 5. TEST WORDPRESS INTEGRATION
// ==========================================

echo "<div class='step'>🔌 Step 5/8: Testing WordPress integration...</div>";

// Check if WordPress is loaded
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
    echo "<div class='success'>✅ WordPress loaded</div>";

    // Check if plugin is active
    if (function_exists('is_plugin_active')) {
        if (is_plugin_active('kpi-dashboard/kpi-dashboard.php')) {
            echo "<div class='success'>✅ Plugin is ACTIVE</div>";
        } else {
            echo "<div class='error'>❌ Plugin is NOT ACTIVE</div>";
            $issues_found[] = "Plugin not activated";

            if ($auto_fix && function_exists('activate_plugin')) {
                $result = activate_plugin('kpi-dashboard/kpi-dashboard.php');
                if (!is_wp_error($result)) {
                    $fixes_applied[] = "Activated plugin";
                    echo "<div class='fixed'>🔧 FIXED: Plugin activated</div>";
                }
            }
        }
    }

    // Check database tables
    global $wpdb;
    $tables = [
        'kpi_users',
        'kpi_departments',
        'kpi_positions',
        'kpi_kpis',
        'kpi_data_entries',
        'kpi_approvals',
        'kpi_notifications',
        'kpi_settings'
    ];

    echo "<div class='info'><strong>Database Tables:</strong></div>";
    echo "<table>";
    echo "<tr><th>Table</th><th>Status</th><th>Rows</th></tr>";

    foreach ($tables as $table) {
        $full_table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;

        echo "<tr>";
        echo "<td>$table</td>";

        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
            echo "<td><span class='badge badge-success'>✅ EXISTS</span></td>";
            echo "<td>$count rows</td>";
        } else {
            echo "<td><span class='badge badge-error'>❌ MISSING</span></td>";
            echo "<td>-</td>";
            $issues_found[] = "Database table missing: $table";
        }
        echo "</tr>";
    }
    echo "</table>";

} else {
    echo "<div class='error'>❌ WordPress not found</div>";
    $issues_found[] = "Cannot load WordPress";
}

// ==========================================
// 6. CLEAR CACHES
// ==========================================

echo "<div class='step'>🗑️ Step 6/8: Clearing caches...</div>";

if ($auto_fix && function_exists('wp_cache_flush')) {
    wp_cache_flush();
    $fixes_applied[] = "Flushed WordPress object cache";
    echo "<div class='fixed'>🔧 Cleared WordPress object cache</div>";
}

if ($auto_fix && function_exists('delete_transient')) {
    // Clear common transients
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_%'");
    $fixes_applied[] = "Cleared all transients";
    echo "<div class='fixed'>🔧 Cleared all transients</div>";
}

if ($auto_fix && function_exists('flush_rewrite_rules')) {
    flush_rewrite_rules();
    $fixes_applied[] = "Flushed rewrite rules";
    echo "<div class='fixed'>🔧 Flushed rewrite rules (permalinks)</div>";
}

// Clear stat cache
clearstatcache();
echo "<div class='success'>✅ Cleared PHP stat cache</div>";

// ==========================================
// 7. TEST API ENDPOINTS
// ==========================================

echo "<div class='step'>🌐 Step 7/8: Testing API endpoints...</div>";

if (function_exists('rest_get_server')) {
    $rest_server = rest_get_server();
    $routes = $rest_server->get_routes();

    $kpi_routes = array_filter($routes, function($route) {
        return strpos($route, '/kpi/v1') === 0;
    }, ARRAY_FILTER_USE_KEY);

    echo "<div class='success'>✅ Found " . count($kpi_routes) . " KPI API routes</div>";

    if (count($kpi_routes) > 0) {
        echo "<div class='info'><strong>Sample routes:</strong><br>";
        $sample = array_slice(array_keys($kpi_routes), 0, 5);
        foreach ($sample as $route) {
            echo "• /wp-json$route<br>";
        }
        echo "</div>";
    } else {
        $issues_found[] = "No KPI API routes registered";
        echo "<div class='error'>❌ No KPI API routes found</div>";
    }
} else {
    echo "<div class='warning'>⚠️ Cannot access REST API (normal for non-WordPress context)</div>";
}

// ==========================================
// 8. CHECK DIRECTORY PERMISSIONS
// ==========================================

echo "<div class='step'>🔐 Step 8/8: Checking directory permissions...</div>";

$dirs = [
    'Plugin root' => $plugin_dir,
    'Assets' => $plugin_dir . 'assets/',
    'Dist' => $plugin_dir . 'assets/dist/',
    'Dist assets' => $plugin_dir . 'assets/dist/assets/',
    'Source' => $plugin_dir . 'assets/src/',
];

echo "<table>";
echo "<tr><th>Directory</th><th>Exists</th><th>Readable</th><th>Writable</th><th>Permissions</th></tr>";

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    $readable = $exists && is_readable($path);
    $writable = $exists && is_writable($path);
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';

    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
    echo "<td>" . ($readable ? "✅" : "❌") . "</td>";
    echo "<td>" . ($writable ? "✅" : "❌") . "</td>";
    echo "<td>$perms</td>";
    echo "</tr>";

    if ($exists && !$readable) {
        $issues_found[] = "Directory not readable: $name";

        if ($auto_fix) {
            if (chmod($path, 0755)) {
                $fixes_applied[] = "Fixed permissions for directory: $name";
                echo "<tr><td colspan='5'><div class='fixed'>🔧 FIXED: Set permissions to 0755</div></td></tr>";
            }
        }
    }
}

echo "</table>";

echo "</div>"; // End progress

// ==========================================
// SUMMARY
// ==========================================

echo "<h2>📊 Summary</h2>";

if (count($issues_found) === 0) {
    echo "<div class='success'>";
    echo "<h3>🎉 SEMUANYA OK! Tidak ada masalah ditemukan.</h3>";
    echo "<p>Plugin seharusnya berfungsi dengan baik.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Ditemukan " . count($issues_found) . " masalah:</h3>";
    echo "<ol>";
    foreach ($issues_found as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ol>";
    echo "</div>";
}

if (count($fixes_applied) > 0) {
    echo "<div class='success'>";
    echo "<h3>🔧 Fixes Applied (" . count($fixes_applied) . "):</h3>";
    echo "<ol>";
    foreach ($fixes_applied as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ol>";
    echo "</div>";
}

// ==========================================
// RECOMMENDATIONS
// ==========================================

echo "<h2>💡 Recommendations</h2>";

$main_js_path = $plugin_dir . 'assets/dist/assets/main-Cn-9-PkT.js';
if (!file_exists($main_js_path)) {
    echo "<div class='error'>";
    echo "<h3>🚨 CRITICAL: Main JavaScript file hilang!</h3>";
    echo "<p><strong>File yang hilang:</strong> <code>assets/dist/assets/main-Cn-9-PkT.js</code> (765 KB)</p>";
    echo "<p><strong>Solusi:</strong></p>";
    echo "<ol>";
    echo "<li>Re-extract file <code>kpi-dashboard-DEPLOY.zip</code> dengan \"Overwrite existing files\"</li>";
    echo "<li>Atau upload manual file ini ke folder: <code>/wp-content/plugins/kpi-dashboard/assets/dist/assets/</code></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>Jika masih ada masalah, jalankan script ini dengan auto-fix: <a href='?key=fix-kpi-2024&fix=yes' class='btn'>🔧 Run Auto-Fix</a></li>";
echo "<li>Clear browser cache: <code>Ctrl + Shift + Delete</code></li>";
echo "<li>Hard reload halaman: <code>Ctrl + F5</code></li>";
echo "<li>Test halaman: <a href='/kpi/users' target='_blank' class='btn'>🔗 Open Users Page</a></li>";
echo "</ol>";
echo "</div>";

// ==========================================
// QUICK TESTS
// ==========================================

echo "<h2>🧪 Quick Tests</h2>";

echo "<div class='info'>";
echo "<p><strong>Test these URLs:</strong></p>";
echo "<ol>";
echo "<li><a href='/wp-content/plugins/kpi-dashboard/assets/dist/assets/main-Cn-9-PkT.js' target='_blank'>Test Main JS File</a> (should download 765 KB file)</li>";
echo "<li><a href='/wp-content/plugins/kpi-dashboard/assets/dist/.vite/manifest.json' target='_blank'>Test Manifest</a> (should show JSON)</li>";
echo "<li><a href='/kpi/' target='_blank'>Test Dashboard</a></li>";
echo "<li><a href='/kpi/users' target='_blank'>Test Users Page</a></li>";
echo "<li><a href='/wp-json/kpi/v1/users' target='_blank'>Test API</a></li>";
echo "</ol>";
echo "</div>";

// ==========================================
// SECURITY WARNING
// ==========================================

echo "<div class='warning'>";
echo "<h3>⚠️ PENTING - SECURITY!</h3>";
echo "<p><strong>HAPUS FILE INI setelah selesai troubleshooting!</strong></p>";
echo "<p>File ini mengandung informasi sensitif tentang struktur server Anda.</p>";
echo "</div>";

?>

</div>
</body>
</html>
