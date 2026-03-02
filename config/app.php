<?php

$envInt = static function (string $key, int $default): int {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    $parsed = filter_var($value, FILTER_VALIDATE_INT);
    return $parsed === false ? $default : (int) $parsed;
};

$envStr = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    if (!is_string($value) || $value === '') {
        return $default;
    }

    return $value;
};

$sqlitePath = $envStr('SQLITE_DB_PATH', $envStr('DB_PATH', __DIR__ . '/../storage/database.sqlite'));

return [
    'name' => getenv('APP_NAME') ?: 'KislayApp',
    'env' => getenv('APP_ENV') ?: 'production',
    'ports' => [
        'http' => $envInt('HTTP_PORT', 9000),
        'gateway' => $envInt('GATEWAY_PORT', 9008),
        'discovery' => $envInt('DISCOVERY_PORT', 9090),
    ],
    'database' => [
        'default' => $envStr('DB_CONNECTION', 'sqlite'),
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => $envStr('MYSQL_HOST', $envStr('DB_HOST', '127.0.0.1')),
                'port' => $envInt('MYSQL_PORT', $envInt('DB_PORT', 3306)),
                'database' => $envStr('MYSQL_DATABASE', $envStr('DB_DATABASE', 'kislayphp')),
                'username' => $envStr('MYSQL_USERNAME', $envStr('DB_USERNAME', 'root')),
                'password' => $envStr('MYSQL_PASSWORD', $envStr('DB_PASSWORD', '')),
                'charset' => $envStr('MYSQL_CHARSET', 'utf8mb4'),
            ],
            'mariadb' => [
                'driver' => 'mariadb',
                'host' => $envStr('MARIADB_HOST', $envStr('DB_HOST', '127.0.0.1')),
                'port' => $envInt('MARIADB_PORT', $envInt('DB_PORT', 3306)),
                'database' => $envStr('MARIADB_DATABASE', $envStr('DB_DATABASE', 'kislayphp')),
                'username' => $envStr('MARIADB_USERNAME', $envStr('DB_USERNAME', 'root')),
                'password' => $envStr('MARIADB_PASSWORD', $envStr('DB_PASSWORD', '')),
                'charset' => $envStr('MARIADB_CHARSET', 'utf8mb4'),
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => $envStr('PGSQL_HOST', $envStr('DB_HOST', '127.0.0.1')),
                'port' => $envInt('PGSQL_PORT', $envInt('DB_PORT', 5432)),
                'database' => $envStr('PGSQL_DATABASE', $envStr('DB_DATABASE', 'kislayphp')),
                'username' => $envStr('PGSQL_USERNAME', $envStr('DB_USERNAME', 'postgres')),
                'password' => $envStr('PGSQL_PASSWORD', $envStr('DB_PASSWORD', '')),
            ],
        ],
    ],
    'services' => [
        'user-service' => [
            'port' => 9001,
            'health' => '/health',
        ],
    ],
];
