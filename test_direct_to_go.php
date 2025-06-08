<?php
// SIMPLE DIRECT TEST TO GO BACKEND
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIRECT TEST TO GO BACKEND ===\n\n";

// GANTI INI dengan nilai sebenarnya
$go_api = 'http://localhost:9090';
$jwt_token = 'TOKEN_ANDA'; // GANTI dengan token valid
$cart_id = 3; // GANTI dengan cart_id yang ada

echo "Testing DELETE cart_id=$cart_id dengan curl langsung ke Go Backend\n\n";

// Format yang sangat spesifik sesuai permintaan Go backend
$data = json_encode(['cart_id' => (int)$cart_id]);

// Tampilkan request
echo "REQUEST:\n";
echo "URL: $go_api/customer/cart\n";
echo "Method: DELETE\n";
echo "Headers: Authorization: Bearer {JWT_TOKEN}, Content-Type: application/json\n";
echo "Body: $data\n\n";

// Execute curl
$ch = curl_init("$go_api/customer/cart");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $jwt_token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Tampilkan response
echo "RESPONSE:\n";
echo "HTTP Code: $http_code\n";

if (!empty($curl_error)) {
    echo "CURL ERROR: $curl_error\n";
} else {
    echo "Response Body: $response\n\n";
    
    if (!empty($response)) {
        $json = json_decode($response, true);
        if ($json === null) {
            echo "ERROR: Invalid JSON response\n";
        } else {
            echo "Parsed JSON: " . print_r($json, true) . "\n";
            
            // Check format
            if (isset($json['success'])) {
                echo "SUCCESS: Response memiliki field 'success' yang diharapkan\n";
                echo "Success value: " . ($json['success'] ? 'true' : 'false') . "\n";
                echo "Message: " . ($json['message'] ?? 'tidak ada') . "\n";
            } else {
                echo "ERROR: Response tidak memiliki field 'success'\n";
                echo "Expected format: {\"success\": true/false, \"message\": \"...\"}\n";
            }
        }
    } else {
        echo "WARNING: Empty response body\n";
    }
}

echo "\n=== GUNAKAN HASIL INI UNTUK PERBAIKI CODE ===\n";
echo "1. Jika sukses, format response-nya adalah: {\"success\": true, \"message\": \"...\"}\n";
echo "2. Jika error, format response-nya adalah: {\"success\": false, \"message\": \"...\"}\n";
echo "3. Jika format berbeda, update ApiController.php untuk menangani format yang benar\n"; 