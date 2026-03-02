<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;
use Throwable;

final class Database
{
    /** @var array<string, PDO> */
    private static array $instances = [];
    private static ?array $databaseConfig = null;
    private static bool $persistenceBooted = false;

    public static function getConnection(): PDO
    {
        return self::connection();
    }

    public static function connection(?string $name = null): PDO
    {
        $config = self::databaseConfig();
        $connectionName = $name ?? ($config['default'] ?? 'sqlite');

        if (!is_string($connectionName) || $connectionName === '') {
            throw new RuntimeException('Database connection name must be a non-empty string');
        }

        if (isset(self::$instances[$connectionName])) {
            return self::$instances[$connectionName];
        }

        if (class_exists('\\Kislay\\Persistence\\DB')) {
            if (!self::$persistenceBooted) {
                \Kislay\Persistence\DB::boot($config);
                self::$persistenceBooted = true;
            }

            $pdo = \Kislay\Persistence\DB::connection($connectionName);
            if (!$pdo instanceof PDO) {
                throw new RuntimeException('Kislay\\Persistence\\DB::connection did not return PDO');
            }

            self::$instances[$connectionName] = $pdo;
            return $pdo;
        }

        $connections = $config['connections'] ?? [];
        $connectionConfig = $connections[$connectionName] ?? null;

        if (!is_array($connectionConfig)) {
            throw new RuntimeException("Database connection [{$connectionName}] is not configured");
        }

        $pdo = self::createPdo($connectionConfig);
        self::configurePdo($pdo);

        // Keep skeleton self-bootstrapping for sqlite only.
        self::ensureSchemaIfSqlite($connectionConfig, $pdo);
        self::registerConnection($pdo);

        self::$instances[$connectionName] = $pdo;
        return $pdo;
    }

    public static function transaction(callable $callback, ?string $connection = null): mixed
    {
        $pdo = self::connection($connection);

        if (class_exists('\\Kislay\\Persistence\\Runtime')) {
            return \Kislay\Persistence\Runtime::transaction($pdo, $callback);
        }

        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private static function databaseConfig(): array
    {
        if (self::$databaseConfig !== null) {
            return self::$databaseConfig;
        }

        $config = Configuration::all();
        $database = $config['database'] ?? null;

        if (!is_array($database)) {
            throw new RuntimeException('Database configuration is missing in config/app.php');
        }

        self::$databaseConfig = $database;
        return self::$databaseConfig;
    }

    private static function createPdo(array $connectionConfig): PDO
    {
        $driver = strtolower((string) ($connectionConfig['driver'] ?? 'sqlite'));

        return match ($driver) {
            'sqlite' => self::createSqlite($connectionConfig),
            'mysql', 'mariadb' => self::createMysql($connectionConfig),
            'pgsql', 'postgres', 'postgresql' => self::createPgsql($connectionConfig),
            default => throw new RuntimeException("Unsupported database driver [{$driver}]"),
        };
    }

    private static function createSqlite(array $connectionConfig): PDO
    {
        $database = (string) ($connectionConfig['database'] ?? '');
        if ($database === '') {
            throw new RuntimeException('SQLite connection requires [database] path');
        }

        return new PDO('sqlite:' . $database);
    }

    private static function createMysql(array $connectionConfig): PDO
    {
        $host = (string) ($connectionConfig['host'] ?? '127.0.0.1');
        $port = (int) ($connectionConfig['port'] ?? 3306);
        $database = (string) ($connectionConfig['database'] ?? '');
        $username = (string) ($connectionConfig['username'] ?? '');
        $password = (string) ($connectionConfig['password'] ?? '');
        $charset = (string) ($connectionConfig['charset'] ?? 'utf8mb4');

        if ($database === '') {
            throw new RuntimeException('MySQL/MariaDB connection requires [database]');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
        return new PDO($dsn, $username, $password);
    }

    private static function createPgsql(array $connectionConfig): PDO
    {
        $host = (string) ($connectionConfig['host'] ?? '127.0.0.1');
        $port = (int) ($connectionConfig['port'] ?? 5432);
        $database = (string) ($connectionConfig['database'] ?? '');
        $username = (string) ($connectionConfig['username'] ?? '');
        $password = (string) ($connectionConfig['password'] ?? '');

        if ($database === '') {
            throw new RuntimeException('PostgreSQL connection requires [database]');
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
        return new PDO($dsn, $username, $password);
    }

    private static function configurePdo(PDO $pdo): void
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    private static function ensureSchemaIfSqlite(array $connectionConfig, PDO $pdo): void
    {
        $driver = strtolower((string) ($connectionConfig['driver'] ?? 'sqlite'));
        if ($driver !== 'sqlite') {
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_created_at_id ON users (created_at DESC, id DESC)');
    }

    private static function registerConnection(PDO $pdo): void
    {
        if (class_exists('\\Kislay\\Persistence\\Runtime')) {
            \Kislay\Persistence\Runtime::track($pdo);
        }
    }
}
