<?php
declare(strict_types=0);

namespace app\controller;

use lib\controller\Controller;
use lib\http\Request;
use lib\http\Response;
use lib\router\Route;
use lib\db\SQLBuilder;

use app\services\UserService;

class HomeController extends Controller {
    private UserService $userService;
    private SQLBuilder $builder;

    public function __construct(UserService $userService, SQLBuilder $builder) {
        $this->userService = $userService;
        $this->builder = $builder;
    }
    
    #[Route(path: '/', methods: ['GET'], name: 'home')]
    public function getAll(Request $request, Response $response): void {
        //$this->logger->log("HomeController loaded");
        
        $projects = $this->userService->getProjects();
        $html = $this->render('home.index', ['name' => 'Kenzo', 'projects' => $projects]);
        $response->send($html);
    }

    #[Route(path: '/', methods: ['POST'], name: 'home')]
    public function postAll(Request $request, Response $response): void {
        $this->logger->log("HomeController loaded");
        $response->send("hello world");
    }


    #[Route(path: '/users', methods: ['GET'], name: 'users')]
    public function getUsers(Request $request, Response $response): void {
        $usersQuery = $this->builder->SELECT('users')->go();
        echo $usersQuery;
        $response->send("users called");
    }

    #[Route(path: '/users/{id}', methods: ['GET'], name: 'user.profile', requirements: ['id' => '\d+'])]
    public function getUser(Request $request, Response $response): void {
        echo "users id called: ";
        $id = (int) $request->getParam('id');
        $user = $this->userService->getUserById($id);
        $response->send(json_encode($user));
    }

    #[Route(path: '/page/{id?}', methods: ['GET'], name: 'page', requirements: ['id' => '\d+'], defaults: ['id' => 1 ])]
    public function getPage(Request $request, Response $response): void {
        $pNumber = (int) $request->getParam('id');
        echo "page id called: " . $pNumber;
    }

    #[Route(path: '/blog/{year?}/{month?}/{day?}', methods: ['GET'], name: 'blog.archive', defaults: ['year' => 2024, 'month' => 1, 'day' => 1])]
    public function getBlogPost(Request $request, Response $response) {
        $year = (int) $request->getParam('year');
        $month = (int) $request->getParam('month');
        $day = (int) $request->getParam('day');

        echo "blog post of: {$year} - {$month} - {$day}";
    }

    public function notFound(Request $request, Response $response): void {
        echo "not founnd";
        // http_response_code(404);
        // $response->send("404 Not Found - Custom Handler");
    }
}
?>
