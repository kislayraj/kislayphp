<?php

declare(strict_types=1);

namespace App\ORM;

use App\Services\Database;
use PDO;

final class DB
{
    private function __construct()
    {
    }

    public static function connection(?string $name = null): PDO
    {
        return Database::connection($name);
    }

    public static function transaction(callable $callback, ?string $connection = null): mixed
    {
        return Database::transaction($callback, $connection);
    }

    public static function table(string $table, ?string $connection = null): QueryBuilder
    {
        return new QueryBuilder($table, null, $connection);
    }
}
