<?php
/**
 * KPI Dashboard - Fix Blank Page (Advanced Diagnostic)
 *
 * Upload ke: /public_html/
 * Akses: https://www.mbdcorp.id/fix-blank-page.php?key=fix-blank-2024
 * Auto-fix: https://www.mbdcorp.id/fix-blank-page.php?key=fix-blank-2024&fix=yes
 */

$secret = isset($_GET['key']) ? $_GET['key'] : '';
if ($secret !== 'fix-blank-2024') {
    die('Access denied. Use: ?key=fix-blank-2024');
}

$auto_fix = isset($_GET['fix']) && $_GET['fix'] === 'yes';
$plugin_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/kpi-dashboard/';
$issues = [];
$fixes = [];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Blank Page - KPI Dashboard</title>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .step h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .code {
            background: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            margin: 10px 0;
        }
        .code .keyword { color: #c678dd; }
        .code .string { color: #98c379; }
        .code .number { color: #d19a66; }
        .code .comment { color: #5c6370; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-error { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #000; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px 10px 0;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .fixed {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            color: #155724;
        }
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
        }
        .api-test {
            background: #f8f9fa;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            font-family: monospace;
        }
        .summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .summary h2 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .icon { font-size: 20px; margin-right: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><span class="icon">🔍</span>Fix Blank Page - Advanced Diagnostic</h1>
        <p>Server: <?php echo $_SERVER['HTTP_HOST']; ?> | <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    <div class="content">

<?php

if ($auto_fix) {
    echo '<div class="warning step"><strong>⚡ AUTO-FIX MODE ENABLED</strong><br>Script akan otomatis memperbaiki masalah.</div>';
} else {
    echo '<div class="info step"><strong>📊 SCAN MODE</strong><br>Untuk auto-fix, tambahkan: <code>&fix=yes</code></div>';
}

// Load WordPress
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
} else {
    die('<div class="error step">❌ Cannot load WordPress!</div>');
}

// ============================================
// TEST 1: Fetch /kpi/users Page
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🌐</span>Test 1/10: Fetching /kpi/users Page</h2>';

$page_url = home_url('/kpi/users');
$response = wp_remote_get($page_url, [
    'timeout' => 15,
    'sslverify' => false
]);

if (is_wp_error($response)) {
    $issues[] = "Cannot fetch page: " . $response->get_error_message();
    echo '<div class="error">❌ Error: ' . esc_html($response->get_error_message()) . '</div>';
} else {
    $status = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $body_length = strlen($body);

    echo '<table>';
    echo '<tr><th>Property</th><th>Value</th></tr>';
    echo '<tr><td>Status Code</td><td><span class="badge ' . ($status == 200 ? 'badge-success' : 'badge-error') . '">' . $status . '</span></td></tr>';
    echo '<tr><td>Content Length</td><td>' . number_format($body_length) . ' bytes</td></tr>';
    echo '<tr><td>Has Content</td><td>' . ($body_length > 100 ? '✅ YES' : '❌ NO') . '</td></tr>';
    echo '</table>';

    // Check for React root
    $has_root = strpos($body, 'kpi-dashboard-root') !== false;
    $has_script = strpos($body, 'main-Cn-9-PkT.js') !== false || strpos($body, 'main-') !== false;

    echo '<div class="info">';
    echo '<strong>HTML Analysis:</strong><br>';
    echo '• React Root Element: ' . ($has_root ? '✅ Found' : '❌ Not found') . '<br>';
    echo '• Main Script: ' . ($has_script ? '✅ Found' : '❌ Not found') . '<br>';
    echo '</div>';

    if (!$has_root) {
        $issues[] = "React root element (#kpi-dashboard-root) not found in HTML";
    }
    if (!$has_script) {
        $issues[] = "Main JavaScript file not loaded in HTML";
    }

    // Show relevant HTML snippet
    if (preg_match('/<div[^>]*id=["\']kpi-dashboard-root["\'][^>]*>.*?<\/div>/s', $body, $matches)) {
        echo '<div class="success">✅ React root element found:</div>';
        echo '<div class="code">' . htmlspecialchars($matches[0]) . '</div>';
    }

    // Check for script tags
    if (preg_match_all('/<script[^>]*src=["\']([^"\']*main-[^"\']*\.js)["\'][^>]*>/i', $body, $script_matches)) {
        echo '<div class="success">✅ Found ' . count($script_matches[1]) . ' main script(s):</div>';
        foreach ($script_matches[1] as $script_url) {
            echo '<div class="api-test">📄 ' . esc_html($script_url) . '</div>';
        }
    }
}

echo '</div>';

// ============================================
// TEST 2: Check Plugin Enqueue
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">⚙️</span>Test 2/10: Plugin Script Enqueue</h2>';

// Simulate plugin load
do_action('wp_enqueue_scripts');

global $wp_scripts;
$kpi_scripts = [];

if (isset($wp_scripts->registered)) {
    foreach ($wp_scripts->registered as $handle => $script) {
        if (strpos($handle, 'kpi') !== false || (isset($script->src) && strpos($script->src, 'kpi-dashboard') !== false)) {
            $kpi_scripts[$handle] = $script;
        }
    }
}

if (count($kpi_scripts) > 0) {
    echo '<div class="success">✅ Found ' . count($kpi_scripts) . ' KPI-related script(s)</div>';
    echo '<table>';
    echo '<tr><th>Handle</th><th>Source</th><th>Dependencies</th></tr>';
    foreach ($kpi_scripts as $handle => $script) {
        echo '<tr>';
        echo '<td>' . esc_html($handle) . '</td>';
        echo '<td>' . esc_html($script->src) . '</td>';
        echo '<td>' . implode(', ', $script->deps) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    $issues[] = "No KPI scripts enqueued";
    echo '<div class="error">❌ No KPI scripts found in enqueue</div>';
}

echo '</div>';

// ============================================
// TEST 3: Test JavaScript File Accessibility
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">📦</span>Test 3/10: JavaScript File Accessibility</h2>';

$js_files = [
    'main-Cn-9-PkT.js' => plugins_url('assets/dist/assets/main-Cn-9-PkT.js', $plugin_dir . 'kpi-dashboard.php'),
];

// Try to find any main-*.js file
$dist_assets = $plugin_dir . 'assets/dist/assets/';
if (is_dir($dist_assets)) {
    $files = scandir($dist_assets);
    foreach ($files as $file) {
        if (preg_match('/^main-([a-zA-Z0-9_-]+)\.js$/', $file)) {
            $js_files[$file] = plugins_url('assets/dist/assets/' . $file, $plugin_dir . 'kpi-dashboard.php');
        }
    }
}

echo '<table>';
echo '<tr><th>File</th><th>URL</th><th>Exists</th><th>Size</th><th>Readable</th></tr>';

foreach ($js_files as $filename => $url) {
    $local_path = $plugin_dir . 'assets/dist/assets/' . $filename;
    $exists = file_exists($local_path);
    $size = $exists ? filesize($local_path) : 0;
    $readable = $exists && is_readable($local_path);

    echo '<tr>';
    echo '<td>' . esc_html($filename) . '</td>';
    echo '<td><a href="' . esc_url($url) . '" target="_blank">Test URL</a></td>';
    echo '<td>' . ($exists ? '✅' : '❌') . '</td>';
    echo '<td>' . ($exists ? number_format($size) . ' bytes' : '-') . '</td>';
    echo '<td>' . ($readable ? '✅' : '❌') . '</td>';
    echo '</tr>';

    if (!$exists) {
        $issues[] = "JavaScript file missing: $filename";
    } elseif (!$readable) {
        $issues[] = "JavaScript file not readable: $filename";

        if ($auto_fix) {
            if (chmod($local_path, 0644)) {
                $fixes[] = "Fixed permissions for $filename";
                echo '<tr><td colspan="5"><div class="fixed">🔧 Fixed permissions to 0644</div></td></tr>';
            }
        }
    }
}

echo '</table>';
echo '</div>';

// ============================================
// TEST 4: Test API Endpoints
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🔌</span>Test 4/10: API Endpoints</h2>';

$test_endpoints = [
    'Users List' => '/wp-json/kpi/v1/users',
    'Departments' => '/wp-json/kpi/v1/departments',
    'Auth Me' => '/wp-json/kpi/v1/auth/me',
];

echo '<table>';
echo '<tr><th>Endpoint</th><th>URL</th><th>Status</th><th>Response</th></tr>';

foreach ($test_endpoints as $name => $endpoint) {
    $url = home_url($endpoint);
    $api_response = wp_remote_get($url, [
        'timeout' => 10,
        'sslverify' => false,
    ]);

    if (is_wp_error($api_response)) {
        $status = 'ERROR';
        $response_text = $api_response->get_error_message();
        $badge_class = 'badge-error';
    } else {
        $status = wp_remote_retrieve_response_code($api_response);
        $response_body = wp_remote_retrieve_body($api_response);
        $json = json_decode($response_body, true);

        $badge_class = ($status == 200) ? 'badge-success' : 'badge-error';

        if ($json) {
            if (isset($json['success'])) {
                $response_text = 'Success: ' . ($json['success'] ? 'true' : 'false');
                if (isset($json['data']) && is_array($json['data'])) {
                    $response_text .= ' (' . count($json['data']) . ' items)';
                }
            } else {
                $response_text = 'JSON response';
            }
        } else {
            $response_text = substr($response_body, 0, 100);
        }
    }

    echo '<tr>';
    echo '<td>' . esc_html($name) . '</td>';
    echo '<td><code>' . esc_html($endpoint) . '</code></td>';
    echo '<td><span class="badge ' . $badge_class . '">' . esc_html($status) . '</span></td>';
    echo '<td>' . esc_html($response_text) . '</td>';
    echo '</tr>';

    if ($status != 200 && $status != 401) {
        $issues[] = "API endpoint error: $name ($status)";
    }
}

echo '</table>';
echo '</div>';

// ============================================
// TEST 5: Check Rewrite Rules
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🔀</span>Test 5/10: Rewrite Rules</h2>';

global $wp_rewrite;
$rules = get_option('rewrite_rules');

$kpi_rules = [];
foreach ($rules as $pattern => $rewrite) {
    if (strpos($pattern, 'kpi') !== false || strpos($rewrite, 'kpi') !== false) {
        $kpi_rules[$pattern] = $rewrite;
    }
}

if (count($kpi_rules) > 0) {
    echo '<div class="success">✅ Found ' . count($kpi_rules) . ' KPI rewrite rules</div>';
    echo '<table>';
    echo '<tr><th>Pattern</th><th>Rewrite To</th></tr>';
    $count = 0;
    foreach ($kpi_rules as $pattern => $rewrite) {
        if ($count++ < 10) {
            echo '<tr>';
            echo '<td><code>' . esc_html($pattern) . '</code></td>';
            echo '<td><code>' . esc_html($rewrite) . '</code></td>';
            echo '</tr>';
        }
    }
    if (count($kpi_rules) > 10) {
        echo '<tr><td colspan="2"><em>... and ' . (count($kpi_rules) - 10) . ' more</em></td></tr>';
    }
    echo '</table>';
} else {
    $issues[] = "No KPI rewrite rules found";
    echo '<div class="error">❌ No KPI rewrite rules registered</div>';

    if ($auto_fix) {
        flush_rewrite_rules();
        $fixes[] = "Flushed rewrite rules";
        echo '<div class="fixed">🔧 Flushed rewrite rules - refresh to see changes</div>';
    }
}

echo '</div>';

// ============================================
// TEST 6: Check for JavaScript Errors in Console
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🐛</span>Test 6/10: JavaScript Console Check</h2>';

echo '<div class="info">';
echo '<strong>Client-Side Test Required</strong><br>';
echo 'Buka halaman <a href="' . home_url('/kpi/users') . '" target="_blank">/kpi/users</a> lalu:<br>';
echo '1. Tekan <strong>F12</strong> untuk buka Developer Tools<br>';
echo '2. Klik tab <strong>Console</strong><br>';
echo '3. Screenshot jika ada error merah<br>';
echo '</div>';

echo '<div class="code">';
echo '<span class="comment">// Common errors to look for:</span><br>';
echo '<span class="string">"Failed to load resource"</span> <span class="comment">← File not found</span><br>';
echo '<span class="string">"Uncaught SyntaxError"</span> <span class="comment">← JavaScript syntax error</span><br>';
echo '<span class="string">"Uncaught TypeError"</span> <span class="comment">← Variable undefined</span><br>';
echo '<span class="string">"401 Unauthorized"</span> <span class="comment">← Authentication issue</span><br>';
echo '</div>';

echo '<a href="' . home_url('/kpi/users') . '" target="_blank" class="btn">🔗 Open /kpi/users in New Tab</a>';

echo '</div>';

// ============================================
// TEST 7: Check PHP Errors
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">⚠️</span>Test 7/10: PHP Error Log</h2>';

$error_log_paths = [
    $_SERVER['DOCUMENT_ROOT'] . '/error_log',
    $_SERVER['DOCUMENT_ROOT'] . '/../error_log',
    ini_get('error_log'),
];

$found_errors = false;

foreach ($error_log_paths as $log_path) {
    if ($log_path && file_exists($log_path) && is_readable($log_path)) {
        $found_errors = true;
        $log_lines = file($log_path);
        $recent_lines = array_slice($log_lines, -20);

        $kpi_errors = array_filter($recent_lines, function($line) {
            return stripos($line, 'kpi') !== false;
        });

        if (count($kpi_errors) > 0) {
            echo '<div class="warning">⚠️ Found ' . count($kpi_errors) . ' KPI-related errors in: ' . esc_html($log_path) . '</div>';
            echo '<div class="code">';
            foreach ($kpi_errors as $line) {
                echo esc_html($line) . '<br>';
            }
            echo '</div>';
            $issues[] = "PHP errors found in error log";
        } else {
            echo '<div class="success">✅ No KPI-related PHP errors in: ' . esc_html($log_path) . '</div>';
        }
        break;
    }
}

if (!$found_errors) {
    echo '<div class="info">ℹ️ Error log not found or not readable</div>';
}

echo '</div>';

// ============================================
// TEST 8: Check .htaccess Rules
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">📝</span>Test 8/10: .htaccess Configuration</h2>';

$htaccess_path = $_SERVER['DOCUMENT_ROOT'] . '/.htaccess';

if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);

    // Check for common issues
    $has_rewrite_engine = stripos($htaccess_content, 'RewriteEngine') !== false;
    $has_rewrite_base = stripos($htaccess_content, 'RewriteBase') !== false;
    $blocks_js = preg_match('/deny.*\.js/i', $htaccess_content);

    echo '<table>';
    echo '<tr><th>Check</th><th>Status</th></tr>';
    echo '<tr><td>RewriteEngine On</td><td>' . ($has_rewrite_engine ? '✅' : '❌') . '</td></tr>';
    echo '<tr><td>RewriteBase Set</td><td>' . ($has_rewrite_base ? '✅' : '⚠️') . '</td></tr>';
    echo '<tr><td>Blocks .js Files</td><td>' . ($blocks_js ? '❌ YES (BAD!)' : '✅ NO') . '</td></tr>';
    echo '</table>';

    if (!$has_rewrite_engine) {
        $issues[] = ".htaccess missing RewriteEngine On";
    }
    if ($blocks_js) {
        $issues[] = ".htaccess blocking JavaScript files";
    }
} else {
    echo '<div class="warning">⚠️ .htaccess file not found</div>';
}

echo '</div>';

// ============================================
// TEST 9: Clear All Caches
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🗑️</span>Test 9/10: Cache Management</h2>';

if ($auto_fix) {
    // Clear object cache
    wp_cache_flush();
    $fixes[] = "Cleared WordPress object cache";

    // Clear transients
    global $wpdb;
    $deleted = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_%'");
    $fixes[] = "Cleared $deleted transients";

    // Flush rewrite rules
    flush_rewrite_rules();
    $fixes[] = "Flushed rewrite rules";

    // Clear stat cache
    clearstatcache();

    echo '<div class="fixed">';
    echo '🔧 Cache Cleared:<br>';
    echo '• Object cache flushed<br>';
    echo '• ' . $deleted . ' transients deleted<br>';
    echo '• Rewrite rules refreshed<br>';
    echo '• PHP stat cache cleared<br>';
    echo '</div>';
} else {
    echo '<div class="info">ℹ️ Run with <code>&fix=yes</code> to clear caches</div>';
}

echo '</div>';

// ============================================
// TEST 10: Plugin Conflict Check
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🔌</span>Test 10/10: Plugin Conflicts</h2>';

if (!function_exists('get_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$all_plugins = get_plugins();
$active_plugins = get_option('active_plugins');

$conflicting_plugins = [];

foreach ($active_plugins as $plugin) {
    $plugin_name = isset($all_plugins[$plugin]['Name']) ? $all_plugins[$plugin]['Name'] : $plugin;

    // Check for common conflicting plugins
    if (stripos($plugin_name, 'cache') !== false ||
        stripos($plugin_name, 'minify') !== false ||
        stripos($plugin_name, 'optimize') !== false ||
        stripos($plugin_name, 'security') !== false && stripos($plugin_name, 'wordfence') !== false) {
        $conflicting_plugins[] = $plugin_name;
    }
}

if (count($conflicting_plugins) > 0) {
    echo '<div class="warning">';
    echo '⚠️ Found ' . count($conflicting_plugins) . ' potentially conflicting plugin(s):<br><ul>';
    foreach ($conflicting_plugins as $plugin) {
        echo '<li>' . esc_html($plugin) . '</li>';
    }
    echo '</ul>';
    echo '<strong>Recommendation:</strong> Try temporarily deactivating these plugins to test.';
    echo '</div>';
} else {
    echo '<div class="success">✅ No obvious conflicting plugins detected</div>';
}

echo '<div class="info">';
echo '<strong>Active Plugins:</strong> ' . count($active_plugins) . ' total<br>';
echo '</div>';

echo '</div>';

// ============================================
// SUMMARY
// ============================================

echo '<div class="summary">';
echo '<h2><span class="icon">📊</span>Diagnostic Summary</h2>';

if (count($issues) == 0) {
    echo '<div class="success step">';
    echo '<h3>✅ No Major Issues Found</h3>';
    echo '<p>Server configuration looks good. Blank page likely caused by JavaScript error.</p>';
    echo '</div>';
} else {
    echo '<div class="error step">';
    echo '<h3>⚠️ Found ' . count($issues) . ' Issue(s):</h3>';
    echo '<ol>';
    foreach ($issues as $issue) {
        echo '<li>' . esc_html($issue) . '</li>';
    }
    echo '</ol>';
    echo '</div>';
}

if (count($fixes) > 0) {
    echo '<div class="success step">';
    echo '<h3>🔧 Applied ' . count($fixes) . ' Fix(es):</h3>';
    echo '<ol>';
    foreach ($fixes as $fix) {
        echo '<li>' . esc_html($fix) . '</li>';
    }
    echo '</ol>';
    echo '</div>';
}

echo '</div>';

// ============================================
// RECOMMENDATIONS
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">💡</span>Next Steps & Solutions</h2>';

echo '<div class="info">';
echo '<h3>🎯 Most Likely Causes of Blank Page:</h3>';
echo '<ol>';
echo '<li><strong>JavaScript Error</strong> - Check browser console (F12)</li>';
echo '<li><strong>API Authentication</strong> - Check if /wp-json/kpi/v1/auth/me returns 401</li>';
echo '<li><strong>React Not Mounting</strong> - Check if #kpi-dashboard-root exists</li>';
echo '<li><strong>Cache Issue</strong> - Clear browser cache (Ctrl+Shift+Delete)</li>';
echo '</ol>';
echo '</div>';

echo '<h3>🔧 Try These Fixes:</h3>';

echo '<div class="warning">';
echo '<strong>FIX 1: Clear Everything</strong><br>';
if (!$auto_fix) {
    echo '<a href="?key=fix-blank-2024&fix=yes" class="btn">🔧 Run Auto-Fix Now</a><br>';
}
echo '1. WordPress Admin → Plugins → Deactivate then Activate "KPI Dashboard"<br>';
echo '2. Settings → Permalinks → Save Changes<br>';
echo '3. Browser: Ctrl+Shift+Delete → Clear cache<br>';
echo '4. Test in Incognito: Ctrl+Shift+N<br>';
echo '</div>';

echo '<div class="warning">';
echo '<strong>FIX 2: Check JavaScript Console</strong><br>';
echo '1. <a href="' . home_url('/kpi/users') . '" target="_blank" class="btn">Open /kpi/users</a><br>';
echo '2. Press F12 → Console tab<br>';
echo '3. Look for RED errors<br>';
echo '4. Screenshot and send to developer<br>';
echo '</div>';

echo '<div class="warning">';
echo '<strong>FIX 3: Test API Manually</strong><br>';
echo '1. <a href="' . home_url('/wp-json/kpi/v1/users') . '" target="_blank" class="btn">Test API: /users</a><br>';
echo '2. Should show JSON with users data<br>';
echo '3. If 401/403 → Authentication issue<br>';
echo '4. If 500 → Server error (check PHP logs)<br>';
echo '</div>';

echo '<div class="warning">';
echo '<strong>FIX 4: Re-upload Build Files</strong><br>';
echo '1. Delete: /wp-content/plugins/kpi-dashboard/assets/dist/<br>';
echo '2. Re-extract kpi-dashboard-DEPLOY.zip<br>';
echo '3. Verify main-Cn-9-PkT.js exists (765 KB)<br>';
echo '4. Deactivate/Activate plugin<br>';
echo '</div>';

echo '</div>';

// ============================================
// QUICK TESTS
// ============================================

echo '<div class="step">';
echo '<h2><span class="icon">🧪</span>Quick Manual Tests</h2>';

echo '<div class="api-test">';
echo '<strong>Test 1:</strong> <a href="' . plugins_url('assets/dist/assets/main-Cn-9-PkT.js', $plugin_dir . 'kpi-dashboard.php') . '" target="_blank" class="btn">Download main-Cn-9-PkT.js</a>';
echo '<br><em>Should download 765 KB file, NOT 404</em>';
echo '</div>';

echo '<div class="api-test">';
echo '<strong>Test 2:</strong> <a href="' . home_url('/kpi/users') . '" target="_blank" class="btn">Open /kpi/users</a>';
echo '<br><em>Press F12 → Console → Check for errors</em>';
echo '</div>';

echo '<div class="api-test">';
echo '<strong>Test 3:</strong> <a href="' . home_url('/wp-json/kpi/v1/users') . '" target="_blank" class="btn">Test API /users</a>';
echo '<br><em>Should show JSON response</em>';
echo '</div>';

echo '<div class="api-test">';
echo '<strong>Test 4:</strong> <a href="' . home_url('/kpi/') . '" target="_blank" class="btn">Open Dashboard</a>';
echo '<br><em>Test if other pages work</em>';
echo '</div>';

echo '</div>';

// ============================================
// SECURITY WARNING
// ============================================

echo '<div class="error step">';
echo '<h2><span class="icon">🔒</span>SECURITY WARNING</h2>';
echo '<p><strong>DELETE THIS FILE after troubleshooting!</strong></p>';
echo '<p>File contains sensitive server information.</p>';
echo '<a href="#" onclick="if(confirm(\'Delete fix-blank-page.php?\')){alert(\'Please delete manually via cPanel File Manager\');}" class="btn btn-danger">Delete This File</a>';
echo '</div>';

?>

    </div>
</div>

<script>
// Auto-scroll to first error
window.onload = function() {
    var firstError = document.querySelector('.error');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};
</script>

</body>
</html>
