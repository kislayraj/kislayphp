<?php

declare(strict_types=1);

namespace App\ORM;

use App\Services\Database;
use InvalidArgumentException;
use PDO;

final class QueryBuilder
{
    private string $table;
    private ?string $modelClass;
    private ?string $connection;
    private array $wheres = [];
    private array $bindings = [];
    private array $orders = [];
    private ?int $limitValue = null;
    private ?int $offsetValue = null;

    public function __construct(string $table, ?string $modelClass = null, ?string $connection = null)
    {
        $this->table = self::sanitizeIdentifier($table);
        $this->modelClass = $modelClass;
        $this->connection = $connection;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $column = self::sanitizeIdentifier($column);

        $operator = '=';
        $operand = $operatorOrValue;

        if (func_num_args() === 3) {
            $operator = strtoupper((string) $operatorOrValue);
            $operand = $value;
        }

        $allowedOperators = ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE'];
        if (!in_array($operator, $allowedOperators, true)) {
            throw new InvalidArgumentException("Unsupported operator: {$operator}");
        }

        $bindingKey = ':w' . count($this->bindings);
        $this->wheres[] = "{$column} {$operator} {$bindingKey}";
        $this->bindings[$bindingKey] = $operand;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $column = self::sanitizeIdentifier($column);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = "{$column} {$direction}";
        return $this;
    }

    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'ASC');
    }

    public function limit(int $limit): self
    {
        $this->limitValue = max(1, $limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetValue = max(0, $offset);
        return $this;
    }

    public function get(): array
    {
        $rows = $this->fetchRows();
        if ($this->modelClass === null) {
            return $rows;
        }

        return array_map(
            fn (array $row) => $this->modelClass::hydrate($row),
            $rows
        );
    }

    public function first(): mixed
    {
        $clone = clone $this;
        $clone->limit(1);

        $items = $clone->get();
        return $items[0] ?? null;
    }

    public function count(): int
    {
        $pdo = Database::connection($this->connection);
        $sql = 'SELECT COUNT(*) FROM ' . $this->table;

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        $stmt = $pdo->prepare($sql);
        $this->bindAll($stmt);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $perPage = max(1, $perPage);
        $page = max(1, $page);

        $total = $this->count();
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $items = (clone $this)
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        $count = count($items);
        $from = $count > 0 ? (($page - 1) * $perPage) + 1 : null;
        $to = $count > 0 ? $from + $count - 1 : null;

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to,
            'has_more_pages' => $page < $lastPage,
        ];
    }

    private function fetchRows(): array
    {
        $pdo = Database::connection($this->connection);
        $sql = 'SELECT * FROM ' . $this->table;

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        $stmt = $pdo->prepare($sql);
        $this->bindAll($stmt);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bindAll(\PDOStatement $stmt): void
    {
        foreach ($this->bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }

    private static function sanitizeIdentifier(string $identifier): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException("Invalid identifier: {$identifier}");
        }

        return $identifier;
    }
}
