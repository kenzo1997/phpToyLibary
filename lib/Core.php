<?php 
namespace lib;

use lib\router\Router;
use lib\http\Request;
use lib\http\Response;
use lib\container\Container;

class Core {
    private Router $router;
    private Request $request;
    private Response $response;
    private Container $container;

    public function __construct(Router $router, Container $container) {
        $this->router = $router;
        $this->container = $container;
        $this->request = new Request();
        $this->response = new Response();
    }
    
    public function start(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $route = $this->router->resolve($uri, $method);

        if (!$route) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        // Inject route params into the request
        foreach ($route['params'] as $key => $value) {
            $this->request->setParam($key, $value);
        }

        $controller = $this->container->get($route['controller']);

        foreach ($route['middlewares'] as $mwData) {
            $middleware = $this->container->get($mwData['class']);
            $allowedRoles = $mwData['allowedRoles'] ?? [];
            $middleware->run($this->request, $this->response, $allowedRoles);
        }

        $controller->{$route['method']}($this->request, $this->response);
    }
}
?>
