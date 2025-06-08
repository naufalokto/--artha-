<?php
/**
 * Skrip sederhana untuk memperbarui konfigurasi Midtrans
 * Jalankan dengan: php setup_midtrans.php
 */

// Path ke file .env
$envFile = __DIR__ . '/.env';

// Konfigurasi Midtrans yang benar
$midtransConfig = [
    'MIDTRANS_SERVER_KEY=SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl',
    'MIDTRANS_CLIENT_KEY=SB-Mid-client-44JyAuImP_XPOzeZ',
    'MIDTRANS_IS_PRODUCTION=false',
    'MIDTRANS_IS_SANITIZED=true',
    'MIDTRANS_IS_3DS=true',
    'MIDTRANS_CALLBACK_URL=http://localhost:8000/payment/callback',
    'MIDTRANS_WEBHOOK_URL=http://localhost:8000/midtrans/webhook'
];

// Periksa apakah file .env ada
if (file_exists($envFile)) {
    echo "File .env ditemukan, memperbarui konfigurasi Midtrans...\n";
    
    // Baca file .env
    $envContent = file_get_contents($envFile);
    
    // Cek apakah konfigurasi Midtrans sudah ada
    $updated = false;
    foreach ($midtransConfig as $config) {
        list($key, $value) = explode('=', $config, 2);
        
        // Cek apakah kunci sudah ada
        if (preg_match("/^{$key}=.*/m", $envContent)) {
            // Update nilai yang sudah ada
            $envContent = preg_replace("/^{$key}=.*/m", $config, $envContent);
            echo "- Memperbarui {$key}\n";
            $updated = true;
        }
    }
    
    // Jika tidak ada konfigurasi yang diperbarui, tambahkan di akhir file
    if (!$updated) {
        echo "- Menambahkan konfigurasi Midtrans ke file .env\n";
        $envContent .= "\n# Midtrans Configuration\n";
        $envContent .= implode("\n", $midtransConfig);
        $envContent .= "\n";
    }
    
    // Tulis kembali file .env
    file_put_contents($envFile, $envContent);
    echo "Konfigurasi Midtrans berhasil diperbarui!\n";
} else {
    echo "File .env tidak ditemukan, membuat file .env baru...\n";
    
    // Buat file .env baru dengan konfigurasi dasar
    $envContent = "APP_NAME=Laravel\n";
    $envContent .= "APP_ENV=local\n";
    $envContent .= "APP_DEBUG=true\n";
    $envContent .= "APP_URL=http://localhost:8000\n\n";
    
    // Tambahkan konfigurasi Midtrans
    $envContent .= "# Midtrans Configuration\n";
    $envContent .= implode("\n", $midtransConfig);
    $envContent .= "\n";
    
    // Tulis file .env
    file_put_contents($envFile, $envContent);
    echo "File .env baru dengan konfigurasi Midtrans berhasil dibuat!\n";
}

// Juga perbarui midtrans.env
$midtransEnvFile = __DIR__ . '/midtrans.env';
if (file_exists($midtransEnvFile)) {
    echo "\nMemperbarui file midtrans.env...\n";
    file_put_contents($midtransEnvFile, implode("\n", $midtransConfig));
    echo "File midtrans.env berhasil diperbarui!\n";
} else {
    echo "\nMembuat file midtrans.env...\n";
    file_put_contents($midtransEnvFile, implode("\n", $midtransConfig));
    echo "File midtrans.env berhasil dibuat!\n";
}

echo "\nPERHATIAN: Setelah menjalankan skrip ini, jalankan perintah berikut:\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan serve\n";

echo "\nKemudian buka browser dan akses:\n";
echo "http://localhost:8000/test-midtrans\n";
echo "untuk memverifikasi konfigurasi Midtrans.\n"; 