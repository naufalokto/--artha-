<?php
/**
 * Test file untuk memverifikasi fungsi delete pada backend Go
 * 
 * Endpoint yang diuji:
 * 1. DELETE /rawmaterial - untuk menghapus raw material
 * 2. DELETE /stocks - untuk menghapus stock product
 */

// Konfigurasi
$baseUrl = 'http://localhost:9090';
$token = 'your_token_here'; // Ganti dengan token yang valid

echo "=== Test Delete Functionality ===\n\n";

// Test 1: Delete Raw Material
echo "1. Testing DELETE /rawmaterial (Single Delete)\n";
echo "URL: {$baseUrl}/rawmaterial?raw_material_id=1\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/rawmaterial?raw_material_id=1');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

// Test 2: Delete Raw Material (Multiple Delete)
echo "2. Testing DELETE /rawmaterial (Multiple Delete)\n";
echo "URL: {$baseUrl}/rawmaterial\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/rawmaterial');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'raw_material_ids' => [1, 2, 3]
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

// Test 3: Delete Stock Product (Single Delete)
echo "3. Testing DELETE /stocks (Single Delete)\n";
echo "URL: {$baseUrl}/stocks?product_id=1\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/stocks?product_id=1');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

// Test 4: Delete Stock Product (Multiple Delete)
echo "4. Testing DELETE /stocks (Multiple Delete)\n";
echo "URL: {$baseUrl}/stocks\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/stocks');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'product_ids' => [1, 2, 3]
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

echo "=== Test Complete ===\n";
echo "Note: Pastikan backend Go berjalan di localhost:9090\n";
echo "Note: Ganti 'your_token_here' dengan token yang valid\n";
?> 