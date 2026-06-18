<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \lib\controller\Request;

final class RequestTest extends TestCase {
  public function testSetParams(): void {
    $request = new Request();
    $request->setParams("id", 4);
    $this->assertEquals($request->getParam("id"), 4);
  }

  public function testSetMultipleParams(): void {
    $request = new Request();
    $request->setParams("id", 4);
    $request->setParams('name', 'bob');
    $this->assertEquals($request->getParams(), ['id' => 4, 'name' => 'bob']);
  }

  public function testSetParamsThrowsErrorNameEmpty(): void {
  $this->expectException(Exception::class);
  $request = new Request();
  $request->setParams("", 4);
}

  public function testSetParamsThrowsErrorNameNull(): void {
    $this->expectException(Exception::class);
    $request = new Request();
    $request->setParams(null, 4);
  }

  public function testGetParamThrowsErrorNameEmpty(): void {
    $this->expectException(Exception::class);
    $request = new Request();
    $request->setParams("id", 4);
    $request->getParam("");
  }

  public function testGetParamThrowsErrorNameNull(): void {
    $this->expectException(Exception::class);
    $request = new Request();
    $request->setParams("id", 4);
    $request->getParam(null);
  }

  public function test(): void {
    $this->expectException(Exception::class);
    $request = new Request();
    $request->setParams("id", 4);
    $request->getParam('q');
  }
}
?>
