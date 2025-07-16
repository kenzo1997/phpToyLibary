# 📦 PHP Toy Framework (WIP)

A lightweight, modular, and extendable custom PHP micro-framework built from scratch. Ideal for learning, experimentation, or powering small-to-medium projects.

---

## ✨ Features

- ✅ **Attribute-Based Routing**
  - Optional parameters, named routes, regex constraints
  - Grouping, prefixes, and middleware per route/class
- 🛡 **Middleware System**
  - Role-based access control
- 🔧 **Dependency Injection Container**
  - Auto-resolves constructor dependencies
- 🖼 **Blade-Like Templating Engine**
  - Supports `@if`, `@foreach`, `@include`, etc.
- 🧵 **Event Dispatcher**
  - Priority-based, one-time listeners, wildcard support
- 🛠 **Console Generator**
  - `make:model`, `make:controller`, `make:middleware`
- 🗃 **SQL Builder**
  - Fluent and dynamic query generation
- 💾 **Session, Cookie, and Config Wrappers**
- 🔌 **Modular Loader System**

---

## 🗂 Project Structure

```
lib/
├── Core.php                  # App entrypoint
├── container/               # DI container
├── controller/              # Base controller class
├── console/                 # CLI tooling & generators
├── db/                      # SQL builder
├── events/                  # Event dispatcher
├── extra/                   # Config, security, cache, modules
├── http/                    # Request, response, session, cookie
├── router/                  # Routing system with attributes
├── template/                # Blade-like template engine
```

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.1+
- Composer (optional)

### Installation

```bash
git clone https://github.com/your-username/php-toy-framework.git
cd php-toy-framework
```

Create a public entry file (e.g. `public/index.php`) and bootstrap the framework:

```php
use lib\Core;
use lib\router\Router;
use lib\container\Container;
use App\Controllers\PostController;

$router = new Router();
$router->registerController(PostController::class);

$container = new Container();
$core = new Core($router, $container);
$core->start();
```

---

## 📘 Example Controller

```php
use lib\router\Route;
use lib\router\Middelware;
use lib\http\Request;
use lib\http\Response;

#[Route(prefix: '/posts')]
class PostController {

    #[Route(path: '/', methods: ['GET'], name: 'posts.index')]
    public function index(Request $req, Response $res) {
        $res->send("List of posts");
    }

    #[Route(path: '/{id}', methods: ['GET'], name: 'posts.show')]
    public function show(Request $req, Response $res) {
        $res->send("Post ID: " . $req->getParam('id'));
    }
}
```

---

## ⚙ Middleware Example

```php
#[Middelware(middlewareClass: AuthMiddleware::class, allowedRoles: ['admin'])]
```

Middleware class must define:

```php
public function run(Request $request, Response $response, array $allowedRoles = []): void
```

---

## 🧩 Console Commands

```bash
php cli.php make:controller User
php cli.php make:model Task name:string dueDate:string
php cli.php make:middleware AuthMiddleware
```

---

## 🖼 Templating

Create `views/home.blade.php`:

```blade
<h1>Welcome, {{ $name }}</h1>

@if ($loggedIn)
  <p>You are logged in.</p>
@endif
```

Render in controller:

```php
$this->render('home', ['name' => 'Kenzo', 'loggedIn' => true]);
```

---

## 🗃 SQL Builder

```php
$sql = (new SQLBuilder())->SELECT('users')->go();
echo $sql; // SELECT * FROM users
```

---

## 🔐 Security & Sessions

```php
$security = new Security();
$clean = $security->sanitize($_POST['input']);

$token = $security->generateCsrfToken();
$isValid = $security->validateCsrfToken($_POST['csrf']);
```

---

## 🔥 Event Dispatcher

```php
$dispatcher->listen('user.registered', function ($event) {
    // handle user registered
});
```

---

## 🧪 Config System

```php
Config::load(__DIR__ . '/../config');
$value = config('app.debug', false);
```

---

## 🙌 Author

**Kenzo Coenaerts**  
Built for education, fun, and real-world exploration of framework design.

---

## 📄 License

MIT License — use this freely in personal or commercial projects.
