<?php
/**
 * Debug Script untuk Delete Cart
 * Dengan logging detail dan inspeksi response
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DELETE CART DEBUG SCRIPT ===\n\n";

// Konfigurasi
$go_api = 'http://localhost:9090';

// Ambil token dari file
$jwt_token = trim(file_get_contents('jwt_token.txt'));
if (empty($jwt_token)) {
    echo "❌ Token not found. Please run get_token.php first.\n";
    exit(1);
}

echo "Token loaded: " . substr($jwt_token, 0, 20) . "...\n\n";

// Function untuk get cart contents
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
    
    // Debug info
    echo "\nGET Cart Request Headers:\n";
    print_r($headers);
    
    echo "\nGET Cart Response Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    
    curl_close($ch);
    return json_decode($response, true);
}

// Function untuk delete cart dengan format yang benar
function deleteCartItems($cart_ids, $token, $api_url) {
    echo "\n=== Deleting Cart Items ===\n";
    echo "Cart IDs to delete: " . implode(", ", $cart_ids) . "\n";
    
    $delete_data = ['cart_ids' => array_map('intval', $cart_ids)];
    echo "Delete payload: " . json_encode($delete_data, JSON_PRETTY_PRINT) . "\n";

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    // Debug request info
    echo "\nRequest URL: " . $api_url . '/customer/cart' . "\n";
    echo "Request Headers:\n";
    print_r($headers);
    echo "Request Method: DELETE\n";

    $ch = curl_init($api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($delete_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Enable verbose debug output
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Get verbose debug information
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    echo "\nVerbose cURL Log:\n" . $verboseLog . "\n";
    echo "Response HTTP Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    
    // Additional response info
    $curl_info = curl_getinfo($ch);
    echo "\ncURL Info:\n";
    print_r($curl_info);
    
    curl_close($ch);

    if ($http_code === 200) {
        echo "✅ Delete successful!\n";
        return true;
    } else {
        echo "❌ Delete failed!\n";
        return false;
    }
}

// Main test flow
echo "Starting delete cart debug test...\n";

// 1. Get current cart contents
$cart_items = getCart($jwt_token, $go_api);

if (empty($cart_items)) {
    echo "\n❌ Cart is empty! Nothing to delete.\n";
    exit(1);
}

echo "\nFound " . count($cart_items) . " items in cart.\n";

// 2. Collect all cart IDs to delete
$cart_ids = array_map(function($item) {
    return $item['cart_id'];
}, $cart_items);

echo "\nAttempting to delete cart items with IDs: " . implode(", ", $cart_ids) . "\n";

// 3. Delete all items in one request
$delete_result = deleteCartItems($cart_ids, $jwt_token, $go_api);

if ($delete_result) {
    echo "✅ Successfully deleted cart items\n";
    
    // Verify deletion
    $updated_cart = getCart($jwt_token, $go_api);
    if (empty($updated_cart)) {
        echo "✅ Cart is now empty as expected.\n";
    } else {
        echo "❌ Some items still exist in cart after deletion!\n";
        echo "Remaining items:\n";
        print_r($updated_cart);
    }
} else {
    echo "❌ Failed to delete cart items\n";
}

echo "\nDelete cart debug test completed.\n"; 