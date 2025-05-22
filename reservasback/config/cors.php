<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | The allowed origins, methods, and headers for cross-origin requests.
    | These options can be used to define which domains or hosts can interact
    | with your application.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'],  // URL de Vue

    //'allowed_origins' => ['http://localhost:4200'],  // Reemplaza con la URL de tu front-end

    'allowed_origins_patterns' => [],
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:5173')),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
