<?php
/**
 * Debug Checkout Process
 * Test langsung ke Go backend untuk diagnosa error "Gagal menyimpan transaksi"
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CHECKOUT DEBUG TEST ===\n\n";

// Konfigurasi
$go_api = 'http://localhost:9090';
$jwt_token = 'TOKEN_ANDA'; // GANTI dengan token valid

// Sample checkout data - GANTI dengan data yang sebenarnya dari cart
$test_items = [
    [
        'product_id' => 1,
        'quantity' => 2,
        'price' => 20000,
        'name' => 'Test Product'
    ]
];

echo "🔧 Konfigurasi:\n";
echo "API URL: $go_api\n";
echo "JWT Token: " . (strlen($jwt_token) > 20 ? substr($jwt_token, 0, 20) . "..." : $jwt_token) . "\n\n";

// 1. GET CART first to verify items
echo "=== STEP 1: GET CART ===\n";
$ch = curl_init("$go_api/customer/cart");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $jwt_token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET Cart Status: $http_code\n";
echo "Response: " . $response . "\n\n";

if ($http_code === 200) {
    $cart_data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Cart data valid\n";
        echo "Items in cart: " . count($cart_data) . "\n";
        
        // Use actual cart data for checkout
        $test_items = array_map(function($item) {
            return [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['product']['price'],
                'name' => $item['product']['product_name']
            ];
        }, $cart_data);
    } else {
        echo "❌ Failed to parse cart data\n";
    }
} else {
    echo "❌ Failed to get cart\n";
}

// 2. Try CHECKOUT
echo "\n=== STEP 2: CHECKOUT ===\n";

$checkout_data = ['items' => $test_items];
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

echo "Checkout Status: $http_code\n";
if (!empty($curl_error)) {
    echo "CURL Error: $curl_error\n";
} else {
    echo "Response: " . $response . "\n";
    
    $json = json_decode($response, true);
    if ($json === null) {
        echo "\n❌ Invalid JSON response\n";
        echo "Raw response (hex): " . bin2hex($response) . "\n";
    } else {
        echo "\nParsed response:\n";
        print_r($json);
        
        if (isset($json['error']) || isset($json['message'])) {
            echo "\n⚠️ Error details:\n";
            echo "Error: " . ($json['error'] ?? $json['message']) . "\n";
        }
    }
}

echo "\n=== ANALISIS ===\n";
echo "1. HTTP Status $http_code\n";
if ($http_code >= 500) {
    echo "   ❌ Backend error (500) - Check Go backend logs\n";
} elseif ($http_code === 401) {
    echo "   ❌ Unauthorized - JWT token invalid/expired\n";
} elseif ($http_code === 400) {
    echo "   ❌ Bad Request - Check payload format\n";
} elseif ($http_code === 200) {
    echo "   ✅ Request accepted\n";
}

echo "\n=== TROUBLESHOOTING STEPS ===\n";
echo "1. Pastikan format checkout data sesuai:\n";
echo "   {\"items\": [{\"product_id\": 1, \"quantity\": 1, \"price\": 1000, \"name\": \"...\"}]}\n";
echo "2. Pastikan JWT token masih valid\n";
echo "3. Cek Go backend logs untuk detail error\n";
echo "4. Pastikan semua product_id masih ada di database\n";
echo "5. Cek koneksi Go backend ke database\n"; 