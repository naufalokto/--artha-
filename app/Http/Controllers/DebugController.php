<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function midtransConfig()
    {
        $config = [
            'server_key' => config('midtrans.server_key'),
            'client_key' => config('midtrans.client_key'),
            'is_production' => config('midtrans.is_production'),
            'snap_url' => config('midtrans.snap_url'),
            'callback_url' => config('midtrans.callback_url'),
            'webhook_url' => config('midtrans.webhook_url'),
        ];
        
        $env = [
            'MIDTRANS_SERVER_KEY' => env('MIDTRANS_SERVER_KEY'),
            'MIDTRANS_CLIENT_KEY' => env('MIDTRANS_CLIENT_KEY'),
            'MIDTRANS_IS_PRODUCTION' => env('MIDTRANS_IS_PRODUCTION'),
        ];
        
        return response()->json([
            'config' => $config,
            'env' => $env,
            'files' => [
                'midtrans_env_exists' => file_exists(base_path('midtrans.env')),
                'env_exists' => file_exists(base_path('.env')),
            ],
            'app_env' => [
                'app_env' => env('APP_ENV'),
                'app_debug' => env('APP_DEBUG'),
            ]
        ]);
    }
} 