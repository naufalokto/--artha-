<?php
/**
 * Manual Test Script untuk Debug Checkout Error 500
 * 
 * Jalankan script ini untuk test checkout tanpa UI
 * php test_checkout.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CHECKOUT DEBUG TEST SCRIPT ===\n\n";

// 1. Test koneksi ke Go Backend
echo "1. Testing Go Backend Connection...\n";

$api_url = 'http://localhost:9090';

// Test basic endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url . '/stocks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Go Backend is running on $api_url\n";
    echo "Stocks endpoint response: " . substr($response, 0, 100) . "...\n\n";
} else {
    echo "❌ Go Backend connection failed. HTTP Code: $http_code\n";
    echo "Make sure your Go backend is running on port 9090\n\n";
    exit(1);
}

// 2. Test dengan JWT Token dummy
echo "2. Testing Checkout Endpoint with Dummy Data...\n";

$jwt_token = 'YOUR_JWT_TOKEN_HERE'; // Ganti dengan token real dari browser

$test_data = [
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 1,
            'price' => 10000,
            'name' => 'Test Product'
        ]
    ]
];

echo "Test payload: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url . '/customer/checkout');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $jwt_token
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $http_code\n";
echo "Response: $response\n";

if ($error) {
    echo "Curl Error: $error\n";
}

// 3. Analisis berdasarkan response
echo "\n3. Analysis:\n";

switch ($http_code) {
    case 200:
        echo "✅ Checkout successful!\n";
        $data = json_decode($response, true);
        if (isset($data['token'])) {
            echo "✅ Midtrans token received\n";
        }
        break;
        
    case 401:
        echo "❌ Authentication failed - JWT token invalid\n";
        echo "Solution: Get valid JWT token from browser developer tools\n";
        break;
        
    case 403:
        echo "❌ Authorization failed - User not customer role\n";
        echo "Solution: Login as customer role\n";
        break;
        
    case 500:
        echo "❌ Backend server error - Check these:\n";
        echo "   - Database connection\n";
        echo "   - Table 'transactions' exists\n";
        echo "   - Midtrans configuration in Go backend\n";
        echo "   - Go backend logs for detailed error\n";
        break;
        
    default:
        echo "❌ Unexpected error: HTTP $http_code\n";
        break;
}

echo "\n=== DEBUG INSTRUCTIONS ===\n";
echo "1. Check Go backend console for error details\n";
echo "2. Check Laravel logs: storage/logs/laravel.log\n";
echo "3. Verify database tables exist:\n";
echo "   - transactions\n";
echo "   - transaction_details\n";
echo "   - users\n";
echo "   - products\n";
echo "4. Check JWT token is valid\n";
echo "5. Verify Midtrans keys in Go backend\n";

echo "\n=== HOW TO GET JWT TOKEN ===\n";
echo "1. Open browser, login as customer\n";
echo "2. Open Developer Tools (F12)\n";
echo "3. Go to Application tab > Session Storage\n";
echo "4. Find 'jwt_token' value\n";
echo "5. Replace 'YOUR_JWT_TOKEN_HERE' in this script\n";

echo "\n=== SQL CHECK COMMANDS ===\n";
echo "-- Check if tables exist:\n";
echo "SELECT table_name FROM information_schema.tables WHERE table_schema = 'your_database';\n";
echo "\n-- Check transactions table structure:\n";
echo "DESCRIBE transactions;\n";
echo "\n-- Check if user exists:\n";
echo "SELECT * FROM users WHERE role = 'customer' LIMIT 1;\n";

echo "\nDone.\n";
?> 