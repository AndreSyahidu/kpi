<?php
/**
 * KPI Dashboard - Blank Page Diagnostic
 *
 * Shows detailed info about what's rendering on /kpi page
 * Access: http://yoursite.com/wp-content/plugins/kpi-dashboard/debug-page-render.php
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
    die('Error: Could not find WordPress.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>KPI Dashboard - Page Render Debug</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f6f7f7;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #1565C0; margin-top: 0; }
        h2 { color: #333; border-bottom: 2px solid #1565C0; padding-bottom: 10px; }
        .success { color: #00a32a; }
        .error { color: #d63638; }
        .warning { color: #f0b849; }
        pre {
            background: #f6f7f7;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .test-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .test-box.pass { border-color: #00a32a; background: #f0fff4; }
        .test-box.fail { border-color: #d63638; background: #fff5f5; }
        code {
            background: #f6f7f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 KPI Dashboard - Page Render Debug</h1>
    <p>This script simulates what happens when you visit <code>/kpi</code></p>

<?php
// Check if router class exists
echo '<h2>1. Router Class Check</h2>';
$router_file = __DIR__ . '/includes/core/class-router.php';
if (file_exists($router_file)) {
    require_once $router_file;
    if (class_exists('KPI_Dashboard_Router')) {
        echo '<div class="test-box pass">✅ <strong>Router class loaded successfully</strong></div>';
    } else {
        echo '<div class="test-box fail">❌ <strong>Router class not found</strong></div>';
    }
} else {
    echo '<div class="test-box fail">❌ <strong>Router file not found</strong></div>';
}

// Check rewrite rules
echo '<h2>2. URL Rewrite Rules</h2>';
$rules = get_option('rewrite_rules');
$has_kpi = false;
if ($rules && is_array($rules)) {
    echo '<div class="test-box">';
    foreach ($rules as $pattern => $rewrite) {
        if (strpos($pattern, 'kpi') !== false || strpos($rewrite, 'kpi') !== false) {
            echo "✅ Found rule: <code>$pattern</code> → <code>$rewrite</code><br>";
            $has_kpi = true;
        }
    }
    if (!$has_kpi) {
        echo '<span class="error">❌ No KPI rewrite rule found</span><br>';
        echo '<strong>Fix:</strong> Run <code>wp rewrite flush</code> or go to Settings → Permalinks → Save';
    }
    echo '</div>';
} else {
    echo '<div class="test-box fail">❌ Could not load rewrite rules</div>';
}

// Check frontend files
echo '<h2>3. Frontend Build Files</h2>';
$dist_dir = __DIR__ . '/assets/dist';
$manifest = $dist_dir . '/.vite/manifest.json';

if (!file_exists($dist_dir)) {
    echo '<div class="test-box fail">';
    echo '❌ <strong>assets/dist/ directory does not exist</strong><br>';
    echo 'Run: <code>tar -xzf assets-dist-prebuilt.tar.gz</code>';
    echo '</div>';
} else if (!file_exists($manifest)) {
    echo '<div class="test-box fail">';
    echo '❌ <strong>Manifest file not found</strong><br>';
    echo 'File expected: <code>' . $manifest . '</code>';
    echo '</div>';
} else {
    echo '<div class="test-box pass">';
    echo '✅ <strong>Frontend files exist</strong><br><br>';

    $manifest_data = json_decode(file_get_contents($manifest), true);

    if (isset($manifest_data['assets/src/main.tsx'])) {
        $entry = $manifest_data['assets/src/main.tsx'];
        echo "Entry point: <code>assets/src/main.tsx</code><br>";
        echo "JS file: <code>{$entry['file']}</code><br>";

        $js_file = $dist_dir . '/' . $entry['file'];
        if (file_exists($js_file)) {
            $js_size = round(filesize($js_file) / 1024, 2);
            echo "JS size: <strong>{$js_size} KB</strong> ✅<br>";
        } else {
            echo "<span class='error'>❌ JS file not found: $js_file</span><br>";
        }

        if (isset($entry['css'])) {
            echo "CSS file: <code>{$entry['css'][0]}</code><br>";
            $css_file = $dist_dir . '/' . $entry['css'][0];
            if (file_exists($css_file)) {
                $css_size = round(filesize($css_file) / 1024, 2);
                echo "CSS size: <strong>{$css_size} KB</strong> ✅<br>";
            } else {
                echo "<span class='error'>❌ CSS file not found: $css_file</span><br>";
            }
        }
    } else {
        echo '<span class="error">❌ Main entry not found in manifest</span>';
    }
    echo '</div>';
}

// Simulate page render
echo '<h2>4. Simulated Page Render</h2>';
echo '<div class="test-box">';
echo '<p>This is what gets rendered on <code>/kpi</code> page:</p>';

$build_url = plugins_url('assets/dist', __FILE__);

if (file_exists($manifest)) {
    $manifest_data = json_decode(file_get_contents($manifest), true);
    $main_js = isset($manifest_data['assets/src/main.tsx']['file'])
        ? $build_url . '/' . $manifest_data['assets/src/main.tsx']['file']
        : 'NOT FOUND';
    $main_css = isset($manifest_data['assets/src/main.tsx']['css'])
        ? $build_url . '/' . $manifest_data['assets/src/main.tsx']['css'][0]
        : 'NOT FOUND';

    echo '<strong>HTML Structure:</strong>';
    echo '<pre style="font-size: 12px;">';
    echo htmlspecialchars('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI Dashboard - MBD Corp</title>
    <link rel="stylesheet" href="' . $main_css . '">
    <script>
        window.KPI_DASHBOARD_CONFIG = {
            apiUrl: "' . rest_url('kpi/v1') . '",
            siteUrl: "' . get_site_url() . '",
            baseUrl: "' . get_site_url() . '/kpi",
        };
    </script>
</head>
<body>
    <div id="root"></div>
    <script type="module" src="' . $main_js . '"></script>
</body>
</html>');
    echo '</pre>';

    echo '<strong>URLs that browser will load:</strong><br>';
    echo 'CSS: <a href="' . $main_css . '" target="_blank">' . $main_css . '</a><br>';
    echo 'JS: <a href="' . $main_js . '" target="_blank">' . $main_js . '</a><br>';
    echo '<br>';
    echo '<p><strong>Test:</strong> Click the links above. Both should load (not 404).</p>';

} else {
    echo '<span class="error">❌ Cannot simulate - manifest not found</span>';
}
echo '</div>';

// Browser console check
echo '<h2>5. What to Check in Browser</h2>';
echo '<div class="test-box">';
echo '<ol>';
echo '<li>Visit: <a href="' . get_site_url() . '/kpi" target="_blank">' . get_site_url() . '/kpi</a></li>';
echo '<li>Press <kbd>F12</kbd> to open DevTools</li>';
echo '<li>Go to <strong>Console</strong> tab</li>';
echo '<li>Look for errors (red text)</li>';
echo '<li>Check <strong>Network</strong> tab - see if JS/CSS files load (status 200)</li>';
echo '</ol>';
echo '<br>';
echo '<strong>Common errors and fixes:</strong>';
echo '<ul>';
echo '<li><code>Failed to fetch</code> → API not working, check REST API</li>';
echo '<li><code>Uncaught SyntaxError</code> → Rebuild frontend</li>';
echo '<li><code>404 on main.js</code> → Files not extracted properly</li>';
echo '<li><code>CORS error</code> → WordPress REST API blocked</li>';
echo '</ul>';
echo '</div>';

// Configuration check
echo '<h2>6. WordPress Configuration</h2>';
echo '<div class="test-box">';
echo '<table style="width: 100%; border-collapse: collapse;">';
echo '<tr><td><strong>Site URL:</strong></td><td>' . get_site_url() . '</td></tr>';
echo '<tr><td><strong>Home URL:</strong></td><td>' . get_home_url() . '</td></tr>';
echo '<tr><td><strong>REST API Base:</strong></td><td>' . rest_url() . '</td></tr>';
echo '<tr><td><strong>REST API Test:</strong></td><td><a href="' . rest_url('kpi/v1') . '" target="_blank">Test KPI API</a></td></tr>';
echo '<tr><td><strong>Permalink Structure:</strong></td><td>' . get_option('permalink_structure') . '</td></tr>';
echo '</table>';
echo '</div>';

// Final recommendations
echo '<h2>7. Final Checklist</h2>';
echo '<div class="test-box">';
echo '<input type="checkbox"> Files extracted (run <code>tar -xzf assets-dist-prebuilt.tar.gz</code>)<br>';
echo '<input type="checkbox"> Permalinks flushed (Settings → Permalinks → Save)<br>';
echo '<input type="checkbox"> Browser cache cleared (Ctrl+Shift+Del)<br>';
echo '<input type="checkbox"> No errors in browser console (F12)<br>';
echo '<input type="checkbox"> JS/CSS files load successfully (check Network tab)<br>';
echo '<input type="checkbox"> REST API accessible (test link above)<br>';
echo '</div>';

?>

    <h2>Need More Help?</h2>
    <div class="test-box">
        <p><strong>Send these screenshots:</strong></p>
        <ol>
            <li>This page (full screenshot)</li>
            <li>Browser console when visiting <code>/kpi</code> (F12 → Console tab)</li>
            <li>Browser network tab (F12 → Network → reload page)</li>
        </ol>
        <p>Or run in terminal:</p>
        <pre>cd ~/mbdcorp.id/wp-content/plugins/kpi-dashboard
ls -la assets/dist/assets/</pre>
    </div>

</div>
</body>
</html>
