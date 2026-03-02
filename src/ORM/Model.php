<?php

declare(strict_types=1);

namespace App\ORM;

use App\Services\Database;
use PDO;
use RuntimeException;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static ?string $connection = null;
    protected static array $fillable = [];
    /** @var array<class-string, bool> */
    private static array $booted = [];
    /** @var array<class-string, array<string, array<int, callable>>> */
    private static array $eventListeners = [];

    protected array $attributes = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->attributes = $attributes;
        $this->exists = $exists;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function fill(array $attributes): static
    {
        $data = static::filterFillable($attributes);
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function save(): bool
    {
        static::bootIfNotBooted();

        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    public function delete(): bool
    {
        static::bootIfNotBooted();

        if (!$this->exists) {
            return false;
        }

        if (!$this->fireModelEvent('deleting', true)) {
            return false;
        }

        $primaryKey = static::assertIdentifier(static::$primaryKey);
        if (!array_key_exists($primaryKey, $this->attributes)) {
            throw new RuntimeException('Cannot delete model without primary key');
        }
        $id = $this->attributes[$primaryKey];
        $table = static::assertIdentifier(static::$table);

        Database::transaction(function (PDO $db) use ($table, $primaryKey, $id): void {
            $stmt = $db->prepare("DELETE FROM {$table} WHERE {$primaryKey} = :pk");
            $stmt->bindValue(':pk', $id);
            $stmt->execute();
        }, static::$connection);

        $this->exists = false;
        $this->fireModelEvent('deleted', false);
        return true;
    }

    public static function query(): QueryBuilder
    {
        static::bootIfNotBooted();
        return new QueryBuilder(static::$table, static::class, static::$connection);
    }

    public static function where(string $column, mixed $operatorOrValue, mixed $value = null): QueryBuilder
    {
        if (func_num_args() === 2) {
            return static::query()->where($column, $operatorOrValue);
        }

        return static::query()->where($column, $operatorOrValue, $value);
    }

    public static function latest(string $column = 'created_at'): QueryBuilder
    {
        return static::query()->latest($column);
    }

    public static function oldest(string $column = 'created_at'): QueryBuilder
    {
        return static::query()->oldest($column);
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find(int|string $id): ?static
    {
        $row = static::where(static::$primaryKey, $id)->first();
        return $row instanceof static ? $row : null;
    }

    public static function create(array $attributes): static
    {
        static::bootIfNotBooted();

        $model = new static();
        $model->fill($attributes);
        if (!$model->save()) {
            throw new RuntimeException('Model create was cancelled by event listener');
        }

        return $model;
    }

    public static function firstOrCreate(array $attributes, array $values = []): static
    {
        if ($attributes === []) {
            throw new RuntimeException('firstOrCreate requires at least one attribute');
        }

        $query = static::query();
        foreach ($attributes as $column => $value) {
            if (!is_string($column) || $column === '') {
                throw new RuntimeException('firstOrCreate attribute keys must be non-empty strings');
            }
            $query->where($column, $value);
        }

        $existing = $query->first();
        if ($existing instanceof static) {
            return $existing;
        }

        return static::create(array_merge($attributes, $values));
    }

    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        return static::query()->paginate($perPage, $page);
    }

    public static function hydrate(array $attributes): static
    {
        static::bootIfNotBooted();
        return new static($attributes, true);
    }

    protected static function pdo(): PDO
    {
        return Database::connection(static::$connection);
    }

    protected static function filterFillable(array $attributes): array
    {
        if (empty(static::$fillable)) {
            return $attributes;
        }

        return array_intersect_key($attributes, array_flip(static::$fillable));
    }

    protected static function boot(): void
    {
    }

    protected static function booted(): void
    {
    }

    protected static function bootIfNotBooted(): void
    {
        $class = static::class;
        if (isset(self::$booted[$class])) {
            return;
        }

        self::$booted[$class] = true;
        static::boot();
        static::booted();
    }

    protected static function registerModelEvent(string $event, callable $callback): void
    {
        static::bootIfNotBooted();
        self::$eventListeners[static::class][$event][] = $callback;
    }

    protected function fireModelEvent(string $event, bool $halt): bool
    {
        $listeners = self::$eventListeners[static::class][$event] ?? [];
        foreach ($listeners as $listener) {
            $result = $listener($this);
            if ($halt && $result === false) {
                return false;
            }
        }

        return true;
    }

    public static function creating(callable $callback): void
    {
        static::registerModelEvent('creating', $callback);
    }

    public static function created(callable $callback): void
    {
        static::registerModelEvent('created', $callback);
    }

    public static function saving(callable $callback): void
    {
        static::registerModelEvent('saving', $callback);
    }

    public static function saved(callable $callback): void
    {
        static::registerModelEvent('saved', $callback);
    }

    public static function updating(callable $callback): void
    {
        static::registerModelEvent('updating', $callback);
    }

    public static function updated(callable $callback): void
    {
        static::registerModelEvent('updated', $callback);
    }

    public static function deleting(callable $callback): void
    {
        static::registerModelEvent('deleting', $callback);
    }

    public static function deleted(callable $callback): void
    {
        static::registerModelEvent('deleted', $callback);
    }

    protected function performInsert(): bool
    {
        if (!$this->fireModelEvent('saving', true)) {
            return false;
        }
        if (!$this->fireModelEvent('creating', true)) {
            return false;
        }

        $data = static::filterFillable($this->attributes);
        if (empty($data)) {
            throw new RuntimeException('No fillable attributes provided');
        }

        $table = static::assertIdentifier(static::$table);
        $primaryKey = static::assertIdentifier(static::$primaryKey);
        $columns = array_map(
            static fn (string $column): string => static::assertIdentifier($column),
            array_keys($data)
        );
        $columnSql = implode(', ', $columns);
        $paramSql = implode(', ', array_map(static fn (string $col): string => ':' . $col, $columns));

        $id = Database::transaction(function (PDO $db) use ($data, $table, $primaryKey, $columnSql, $paramSql) {
            $driver = strtolower((string) $db->getAttribute(PDO::ATTR_DRIVER_NAME));
            $supportsReturning = ($driver === 'pgsql');

            $sql = "INSERT INTO {$table} ({$columnSql}) VALUES ({$paramSql})";
            if ($supportsReturning) {
                $sql .= " RETURNING {$primaryKey}";
            }
            $stmt = $db->prepare($sql);

            foreach ($data as $column => $value) {
                $stmt->bindValue(':' . $column, $value);
            }

            $stmt->execute();

            if ($supportsReturning) {
                return $stmt->fetchColumn();
            }

            return $db->lastInsertId();
        }, static::$connection);

        if ($id === false || $id === null || $id === '') {
            throw new RuntimeException('Model create failed to resolve inserted primary key');
        }

        $this->attributes[$primaryKey] = static::normalizePrimaryKey($id);
        $this->exists = true;

        $fresh = static::find($this->attributes[$primaryKey]);
        if ($fresh !== null) {
            $this->attributes = $fresh->toArray();
        }

        $this->fireModelEvent('created', false);
        $this->fireModelEvent('saved', false);
        return true;
    }

    protected function performUpdate(): bool
    {
        $primaryKey = static::assertIdentifier(static::$primaryKey);
        if (!array_key_exists($primaryKey, $this->attributes)) {
            throw new RuntimeException('Cannot update model without primary key');
        }

        if (!$this->fireModelEvent('saving', true)) {
            return false;
        }
        if (!$this->fireModelEvent('updating', true)) {
            return false;
        }

        $table = static::assertIdentifier(static::$table);
        $id = $this->attributes[$primaryKey];
        $data = static::filterFillable($this->attributes);
        unset($data[$primaryKey]);

        if (!empty($data)) {
            $assignments = [];
            foreach (array_keys($data) as $column) {
                $safeColumn = static::assertIdentifier((string) $column);
                $assignments[] = "{$safeColumn} = :u_{$safeColumn}";
            }

            $sql = "UPDATE {$table} SET " . implode(', ', $assignments) . " WHERE {$primaryKey} = :pk";

            Database::transaction(function (PDO $db) use ($data, $sql, $id): void {
                $stmt = $db->prepare($sql);
                foreach ($data as $column => $value) {
                    $stmt->bindValue(':u_' . $column, $value);
                }
                $stmt->bindValue(':pk', $id);
                $stmt->execute();
            }, static::$connection);
        }

        $fresh = static::find($id);
        if ($fresh !== null) {
            $this->attributes = $fresh->toArray();
        }

        $this->fireModelEvent('updated', false);
        $this->fireModelEvent('saved', false);
        return true;
    }

    protected static function assertIdentifier(string $identifier): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new RuntimeException("Invalid SQL identifier: {$identifier}");
        }

        return $identifier;
    }

    protected static function normalizePrimaryKey(mixed $value): int|string
    {
        if (is_int($value) || is_string($value) && $value !== '' && ctype_digit($value)) {
            return (int) $value;
        }

        return (string) $value;
    }
}
