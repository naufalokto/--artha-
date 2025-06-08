<?php
/**
 * Script untuk Debug Delete Cart Error 500
 * 
 * Jalankan script ini untuk test delete cart tanpa UI
 * php test_delete_cart.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DELETE CART DEBUG TEST SCRIPT ===\n\n";

// Konfigurasi
$laravel_url = 'http://localhost:8000';
$api_url = 'http://localhost:9090';

// 1. Test langsung ke Go backend
echo "1. Testing Direct to Go Backend...\n";
echo "   URL: {$api_url}/customer/cart\n";

// Dummy JWT token (ganti dengan token valid dari login)
$jwt_token = 'your-jwt-token-here'; // GANTI INI dengan token yang valid

// Test data
$test_data = [
    'cart_ids' => [1, 2] // Ganti dengan cart_id yang valid
];

// Alternatif format yang mungkin diterima Go backend
$test_formats = [
    // Format 1: Array of IDs
    ['cart_ids' => [1, 2]],
    
    // Format 2: Single ID in array
    ['cart_id' => [1]],
    
    // Format 3: Direct array
    [1, 2],
    
    // Format 4: String IDs
    ['cart_ids' => ['1', '2']],
    
    // Format 5: Object dengan ID
    ['ids' => [1, 2]]
];

echo "\n2. Testing Different Request Formats...\n";

foreach ($test_formats as $index => $format) {
    echo "\n   Format " . ($index + 1) . ": " . json_encode($format) . "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($format));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "   Response Code: {$http_code}\n";
    
    if ($error) {
        echo "   cURL Error: {$error}\n";
    } else {
        echo "   Response: " . substr($response, 0, 200) . "\n";
        
        if ($http_code == 200) {
            echo "   ✅ SUCCESS! Format ini bekerja!\n";
            echo "   Full Response: {$response}\n";
            break;
        }
    }
}

// 3. Test via Laravel endpoint
echo "\n\n3. Testing via Laravel Endpoint...\n";
echo "   URL: {$laravel_url}/customer/cart\n";

// Simulasi session dengan cookie (jika menggunakan browser)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $laravel_url . '/customer/cart');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-CSRF-TOKEN: your-csrf-token-here' // Ganti dengan CSRF token valid
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['cart_ids' => [1, 2]]));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Response Code: {$http_code}\n";
echo "   Response: " . substr($response, 0, 500) . "\n";

// 4. Saran debugging
echo "\n\n=== SARAN DEBUGGING ===\n";
echo "1. Pastikan Go backend berjalan di port 9090\n";
echo "2. Ganti 'your-jwt-token-here' dengan token JWT valid dari login\n";
echo "3. Ganti cart_ids dengan ID yang benar-benar ada di database\n";
echo "4. Cek log Laravel di: storage/logs/laravel.log\n";
echo "5. Cek log Go backend untuk melihat error detail\n";

echo "\n\n=== FORMAT REQUEST YANG MUNGKIN ===\n";
echo "Berdasarkan implementasi Go yang umum, coba format ini:\n";
echo "- {\"cart_ids\": [1, 2]} - Array of integers\n";
echo "- {\"ids\": [1, 2]} - Simplified key\n";
echo "- {\"cart_id\": 1} - Single ID\n";
echo "- [1, 2] - Direct array\n";

echo "\n\nScript selesai.\n"; 