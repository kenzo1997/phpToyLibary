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
  private $sql = "";

  public function SELECT($table, $values='*', $distinct=false) {
      if($values == '*' ) {
          $this->sql .= $distinct ? 'SELECT DISTINCT * ' : 'SELECT * ';
          $this->sql .= 'FROM ' . $this->sanitizeData($table);
          return $this;
      }

      $this->sql .= $distinct ? 'SELECT DISTINCT ' : 'SELECT ';

      foreach($values as $val) {
          $this->sql .= $this->sanitizeData($val) . ', ';
      }

      $this->sql = substr($this->sql, 0, -2);

      $this->sql .= ' FROM ' . $this->sanitizeData($table);
      return $this;
  }

  public function INSERT($table, $values) {
      $this->sql .= 'INSERT INTO ' . $table . '(';

      foreach ($values as $key => $value) {
        $this->sql .= $key . ", ";
      }

      $this->sql = substr($this->sql, 0, -2);
      $this->sql .= ") VALUES (";

      foreach($values as $key => $value) {
        $this->sql .= $value . ', ';
      }

      $this->sql = substr($this->sql, 0, -2);
      $this->sql .= ')';

      return $this;
  }

  public function UPDATE($table, $values) {
    $this->sql .= 'UPDATE ' . $this->sanitizeData($table) . ' SET ';

    foreach($values as $key => $value) {
        $this->sql .= $this->sanitizeData($key) . '=' . $this->sanitizeData($value) . ' ';
    }

    $this->sql = substr($this->sql, 0, -1);
    return $this;
  }

  public function DELETE($table) {
    $this->sql = "DELETE FROM " . $this->sanitizeData($table);
    return $this;
  }

  public function INNER_JOIN($table) {
      $this->sql .= " INNER JOIN " . $table;
      return $this;
  }

  public function LEFT_OUTER_JOIN($table) {
    $this->sql .= " LEFT OUTER JOIN " . $table;
    return $this;
  }

  public function RIGHT_OUTER_JOIN($table) {
    $this->sql .= " RIGHT OUTER JOIN " . $table;
    return $this;
  }

  public function FULL_OUTER_JOIN($table) {
    $this->sql .= " FULL OUTER JOIN " . $table;
    return $this;
  }

  public function USING($joinVar) {
    $this->sql .= " USING(" . $joinVar . ")";
    return $this;
  }

  public function ON($joinVar1, $joinVar2) {
    $this->sql .= " ON(" . $joinVar1 . "=" . $joinVar2 . ")";
    return $this;
  }

  public function go() {
    return $this->sql;
  }

  private function sanitizeData($data) {
    return $data;
  }
}

 ?>
