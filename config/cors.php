<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar tus ajustes para cross-origin resource sharing
    | o "CORS". Esto determina qué operaciones de cross-origin pueden ejecutarse
    | en navegadores web. Usualmente, las APIs de navegador no permiten cross-origin
    | a menos que se implemente esta característica.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // En producción, limita a tu dominio del frontend

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Importante para enviar cookies de autenticación

];