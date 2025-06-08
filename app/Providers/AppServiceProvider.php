<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Initialize Midtrans configuration
        \App\Helpers\MidtransConfig::initialize();
        
        // Load midtrans.env file if it exists
        $midtransEnvPath = base_path('midtrans.env');
        if (file_exists($midtransEnvPath)) {
            $midtransEnv = file_get_contents($midtransEnvPath);
            $lines = preg_split('/\r\n|\r|\n/', $midtransEnv);
            
            foreach ($lines as $line) {
                if (!empty($line) && strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    if (!empty($key) && !empty($value) && !env($key)) {
                        putenv("$key=$value");
                    }
                }
            }
            
            \Log::info('Midtrans environment loaded from midtrans.env file');
        } else {
            \Log::warning('midtrans.env file not found at: ' . $midtransEnvPath);
        }
    }
}
