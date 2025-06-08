<?php
/**
 * Test Script untuk Verifikasi Format Data Checkout
 * Fokus pada validasi format data sesuai spesifikasi Go backend
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CHECKOUT FORMAT VERIFICATION TEST ===\n\n";

// Konfigurasi
$go_api = 'http://localhost:9090';
$jwt_token = 'TOKEN_ANDA'; // Ganti dengan token valid

// Test Case 1: Format Data Valid
$valid_test = [
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 2,
            'price' => 10000,
            'name' => 'Minyak Goreng'
        ]
    ]
];

// Test Case 2: Product ID Invalid
$invalid_product_id = [
    'items' => [
        [
            'product_id' => 0, // Invalid: harus > 0
            'quantity' => 2,
            'price' => 10000,
            'name' => 'Test Product'
        ]
    ]
];

// Test Case 3: Quantity Invalid
$invalid_quantity = [
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 0, // Invalid: harus > 0
            'price' => 10000,
            'name' => 'Test Product'
        ]
    ]
];

// Test Case 4: Price Invalid
$invalid_price = [
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 2,
            'price' => 0, // Invalid: harus > 0
            'name' => 'Test Product'
        ]
    ]
];

// Test Case 5: Name Empty
$invalid_name = [
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 2,
            'price' => 10000,
            'name' => '' // Invalid: tidak boleh kosong
        ]
    ]
];

// Function untuk test request
function testCheckoutFormat($data, $token, $api_url) {
    echo "\nTesting data format:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

    $ch = curl_init($api_url . '/customer/checkout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "\nResponse HTTP Code: " . $http_code . "\n";
    echo "Response Body: " . $response . "\n";
    echo "----------------------------------------\n";

    return $http_code === 200;
}

// Jalankan test
echo "Testing Valid Format:\n";
testCheckoutFormat($valid_test, $jwt_token, $go_api);

echo "\nTesting Invalid Product ID:\n";
testCheckoutFormat($invalid_product_id, $jwt_token, $go_api);

echo "\nTesting Invalid Quantity:\n";
testCheckoutFormat($invalid_quantity, $jwt_token, $go_api);

echo "\nTesting Invalid Price:\n";
testCheckoutFormat($invalid_price, $jwt_token, $go_api);

echo "\nTesting Invalid Name:\n";
testCheckoutFormat($invalid_name, $jwt_token, $go_api); 