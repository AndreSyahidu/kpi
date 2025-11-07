<?php
/**
 * Debug Rewrite Rules - KPI Dashboard
 * Check if /kpi route is registered
 */

// Load WordPress
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 KPI Dashboard - Rewrite Rules Debug</h1>";
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

// 1. Check Permalink Structure
echo "<h2>1. WordPress Permalink Settings</h2>";
echo "<div class='box'>";
$permalink_structure = get_option('permalink_structure');
if (empty($permalink_structure)) {
    echo "<span class='error'>✗ Permalinks are set to PLAIN (default)</span><br>";
    echo "<span class='warning'>⚠ Custom routes require pretty permalinks!</span><br><br>";
    echo "<strong>Fix:</strong><br>";
    echo "1. Go to WordPress Admin → Settings → Permalinks<br>";
    echo "2. Select any option EXCEPT 'Plain'<br>";
    echo "3. Recommended: 'Post name' (/%postname%/)<br>";
    echo "4. Click 'Save Changes'";
} else {
    echo "<span class='success'>✓ Pretty permalinks enabled</span><br>";
    echo "Structure: <code>$permalink_structure</code>";
}
echo "</div>";

// 2. Check Rewrite Rules
echo "<h2>2. Registered Rewrite Rules</h2>";
echo "<div class='box'>";

global $wp_rewrite;
$rules = get_option('rewrite_rules');

if (empty($rules)) {
    echo "<span class='error'>✗ No rewrite rules found!</span><br>";
    echo "<span class='warning'>⚠ Run flush_rewrite_rules()</span>";
} else {
    $kpi_rules = [];
    foreach ($rules as $pattern => $replacement) {
        if (strpos($pattern, 'kpi') !== false || strpos($replacement, 'kpi_dashboard') !== false) {
            $kpi_rules[$pattern] = $replacement;
        }
    }

    if (!empty($kpi_rules)) {
        echo "<span class='success'>✓ Found " . count($kpi_rules) . " KPI rewrite rules</span><br><br>";
        echo "<strong>KPI Routes:</strong><br>";
        echo "<pre>";
        foreach ($kpi_rules as $pattern => $replacement) {
            echo "Pattern: $pattern\n";
            echo "   → $replacement\n\n";
        }
        echo "</pre>";
    } else {
        echo "<span class='error'>✗ No KPI rewrite rules found</span><br>";
        echo "<span class='warning'>⚠ Plugin rewrite rules not registered</span>";
    }
}

echo "</div>";

// 3. Test Query Vars
echo "<h2>3. Custom Query Variables</h2>";
echo "<div class='box'>";

global $wp;
$query_vars = $wp->public_query_vars;

$has_kpi = in_array('kpi_dashboard', $query_vars);
$has_route = in_array('kpi_route', $query_vars);

if ($has_kpi) {
    echo "<span class='success'>✓ 'kpi_dashboard' query var registered</span><br>";
} else {
    echo "<span class='error'>✗ 'kpi_dashboard' query var NOT registered</span><br>";
}

if ($has_route) {
    echo "<span class='success'>✓ 'kpi_route' query var registered</span><br>";
} else {
    echo "<span class='error'>✗ 'kpi_route' query var NOT registered</span><br>";
}

echo "</div>";

// 4. Test URL Parsing
echo "<h2>4. URL Parsing Test</h2>";
echo "<div class='box'>";

$test_url = home_url('/kpi');
echo "Testing URL: <code>$test_url</code><br><br>";

// Simulate parsing
$parsed = parse_url($test_url);
echo "<strong>Parsed URL:</strong><br>";
echo "<pre>";
print_r($parsed);
echo "</pre>";

// Try to match against rules
if (!empty($rules)) {
    $path = trim($parsed['path'], '/');
    $matched = false;

    foreach ($rules as $pattern => $replacement) {
        if (preg_match("#^$pattern$#", $path, $matches)) {
            echo "<span class='success'>✓ URL matches pattern: <code>$pattern</code></span><br>";
            echo "Replacement: <code>$replacement</code><br>";
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        echo "<span class='error'>✗ URL does not match any rewrite rule</span><br>";
        echo "<span class='warning'>⚠ This is why you get 404!</span>";
    }
}

echo "</div>";

// 5. Plugin Status
echo "<h2>5. KPI Dashboard Plugin Status</h2>";
echo "<div class='box'>";

if (class_exists('KPI_Dashboard')) {
    echo "<span class='success'>✓ KPI_Dashboard class loaded</span><br>";
} else {
    echo "<span class='error'>✗ KPI_Dashboard class NOT loaded</span><br>";
}

if (class_exists('KPI_Dashboard_Router')) {
    echo "<span class='success'>✓ KPI_Dashboard_Router class loaded</span><br>";

    // Check if hooks are registered
    $priority = has_action('init', 'KPI_Dashboard_Router::add_rewrite_rules');
    if ($priority !== false) {
        echo "<span class='success'>✓ Rewrite rules hook registered</span><br>";
    } else {
        echo "<span class='warning'>⚠ Rewrite rules hook not found</span><br>";
    }
} else {
    echo "<span class='error'>✗ KPI_Dashboard_Router class NOT loaded</span><br>";
}

echo "</div>";

// 6. Solutions
echo "<h2>6. How to Fix 404 Error</h2>";
echo "<div class='box'>";

$issues = [];

if (empty($permalink_structure)) {
    $issues[] = "Permalinks set to Plain - change to Post name";
}

if (empty($kpi_rules)) {
    $issues[] = "KPI rewrite rules not registered";
}

if (!$has_kpi || !$has_route) {
    $issues[] = "Query vars not registered";
}

if (!empty($issues)) {
    echo "<strong>Detected Issues:</strong><br>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";

    echo "<br><strong>Solution Steps:</strong><br>";
    echo "<ol>";

    if (empty($permalink_structure)) {
        echo "<li><strong>Enable Pretty Permalinks:</strong><br>";
        echo "   WordPress Admin → Settings → Permalinks → Select 'Post name' → Save</li>";
    }

    echo "<li><strong>Flush Rewrite Rules (choose one):</strong><br>";
    echo "   <strong>Method A:</strong> WordPress Admin → Settings → Permalinks → Click 'Save Changes'<br>";
    echo "   <strong>Method B:</strong> Deactivate and reactivate KPI Dashboard plugin<br>";
    echo "   <strong>Method C:</strong> Run in terminal: <code>wp rewrite flush</code></li>";

    echo "<li><strong>Clear browser cache</strong> and test again</li>";

    echo "</ol>";
} else {
    echo "<span class='success'>✓ No obvious issues detected</span><br><br>";
    echo "If still getting 404, try:<br>";
    echo "1. Clear browser cache<br>";
    echo "2. Test in incognito/private window<br>";
    echo "3. Check .htaccess file permissions<br>";
    echo "4. Contact hosting support if using special server configuration";
}

echo "</div>";

// 7. Quick Actions
echo "<h2>7. Quick Actions</h2>";
echo "<div class='box'>";
echo "<form method='post'>";
echo "<button type='submit' name='flush_rules' style='background: #1565C0; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 10px;'>Flush Rewrite Rules Now</button>";
echo "<button type='submit' name='test_route' style='background: #2E7D32; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;'>Test Route Handler</button>";
echo "</form>";

if (isset($_POST['flush_rules'])) {
    flush_rewrite_rules(false);
    echo "<br><br><div style='background: #1d3d1d; padding: 15px; border-left: 3px solid #81c784; margin-top: 15px;'>";
    echo "<span class='success'>✓ Rewrite rules flushed successfully!</span><br>";
    echo "Now test: <a href='" . home_url('/kpi') . "' target='_blank' style='color: #4fc3f7;'>" . home_url('/kpi') . "</a>";
    echo "</div>";
}

if (isset($_POST['test_route'])) {
    echo "<br><br><strong>Testing Route Handler:</strong><br>";
    echo "Simulating request to /kpi...<br><br>";

    global $wp_query;
    $wp_query->set('kpi_dashboard', 1);

    if (get_query_var('kpi_dashboard')) {
        echo "<span class='success'>✓ Query var can be set and retrieved</span><br>";
    } else {
        echo "<span class='error'>✗ Query var cannot be retrieved</span><br>";
    }
}

echo "</div>";
