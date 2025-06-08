<?php
/**
 * Enhanced Checkout Debug Script
 * 
 * This script provides a comprehensive test of the checkout process
 * including JWT validation, cart items retrieval, and checkout processing.
 * 
 * Instructions:
 * 1. Replace YOUR_JWT_TOKEN with a valid token (login as customer first)
 * 2. Run this script from the command line: php test_checkout_debug_new.php
 * 3. Check the output for any errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== 🛒 ARTHA CHECKOUT DEBUG TOOL ===\n\n";

// Configuration
$go_api = 'http://localhost:9090';
$jwt_token = 'YOUR_JWT_TOKEN'; // REPLACE with a valid token

// Get JWT token from command line (if provided)
$options = getopt("t:");
if (isset($options['t'])) {
    $jwt_token = $options['t'];
    echo "Using JWT token from command line argument\n";
}

// Validate JWT token format
if ($jwt_token === 'YOUR_JWT_TOKEN' || !preg_match('/^[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+$/', $jwt_token)) {
    echo "⚠️ WARNING: JWT token not set or invalid format\n";
    echo "Please update the script with a valid JWT token\n";
    echo "Or run with: php test_checkout_debug_new.php -t YOUR_JWT_TOKEN\n\n";
}

echo "🔧 Configuration:\n";
echo "API URL: $go_api\n";
echo "JWT Token: " . (strlen($jwt_token) > 15 ? substr($jwt_token, 0, 15) . "..." : $jwt_token) . "\n\n";

// 1. Validate JWT token by decoding and checking expiration
echo "=== STEP 1: VALIDATE JWT TOKEN ===\n";
$token_parts = explode('.', $jwt_token);
if (count($token_parts) !== 3) {
    echo "❌ Invalid JWT format - must have 3 parts\n\n";
} else {
    $payload = json_decode(base64_decode($token_parts[1]), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Failed to decode JWT payload\n\n";
    } else {
        echo "✅ JWT payload decoded successfully\n";
        echo "User ID: " . ($payload['user_id'] ?? $payload['UserID'] ?? 'Unknown') . "\n";
        echo "Role: " . ($payload['role'] ?? $payload['Role'] ?? 'Unknown') . "\n";
        echo "Name: " . ($payload['name'] ?? $payload['Name'] ?? 'Unknown') . "\n";
        
        // Check if token is expired
        $expires = $payload['exp'] ?? $payload['ExpiresAt'] ?? null;
        if ($expires) {
            $expiry_time = is_numeric($expires) ? $expires : strtotime($expires);
            $now = time();
            echo "Expires: " . date('Y-m-d H:i:s', $expiry_time) . "\n";
            echo "Current: " . date('Y-m-d H:i:s', $now) . "\n";
            echo "Status: " . ($expiry_time > $now ? "✅ Valid" : "❌ Expired") . "\n\n";
        } else {
            echo "No expiration found in token\n\n";
        }
    }
}

// 2. Get current cart items
echo "=== STEP 2: GET CART ITEMS ===\n";
$ch = curl_init("$go_api/customer/cart");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $jwt_token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";

// Sample items to use if cart is empty
$test_items = [
    [
        'product_id' => 1,
        'quantity' => 1,
        'price' => 25000.50,
        'name' => 'Minyak Kelapa'
    ]
];

if ($http_code === 200) {
    $cart_data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($cart_data) && count($cart_data) > 0) {
        echo "✅ Cart data retrieved: " . count($cart_data) . " items\n";
        
        // Format cart data for checkout
        $test_items = array_map(function($item) {
            return [
                'product_id' => (int)$item['product_id'],
                'quantity' => (int)$item['quantity'],
                'price' => (float)$item['product']['price'],
                'name' => $item['product']['product_name']
            ];
        }, $cart_data);
        
        // Print first item as sample
        echo "Sample item: " . json_encode($test_items[0], JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "⚠️ Cart is empty or invalid format. Using test items.\n\n";
    }
} else if ($http_code === 404) {
    echo "⚠️ Cart not found (404). This is normal for empty cart. Using test items.\n\n";
} else {
    echo "❌ Failed to get cart. HTTP $http_code. Using test items.\n\n";
}

// 3. Process checkout
echo "=== STEP 3: PROCESS CHECKOUT ===\n";

// Decode JWT to get user info for Go backend payload
$payload = json_decode(base64_decode($token_parts[1]), true);
$user_id = $payload['user_id'] ?? $payload['UserID'] ?? 0;
$user_name = $payload['name'] ?? $payload['Name'] ?? 'Customer';

// Format checkout data according to Go backend expectations
$checkout_data = [
    'user_id' => (int)$user_id,
    'user_fullname' => $user_name,
    'status' => 'Pending',
    'total_amount' => array_reduce($test_items, function($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0),
    'items' => $test_items
];

echo "Checkout payload:\n";
echo json_encode($checkout_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init("$go_api/customer/checkout");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkout_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $jwt_token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Checkout HTTP Status: $http_code\n";
if (!empty($curl_error)) {
    echo "CURL Error: $curl_error\n\n";
} else {
    echo "Response: " . $response . "\n\n";
    
    $json = json_decode($response, true);
    if ($json === null) {
        echo "❌ Invalid JSON response\n";
        echo "Raw response (hex): " . bin2hex($response) . "\n\n";
    } else {
        echo "Parsed response:\n";
        print_r($json);
        
        // Check for Midtrans token
        if (isset($json['token']) || isset($json['snap_token'])) {
            echo "\n✅ SUCCESS: Midtrans token received!\n";
            echo "Token: " . ($json['token'] ?? $json['snap_token']) . "\n";
            echo "Redirect URL: " . ($json['redirect_url'] ?? 'Not provided') . "\n\n";
        } else {
            echo "\n❌ ERROR: No Midtrans token in response\n\n";
        }
    }
}

// 4. Overall status check
echo "=== CHECKOUT STATUS SUMMARY ===\n";
if ($http_code === 200 && (isset($json['token']) || isset($json['snap_token']))) {
    echo "✅ CHECKOUT SUCCESSFUL!\n";
    echo "- JWT Token valid\n";
    echo "- Cart items processed\n";
    echo "- Midtrans token received\n\n";
    echo "Next steps:\n";
    echo "1. Frontend should display Midtrans payment page using the token\n";
    echo "2. After payment, Midtrans will notify the webhook endpoint\n";
    echo "3. Transaction status will be updated\n";
} else {
    echo "❌ CHECKOUT FAILED\n";
    
    if ($http_code >= 500) {
        echo "- Backend server error (500): Check Go backend logs\n";
        echo "  Common issues: Database connection, transaction table missing\n";
    } elseif ($http_code === 401) {
        echo "- Authentication error (401): JWT token invalid or expired\n";
        echo "  Solution: Login again to get a fresh token\n";
    } elseif ($http_code === 400) {
        echo "- Bad request (400): Check payload format\n";
        echo "  Common issues: Missing required fields, invalid data types\n";
    } else {
        echo "- Unexpected error: HTTP $http_code\n";
    }
    
    echo "\nTroubleshooting:\n";
    echo "1. Check Go backend logs for detailed error messages\n";
    echo "2. Verify database connection and schema\n";
    echo "3. Confirm Midtrans credentials are configured correctly\n";
    echo "4. Ensure user has Customer role\n";
} 