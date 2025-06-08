<?php

namespace App\Helpers;

class MidtransConfig
{
    /**
     * Initialize Midtrans configuration
     */
    public static function initialize()
    {
        // Hardcoded Midtrans configuration
        $config = [
            'MIDTRANS_SERVER_KEY' => 'SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl',
            'MIDTRANS_CLIENT_KEY' => 'SB-Mid-client-44JyAuImP_XPOzeZ',
            'MIDTRANS_IS_PRODUCTION' => false,
            'MIDTRANS_IS_SANITIZED' => true,
            'MIDTRANS_IS_3DS' => true,
            'MIDTRANS_CALLBACK_URL' => 'http://localhost:8000/payment/callback',
            'MIDTRANS_WEBHOOK_URL' => 'http://localhost:8000/midtrans/webhook'
        ];
        
        // Set environment variables if not already set
        foreach ($config as $key => $value) {
            if (empty(env($key))) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        
        // Update config at runtime
        config([
            'midtrans.server_key' => $config['MIDTRANS_SERVER_KEY'],
            'midtrans.client_key' => $config['MIDTRANS_CLIENT_KEY'],
            'midtrans.is_production' => $config['MIDTRANS_IS_PRODUCTION'],
            'midtrans.is_sanitized' => $config['MIDTRANS_IS_SANITIZED'],
            'midtrans.is_3ds' => $config['MIDTRANS_IS_3DS'],
            'midtrans.callback_url' => $config['MIDTRANS_CALLBACK_URL'],
            'midtrans.webhook_url' => $config['MIDTRANS_WEBHOOK_URL']
        ]);
        
        \Log::info('Midtrans configuration has been initialized from MidtransConfig helper');
    }
} 