<?php
declare(strict_types=0);

namespace app\controller;

use lib\controller\Controller;
use lib\http\Request;
use lib\http\Response;
use lib\router\Route;
use lib\http\SessionWrapper;
use lib\db\Database;

#[Route(prefix: '/login')]
class LoginController extends Controller {
    private SessionWrapper $session;
    private Database $db;

    public function __construct(SessionWrapper $session, Database $db) {
        $this->session = $session;
        $this->db = $db;
    }

    #[Route(path: '/', methods: ['GET'], name: 'home')]
    public function getAll(Request $request, Response $response): void {
        $html = $this->render('login.index');
        $response->send($html);
    }

    #[Route(path: '/', methods: ['POST'], name: 'home')]
    public function login(Request $request, Response $response): void {
        $username = $request->body["username"] ?? '';
        $password = $request->body["password"] ?? '';

        // Find user by username
        $user = $this->db->query("SELECT * FROM users WHERE name = ?", [$username]);
        $user = $user[0] ?? null;

        if (!$user || $user['password'] !== $password) {
            $html = $this->render('login.index', ['error' => 'username or password incorrect']);
            $response->send($html);
            return;
}

        $this->session->set('user', $user['name']);
        $response->redirect('/api');

    }

    #[Route(path: '/logout', methods: ['POST'], name: 'home')]
    public function logout(Request $request, Response $response): void {
        $this->session->destroy();
        $response->redirect('/login');
    }

}
