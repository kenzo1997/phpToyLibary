<?php
declare(strict_types=0);

namespace app\controller;

use lib\controller\Controller;
use lib\http\Request;
use lib\http\Response;
use lib\router\Route;
use lib\http\SessionWrapper;
use lib\events\EventDispatcher;


#[Route(prefix: '/register')]
class RegisterController extends Controller {
    private SessionWrapper $session;
    private EventDispatcher $dispatcher;

    public function __construct(SessionWrapper $session, EventDispatcher $dispatcher) {
        $this->session = $session;
        $this->dispatcher = $dispatcher;
    }
    
    #[Route(path: '/', methods: ['GET'], name: 'home')]
    public function getAll(Request $request, Response $response): void {
        $html = $this->render('register.index');
        $response->send($html);
    }

    #[Route(path: '/', methods: ['POST'], name: 'home')]
    public function login(Request $request, Response $response): void {
        $username = $request->body["username"];       
        $password = $request->body["password"]; 

        $this->session->set($username . $password, "hello freak");

        // 🔥 Dispatch event using a string and payload array
        $this->dispatcher->dispatch('user.registered', [
            'username' => $username
        ]);

        $response->redirect('/login');    
    }
}
?>
