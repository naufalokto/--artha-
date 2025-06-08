<?php

// Load Laravel environment variables
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check Midtrans config
echo "Midtrans Server Key: " . config('midtrans.server_key') . "\n";
echo "Midtrans Client Key: " . config('midtrans.client_key') . "\n";
echo "Midtrans Production Mode: " . (config('midtrans.is_production') ? 'Yes' : 'No') . "\n";
echo "Midtrans Callback URL: " . config('midtrans.callback_url') . "\n";

// Check loaded from environment
echo "\nEnvironment Variables:\n";
echo "MIDTRANS_SERVER_KEY: " . env('MIDTRANS_SERVER_KEY') . "\n";
echo "MIDTRANS_CLIENT_KEY: " . env('MIDTRANS_CLIENT_KEY') . "\n"; 