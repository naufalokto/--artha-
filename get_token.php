<?php
/**
 * Script untuk mendapatkan token JWT yang valid
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== GET JWT TOKEN ===\n\n";

// Konfigurasi
$go_api = 'http://localhost:9090';

// Data login
$login_data = [
    'email' => 'customer@mail.com',
    'password' => 'password'
];

// Function untuk login dan mendapatkan token
function getToken($data, $api_url) {
    echo "Attempting login with email: " . $data['email'] . "\n";
    echo "Request URL: " . $api_url . "/login\n";
    echo "Request payload: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";

    $ch = curl_init($api_url . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
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
    echo "Raw Response Body: " . $response . "\n";

    if ($response === false) {
        echo "cURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        return null;
    }

    $curl_info = curl_getinfo($ch);
    echo "\ncURL Info:\n";
    print_r($curl_info);
    
    curl_close($ch);

    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['token'])) {
            echo "\n✅ Token received successfully!\n";
            
            // Save token with error checking
            $token_file = __DIR__ . '/jwt_token.txt';
            echo "Saving token to: " . $token_file . "\n";
            
            if (file_put_contents($token_file, $data['token']) === false) {
                echo "❌ Failed to save token to file!\n";
                echo "Current directory: " . getcwd() . "\n";
                echo "Directory permissions: " . substr(sprintf('%o', fileperms('.')), -4) . "\n";
                return null;
            }
            
            // Verify the token was saved
            if (!file_exists($token_file)) {
                echo "❌ Token file was not created!\n";
                return null;
            }
            
            $saved_token = file_get_contents($token_file);
            if ($saved_token !== $data['token']) {
                echo "❌ Saved token does not match received token!\n";
                return null;
            }
            
            echo "✅ Token successfully saved to file\n";
            echo "Token: " . substr($data['token'], 0, 20) . "...\n";
            return $data['token'];
        } else {
            echo "\n❌ Token not found in response data\n";
            echo "Response data structure:\n";
            print_r($data);
        }
    } else {
        echo "\n❌ Login request failed\n";
    }

    return null;
}

// Get token
$token = getToken($login_data, $go_api);

if ($token) {
    echo "\nYou can now use this token for other test scripts.\n";
    echo "Token file location: " . __DIR__ . '/jwt_token.txt' . "\n";
} else {
    echo "\nPlease check your login credentials and try again.\n";
    echo "Also verify that the Go backend is running at: " . $go_api . "\n";
} 