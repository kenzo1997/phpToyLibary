<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \lib\router\{Router, RouterException};

final class RouterTest extends TestCase {
  public function testDefaultsRoute(): void {
    $router = new Router();
    $this->assertEquals(
      $router->get(),
      "IndexController"
    );
  }

  public function testAddDefaultsRoute(): void {
    $router = new Router();
    $router->default('home');
    $this->assertEquals(
      $router->get(),
      "HomeController"
    );
  }

  public function testAddRoute(): void {
    $router = new Router();
    $router->route('user');
    $this->assertEquals(
      $router->get('user'),
      "UserController"
    );
  }

  public function testAddRouteThrowsErrorIfNull(): void {
    $this->expectException(RouterException::class);
    $router = new Router();
    $router->route(null);
  }


  public function testAddRouteThrowsErrorIfEmpty(): void {
    $this->expectException(RouterException::class);
    $router = new Router();
    $router->route('');
  }

  public function testGetParams(): void {
    $router = new Router();
    $router->route('user/:id');
    $this->assertEquals(
      $router->getRouteParams()[0],
      ':id'
    );
  }
}
