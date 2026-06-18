<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use \lib\db\SQLBuilder;

final class SQLBuilderTest extends TestCase {
    public function testSelectAllFromTable(): void {
      $sql = new SQLBuilder();
      $this->assertEquals(
        $sql->SELECT('users')->go(),
        "SELECT * FROM users"
      );
    }

    public function testSelectAllDistinctFromTable(): void {
      $sql = new SQLBuilder();
      $this->assertEquals(
        $sql->SELECT('users', '*', true)->go(),
        "SELECT DISTINCT * FROM users"
      );
    }

    public function testSelectColumsFromTable(): void {
      $sql = new SQLBuilder();
      $this->assertEquals(
        $sql->SELECT('users', ['name', 'age', 'weight', 'height'])->go(),
        "SELECT name, age, weight, height FROM users"
      );
    }

    //---------
    public function testInsertStatement(): void {
      $sql = new SQLBuilder();

      $this->assertEquals(
        $sql->INSERT('users', [
          'name' => 'bob',
          'age' => 12,
          'weight' => 250,
          'height' => 122
        ])->go(),
        "INSERT INTO users(name, age, weight, height) VALUES (bob, 12, 250, 122)"
      );
    }

    public function testUpdateStatement(): void {
      $sql = new SQLBuilder();

      $this->assertEquals(
        $sql->UPDATE('users', ['name' => 'rob'])->go(),
        "UPDATE users SET name=rob"
      );
    }

    public function testDeleteStatement(): void {
      $sql = new SQLBuilder();

      $this->assertEquals(
          $sql->DELETE('users')->go(),
          "DELETE FROM users"
      );
    }

    public function testInnerJoinStatement(): void {
      $sql = new SQLBuilder();
      $res = $sql->SELECT('users')
                 ->INNER_JOIN('clubs')
                 ->USING('name')
                 ->go();

      $this->assertEquals($res, "SELECT * FROM users INNER JOIN clubs USING(name)");
    }

    public function testLeftOuterJoinStatemenet(): void {
      $sql = new SQLBuilder();
      $res = $sql->SELECT('users')
                 ->LEFT_OUTER_JOIN('clubs')
                 ->USING('name')
                 ->go();

      $this->assertEquals($res, "SELECT * FROM users LEFT OUTER JOIN clubs USING(name)");
    }

    public function testRightOuterJoinStatemenet(): void {
      $sql = new SQLBuilder();
      $res = $sql->SELECT('users')
                 ->RIGHT_OUTER_JOIN('clubs')
                 ->USING('name')
                 ->go();

      $this->assertEquals($res, "SELECT * FROM users RIGHT OUTER JOIN clubs USING(name)");
    }

    public function functiontestFullOuterjoinStatemenet(): void {
      $sql = new SQLBuilder();
      $res = $sql->SELECT('users')
                 ->FUlL_OUTER_JOIN('clubs')
                 ->ON('name')
                 ->go();

      $this->assertEquals($res, "SELECT * FROM users RIGHT OUTER JOIN clubs ON(name = sname)");
    }
}
?>
