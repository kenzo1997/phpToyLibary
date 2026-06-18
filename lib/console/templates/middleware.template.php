<?php

namespace {{namespace}};

use lib\http\Request;
use lib\http\Response;

/**
 * {{name}} Middleware
 *
 * @package {{namespace}}
 * @created {{CREATION_DATE}}
 */
class {{name}} {

    public function __construct() {
        // Inject services here if needed
    }

    public function run(Request $request, Response $response, array $allowedRoles=[]): void {
        // Middleware logic here
        // Example:
        // if (!$request->getParam('authenticated')) {
        //     $response->redirect('/');
        //     return;
        // }

        // Continue to next middleware/controller
    }
}
