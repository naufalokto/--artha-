<?php
/**
 * Script untuk Debug Go Backend Delete Cart
 * 
 * Jalankan script ini untuk test delete cart langsung ke Go backend
 * php test_go_backend_delete.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== GO BACKEND DELETE CART TEST ===\n\n";

// Konfigurasi
$api_url = 'http://localhost:9090';

// PENTING: Ganti dengan JWT token valid dari login
// Cara mendapatkan token:
// 1. Login di browser
// 2. Buka DevTools > Application > Local Storage
// 3. Atau dari Network tab saat request
$jwt_token = 'your-jwt-token-here'; // GANTI INI!

// Test dengan cart_id yang ada
$cart_ids_to_test = [
    [1],      // Single ID
    [1, 2],   // Multiple IDs
    [3],      // ID dari log Go Anda
];

echo "Testing DELETE cart endpoint di Go backend...\n";
echo "URL: {$api_url}/customer/cart\n\n";

foreach ($cart_ids_to_test as $cart_ids) {
    echo "----------------------------------------\n";
    echo "Testing dengan cart_ids: " . json_encode($cart_ids) . "\n";
    
    $payload = [
        'cart_ids' => $cart_ids
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Pisahkan header dan body
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    echo "HTTP Status Code: {$http_code}\n";
    
    if ($error) {
        echo "cURL Error: {$error}\n";
    } else {
        echo "Response Headers:\n{$headers}\n";
        echo "Response Body: ";
        
        if (empty($body)) {
            echo "(EMPTY)\n";
        } else {
            echo "{$body}\n";
            echo "Body Length: " . strlen($body) . " bytes\n";
            
            // Try to decode JSON
            $json = json_decode($body, true);
            if ($json !== null) {
                echo "Decoded JSON: " . print_r($json, true);
            }
        }
        
        // Analisis hasil
        if ($http_code === 200 || $http_code === 204) {
            echo "✅ SUCCESS - Delete berhasil!\n";
        } elseif ($http_code === 500) {
            if (empty($body) || strpos($body, 'Failed') === false) {
                echo "⚠️  500 tapi mungkin berhasil (body kosong atau tidak ada pesan error)\n";
            } else {
                echo "❌ ERROR 500 - " . $body . "\n";
            }
        } else {
            echo "❌ ERROR {$http_code}\n";
        }
    }
    
    echo "\n";
}

// Test GET cart untuk verifikasi
echo "----------------------------------------\n";
echo "Verifikasi dengan GET cart...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url . '/customer/cart');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $jwt_token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET Cart Status: {$http_code}\n";
if ($http_code === 200) {
    $cart_items = json_decode($response, true);
    echo "Cart Items: " . count($cart_items) . " items\n";
    foreach ($cart_items as $item) {
        echo "  - Cart ID: {$item['cart_id']}, Product: {$item['product']['product_name']}\n";
    }
} else {
    echo "Error getting cart: {$response}\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "1. Jika DELETE return 500 tapi body kosong, kemungkinan sebenarnya berhasil\n";
echo "2. Go backend mungkin ada masalah di response handling setelah DELETE sukses\n";
echo "3. Cek apakah item benar-benar terhapus dengan GET cart\n";
echo "4. Minta developer Go untuk fix response setelah DELETE query\n";

echo "\n=== SARAN FIX DI GO BACKEND ===\n";
echo "// Setelah DELETE query berhasil:\n";
echo "if err == nil {\n";
echo "    w.WriteHeader(http.StatusOK) // atau 204 No Content\n";
echo "    json.NewEncoder(w).Encode(map[string]string{\n";
echo "        \"message\": \"Cart item deleted successfully\"\n";
echo "    })\n";
echo "    return\n";
echo "}\n"; 