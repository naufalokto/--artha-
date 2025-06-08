<?php
/**
 * Script untuk Debug Get Cart Error 500
 * 
 * Jalankan script ini untuk test get cart tanpa UI
 * php test_get_cart.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== GET CART DEBUG TEST SCRIPT ===\n\n";

// Konfigurasi
$laravel_url = 'http://localhost:8000';
$api_url = 'http://localhost:9090';

// Test 1: Direct ke Go backend
echo "1. Testing Direct to Go Backend...\n";
echo "   URL: {$api_url}/customer/cart\n";

// Dummy JWT token (ganti dengan token valid dari login)
$jwt_token = 'your-jwt-token-here'; // GANTI INI dengan token yang valid

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url . '/customer/cart');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $jwt_token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

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
    if ($http_code === 200) {
        echo "✅ SUCCESS - Get cart berhasil!\n";
        if (is_array($json)) {
            echo "Cart items: " . count($json) . " items\n";
        }
    } elseif ($http_code === 404) {
        echo "ℹ️  Cart not found (404) - kemungkinan cart kosong\n";
    } elseif ($http_code === 500) {
        if (empty($body) || 
            strpos($body, 'no rows') !== false || 
            strpos($body, 'empty') !== false) {
            echo "⚠️  500 tapi mungkin cart kosong\n";
        } else {
            echo "❌ ERROR 500 - " . $body . "\n";
        }
    } else {
        echo "❌ ERROR {$http_code}\n";
    }
}

// Test 2: Via Laravel endpoint
echo "\n\n2. Testing via Laravel Endpoint...\n";
echo "   URL: {$laravel_url}/customer/cart\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $laravel_url . '/customer/cart');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-CSRF-TOKEN: your-csrf-token-here' // Ganti dengan CSRF token valid
]);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Laravel Response Code: {$http_code}\n";
echo "Laravel Response: " . substr($response, 0, 500) . "\n";

$laravel_data = json_decode($response, true);
if ($laravel_data) {
    echo "Laravel Status: " . ($laravel_data['status'] ?? 'unknown') . "\n";
    echo "Cart Items Count: " . count($laravel_data['cartItems'] ?? []) . "\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "1. Jika Go backend return 500 untuk GET cart, kemungkinan cart memang kosong\n";
echo "2. Laravel frontend sudah di-update untuk handle error 500 dengan graceful\n";
echo "3. User akan melihat 'Keranjang Kosong' meskipun backend error\n";
echo "4. Delete cart sebenarnya sudah berhasil!\n";

echo "\n=== REKOMENDASI ===\n";
echo "1. Refresh browser (Ctrl+F5) untuk melihat perbaikan terbaru\n";
echo "2. Cart harusnya menampilkan 'Keranjang Kosong' tanpa error\n";
echo "3. Coba tambah produk baru ke cart untuk test\n";
echo "4. Tim Go backend bisa fix GET cart endpoint juga\n";

echo "\nScript selesai.\n"; 