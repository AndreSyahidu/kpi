<?php
/**
 * Check Database Tables - KPI Dashboard
 * Verify all tables are created with correct structure
 */

// Load WordPress
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🗄️ KPI Dashboard - Database Tables Check</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
    h1 { color: #4fc3f7; }
    h2 { color: #81c784; margin-top: 30px; }
    .success { color: #81c784; }
    .error { color: #e57373; }
    .warning { color: #ffb74d; }
    pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
    .box { background: #2d2d2d; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 3px solid #4fc3f7; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #444; }
    th { background: #333; color: #4fc3f7; }
</style>";

global $wpdb;

// Required tables for the NEW structure
$required_tables = [
    'kpi_users' => 'Users with KPI system accounts',
    'kpi_departments' => 'Company departments/divisions',
    'kpi_positions' => 'Job positions within departments',
    'kpi_department_heads' => 'Department heads assignments',
    'kpi_definitions' => 'KPI definitions and metrics',
    'kpi_assignments' => 'KPI assignments to departments/positions/users',
    'kpi_data' => 'Actual KPI data entries',
    'kpi_audit_logs' => 'Audit trail for all actions',
    'kpi_notifications' => 'In-app notifications',
    'kpi_alert_rules' => 'Alert rules configuration',
    'kpi_user_notification_prefs' => 'User notification preferences',
    'kpi_scheduled_reports' => 'Scheduled report configurations',
    'kpi_report_history' => 'Generated reports history',
    'kpi_comments' => 'Comments on KPI entries',
    'kpi_settings' => 'System settings',
    'kpi_sessions' => 'User sessions and tokens',
];

echo "<h2>1. Database Tables Status</h2>";
echo "<div class='box'>";
echo "<table>";
echo "<tr><th>Table Name</th><th>Status</th><th>Row Count</th><th>Description</th></tr>";

$all_exist = true;
$table_stats = [];

foreach ($required_tables as $table_name => $description) {
    $full_table = $wpdb->prefix . $table_name;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table;

    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
        $table_stats[$table_name] = $count;
        echo "<tr>";
        echo "<td><code>$full_table</code></td>";
        echo "<td><span class='success'>✓ EXISTS</span></td>";
        echo "<td>$count rows</td>";
        echo "<td>$description</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td><code>$full_table</code></td>";
        echo "<td><span class='error'>✗ MISSING</span></td>";
        echo "<td>-</td>";
        echo "<td>$description</td>";
        echo "</tr>";
        $all_exist = false;
    }
}

echo "</table>";

if (!$all_exist) {
    echo "<br><div style='background: #3d2020; padding: 15px; border-left: 3px solid #e57373; margin-top: 20px;'>";
    echo "<h3 style='margin-top: 0; color: #e57373;'>⚠️ MISSING TABLES DETECTED</h3>";
    echo "<p><strong>Solution:</strong></p>";
    echo "<ol>";
    echo "<li>Go to WordPress Plugins page</li>";
    echo "<li>Deactivate 'KPI Dashboard' plugin</li>";
    echo "<li>Activate it again</li>";
    echo "</ol>";
    echo "<p>This will trigger the activator to recreate missing tables.</p>";
    echo "</div>";
} else {
    echo "<br><div style='background: #1d3d1d; padding: 15px; border-left: 3px solid #81c784; margin-top: 20px;'>";
    echo "<h3 style='margin-top: 0; color: #81c784;'>✅ ALL TABLES EXIST</h3>";
    echo "</div>";
}

echo "</div>";

// Check if default admin exists
echo "<h2>2. Default Admin User</h2>";
echo "<div class='box'>";

$admin_table = $wpdb->prefix . 'kpi_users';
if ($wpdb->get_var("SHOW TABLES LIKE '$admin_table'") == $admin_table) {
    $admin = $wpdb->get_row("SELECT * FROM $admin_table WHERE username = 'admin' LIMIT 1");

    if ($admin) {
        echo "<span class='success'>✓ Default admin user exists</span><br><br>";
        echo "<strong>Admin Details:</strong><br>";
        echo "<pre>";
        echo "ID: " . $admin->id . "\n";
        echo "Username: " . $admin->username . "\n";
        echo "Email: " . $admin->email . "\n";
        echo "Full Name: " . $admin->full_name . "\n";
        echo "Role: " . $admin->role . "\n";
        echo "Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
        echo "Last Login: " . ($admin->last_login ?: 'Never') . "\n";
        echo "</pre>";

        echo "<div style='background: #2d2d3d; padding: 10px; margin-top: 10px; border-left: 3px solid #4fc3f7;'>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin</code>";
        echo "</div>";
    } else {
        echo "<span class='error'>✗ Default admin user NOT found</span><br>";
        echo "<span class='warning'>⚠ Run activator to create default admin</span>";
    }
} else {
    echo "<span class='error'>✗ Users table does not exist</span>";
}

echo "</div>";

// Check default departments
echo "<h2>3. Default Departments</h2>";
echo "<div class='box'>";

$dept_table = $wpdb->prefix . 'kpi_departments';
if ($wpdb->get_var("SHOW TABLES LIKE '$dept_table'") == $dept_table) {
    $departments = $wpdb->get_results("SELECT * FROM $dept_table ORDER BY sort_order");

    if ($departments) {
        echo "<span class='success'>✓ Found " . count($departments) . " departments</span><br><br>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Slug</th><th>Color</th><th>Active</th></tr>";

        foreach ($departments as $dept) {
            $status = $dept->is_active ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
            echo "<tr>";
            echo "<td>{$dept->id}</td>";
            echo "<td>{$dept->name}</td>";
            echo "<td><code>{$dept->slug}</code></td>";
            echo "<td><span style='display:inline-block;width:20px;height:20px;background:{$dept->color_code};border-radius:3px;'></span> {$dept->color_code}</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<span class='warning'>⚠ No departments found</span><br>";
        echo "Reactivate plugin to create default departments.";
    }
} else {
    echo "<span class='error'>✗ Departments table does not exist</span>";
}

echo "</div>";

// Check REST API endpoints
echo "<h2>4. REST API Endpoints</h2>";
echo "<div class='box'>";

$wp_rest_server = rest_get_server();
$routes = $wp_rest_server->get_routes();
$kpi_routes = [];

foreach ($routes as $route => $handlers) {
    if (strpos($route, '/kpi/v1') === 0) {
        $kpi_routes[] = $route;
    }
}

if (!empty($kpi_routes)) {
    echo "<span class='success'>✓ Found " . count($kpi_routes) . " KPI API endpoints</span><br><br>";
    echo "<strong>Key Endpoints:</strong><br>";
    echo "<pre>";
    $important = ['auth', 'users', 'departments', 'kpis', 'data', 'reports', 'analytics'];
    foreach ($kpi_routes as $route) {
        foreach ($important as $keyword) {
            if (strpos($route, "/$keyword") !== false) {
                echo $route . "\n";
                break;
            }
        }
    }
    echo "</pre>";
} else {
    echo "<span class='error'>✗ No KPI API endpoints found</span>";
}

echo "</div>";

// Summary
echo "<h2>5. System Health Summary</h2>";
echo "<div class='box'>";

$issues = [];
$warnings = [];

if (!$all_exist) {
    $issues[] = "Some database tables are missing";
}

if (empty($kpi_routes)) {
    $issues[] = "REST API endpoints not registered";
}

$users_count = isset($table_stats['kpi_users']) ? $table_stats['kpi_users'] : 0;
$dept_count = isset($table_stats['kpi_departments']) ? $table_stats['kpi_departments'] : 0;
$kpi_count = isset($table_stats['kpi_definitions']) ? $table_stats['kpi_definitions'] : 0;

if ($users_count == 0) {
    $warnings[] = "No users in the system (not even default admin)";
}

if ($dept_count == 0) {
    $warnings[] = "No departments configured";
}

if ($kpi_count == 0) {
    $warnings[] = "No KPI definitions created yet (this is normal for fresh install)";
}

if (empty($issues)) {
    echo "<div style='background: #1d3d1d; padding: 15px; border-left: 3px solid #81c784;'>";
    echo "<h3 style='margin-top: 0; color: #81c784;'>✅ SYSTEM IS HEALTHY</h3>";
    echo "<p>All core tables exist and REST API is registered.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #3d2020; padding: 15px; border-left: 3px solid #e57373;'>";
    echo "<h3 style='margin-top: 0; color: #e57373;'>❌ CRITICAL ISSUES</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($warnings)) {
    echo "<div style='background: #3d3520; padding: 15px; border-left: 3px solid #ffb74d; margin-top: 15px;'>";
    echo "<h3 style='margin-top: 0; color: #ffb74d;'>⚠️ WARNINGS</h3>";
    echo "<ul>";
    foreach ($warnings as $warning) {
        echo "<li>$warning</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<br><strong>Quick Stats:</strong><br>";
echo "<table style='width: auto;'>";
echo "<tr><td>Users:</td><td><strong>$users_count</strong></td></tr>";
echo "<tr><td>Departments:</td><td><strong>$dept_count</strong></td></tr>";
echo "<tr><td>KPI Definitions:</td><td><strong>$kpi_count</strong></td></tr>";
echo "<tr><td>API Endpoints:</td><td><strong>" . count($kpi_routes) . "</strong></td></tr>";
echo "</table>";

echo "</div>";

// Next steps
echo "<h2>6. What to Do Next</h2>";
echo "<div class='box'>";

if ($all_exist && !empty($kpi_routes) && $users_count > 0) {
    echo "<div style='background: #1d3d1d; padding: 15px; border-left: 3px solid #81c784;'>";
    echo "<h3 style='margin-top: 0; color: #81c784;'>✅ Ready to Use!</h3>";
    echo "<ol>";
    echo "<li>Visit: <a href='" . site_url('/kpi') . "' target='_blank' style='color: #4fc3f7;'>" . site_url('/kpi') . "</a></li>";
    echo "<li>Login with: <code>admin</code> / <code>admin</code></li>";
    echo "<li>Start creating KPI definitions and assign them to departments</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #3d2020; padding: 15px; border-left: 3px solid #e57373;'>";
    echo "<h3 style='margin-top: 0; color: #e57373;'>🔧 Fix Required</h3>";
    echo "<ol>";
    echo "<li>Go to: <a href='" . admin_url('plugins.php') . "' style='color: #4fc3f7;'>WordPress Plugins Page</a></li>";
    echo "<li><strong>Deactivate</strong> KPI Dashboard plugin</li>";
    echo "<li><strong>Activate</strong> it again</li>";
    echo "<li>Refresh this page to verify tables are created</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</div>";
