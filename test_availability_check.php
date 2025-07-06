<?php
/**
 * Test file untuk verifikasi availability check (username dan email)
 * 
 * Endpoint: POST /check-availability
 * 
 * Format request:
 * {
 *   "username": "username_baru",
 *   "email": "email@example.com"
 * }
 */

echo "=== TEST AVAILABILITY CHECK ===\n";

// Test data untuk availability check
$testData = [
    'username' => 'test_user_' . time(),
    'email' => 'test_email_' . time() . '@example.com'
];

echo "Test Data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Test 1: Cek apakah Laravel server berjalan
echo "=== Test 1: Cek Laravel Server ===\n";
$laravelUrl = 'http://localhost:8000';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = @file_get_contents($laravelUrl, false, $context);
if ($response !== false) {
    echo "✅ Laravel server berjalan di $laravelUrl\n";
} else {
    echo "❌ Laravel server tidak berjalan di $laravelUrl\n";
    echo "Jalankan: php artisan serve\n\n";
}

// Test 2: Cek apakah Go backend berjalan
echo "\n=== Test 2: Cek Go Backend ===\n";
$goUrl = 'http://localhost:9090';
$response = @file_get_contents($goUrl, false, $context);
if ($response !== false) {
    echo "✅ Go backend berjalan di $goUrl\n";
} else {
    echo "❌ Go backend tidak berjalan di $goUrl\n";
    echo "Pastikan Go backend berjalan di port 9090\n\n";
}

// Test 3: Test availability check dengan data baru
echo "\n=== Test 3: Test Availability Check (Data Baru) ===\n";

$postData = json_encode($testData);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-CSRF-TOKEN: test-token'
        ],
        'content' => $postData,
        'timeout' => 10
    ]
]);

$response = @file_get_contents('http://localhost:8000/check-availability', false, $context);
if ($response !== false) {
    echo "✅ Request berhasil dikirim\n";
    echo "Response:\n";
    echo $response . "\n";
    
    // Parse response
    $responseData = json_decode($response, true);
    if ($responseData) {
        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            echo "\n🎉 AVAILABILITY CHECK BERHASIL!\n";
            echo "Username Available: " . ($responseData['username_available'] ? 'Yes' : 'No') . "\n";
            echo "Email Available: " . ($responseData['email_available'] ? 'Yes' : 'No') . "\n";
            echo "Message: " . $responseData['message'] . "\n";
        } else {
            echo "\n❌ AVAILABILITY CHECK GAGAL\n";
            echo "Error: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
    }
} else {
    echo "❌ Gagal mengirim request\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

// Test 4: Test availability check dengan data yang sudah ada
echo "\n=== Test 4: Test Availability Check (Data Existing) ===\n";

$existingData = [
    'username' => 'naufalo', // Username yang sudah ada
    'email' => 'naufal.siswanto001@gmail.com' // Email yang sudah ada
];

echo "Testing with existing data:\n";
echo json_encode($existingData, JSON_PRETTY_PRINT) . "\n\n";

$postData = json_encode($existingData);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type': 'application/json',
            'Accept: application/json',
            'X-CSRF-TOKEN: test-token'
        ],
        'content' => $postData,
        'timeout' => 10
    ]
]);

$response = @file_get_contents('http://localhost:8000/check-availability', false, $context);
if ($response !== false) {
    echo "✅ Request berhasil dikirim\n";
    echo "Response:\n";
    echo $response . "\n";
    
    // Parse response
    $responseData = json_decode($response, true);
    if ($responseData) {
        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            echo "\n📋 AVAILABILITY CHECK RESULT:\n";
            echo "Username Available: " . ($responseData['username_available'] ? 'Yes' : 'No') . "\n";
            echo "Email Available: " . ($responseData['email_available'] ? 'Yes' : 'No') . "\n";
            echo "Message: " . $responseData['message'] . "\n";
            
            if (!$responseData['username_available'] || !$responseData['email_available']) {
                echo "\n✅ Expected result: Data should not be available\n";
            } else {
                echo "\n⚠️ Unexpected result: Data should not be available\n";
            }
        } else {
            echo "\n❌ AVAILABILITY CHECK GAGAL\n";
            echo "Error: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
    }
} else {
    echo "❌ Gagal mengirim request\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

// Test 5: Test signup dengan data baru
echo "\n=== Test 5: Test Signup dengan Data Baru ===\n";

$signupData = [
    'username' => 'test_signup_' . time(),
    'email' => 'test_signup_' . time() . '@example.com',
    'password' => 'password123',
    'firstname' => 'Test',
    'lastname' => 'Signup',
    'role' => 'Customer'
];

echo "Testing signup with new data:\n";
echo json_encode($signupData, JSON_PRETTY_PRINT) . "\n\n";

$postData = json_encode($signupData);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type': 'application/json',
            'Accept: application/json',
            'X-CSRF-TOKEN: test-token'
        ],
        'content' => $postData,
        'timeout' => 10
    ]
]);

$response = @file_get_contents('http://localhost:8000/signup', false, $context);
if ($response !== false) {
    echo "✅ Request berhasil dikirim\n";
    echo "Response:\n";
    echo $response . "\n";
    
    // Parse response
    $responseData = json_decode($response, true);
    if ($responseData) {
        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            echo "\n🎉 SIGNUP BERHASIL!\n";
            echo "Message: " . $responseData['message'] . "\n";
            if (isset($responseData['user'])) {
                echo "User ID: " . $responseData['user']['user_id'] . "\n";
                echo "Username: " . $responseData['user']['username'] . "\n";
                echo "Email: " . $responseData['user']['email'] . "\n";
                echo "Role: " . $responseData['user']['role'] . "\n";
            }
        } else {
            echo "\n❌ SIGNUP GAGAL\n";
            echo "Error: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
    }
} else {
    echo "❌ Gagal mengirim request\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

echo "\n=== TEST SELESAI ===\n";
echo "Catatan:\n";
echo "1. Pastikan Laravel server berjalan di http://localhost:8000\n";
echo "2. Pastikan Go backend berjalan di http://localhost:9090\n";
echo "3. Test availability check akan mengecek username dan email\n";
echo "4. Test signup akan mencoba mendaftarkan user baru\n";
echo "5. Jika berhasil, user dapat login menggunakan email dan password yang sama\n";
?> 