<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => ['http://localhost:3000', 'https://tu-dominio-frontend.com'],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['X-XSRF-TOKEN', 'Content-Type', 'X-Requested-With', 'Accept', 'Origin', 'Referer'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];