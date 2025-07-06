<?php
/**
 * Test file untuk verifikasi signup customer dengan Go backend
 * 
 * Endpoint: POST http://localhost:9090/signup
 * 
 * Format request sesuai dengan Go backend API:
 * {
 *   "username": "username_baru",
 *   "email": "email@example.com", 
 *   "password": "password123",
 *   "firstname": "Nama",
 *   "lastname": "Belakang",
 *   "role": "Customer"
 * }
 */

// Test data untuk signup
$testData = [
    'username' => 'test_customer_' . time(),
    'email' => 'test_customer_' . time() . '@example.com',
    'password' => 'password123',
    'firstname' => 'Test',
    'lastname' => 'Customer',
    'role' => 'Customer'
];

echo "=== TEST SIGNUP CUSTOMER ===\n";
echo "URL: http://localhost:9090/signup\n";
echo "Method: POST\n";
echo "Test Data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Test langsung ke Go backend
echo "=== Testing langsung ke Go backend ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:9090/signup');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL Error: " . $error . "\n";
} else {
    echo "HTTP Status Code: " . $httpCode . "\n";
    echo "Response:\n";
    echo $response . "\n\n";
}

// Test melalui Laravel route
echo "=== Testing melalui Laravel route ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/signup');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: test-token'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL Error: " . $error . "\n";
} else {
    echo "HTTP Status Code: " . $httpCode . "\n";
    echo "Response:\n";
    echo $response . "\n\n";
}

echo "=== TEST SELESAI ===\n";
echo "Catatan:\n";
echo "1. Pastikan Go backend berjalan di http://localhost:9090\n";
echo "2. Pastikan Laravel server berjalan di http://localhost:8000\n";
echo "3. Jika berhasil, user akan terdaftar dengan role 'Customer'\n";
echo "4. User dapat login menggunakan email dan password yang sama\n";
?> 