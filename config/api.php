<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your API settings
    |
    */

    'base_url' => env('GO_API_URL', 'http://localhost:9090'),
    
    'endpoints' => [
        'users' => '/admin/viewuser',
        'create_user' => '/admin/users',
        'delete_user' => '/admin/users',
    ],
    
    'timeout' => 30, // seconds
    
    'retry_attempts' => 3,
    
    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],
]; 