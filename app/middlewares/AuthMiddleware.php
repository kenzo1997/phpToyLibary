<?php
namespace app\middlewares;

use lib\http\Request;
use lib\http\Response;

use lib\http\SessionWrapper;

class AuthMiddleware {
    private SessionWrapper $session;

    public function __construct(SessionWrapper $session) {
        $this->session = $session;
    }
    
    public function run(Request $request, Response $response, array $allowedRoles = []): void {
        if(!$this->session->get('user')) {
            $response->redirect('/login');
        } 
    }
}
?>
