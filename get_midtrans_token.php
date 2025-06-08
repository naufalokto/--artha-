<?php
/**
 * Midtrans Token Generator
 * 
 * Script ini akan menghasilkan token Midtrans untuk digunakan dalam proses pembayaran.
 * 
 * Instruksi:
 * 1. Ubah SERVER_KEY dan CLIENT_KEY dengan kunci Midtrans Anda
 * 2. Jalankan script: php get_midtrans_token.php
 */

// Konfigurasi Midtrans
$isProduction = false;
$serverKey = 'SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl';
$clientKey = 'SB-Mid-client-44JyAuImP_XPOzeZ';

// Ambil kunci dari command line jika tersedia
$options = getopt("s:c:");
if (isset($options['s'])) {
    $serverKey = $options['s'];
    echo "Menggunakan Server Key dari command line\n";
}
if (isset($options['c'])) {
    $clientKey = $options['c'];
    echo "Menggunakan Client Key dari command line\n";
}

// Validasi kunci
if (strpos($serverKey, 'YOUR_SERVER_KEY') !== false) {
    echo "⚠️ SERVER KEY belum dikonfigurasi! Silakan update script ini.\n";
    echo "Atau jalankan dengan: php get_midtrans_token.php -s YOUR_SERVER_KEY -c YOUR_CLIENT_KEY\n\n";
}

// Base URL berdasarkan mode
$baseUrl = $isProduction 
    ? 'https://app.midtrans.com/snap/v1'
    : 'https://app.sandbox.midtrans.com/snap/v1';

// Data transaksi sample
$orderId = 'ORDER-' . time();
$amount = 25000;

// Data untuk Snap API
$transactionData = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => $amount
    ],
    'customer_details' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '08123456789'
    ],
    'item_details' => [
        [
            'id' => 'PROD-1',
            'price' => $amount,
            'quantity' => 1,
            'name' => 'Minyak Goreng'
        ]
    ],
    'enabled_payments' => [
        'credit_card', 'cimb_clicks', 'bca_klikbca', 'bca_klikpay',
        'bri_epay', 'echannel', 'permata_va', 'bca_va', 'bni_va',
        'bri_va', 'other_va', 'gopay', 'indomaret', 'alfamart',
        'shopeepay', 'akulaku'
    ]
];

echo "=== 💳 MIDTRANS TOKEN GENERATOR ===\n\n";
echo "Mode: " . ($isProduction ? "PRODUCTION" : "SANDBOX") . "\n";
echo "Server Key: " . maskString($serverKey) . "\n";
echo "Client Key: " . maskString($clientKey) . "\n";
echo "API URL: " . $baseUrl . "\n";
echo "Order ID: " . $orderId . "\n";
echo "Amount: " . $amount . "\n\n";

// Buat token dengan Snap API
echo "Menghubungi Midtrans API...\n";
$ch = curl_init($baseUrl . '/transactions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transactionData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode($serverKey . ':')
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Status HTTP: " . $httpCode . "\n";

if (!empty($curlError)) {
    echo "Error CURL: " . $curlError . "\n\n";
    exit(1);
}

$responseData = json_decode($response, true);

if ($httpCode == 201 || $httpCode == 200) {
    echo "✅ TOKEN BERHASIL DIBUAT!\n\n";
    echo "Token: " . ($responseData['token'] ?? 'Not available') . "\n";
    echo "Redirect URL: " . ($responseData['redirect_url'] ?? 'Not available') . "\n\n";
    
    echo "Contoh Respons untuk Frontend:\n";
    echo json_encode([
        'message' => 'Transaksi berhasil dibuat',
        'token' => $responseData['token'] ?? '',
        'redirect_url' => $responseData['redirect_url'] ?? ''
    ], JSON_PRETTY_PRINT) . "\n\n";
    
    echo "Untuk menguji di frontend, salin respons di atas dan kembalikan dari API endpoint Anda.\n";
} else {
    echo "❌ GAGAL MEMBUAT TOKEN\n";
    echo "Response: " . $response . "\n\n";
    
    if (isset($responseData['error_messages'])) {
        echo "Error Messages:\n";
        foreach ($responseData['error_messages'] as $error) {
            echo "- " . $error . "\n";
        }
    }
    
    echo "\nKemungkinan Penyebab:\n";
    echo "1. Server Key tidak valid\n";
    echo "2. Format data transaksi salah\n";
    echo "3. Masalah koneksi ke server Midtrans\n";
}

// Fungsi untuk menyembunyikan sebagian string
function maskString($str) {
    if (strlen($str) <= 8) return "****";
    return substr($str, 0, 4) . "..." . substr($str, -4);
} 