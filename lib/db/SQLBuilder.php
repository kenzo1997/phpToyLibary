<?php
declare(strict_types=1);

namespace lib\db;

/**
 * SQLBuilder
 *
 * @package  db\
 * @author   Kenzo Coenaerts
 */
class SQLBuilder {
    private string $sql = '';
    private array $bindings = [];
    private bool $usePlaceholders = false;

    public function __construct(bool $usePlaceholders = true) {
        $this->usePlaceholders = $usePlaceholders;
    }

    public function SELECT(string $table, array|string $values = '*', bool $distinct = false): self {
        if ($values === '*') {
            $this->sql .= $distinct ? 'SELECT DISTINCT * ' : 'SELECT * ';
            $this->sql .= 'FROM ' . $this->escapeIdentifier($table);
            return $this;
        }

        $this->sql .= $distinct ? 'SELECT DISTINCT ' : 'SELECT ';

        if (is_array($values)) {
            $cols = [];
            foreach ($values as $val) {
                $cols[] = $this->escapeIdentifier($val);
            }
            $this->sql .= implode(', ', $cols) . ' ';
        } else {
            $this->sql .= $this->escapeIdentifier($values) . ' ';
        }

        $this->sql .= 'FROM ' . $this->escapeIdentifier($table);
        return $this;
    }

    public function INSERT(string $table, array $data): self {
        $this->sql .= 'INSERT INTO ' . $this->escapeIdentifier($table) . ' (';

        $columns = [];
        $placeholders = [];
        foreach ($data as $key => $value) {
            $columns[] = $this->escapeIdentifier($key);
            $this->bindings[] = $value;
            $placeholders[] = '?';
        }

        $this->sql .= implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        return $this;
    }

    public function UPDATE(string $table, array $data): self {
        $this->sql .= 'UPDATE ' . $this->escapeIdentifier($table) . ' SET ';

        $sets = [];
        foreach ($data as $key => $value) {
            $this->bindings[] = $value;
            $sets[] = $this->escapeIdentifier($key) . ' = ?';
        }
        $this->sql .= implode(', ', $sets);
        return $this;
    }

    public function DELETE(string $table): self {
        $this->sql = "DELETE FROM " . $this->escapeIdentifier($table);
        return $this;
    }

    public function INNER_JOIN(string $table): self {
        $this->sql .= " INNER JOIN " . $this->escapeIdentifier($table);
        return $this;
    }

    public function LEFT_OUTER_JOIN(string $table): self {
        $this->sql .= " LEFT JOIN " . $this->escapeIdentifier($table);
        return $this;
    }

    public function RIGHT_OUTER_JOIN(string $table): self {
        $this->sql .= " RIGHT JOIN " . $this->escapeIdentifier($table);
        return $this;
    }

    public function FULL_OUTER_JOIN(string $table): self {
        $this->sql .= " FULL JOIN " . $this->escapeIdentifier($table);
        return $this;
    }

    public function USING(string $column): self {
        $this->sql .= " USING (" . $this->escapeIdentifier($column) . ")";
        return $this;
    }

    public function ON(string $col1, string $col2, string $operator = '='): self {
        $this->sql .= " ON (" . $this->escapeIdentifier($col1) . " " . $operator . " " . $this->escapeIdentifier($col2) . ")";
        return $this;
    }

    // WHERE clause methods
    public function WHERE(string $column, string $operator = '=', mixed $value = null): self {
        $this->sql .= ' WHERE ' . $this->escapeIdentifier($column) . ' ' . $operator;
        if ($value !== null) {
            $this->bindings[] = $value;
            $this->sql .= ' ?';
        }
        return $this;
    }

    public function AND_WHERE(string $column, string $operator = '=', mixed $value = null): self {
        $this->sql .= ' AND ' . $this->escapeIdentifier($column) . ' ' . $operator;
        if ($value !== null) {
            $this->bindings[] = $value;
            $this->sql .= ' ?';
        }
        return $this;
    }

    public function OR_WHERE(string $column, string $operator = '=', mixed $value = null): self {
        $this->sql .= ' OR ' . $this->escapeIdentifier($column) . ' ' . $operator;
        if ($value !== null) {
            $this->bindings[] = $value;
            $this->sql .= ' ?';
        }
        return $this;
    }

    // Sorting and pagination
    public function ORDER_BY(string $column, string $direction = 'ASC'): self {
        $this->sql .= ' ORDER BY ' . $this->escapeIdentifier($column) . ' ' . strtoupper($direction);
        return $this;
    }

    public function GROUP_BY(string $column): self {
        $this->sql .= ' GROUP BY ' . $this->escapeIdentifier($column);
        return $this;
    }

    public function HAVING(string $column, string $operator = '=', mixed $value = null): self {
        $this->sql .= ' HAVING ' . $this->escapeIdentifier($column) . ' ' . $operator;
        if ($value !== null) {
            $this->bindings[] = $value;
            $this->sql .= ' ?';
        }
        return $this;
    }

    public function LIMIT(int $count): self {
        $this->sql .= ' LIMIT ' . $count;
        return $this;
    }

    public function OFFSET(int $count): self {
        $this->sql .= ' OFFSET ' . $count;
        return $this;
    }

    // Aggregates
    public function COUNT(string $column = '*'): self {
        $this->sql .= ' COUNT(' . ($column === '*' ? '*' : $this->escapeIdentifier($column)) . ')';
        return $this;
    }

    public function SUM(string $column): self {
        $this->sql .= ' SUM(' . $this->escapeIdentifier($column) . ')';
        return $this;
    }

    public function AVG(string $column): self {
        $this->sql .= ' AVG(' . $this->escapeIdentifier($column) . ')';
        return $this;
    }

    public function MIN(string $column): self {
        $this->sql .= ' MIN(' . $this->escapeIdentifier($column) . ')';
        return $this;
    }

    public function MAX(string $column): self {
        $this->sql .= ' MAX(' . $this->escapeIdentifier($column) . ')';
        return $this;
    }

    // Alias support
    public function AS(string $alias): self {
        $this->sql .= ' AS ' . $this->escapeIdentifier($alias);
        return $this;
    }

    // Get SQL and bindings
    public function go(): array {
        return ['sql' => $this->sql, 'bindings' => $this->bindings];
    }

    public function getSQL(): string {
        return $this->sql;
    }

    public function getBindings(): array {
        return $this->bindings;
    }

    public function reset(): self {
        $this->sql = '';
        $this->bindings = [];
        return $this;
    }

    // Execute the query (requires Database instance)
    public function execute(Database $db): mixed {
        $result = $db->query($this->sql, $this->bindings);
        $this->reset();
        return $result;
    }

    // Helper to escape table/column names
    private function escapeIdentifier(string $identifier): string {
        // Allow * for SELECT *
        if ($identifier === '*') {
            return $identifier;
        }
        // Remove backticks if present
        $identifier = trim($identifier, '`');
        // Replace . with backticks for table.column
        if (str_contains($identifier, '.')) {
            $parts = explode('.', $identifier);
            return '`' . implode('`.`', $parts) . '`';
        }
        return '`' . $identifier . '`';
    }
}
