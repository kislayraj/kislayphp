<?php

return [
    'name' => 'KislayApp',
    'env' => getenv('APP_ENV') ?: 'production',
    'ports' => [
        'http' => 9000,
        'gateway' => 9008,
        'discovery' => 9090
    ],
    'database' => [
        'driver' => 'sqlite',
        'path' => __DIR__ . '/../storage/database.sqlite'
    ],
    'services' => [
        'user-service' => [
            'port' => 9001,
            'health' => '/health'
        ]
    ]
];