<?php
/**
 * KPI Dashboard - Frontend Debug Script
 *
 * Run this to check frontend build status
 * Access via: http://yoursite.com/wp-content/plugins/kpi-dashboard/debug-frontend.php
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

echo '<pre style="background: #f6f7f7; padding: 20px; font-family: monospace;">';
echo "KPI Dashboard - Frontend Debug\n";
echo "===============================\n\n";

// Check build directory
echo "Checking Frontend Build:\n";
echo "------------------------\n";

$plugin_dir = __DIR__;
$dist_dir = $plugin_dir . '/assets/dist';
$manifest_file = $dist_dir . '/.vite/manifest.json';

echo "Plugin Directory: $plugin_dir\n";
echo "Dist Directory: $dist_dir\n\n";

// Check if dist exists
if (!file_exists($dist_dir)) {
    echo "❌ assets/dist/ directory NOT FOUND\n";
    echo "\n";
    echo "SOLUTION:\n";
    echo "  cd wp-content/plugins/kpi-dashboard\n";
    echo "  npm install --legacy-peer-deps\n";
    echo "  npm run build\n";
    echo "\n";
} else {
    echo "✅ assets/dist/ directory exists\n";

    // List files in dist
    echo "\nFiles in assets/dist/:\n";
    $files = glob($dist_dir . '/*');
    foreach ($files as $file) {
        $size = is_file($file) ? ' (' . round(filesize($file) / 1024, 2) . ' KB)' : '';
        echo "  - " . basename($file) . $size . "\n";
    }

    // Check for assets folder
    $assets_folder = $dist_dir . '/assets';
    if (file_exists($assets_folder)) {
        echo "\nFiles in assets/dist/assets/:\n";
        $asset_files = glob($assets_folder . '/*');
        foreach ($asset_files as $file) {
            $size = is_file($file) ? ' (' . round(filesize($file) / 1024, 2) . ' KB)' : '';
            echo "  - " . basename($file) . $size . "\n";
        }
    } else {
        echo "\n❌ assets/dist/assets/ NOT FOUND\n";
    }
}

echo "\n";

// Check manifest
echo "Checking Vite Manifest:\n";
echo "-----------------------\n";

if (!file_exists($manifest_file)) {
    echo "❌ Manifest file NOT FOUND: $manifest_file\n";
    echo "\nThis means frontend has NOT been built.\n";
    echo "\nRun:\n";
    echo "  npm run build\n";
} else {
    echo "✅ Manifest file exists\n";

    $manifest = json_decode(file_get_contents($manifest_file), true);
    echo "\nManifest contents:\n";
    echo json_encode($manifest, JSON_PRETTY_PRINT) . "\n";

    // Check if main entry exists
    if (isset($manifest['assets/src/main.tsx'])) {
        echo "\n✅ Main entry found in manifest\n";
        $main = $manifest['assets/src/main.tsx'];
        echo "  - JS file: " . $main['file'] . "\n";
        if (isset($main['css'])) {
            echo "  - CSS file: " . $main['css'][0] . "\n";
        }

        // Check if files actually exist
        $js_file = $dist_dir . '/' . $main['file'];
        if (file_exists($js_file)) {
            echo "  - JS file exists ✅ (" . round(filesize($js_file) / 1024, 2) . " KB)\n";
        } else {
            echo "  - JS file NOT FOUND ❌\n";
        }

        if (isset($main['css'])) {
            $css_file = $dist_dir . '/' . $main['css'][0];
            if (file_exists($css_file)) {
                echo "  - CSS file exists ✅ (" . round(filesize($css_file) / 1024, 2) . " KB)\n";
            } else {
                echo "  - CSS file NOT FOUND ❌\n";
            }
        }
    } else {
        echo "\n❌ Main entry NOT FOUND in manifest\n";
        echo "Expected key: 'assets/src/main.tsx'\n";
    }
}

echo "\n";

// Check router
echo "Checking Router Configuration:\n";
echo "------------------------------\n";

$router_file = $plugin_dir . '/includes/core/class-router.php';
if (file_exists($router_file)) {
    echo "✅ Router file exists\n";

    // Try to load it
    require_once $router_file;

    if (class_exists('KPI_Dashboard_Router')) {
        echo "✅ Router class loaded\n";
    } else {
        echo "❌ Router class NOT FOUND\n";
    }
} else {
    echo "❌ Router file NOT FOUND\n";
}

echo "\n";

// Test URL rewrite
echo "Testing URL Rewrite:\n";
echo "-------------------\n";

$kpi_url = get_site_url() . '/kpi';
echo "KPI Dashboard URL: $kpi_url\n";

// Check if rewrite rules are flushed
$rules = get_option('rewrite_rules');
if ($rules && is_array($rules)) {
    $has_kpi_rule = false;
    foreach ($rules as $pattern => $rewrite) {
        if (strpos($pattern, 'kpi') !== false) {
            echo "✅ Found rewrite rule: $pattern\n";
            $has_kpi_rule = true;
            break;
        }
    }

    if (!$has_kpi_rule) {
        echo "❌ No KPI rewrite rule found\n";
        echo "\nRun: wp rewrite flush\n";
    }
} else {
    echo "⚠️  Could not check rewrite rules\n";
}

echo "\n";

// JavaScript error checking
echo "Common Issues & Solutions:\n";
echo "--------------------------\n";
echo "1. If blank white screen:\n";
echo "   - Open browser DevTools (F12)\n";
echo "   - Check Console tab for JavaScript errors\n";
echo "   - Check Network tab - see if JS/CSS files load (200 status)\n";
echo "\n";
echo "2. If 404 on assets:\n";
echo "   - Frontend not built: npm run build\n";
echo "   - Wrong file paths in manifest\n";
echo "\n";
echo "3. If 'Uncaught SyntaxError':\n";
echo "   - Rebuild frontend: npm run build\n";
echo "\n";
echo "4. If page not found:\n";
echo "   - Flush permalinks: wp rewrite flush\n";
echo "\n";

// Final recommendation
echo "\n";
echo "=================================\n";
echo "QUICK FIX:\n";
echo "=================================\n";
echo "\n";

if (!file_exists($dist_dir) || !file_exists($manifest_file)) {
    echo "Frontend is NOT built. Run these commands:\n\n";
    echo "  cd " . $plugin_dir . "\n";
    echo "  npm install --legacy-peer-deps\n";
    echo "  npm run build\n";
    echo "\n";
    echo "Then refresh the /kpi page.\n";
} else {
    echo "Frontend appears to be built.\n\n";
    echo "Check browser console (F12) for errors:\n";
    echo "  - Press F12 in your browser\n";
    echo "  - Go to Console tab\n";
    echo "  - Refresh /kpi page\n";
    echo "  - Copy any red errors and send to me\n";
}

echo "\n";
echo "=================================\n";

echo '</pre>';
