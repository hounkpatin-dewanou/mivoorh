<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // FRONTEND_URL dans .env (Docker : http://localhost:8002, dev Vite : :5173)
    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL'),
        'http://localhost:5173',
        'http://localhost:8002',
        'https://mivoorh.kladriva.com',
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
