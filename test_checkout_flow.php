<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Log;

// Test configuration
$config = [
    'go_backend_url' => 'http://localhost:9090',
    'laravel_url' => 'http://localhost:8000',
    'test_user' => [
        'email' => 'customer@example.com',
        'password' => 'password123'
    ],
    'test_product' => [
        'product_id' => 1,
        'quantity' => 2,
        'name' => 'Test Product',
        'price' => 50000
    ]
];

// Helper functions
function log_step($message) {
    echo "\n=== $message ===\n";
}

function check_service($url, $name) {
    echo "Checking $name service at $url... ";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 500) {
        echo "✅ OK\n";
        return true;
    } else {
        echo "❌ Failed (HTTP $httpCode)\n";
        return false;
    }
}

function login($config) {
    log_step("1. Login Test");
    
    $ch = curl_init($config['laravel_url'] . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $config['test_user']['email'],
        'password' => $config['test_user']['password']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode === 200 && isset($data['token'])) {
        echo "✅ Login successful\n";
        echo "JWT Token: " . substr($data['token'], 0, 20) . "...\n";
        return $data['token'];
    } else {
        echo "❌ Login failed\n";
        echo "Response: " . $response . "\n";
        return null;
    }
}

function test_checkout($config, $token) {
    log_step("2. Checkout Test");
    
    $checkoutData = [
        'items' => [
            [
                'product_id' => $config['test_product']['product_id'],
                'quantity' => $config['test_product']['quantity'],
                'name' => $config['test_product']['name'],
                'price' => $config['test_product']['price']
            ]
        ]
    ];
    
    $ch = curl_init($config['laravel_url'] . '/customer/checkout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkoutData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode === 200 && isset($data['token'])) {
        echo "✅ Checkout successful\n";
        echo "Midtrans Token: " . substr($data['token'], 0, 20) . "...\n";
        return $data['token'];
    } else {
        echo "❌ Checkout failed\n";
        echo "Response: " . $response . "\n";
        return null;
    }
}

function test_midtrans_token($token) {
    log_step("3. Midtrans Token Test");
    
    if (!$token) {
        echo "❌ No Midtrans token to test\n";
        return;
    }
    
    echo "Testing Midtrans token format... ";
    if (strlen($token) > 20) {
        echo "✅ Valid format\n";
    } else {
        echo "❌ Invalid format\n";
    }
}

// Main test flow
echo "\n🔍 Starting Checkout Flow Test\n";

// Check services
log_step("Service Check");
$goBackendOk = check_service($config['go_backend_url'], 'Go Backend');
$laravelOk = check_service($config['laravel_url'], 'Laravel Frontend');

if (!$goBackendOk || !$laravelOk) {
    echo "\n❌ Required services are not available. Please check your setup.\n";
    exit(1);
}

// Run tests
$jwtToken = login($config);
if (!$jwtToken) {
    echo "\n❌ Cannot proceed with tests - login failed\n";
    exit(1);
}

$midtransToken = test_checkout($config, $jwtToken);
test_midtrans_token($midtransToken);

// Summary
log_step("Test Summary");
echo "Go Backend: " . ($goBackendOk ? "✅" : "❌") . "\n";
echo "Laravel Frontend: " . ($laravelOk ? "✅" : "❌") . "\n";
echo "Login: " . ($jwtToken ? "✅" : "❌") . "\n";
echo "Checkout: " . ($midtransToken ? "✅" : "❌") . "\n";

if ($goBackendOk && $laravelOk && $jwtToken && $midtransToken) {
    echo "\n✅ All tests passed! The checkout flow is working correctly.\n";
} else {
    echo "\n❌ Some tests failed. Please check the logs above for details.\n";
}

echo "\nNext steps:\n";
echo "1. Test the Midtrans payment popup in a browser\n";
echo "2. Verify webhook notifications are received\n";
echo "3. Check transaction status updates\n"; 