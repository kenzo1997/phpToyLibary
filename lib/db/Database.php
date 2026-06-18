<?php
declare(strict_types=1);
namespace lib\db;

class Database {
    private \PDO $connection;
    private bool $inTransaction = false;

    public function __construct(array $config) {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $dbname = $config['dbname'] ?? 'test';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = match ($driver) {
            'mysql' => "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}",
            'sqlite' => "sqlite:{$dbname}",
            default => throw new \Exception("Unsupported database driver: {$driver}"),
        };

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new \PDO($dsn, $config['username'], $config['password'], $options);
    }

    public function getConnection(): \PDO {
        return $this->connection;
    }

    public function query(string $sql, array $bindings = []): array {
        $stmt = $this->prepare($sql, $bindings);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function prepare(string $sql, array $bindings = []): \PDOStatement {
        $stmt = $this->connection->prepare($sql);

        if (empty($bindings)) {
            return $stmt;
        }

        // Check if associative array (named params) or indexed (positional ?)
        $isAssociative = array_keys($bindings) !== range(0, count($bindings) - 1);

        if ($isAssociative) {
            // Named parameters - use them directly
            foreach ($bindings as $key => $value) {
                $stmt->bindValue(':' . $key, $value, $this->getType($value));
            }
        } else {
            // Positional parameters - bind by order (1-indexed for PDO)
            foreach ($bindings as $index => $value) {
                $stmt->bindValue($index + 1, $value, $this->getType($value));
            }
        }

        return $stmt;
    }

    private function getType($value): int {
        return match(gettype($value)) {
            'integer' => \PDO::PARAM_INT,
            'boolean' => \PDO::PARAM_BOOL,
            'NULL' => \PDO::PARAM_NULL,
            default => \PDO::PARAM_STR,
        };
    }

    public function execute(string $sql, array $bindings = []): int {
        $stmt = $this->prepare($sql, $bindings);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->prepare($sql, $data);
        $stmt->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int {
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);

        $whereParts = [];
        foreach (array_keys($where) as $key) {
            $whereParts[] = "{$key} = :where_{$key}";
        }
        $whereClause = implode(' AND ', $whereParts);

        $combined = [...$data, ...array_map(fn($k, $v) => $v, array_keys($where), $where)];

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
        $stmt = $this->prepare($sql, $combined);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int {
        $whereParts = [];
        foreach (array_keys($where) as $key) {
            $whereParts[] = "{$key} = :where_{$key}";
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        $stmt = $this->prepare($sql, $where);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function beginTransaction(): void {
        $this->inTransaction = true;
        $this->connection->beginTransaction();
    }

    public function commit(): void {
        $this->inTransaction = false;
        $this->connection->commit();
    }

    public function rollBack(): void {
        $this->inTransaction = false;
        $this->connection->rollBack();
    }

    public function inTransaction(): bool {
        return $this->inTransaction;
    }

    public function table(string $table): array {
        return $this->query("SELECT * FROM {$table}");
    }

    public function find(string $table, int $id): ?array {
        $result = $this->query("SELECT * FROM {$table} WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }
}
