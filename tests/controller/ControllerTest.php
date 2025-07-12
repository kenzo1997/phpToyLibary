<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \lib\controller\Controller;


final class ControllerTest extends TestCase {
  public function testSetMiddelware(): void {
    $stub = $this->getMockForAbstractClass( Controller::class );
    $stub->setMiddelware('Logger');
    $this->assertTrue(true);
  }
}

?>
