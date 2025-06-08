<?php
/**
 * Test Script untuk Alur Lengkap Cart
 * Menguji: Add Cart -> Get Cart -> Delete Cart -> Checkout
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CART FLOW TEST SCRIPT ===\n\n";

// Konfigurasi
$go_api = 'http://localhost:9090';

// Ambil token dari file
$jwt_token = trim(file_get_contents('jwt_token.txt'));
if (empty($jwt_token)) {
    echo "❌ Token not found. Please run get_token.php first.\n";
    exit(1);
}

echo "Token loaded: " . substr($jwt_token, 0, 20) . "...\n\n";

// Data test untuk add cart
$add_cart_data = [
    'product_id' => 1,
    'quantity' => 2
];

// Function untuk test add cart
function testAddToCart($data, $token, $api_url) {
    echo "\nTesting Add to Cart:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $ch = curl_init($api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "\nAdd Cart Response Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    echo "----------------------------------------\n";

    return json_decode($response, true);
}

// Function untuk get cart
function getCart($token, $api_url) {
    echo "\nGetting Cart Contents:\n";

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $ch = curl_init($api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Get Cart Response Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    echo "----------------------------------------\n";

    return json_decode($response, true);
}

// Function untuk delete cart
function deleteCartItem($cart_id, $token, $api_url) {
    echo "\nDeleting Cart Item " . $cart_id . ":\n";

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $ch = curl_init($api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['cart_id' => (int)$cart_id]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Delete Response Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    echo "----------------------------------------\n";

    return $http_code === 200;
}

// Function untuk checkout
function testCheckout($items, $token, $api_url) {
    echo "\nTesting Checkout:\n";
    
    $checkout_data = [
        'items' => array_map(function($item) {
            return [
                'product_id' => (int)$item['product_id'],
                'quantity' => (int)$item['quantity'],
                'price' => (float)$item['price'],
                'name' => $item['name']
            ];
        }, $items)
    ];

    echo "Checkout Data:\n" . json_encode($checkout_data, JSON_PRETTY_PRINT) . "\n";

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $ch = curl_init($api_url . '/customer/checkout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkout_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Checkout Response Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    echo "----------------------------------------\n";

    return $http_code === 200;
}

// Test alur lengkap
echo "=== STARTING FULL CART FLOW TEST ===\n";

// 1. Add to Cart
$add_result = testAddToCart($add_cart_data, $jwt_token, $go_api);
if (!$add_result) {
    echo "❌ Add to Cart failed!\n";
    exit(1);
}

// 2. Get Cart Contents
$cart_items = getCart($jwt_token, $go_api);
if (empty($cart_items)) {
    echo "❌ Get Cart failed or cart is empty!\n";
    exit(1);
}

// 3. Delete Cart Item
foreach ($cart_items as $item) {
    $delete_result = deleteCartItem($item['cart_id'], $jwt_token, $go_api);
    if (!$delete_result) {
        echo "❌ Delete Cart Item " . $item['cart_id'] . " failed!\n";
    }
}

// 4. Verify Cart Empty
$cart_after_delete = getCart($jwt_token, $go_api);
if (!empty($cart_after_delete)) {
    echo "❌ Cart not empty after deletion!\n";
    exit(1);
}

// 5. Add New Item for Checkout
$new_add_result = testAddToCart($add_cart_data, $jwt_token, $go_api);
if (!$new_add_result) {
    echo "❌ Add new item for checkout failed!\n";
    exit(1);
}

// 6. Get Cart for Checkout
$checkout_items = getCart($jwt_token, $go_api);
if (empty($checkout_items)) {
    echo "❌ Get items for checkout failed!\n";
    exit(1);
}

// 7. Checkout
$checkout_result = testCheckout($checkout_items, $jwt_token, $go_api);
if (!$checkout_result) {
    echo "❌ Checkout failed!\n";
    exit(1);
}

echo "\n✅ Full cart flow test completed!\n"; 