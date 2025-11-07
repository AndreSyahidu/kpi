<?php
/**
 * KPI Dashboard - Flush Rewrite Rules Helper
 *
 * Run this file ONCE via browser to flush and re-register rewrite rules
 * URL: https://www.mbdcorp.id/wp-content/plugins/kpi-dashboard/flush-rewrite.php
 *
 * WHEN TO USE:
 * - After plugin update
 * - If /kpi returns 404
 * - After changing permalink structure
 * - If routes not working
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access. You must be an administrator.', 'Access Denied', ['response' => 403]);
}

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔄 KPI Dashboard - Flush Rewrite Rules</h1>";
echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 40px; background: #f5f5f5; }
    h1 { color: #1565C0; }
    .success { background: #e8f5e9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; }
    .info { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
    .warning { background: #fff3e0; border-left: 4px solid #FF9800; padding: 15px; margin: 20px 0; }
    code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
    ul { line-height: 1.8; }
</style>";

echo "<div class='info'>";
echo "<strong>ℹ️ What This Does:</strong><br>";
echo "This script will flush WordPress rewrite rules and re-register the KPI Dashboard custom routes.";
echo "</div>";

// Step 1: Check if plugin is active
echo "<h2>Step 1: Checking Plugin Status</h2>";
if (class_exists('KPI_Dashboard') && class_exists('KPI_Dashboard_Router')) {
    echo "<div class='success'>✓ KPI Dashboard plugin is active and loaded</div>";
} else {
    echo "<div class='warning'>✗ KPI Dashboard plugin not loaded. Make sure it's activated.</div>";
    exit;
}

// Step 2: Flush rewrite rules
echo "<h2>Step 2: Flushing Rewrite Rules</h2>";
echo "<p>Clearing WordPress rewrite rules cache...</p>";

flush_rewrite_rules(false); // false = soft flush (doesn't touch .htaccess)

echo "<div class='success'>✓ Rewrite rules flushed successfully!</div>";

// Step 3: Verify rules are registered
echo "<h2>Step 3: Verifying Registration</h2>";

global $wp_rewrite;
$rules = get_option('rewrite_rules');

$kpi_rules = [];
if ($rules) {
    foreach ($rules as $pattern => $replacement) {
        if (strpos($pattern, 'kpi') !== false || strpos($replacement, 'kpi_dashboard') !== false) {
            $kpi_rules[$pattern] = $replacement;
        }
    }
}

if (!empty($kpi_rules)) {
    echo "<div class='success'>";
    echo "<strong>✓ KPI routes registered successfully!</strong><br><br>";
    echo "<strong>Found " . count($kpi_rules) . " KPI route(s):</strong><br>";
    echo "<ul>";
    foreach ($kpi_rules as $pattern => $replacement) {
        echo "<li><code>$pattern</code> → <code>$replacement</code></li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<strong>⚠ No KPI routes found in WordPress rewrite rules.</strong><br><br>";
    echo "This might happen if:<br>";
    echo "<ul>";
    echo "<li>Plugin was just activated (try flushing again)</li>";
    echo "<li>Permalink structure is set to 'Plain' (must be 'Post name' or custom)</li>";
    echo "<li>There's a conflict with another plugin</li>";
    echo "</ul>";
    echo "</div>";
}

// Step 4: Test the route
echo "<h2>Step 4: Testing Route</h2>";
$test_url = home_url('/kpi');
echo "<p>Testing URL: <a href='$test_url' target='_blank'>$test_url</a></p>";

echo "<div class='info'>";
echo "<strong>📋 Next Steps:</strong><br>";
echo "<ol>";
echo "<li>Click the test URL above (opens in new tab)</li>";
echo "<li>You should see the KPI Dashboard login page</li>";
echo "<li>If still 404, check these:</li>";
echo "<ul>";
echo "<li>Go to <strong>WordPress Admin → Settings → Permalinks</strong></li>";
echo "<li>Make sure it's NOT set to 'Plain'</li>";
echo "<li>Click 'Save Changes'</li>";
echo "<li>Try again</li>";
echo "</ul>";
echo "<li>If still not working, deactivate and reactivate the plugin</li>";
echo "</ol>";
echo "</div>";

// Step 5: Cleanup instructions
echo "<h2>Step 5: Security Reminder</h2>";
echo "<div class='warning'>";
echo "<strong>⚠️ IMPORTANT:</strong> Delete this file after use for security reasons.<br><br>";
echo "<strong>Delete via:</strong><br>";
echo "<ul>";
echo "<li><strong>Terminal:</strong> <code>rm ~/mbdcorp.id/wp-content/plugins/kpi-dashboard/flush-rewrite.php</code></li>";
echo "<li><strong>cPanel:</strong> File Manager → Navigate to plugin folder → Delete flush-rewrite.php</li>";
echo "<li><strong>FTP:</strong> Connect and delete the file</li>";
echo "</ul>";
echo "</div>";

// Summary
echo "<h2>✅ Summary</h2>";
echo "<div class='success'>";
echo "<strong>Rewrite rules have been flushed!</strong><br><br>";
echo "<strong>What happened:</strong><br>";
echo "<ul>";
echo "<li>✓ WordPress rewrite rules cache cleared</li>";
echo "<li>✓ KPI Dashboard routes re-registered</li>";
echo "<li>✓ Route pattern: <code>^kpi/?$</code> and <code>^kpi/(.+)/?$</code></li>";
echo "<li>✓ Query vars: <code>kpi_dashboard</code> and <code>kpi_route</code></li>";
echo "</ul>";
echo "</div>";

echo "<div style='text-align: center; margin: 40px 0;'>";
echo "<a href='$test_url' style='display: inline-block; background: #1565C0; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: 600;'>Test KPI Dashboard →</a>";
echo "</div>";

echo "<hr style='margin: 40px 0; border: 0; border-top: 1px solid #ddd;'>";
echo "<p style='text-align: center; color: #666; font-size: 14px;'>";
echo "KPI Dashboard v" . KPI_DASHBOARD_VERSION . " | ";
echo "<a href='" . admin_url('plugins.php') . "'>Back to Plugins</a>";
echo "</p>";
