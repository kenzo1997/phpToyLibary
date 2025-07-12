<?php
namespace {{namespace}};

use lib\controller\Controller;
use lib\http\Request;
use lib\http\Response;
use lib\router\Route;
use lib\router\Middelware;

/**
 * {{name}} Controller
 *
 * @package {{namespace}}
 * @created {{CREATION_DATE}}
 */
class {{name}}Controller extends Controller {

    public function __construct() {}

    #[Route(path: '/', methods: ['GET'], name: '{{name}}.index')]
    public function getAll(Request $request, Response $response): void {
        $response->send("This is the {{name}} controller.");
    }

    #[Route(path: '/', methods: ['POST'], name: '{{name}}.create')]
    public function post(Request $request, Response $response): void {
        $response->send("Handling POST in {{name}}");
    }

    #[Route(path: '/{id}', methods: ['GET'], name: '{{name}}.get', requirements: ['id' => '\d+'])]
    public function get(Request $request, Response $response): void {
        $id = $request->getParam('id');
        $response->send("Fetching item with ID: $id");
    }

    #[Route(path: '/{id}', methods: ['PUT'], name: '{{name}}.update')]
    public function update(Request $request, Response $response): void {
        $response->send("Updating item.");
    }

    #[Route(path: '/{id}', methods: ['DELETE'], name: '{{name}}.delete')]
    public function delete(Request $request, Response $response): void {
        $response->send("Deleting item.");
    }
}

