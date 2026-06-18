<?php
declare(strict_types=0);

namespace app\controller;

use lib\controller\Controller;
use lib\http\Request;
use lib\http\Response;
use lib\router\Route;
use lib\http\SessionWrapper;
use lib\events\EventDispatcher;
use lib\db\Database;

#[Route(prefix: '/register')]
class RegisterController extends Controller {
    private SessionWrapper $session;
    private EventDispatcher $dispatcher;
    private Database $db;

    public function __construct(SessionWrapper $session, EventDispatcher $dispatcher, Database $db) {
        $this->session = $session;
        $this->dispatcher = $dispatcher;
        $this->db = $db;
    }

    #[Route(path: '/', methods: ['GET'], name: 'home')]
    public function getAll(Request $request, Response $response): void {
        $html = $this->render('register.index');
        $response->send($html);
    }

    #[Route(path: '/', methods: ['POST'], name: 'home')]
    public function register(Request $request, Response $response): void {
        $username = trim($request->body["username"] ?? '');
        $email = trim($request->body["email"] ?? '');
        $password = $request->body["password"] ?? '';
        $confirmPassword = $request->body["confirm_password"] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $html = $this->render('register.index', ['error' => 'All fields are required']);
            $response->send($html);
            return;
        }

if ($password !== $confirmPassword) {
            $html = $this->render('register.index', ['error' => 'Passwords do not match']);
            $response->send($html);
            return;
        }

        // Check if user already exists
        $existingUser = $this->db->query("SELECT * FROM users WHERE name = ?", [$username]);
        if (!empty($existingUser)) {
            $html = $this->render('register.index', ['error' => 'Username already taken']);
            $response->send($html);
            return;
        }

        // Check if email already exists
        $existingEmail = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        if (!empty($existingEmail)) {
            $html = $this->render('register.index', ['error' => 'Email already registered']);
            $response->send($html);
            return;
        }

        // Hash password and insert user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->db->insert('users', [
            'name' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        // Dispatch event
        $this->dispatcher->dispatch('user.registered', [
            'username' => $username
        ]);

        $response->redirect('/login?registered=1');
    }
}
?>
