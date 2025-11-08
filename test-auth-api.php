<?php
/**
 * Test Auth API Response Structure
 *
 * Usage: php test-auth-api.php
 * Or access via browser: https://www.mbdcorp.id/test-auth-api.php?key=test-auth
 */

// Security key for browser access
if (php_sapi_name() !== 'cli') {
    $key = isset($_GET['key']) ? $_GET['key'] : '';
    if ($key !== 'test-auth') {
        die('Access denied. Use: ?key=test-auth');
    }
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

// HTML output for browser
if (php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>KPI Auth API Test</title>
        <style>
            body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
            .success { color: #4ec9b0; }
            .error { color: #f48771; }
            .warning { color: #dcdcaa; }
            .section { background: #252526; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #007acc; }
            h2 { color: #4ec9b0; margin-top: 0; }
            pre { background: #1e1e1e; padding: 15px; border-radius: 4px; overflow-x: auto; }
            .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; margin: 0 5px; }
            .badge-pass { background: #4ec9b0; color: #000; }
            .badge-fail { background: #f48771; color: #000; }
        </style>
    </head>
    <body>
        <h1>🔐 KPI Dashboard - Auth API Test</h1>
    <?php
}

echo "\n";
echo "========================================\n";
echo "Test 1: Login API Response Structure\n";
echo "========================================\n\n";

// Test login endpoint
$api_url = rest_url('kpi/v1/auth/login');
echo "Endpoint: $api_url\n\n";

$response = wp_remote_post($api_url, [
    'body' => json_encode([
        'username' => 'admin',
        'password' => 'admin',
    ]),
    'headers' => [
        'Content-Type' => 'application/json',
    ],
]);

if (is_wp_error($response)) {
    echo "❌ ERROR: " . $response->get_error_message() . "\n";
    exit;
}

$status = wp_remote_retrieve_response_code($response);
$body = wp_remote_retrieve_body($response);
$data = json_decode($body, true);

echo "HTTP Status: ";
if ($status === 200) {
    echo "<span class='success'>✅ $status</span>\n";
} else {
    echo "<span class='error'>❌ $status</span>\n";
}

echo "\nResponse Structure Check:\n";
echo "-------------------------\n";

$checks = [
    'Has success field' => isset($data['success']),
    'Has message field' => isset($data['message']),
    'Has data field' => isset($data['data']),
    'data.user exists' => isset($data['data']['user']),
    'data.access_token exists' => isset($data['data']['access_token']),
    'data.refresh_token exists' => isset($data['data']['refresh_token']),
    'data.expires_in exists' => isset($data['data']['expires_in']),
];

foreach ($checks as $check => $result) {
    $badge = $result ? "<span class='badge badge-pass'>PASS</span>" : "<span class='badge badge-fail'>FAIL</span>";
    echo "$badge $check\n";
}

echo "\nFull Response:\n";
echo "-------------\n";
echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>\n";

// Test if token is valid
if (isset($data['data']['access_token'])) {
    echo "\n";
    echo "========================================\n";
    echo "Test 2: Token Validation\n";
    echo "========================================\n\n";

    $token = $data['data']['access_token'];
    $me_url = rest_url('kpi/v1/auth/me');

    echo "Endpoint: $me_url\n";
    echo "Token: " . substr($token, 0, 50) . "...\n\n";

    $me_response = wp_remote_get($me_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);

    $me_status = wp_remote_retrieve_response_code($me_response);
    $me_body = wp_remote_retrieve_body($me_response);
    $me_data = json_decode($me_body, true);

    echo "HTTP Status: ";
    if ($me_status === 200) {
        echo "<span class='success'>✅ $me_status</span>\n";
    } else {
        echo "<span class='error'>❌ $me_status</span>\n";
    }

    echo "\nUser Data:\n";
    echo "---------\n";
    if (isset($me_data['data'])) {
        echo "✅ User authenticated successfully\n";
        echo "Username: " . ($me_data['data']['username'] ?? 'N/A') . "\n";
        echo "Email: " . ($me_data['data']['email'] ?? 'N/A') . "\n";
        echo "Role: " . ($me_data['data']['role'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Authentication failed\n";
    }

    echo "\nFull /auth/me Response:\n";
    echo "----------------------\n";
    echo "<pre>" . json_encode($me_data, JSON_PRETTY_PRINT) . "</pre>\n";
}

echo "\n";
echo "========================================\n";
echo "Test 3: Protected Endpoint (without auth)\n";
echo "========================================\n\n";

$users_url = rest_url('kpi/v1/users');
echo "Endpoint: $users_url\n\n";

$users_response = wp_remote_get($users_url);
$users_status = wp_remote_retrieve_response_code($users_response);
$users_body = wp_remote_retrieve_body($users_response);
$users_data = json_decode($users_body, true);

echo "HTTP Status: ";
if ($users_status === 401) {
    echo "<span class='success'>✅ $users_status (correctly requires auth)</span>\n";
} else {
    echo "<span class='warning'>⚠️ $users_status (should be 401)</span>\n";
}

echo "\nResponse:\n";
echo "--------\n";
echo "<pre>" . json_encode($users_data, JSON_PRETTY_PRINT) . "</pre>\n";

// Test with token
if (isset($token)) {
    echo "\n";
    echo "========================================\n";
    echo "Test 4: Protected Endpoint (with auth)\n";
    echo "========================================\n\n";

    echo "Endpoint: $users_url\n";
    echo "With Authorization header\n\n";

    $users_auth_response = wp_remote_get($users_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);

    $users_auth_status = wp_remote_retrieve_response_code($users_auth_response);
    $users_auth_body = wp_remote_retrieve_body($users_auth_response);
    $users_auth_data = json_decode($users_auth_body, true);

    echo "HTTP Status: ";
    if ($users_auth_status === 200) {
        echo "<span class='success'>✅ $users_auth_status</span>\n";
    } else {
        echo "<span class='error'>❌ $users_auth_status</span>\n";
    }

    echo "\nUser Count: ";
    if (isset($users_auth_data['data']) && is_array($users_auth_data['data'])) {
        echo count($users_auth_data['data']) . " users\n";
    } else {
        echo "N/A\n";
    }

    echo "\nResponse (first 2 users):\n";
    echo "------------------------\n";
    if (isset($users_auth_data['data']) && is_array($users_auth_data['data'])) {
        $sample = array_slice($users_auth_data['data'], 0, 2);
        echo "<pre>" . json_encode($sample, JSON_PRETTY_PRINT) . "</pre>\n";
    } else {
        echo "<pre>" . json_encode($users_auth_data, JSON_PRETTY_PRINT) . "</pre>\n";
    }
}

echo "\n";
echo "========================================\n";
echo "Summary\n";
echo "========================================\n\n";

$total_tests = 4;
$passed_tests = 0;

if ($status === 200) $passed_tests++;
if ($me_status === 200) $passed_tests++;
if ($users_status === 401) $passed_tests++;
if ($users_auth_status === 200) $passed_tests++;

echo "Tests Passed: $passed_tests / $total_tests\n";

if ($passed_tests === $total_tests) {
    echo "<span class='success'>🎉 All tests passed! Authentication system working correctly.</span>\n";
} else {
    echo "<span class='error'>⚠️ Some tests failed. Check the output above.</span>\n";
}

if (php_sapi_name() !== 'cli') {
    echo "</body></html>";
}
