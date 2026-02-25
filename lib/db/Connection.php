<?php
declare(strict_types=1);

namespace lib\db;

use PDO;
use PDOException;
use Throwable;

class Connection
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new PDO(
            $config['dsn'],
            $config['username'] ?? null,
            $config['password'] ?? null,
            $config['options'] ?? []
        );

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* =========================
       BASIC QUERY EXECUTION
    ========================== */

    public function execute(SQLBuilder $builder): \PDOStatement
    {
        $stmt = $this->pdo->prepare($builder->toSql());

        foreach ($builder->getBindings() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt;
    }

    /* =========================
       TRANSACTIONS
    ========================== */

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /* =========================
       SAFE TRANSACTION WRAPPER
    ========================== */

    public function transaction(callable $callback): mixed
    {
        try {
            $this->beginTransaction();

            $result = $callback($this);

            $this->commit();

            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
