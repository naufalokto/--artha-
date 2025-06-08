<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Midtrans payment gateway integration
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-44JyAuImP_XPOzeZ'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    // Callback URL for Midtrans to redirect after payment
    'callback_url' => env('MIDTRANS_CALLBACK_URL', 'http://localhost:8000/payment/callback'),
    
    // Webhook URL for Midtrans to send notifications
    'webhook_url' => env('MIDTRANS_WEBHOOK_URL', 'http://localhost:8000/midtrans/webhook'),
    
    // API URLs
    'api_url' => env('MIDTRANS_IS_PRODUCTION', false) 
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',
        
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

]; 