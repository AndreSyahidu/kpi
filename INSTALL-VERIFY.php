<?php
/**
 * KPI Dashboard - Installation Verification
 *
 * Upload this file ke /public_html/ dan akses via browser:
 * https://www.mbdcorp.id/INSTALL-VERIFY.php
 *
 * Script ini akan verify bahwa plugin sudah terinstall dengan benar.
 */

// Auto-detect WordPress
$wp_load_paths = [
    __DIR__ . '/wp-load.php',
    __DIR__ . '/../wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $wp_load) {
    if (file_exists($wp_load)) {
        require_once $wp_load;
        $wp_loaded = true;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI Dashboard - Installation Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
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
        }
        .content {
            padding: 40px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .check-item.pass {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .check-item.fail {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .check-item .icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }
        .check-item .label {
            flex: 1;
            font-weight: 600;
        }
        .check-item .value {
            color: #666;
            font-size: 14px;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }
        .summary h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        .summary .score {
            font-size: 72px;
            font-weight: bold;
            margin: 20px 0;
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
        .section {
            margin: 30px 0;
        }
        .section h3 {
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔍 Installation Verification</h1>
        <p>KPI Dashboard WordPress Plugin</p>
    </div>

    <div class="content">
        <?php if (!$wp_loaded): ?>
            <div class="check-item fail">
                <div class="icon">❌</div>
                <div class="label">WordPress Not Found</div>
            </div>
            <div class="warning-box">
                <strong>Error:</strong> Cannot load WordPress.<br>
                Please make sure this file is in the WordPress root directory.
            </div>
        <?php else: ?>

        <?php
        $all_pass = true;
        $checks = [];

        // Check 1: PHP Version
        $php_ok = version_compare(PHP_VERSION, '8.1.0', '>=');
        $checks[] = [
            'pass' => $php_ok,
            'label' => 'PHP Version',
            'value' => PHP_VERSION . ($php_ok ? ' ✓' : ' (Required: 8.1+)'),
        ];
        $all_pass = $all_pass && $php_ok;

        // Check 2: WordPress Version
        $wp_ok = version_compare(get_bloginfo('version'), '6.4', '>=');
        $checks[] = [
            'pass' => $wp_ok,
            'label' => 'WordPress Version',
            'value' => get_bloginfo('version') . ($wp_ok ? ' ✓' : ' (Required: 6.4+)'),
        ];
        $all_pass = $all_pass && $wp_ok;

        // Check 3: Plugin Installed
        $plugin_file = WP_PLUGIN_DIR . '/kpi-dashboard/kpi-dashboard.php';
        $plugin_exists = file_exists($plugin_file);
        $checks[] = [
            'pass' => $plugin_exists,
            'label' => 'Plugin Files',
            'value' => $plugin_exists ? 'Installed' : 'NOT FOUND',
        ];
        $all_pass = $all_pass && $plugin_exists;

        // Check 4: Plugin Active
        $plugin_active = is_plugin_active('kpi-dashboard/kpi-dashboard.php');
        $checks[] = [
            'pass' => $plugin_active,
            'label' => 'Plugin Status',
            'value' => $plugin_active ? 'Active' : 'INACTIVE',
        ];
        $all_pass = $all_pass && $plugin_active;

        // Check 5: Database Tables
        global $wpdb;
        $tables_exist = true;
        $required_tables = ['kpi_users', 'kpi_departments', 'kpi_definitions', 'kpi_sessions'];
        foreach ($required_tables as $table) {
            $full_table = $wpdb->prefix . $table;
            if ($wpdb->get_var("SHOW TABLES LIKE '$full_table'") != $full_table) {
                $tables_exist = false;
                break;
            }
        }
        $checks[] = [
            'pass' => $tables_exist,
            'label' => 'Database Tables',
            'value' => $tables_exist ? 'Created (16 tables)' : 'MISSING',
        ];
        $all_pass = $all_pass && $tables_exist;

        // Check 6: Permalinks
        $permalink_structure = get_option('permalink_structure');
        $permalinks_ok = !empty($permalink_structure);
        $checks[] = [
            'pass' => $permalinks_ok,
            'label' => 'Permalinks',
            'value' => $permalinks_ok ? 'Post name ✓' : 'Plain (NOT OK)',
        ];
        $all_pass = $all_pass && $permalinks_ok;

        // Check 7: Build Files
        $build_dir = WP_PLUGIN_DIR . '/kpi-dashboard/assets/dist';
        $manifest_file = $build_dir . '/.vite/manifest.json';
        $build_exists = file_exists($manifest_file);
        $checks[] = [
            'pass' => $build_exists,
            'label' => 'Frontend Build',
            'value' => $build_exists ? 'Built ✓' : 'NOT BUILT',
        ];
        $all_pass = $all_pass && $build_exists;

        // Check 8: API Endpoints
        if (function_exists('rest_get_server')) {
            $rest_server = rest_get_server();
            $routes = $rest_server->get_routes();
            $kpi_routes = array_filter($routes, function($route) {
                return strpos($route, '/kpi/v1') === 0;
            }, ARRAY_FILTER_USE_KEY);
            $api_ok = count($kpi_routes) > 0;
            $checks[] = [
                'pass' => $api_ok,
                'label' => 'REST API',
                'value' => $api_ok ? count($kpi_routes) . ' endpoints' : 'NOT REGISTERED',
            ];
            $all_pass = $all_pass && $api_ok;
        }

        // Display checks
        echo '<div class="section">';
        echo '<h3>System Checks</h3>';
        foreach ($checks as $check) {
            $class = $check['pass'] ? 'pass' : 'fail';
            $icon = $check['pass'] ? '✅' : '❌';
            echo '<div class="check-item ' . $class . '">';
            echo '<div class="icon">' . $icon . '</div>';
            echo '<div class="label">' . $check['label'] . '</div>';
            echo '<div class="value">' . $check['value'] . '</div>';
            echo '</div>';
        }
        echo '</div>';

        // Summary
        $score = $all_pass ? 100 : round((array_sum(array_column($checks, 'pass')) / count($checks)) * 100);
        ?>

        <div class="summary">
            <h2>Installation Status</h2>
            <div class="score"><?php echo $score; ?>%</div>
            <?php if ($all_pass): ?>
                <p style="font-size: 20px; margin: 20px 0;">🎉 Perfect! Everything is ready!</p>
                <a href="<?php echo home_url('/kpi'); ?>" class="btn">🚀 Open KPI Dashboard</a>
                <a href="<?php echo admin_url('admin.php?page=kpi-dashboard'); ?>" class="btn">⚙️ Admin Panel</a>
            <?php else: ?>
                <p style="font-size: 18px; margin: 20px 0;">⚠️ Some issues need attention</p>
            <?php endif; ?>
        </div>

        <?php if (!$all_pass): ?>
        <div class="section">
            <h3>🔧 How to Fix</h3>

            <?php if (!$php_ok): ?>
            <div class="warning-box">
                <strong>PHP Version:</strong> Upgrade to PHP 8.1 or higher.<br>
                Contact your hosting provider.
            </div>
            <?php endif; ?>

            <?php if (!$wp_ok): ?>
            <div class="warning-box">
                <strong>WordPress Version:</strong> Update WordPress to 6.4 or higher.<br>
                Dashboard → Updates → Update Now
            </div>
            <?php endif; ?>

            <?php if (!$plugin_exists): ?>
            <div class="warning-box">
                <strong>Plugin Not Installed:</strong><br>
                1. Download kpi-dashboard.zip<br>
                2. Plugins → Add New → Upload Plugin<br>
                3. Install Now → Activate
            </div>
            <?php endif; ?>

            <?php if ($plugin_exists && !$plugin_active): ?>
            <div class="warning-box">
                <strong>Plugin Not Active:</strong><br>
                Plugins → KPI Dashboard → Activate
            </div>
            <?php endif; ?>

            <?php if (!$tables_exist): ?>
            <div class="warning-box">
                <strong>Database Tables Missing:</strong><br>
                1. Deactivate plugin<br>
                2. Activate plugin again<br>
                3. Tables will be created automatically
            </div>
            <?php endif; ?>

            <?php if (!$permalinks_ok): ?>
            <div class="warning-box">
                <strong>Permalinks Not Configured:</strong><br>
                1. Settings → Permalinks<br>
                2. Select "Post name"<br>
                3. Save Changes
            </div>
            <?php endif; ?>

            <?php if (!$build_exists): ?>
            <div class="warning-box">
                <strong>Frontend Not Built:</strong><br>
                This is critical! Plugin package should include built files.<br>
                Re-download the plugin from official source.
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="section">
            <h3>📊 System Information</h3>
            <div class="info-box">
                <strong>Site URL:</strong> <?php echo home_url(); ?><br>
                <strong>KPI Dashboard:</strong> <?php echo home_url('/kpi'); ?><br>
                <strong>REST API:</strong> <?php echo rest_url('kpi/v1'); ?><br>
                <strong>WordPress Path:</strong> <?php echo ABSPATH; ?><br>
                <strong>Plugin Path:</strong> <?php echo WP_PLUGIN_DIR . '/kpi-dashboard'; ?>
            </div>
        </div>

        <div class="section">
            <h3>🎯 Next Steps</h3>
            <?php if ($all_pass): ?>
            <div class="info-box">
                1. <strong>Access Dashboard:</strong> <a href="<?php echo home_url('/kpi'); ?>" target="_blank"><?php echo home_url('/kpi'); ?></a><br>
                2. <strong>Default Login:</strong> admin / admin<br>
                3. <strong>Generate Dummy Data:</strong> KPI Dashboard → System Info → Generate Dummy Data<br>
                4. <strong>Change Password:</strong> Settings → Change Password<br>
                5. <strong>Start Using:</strong> Create departments, users, KPIs!
            </div>
            <?php else: ?>
            <div class="warning-box">
                Fix the issues above, then refresh this page to verify.
            </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>🆘 Need Help?</h3>
            <div class="info-box">
                <strong>Documentation:</strong> README.md and DEPLOYMENT-READY.md<br>
                <strong>Diagnostic Tools:</strong><br>
                • <a href="<?php echo home_url('/validate-kpi-complete.php?key=validate-2024'); ?>" target="_blank">Complete Validation</a><br>
                • <a href="<?php echo home_url('/test-auth-api.php?key=test-auth'); ?>" target="_blank">Auth API Test</a><br>
                • <a href="<?php echo home_url('/fix-blank-page.php?key=fix-blank-2024'); ?>" target="_blank">Blank Page Diagnostic</a>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>
</body>
</html>
