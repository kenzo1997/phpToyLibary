<?php
declare(strict_types=1);

namespace lib\db;

use RuntimeException;

class SQLBuilder
{
    private array $parts = [
        'type'     => null,
        'distinct' => false,
        'select'   => [],
        'from'     => null,
        'joins'    => [],
        'where'    => [],
        'insert'   => [],
        'update'   => [],
        'orderBy' => [],
        'limit'   => null,
        'offset'  => null,
        'groupBy' => [],
        'having'  => [],
    ];

    private array $bindings = [];

    /* =========================
       SELECT
    ========================== */

    public function select(string $table, array|string $columns = ['*'], bool $distinct = false): self
    {
        $this->parts['type']     = 'select';
        $this->parts['from']     = $table;
        $this->parts['select']   = (array) $columns;
        $this->parts['distinct'] = $distinct;

        return $this;
    }

    /* =========================
       INSERT
    ========================== */

    public function insert(string $table, array $values): self
    {
        $this->parts['type'] = 'insert';
        $this->parts['from'] = $table;

        $columns      = [];
        $placeholders = [];

        foreach ($values as $column => $value) {
            $columns[] = $column;

            $placeholder = ':i_' . count($this->bindings);
            $placeholders[] = $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $this->parts['insert'] = [
            'columns'      => $columns,
            'placeholders' => $placeholders
        ];

        return $this;
    }

    /* =========================
       UPDATE
    ========================== */

    public function update(string $table, array $values): self
    {
        $this->parts['type'] = 'update';
        $this->parts['from'] = $table;

        foreach ($values as $column => $value) {
            $placeholder = ':u_' . count($this->bindings);
            $this->parts['update'][] = "$column = $placeholder";
            $this->bindings[$placeholder] = $value;
        }

        return $this;
    }

    /* =========================
       DELETE
    ========================== */

    public function delete(string $table): self
    {
        $this->parts['type'] = 'delete';
        $this->parts['from'] = $table;

        return $this;
    }

    /* =========================
       WHERE
    ========================== */
    public function where(string $column, string $operator, mixed $value): self
    {
        if ($value instanceof self) {
            $subSql = $this->compileSubquery($value);
            $this->parts['where'][] = "$column $operator $subSql";
            return $this;
        }
    
        $placeholder = ':w_' . count($this->bindings);
        $this->parts['where'][] = "$column $operator $placeholder";
        $this->bindings[$placeholder] = $value;
    
        return $this;
    }

    /* =========================
       JOINS
    ========================== */

    public function innerJoin(string $table): self
    {
        return $this->addJoin('INNER', $table);
    }

    public function leftOuterJoin(string $table): self
    {
        return $this->addJoin('LEFT OUTER', $table);
    }

    public function rightOuterJoin(string $table): self
    {
        return $this->addJoin('RIGHT OUTER', $table);
    }

    public function fullOuterJoin(string $table): self
    {
        return $this->addJoin('FULL OUTER', $table);
    }

    private function addJoin(string $type, string $table): self
    {
        $this->parts['joins'][] = [
            'type' => $type,
            'table' => $table,
            'condition' => null
        ];

        return $this;
    }

    public function on(string $left, string $right): self
    {
        $lastIndex = array_key_last($this->parts['joins']);

        if ($lastIndex === null) {
            throw new RuntimeException('ON called without JOIN.');
        }

        $this->parts['joins'][$lastIndex]['condition'] = "ON $left = $right";

        return $this;
    }

    public function using(string $column): self
    {
        $lastIndex = array_key_last($this->parts['joins']);

        if ($lastIndex === null) {
            throw new RuntimeException('USING called without JOIN.');
        }

        $this->parts['joins'][$lastIndex]['condition'] = "USING ($column)";

        return $this;
    }


    //---------------------------------------
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
    
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException('Order direction must be ASC or DESC.');
        }
    
        $this->parts['orderBy'][] = "$column $direction";
    
        return $this;
    }

    public function limit(int $limit): self
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('Limit must be positive.');
        }
    
        $this->parts['limit'] = $limit;
    
        return $this;
    }
    
    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('Offset must be positive.');
        }
    
        $this->parts['offset'] = $offset;
    
        return $this;
    }

    // ----------- GROUO BY
    public function groupBy(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->parts['groupBy'][] = $column;
        }
    
        return $this;
    }

    // ------------ HAVING
    public function having(string $column, string $operator, mixed $value): self
    {
        return $this->addHaving('AND', $column, $operator, $value);
    }
    
    public function orHaving(string $column, string $operator, mixed $value): self
    {
        return $this->addHaving('OR', $column, $operator, $value);
    }

    // ------------- EXISTS
    public function whereExists(self $sub): self
    {
        return $this->addExists('AND', $sub);
    }
    
    public function orWhereExists(self $sub): self
    {
        return $this->addExists('OR', $sub);
    }

    /* =========================
       COMPILATION
    ========================== */

    public function toSql(): string
    {
        return match ($this->parts['type']) {
            'select' => $this->compileSelect(),
            'insert' => $this->compileInsert(),
            'update' => $this->compileUpdate(),
            'delete' => $this->compileDelete(),
            default  => throw new RuntimeException('Invalid query type.')
        };
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    /* =========================
       PRIVATE COMPILERS
    ========================== */
    private function compileSelect(): string
    {
        $sql = 'SELECT ';
    
        if ($this->parts['distinct']) {
            $sql .= 'DISTINCT ';
        }
    
        $sql .= implode(', ', $this->parts['select']);
        $sql .= ' FROM ' . $this->parts['from'];
    
        foreach ($this->parts['joins'] as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']}";
            if ($join['condition']) {
                $sql .= " {$join['condition']}";
            }
        }
    
        if (!empty($this->parts['where'])) {
            $sql .= ' WHERE ' . implode(' AND ', $this->parts['where']);
        }
    
        // ORDER BY
        if (!empty($this->parts['orderBy'])) {
            $sql .= ' ORDER BY ' . implode(', ', $this->parts['orderBy']);
        }
    
        // LIMIT
        if ($this->parts['limit'] !== null) {
            $sql .= ' LIMIT ' . $this->parts['limit'];
        }
    
        // OFFSET
        if ($this->parts['offset'] !== null) {
            $sql .= ' OFFSET ' . $this->parts['offset'];
        }
    
        return $sql;
    }

    private function compileInsert(): string
    {
        $columns = implode(', ', $this->parts['insert']['columns']);
        $placeholders = implode(', ', $this->parts['insert']['placeholders']);

        return "INSERT INTO {$this->parts['from']} ($columns) VALUES ($placeholders)";
    }

    private function compileUpdate(): string
    {
        $sql = "UPDATE {$this->parts['from']} SET ";
        $sql .= implode(', ', $this->parts['update']);

        if (!empty($this->parts['where'])) {
            $sql .= ' WHERE ' . implode(' AND ', $this->parts['where']);
        }

        return $sql;
    }

    private function compileDelete(): string
    {
        $sql = "DELETE FROM {$this->parts['from']}";

        if (!empty($this->parts['where'])) {
            $sql .= ' WHERE ' . implode(' AND ', $this->parts['where']);
        }

        return $sql;
    }

    // ---------------------------------
    private function mergeBindings(SQLBuilder $sub): void
    {
        foreach ($sub->getBindings() as $key => $value) {
            $newKey = ':' . uniqid('sub_');
            $this->bindings[$newKey] = $value;
            $subSql = $sub->toSql();
            $subSql = str_replace($key, $newKey, $subSql);
        }
    }

    private function compileSubquery(SQLBuilder $sub): string
    {
        $sql = $sub->toSql();
    
        foreach ($sub->getBindings() as $placeholder => $value) {
            $newPlaceholder = ':s_' . count($this->bindings);
            $this->bindings[$newPlaceholder] = $value;
            $sql = str_replace($placeholder, $newPlaceholder, $sql);
        }
    
        return "($sql)";
    }

    // ---------------------------------
    public function whereInSub(string $column, SQLBuilder $sub): self
    {
        $subSql = $this->compileSubquery($sub);
        $this->parts['where'][] = "$column IN $subSql";
        return $this;
    }

    public function fromSub(SQLBuilder $sub, string $alias): self
    {
        $this->parts['type'] = 'select';
    
        $subSql = $this->compileSubquery($sub);
        $this->parts['from'] = "$subSql AS $alias";
    
        return $this;
    }

    // --------------------------------
    public function whereGroup(callable $callback): self
    {
        return $this->addWhereGroup('AND', $callback);
    }
    
    public function orWhereGroup(callable $callback): self
    {
        return $this->addWhereGroup('OR', $callback);
    }


    // ------------------------------------------
    private function addWhereGroup(string $boolean, callable $callback): self
    {
        $subBuilder = new self();
    
        $callback($subBuilder);
    
        if (empty($subBuilder->parts['where'])) {
            return $this;
        }
    
        // Merge bindings
        foreach ($subBuilder->getBindings() as $key => $value) {
            $newKey = ':w_' . count($this->bindings);
            $this->bindings[$newKey] = $value;
    
            foreach ($subBuilder->parts['where'] as &$where) {
                $where['condition'] = str_replace($key, $newKey, $where['condition']);
            }
        }
    
        // Compile grouped conditions
        $groupSql = '';
        foreach ($subBuilder->parts['where'] as $index => $where) {
            if ($index > 0) {
                $groupSql .= ' ' . $where['boolean'] . ' ';
            }
            $groupSql .= $where['condition'];
        }
    
        $this->parts['where'][] = [
            'boolean' => $boolean,
            'condition' => '(' . $groupSql . ')'
        ];
    
        return $this;
    }


    // ------------------------
    private function addHaving(string $boolean, string $column, string $operator, mixed $value): self
    {
        $placeholder = ':h_' . count($this->bindings);
    
        $this->bindings[$placeholder] = $value;
    
        $this->parts['having'][] = [
            'boolean' => $boolean,
            'condition' => "$column $operator $placeholder"
        ];
    
        return $this;
    }

    // ----------------------------
    private function addExists(string $boolean, self $sub): self
    {
        $subSql = $this->compileSubquery($sub);
    
        $this->parts['where'][] = [
            'boolean' => $boolean,
            'condition' => "EXISTS $subSql"
        ];
    
        return $this;
    }

    /* =========================
       RESET
    ========================== */

    public function reset(): self
    {
        $this->parts = [
            'type'     => null,
            'distinct' => false,
            'select'   => [],
            'from'     => null,
            'joins'    => [],
            'where'    => [],
            'insert'   => [],
            'update'   => [],
        ];

        $this->bindings = [];

        return $this;
    }
}
