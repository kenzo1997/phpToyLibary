<?php
namespace lib\router;

use lib\http\Request;
use lib\http\Response;

interface MiddlewareInterface {
    public function handle(Request $request, Response $response, callable $next): void;
}
?>
