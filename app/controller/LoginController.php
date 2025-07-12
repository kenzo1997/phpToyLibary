<?php
declare(strict_types=0);

namespace app\controller;

use lib\controller\Controller;
use lib\http\Request;
use lib\http\Response;
use lib\router\Route;
use lib\http\SessionWrapper;

#[Route(prefix: '/login')]
class LoginController extends Controller {
    private SessionWrapper $session;

    public function __construct(SessionWrapper $session) {
        $this->session = $session;
    }
    
    #[Route(path: '/', methods: ['GET'], name: 'home')]
    public function getAll(Request $request, Response $response): void {
        $html = $this->render('login.index');
        $response->send($html);
    }

    #[Route(path: '/', methods: ['POST'], name: 'home')]
    public function login(Request $request, Response $response): void {
        $usernameTest = "mark";
        $passwordTest = "test";

        $username = $request->body["username"];       
        $password = $request->body["password"];

        if($username != $usernameTest || $password != $passwordTest) {
            $html = $this->render('login.index', ['error' => 'username or password incorrect']);
            $response->send($html);
            return;
        } 

        $this->session->set('user', $username);
        $response->redirect('/api');    
        
    }

    #[Route(path: '/logout', methods: ['POST'], name: 'home')]
    public function logout(Request $request, Response $response): void {
        $this->session->destroy();
        $response->redirect('/login');
    }

}
?>
