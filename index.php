<?php

// You MUST tell the container what LoggerInterface means
// $container->set(LoggerInterface::class, new FileLogger());

# ==================================================

declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use lib\Core;
use lib\container\Container;
use lib\router\Router;
use lib\http\SessionWrapper;
use lib\events\EventDispatcher;

use app\controller\HomeController;
use app\controller\LoginController;
use app\controller\RegisterController;
use app\middlewares\AuthMiddleware;


// Register routes
$router = new Router();

$router->registerController(LoginController::class);
$router->registerController(RegisterController::class);

$router->group(['prefix' => '/api',  'middlewares' => [['class' => AuthMiddleware::class]]], function(Router $r) {
   $r->registerController(HomeController::class);
});

// Setup container 
$container = new Container(); 
$container->set(SessionWrapper::class, new SessionWrapper());

$dispatcher = new EventDispatcher();

// Register a listener for "user.registered"
$dispatcher->listen('user.registered', function(array $data) {
    //echo "New user registered: " . $data['username'];
    error_log("New user registered: " . $data['username']);
});

$container->set(EventDispatcher::class, $dispatcher);

// Start the app
$core = new Core($router, $container);
$core->start();

//#[Middelware(middlewareClass: AuthMiddleware::class, allowedRoles: ['admin', 'worker'])]
?>
