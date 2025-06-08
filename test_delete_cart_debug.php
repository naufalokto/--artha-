<?php
/**
 * Debug Delete Cart - Test Langsung ke Go Backend
 * Sesuai spesifikasi yang diberikan:
 * 
 * Headers: Authorization: Bearer {token}, Content-Type: application/json
 * Body: {"cart_id": 123}
 * Response Success: {"success": true, "message": "..."}
 * Response Error: {"success": false, "message": "..."}
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DELETE CART DEBUG - SESUAI SPESIFIKASI ===\n\n";

// Konfigurasi
$api_url = 'http://localhost:9090';

// GANTI INI dengan JWT token valid dari login
$jwt_token = 'your-jwt-token-here'; 

// GANTI INI dengan cart_id yang benar-benar ada
$cart_ids_to_test = [1, 2, 3]; // Cart ID yang mungkin ada

echo "🔧 Konfigurasi:\n";
echo "   API URL: {$api_url}\n";
echo "   JWT Token: " . (strlen($jwt_token) > 20 ? substr($jwt_token, 0, 20) . "..." : $jwt_token) . "\n";
echo "   Cart IDs to test: " . json_encode($cart_ids_to_test) . "\n\n";

// Test setiap cart_id secara individual
foreach ($cart_ids_to_test as $cart_id) {
    echo "----------------------------------------\n";
    echo "🧪 Testing Cart ID: {$cart_id}\n";
    
    // Format PERSIS sesuai spesifikasi Go backend
    $payload = [
        'cart_id' => (int) $cart_id
    ];
    
    echo "📤 Request Details:\n";
    echo "   Method: DELETE\n";
    echo "   URL: {$api_url}/customer/cart\n";
    echo "   Headers:\n";
    echo "     - Authorization: Bearer {$jwt_token}\n";
    echo "     - Content-Type: application/json\n";
    echo "   Body: " . json_encode($payload) . "\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . '/customer/cart');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json'
        // Tidak ada Accept header sesuai spesifikasi
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "📥 Response Details:\n";
    
    if ($error) {
        echo "   ❌ cURL Error: {$error}\n";
        continue;
    }
    
    // Pisahkan header dan body
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    echo "   HTTP Status: {$http_code}\n";
    echo "   Response Headers:\n" . $headers . "\n";
    echo "   Response Body: ";
    
    if (empty($body)) {
        echo "(EMPTY)\n";
        echo "   ⚠️  Warning: Empty response body!\n";
    } else {
        echo $body . "\n";
        echo "   Body Length: " . strlen($body) . " bytes\n";
        
        // Parse JSON
        $json_data = json_decode($body, true);
        $json_error = json_last_error_msg();
        
        if ($json_data === null) {
            echo "   ❌ JSON Parse Error: {$json_error}\n";
            echo "   Raw Body (hex): " . bin2hex($body) . "\n";
        } else {
            echo "   ✅ JSON Parsed Successfully:\n";
            echo "   " . print_r($json_data, true) . "\n";
            
            // Analisis sesuai spesifikasi
            if (isset($json_data['success'])) {
                if ($json_data['success'] === true) {
                    echo "   🎉 SUCCESS! Item deleted successfully\n";
                    echo "   Message: " . ($json_data['message'] ?? 'No message') . "\n";
                } else {
                    echo "   ⚠️  FAILED! success=false\n";
                    echo "   Error Message: " . ($json_data['message'] ?? 'No message') . "\n";
                }
            } else {
                echo "   ❌ INVALID FORMAT! Missing 'success' field\n";
                echo "   Expected: {\"success\": true/false, \"message\": \"...\"}\n";
            }
        }
    }
    
    // Status analysis
    echo "\n📊 Analysis:\n";
    if ($http_code === 200) {
        echo "   ✅ HTTP 200 - Good response code\n";
    } elseif ($http_code === 400) {
        echo "   ⚠️  HTTP 400 - Bad Request (expected for invalid cart_id)\n";
    } elseif ($http_code === 404) {
        echo "   ⚠️  HTTP 404 - Not Found (expected for non-existent cart_id)\n";
    } else {
        echo "   ❌ HTTP {$http_code} - Unexpected status code\n";
    }
    
    echo "\n";
}

echo "=== HASIL TEST ===\n";
echo "1. Jika ada response dengan success=true → Delete berhasil\n";
echo "2. Jika semua response success=false → Cart ID tidak ada/tidak valid\n";
echo "3. Jika ada JSON parse error → Go backend tidak mengirim JSON yang valid\n";
echo "4. Jika response kosong → Go backend mungkin crash/error\n";

echo "\n=== LANGKAH SELANJUTNYA ===\n";
echo "1. Ganti 'your-jwt-token-here' dengan token JWT yang valid\n";
echo "2. Ganti cart_ids_to_test dengan ID yang benar-benar ada\n";
echo "3. Jalankan: php test_delete_cart_debug.php\n";
echo "4. Lihat response mana yang success=true\n";
echo "5. Jika semua gagal, cek Go backend logs\n";

echo "\n=== CARA MENDAPATKAN DATA VALID ===\n";
echo "1. Login di browser → Buka DevTools → Network tab\n";
echo "2. Lakukan request → Copy JWT token dari Authorization header\n";
echo "3. Buka halaman cart → Lihat cart_id dari response GET /customer/cart\n";
echo "4. Update variable di script ini dengan data yang valid\n";

echo "\nScript selesai.\n"; 