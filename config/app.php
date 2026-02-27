<?php

return [
    'name' => 'KislayApp',
    'env' => getenv('APP_ENV') ?: 'production',
    'ports' => [
        'http' => 9000,
        'gateway' => 9008,
        'discovery' => 9090
    ],
    'services' => [
        'user-service' => [
            'port' => 9001,
            'health' => '/health'
        ]
    ]
];