<?php
namespace lib\router;

use lib\router\Route;
use lib\router\Middelware;
use ReflectionClass;
use ReflectionMethod;

class Router {
    private array $routes = [];
    private array $namedRoutes = [];
    private ?array $fallbackRoute = null;
    private array $groupStack = [];

    public function registerController(string $controllerClass): void {
        $reflection = new ReflectionClass($controllerClass);

        // Collect class-level middleware
        $controllerMiddleware = [];
        foreach ($reflection->getAttributes(Middelware::class) as $middlewareAttr) {
            $controllerMiddleware[] = $middlewareAttr->newInstance()->middlewareClass;
        }
        
        // Extract class-level prefix (if any)
        $prefix = '';
        
        $groupMiddleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middlewares'])) {
                $groupMiddleware = array_merge($groupMiddleware, $group['middlewares']);
            }
        }

        foreach ($reflection->getAttributes(Route::class) as $classRouteAttr) {
            $classRoute = $classRouteAttr->newInstance();
            if ($classRoute->prefix !== null) {
                $prefix = rtrim($classRoute->prefix, '/');
            }
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Check each method for Route attributes
            foreach ($method->getAttributes(Route::class) as $routeAttr) {
                $route = $routeAttr->newInstance();

                // ✅ Merge group-level and method-level values

                // Collect method-level middleware (optional feature)
                $methodMiddleware = [];
                foreach ($method->getAttributes(Middelware::class) as $middlewareAttr) {
                    //$methodMiddleware[] = $middlewareAttr->newInstance()->middlewareClass;

                    // ====
                    $middlewareInstance = $middlewareAttr->newInstance();
                    $methodMiddleware[] = [
                        'class' => $middlewareInstance->middlewareClass,
                        'allowedRoles' => $middlewareInstance->allowedRoles,
                    ];

                }

                // $middlewares = [...$controllerMiddleware, ...$methodMiddleware];
                $middlewares = [...$groupMiddleware, ...$controllerMiddleware, ...$methodMiddleware];


                foreach ($route->methods as $httpMethod) {
                    // Concatenate class-level prefix with the method-level path
                    $fullPath = rtrim($prefix . '/' . ltrim($route->path, '/'), '/') ?: '/';
                    
                    // Compile the path with any route requirements
                    $compiledPath = $this->compilePath($fullPath, $route->requirements);

                    $routeData = [
                        'controller' => $controllerClass,
                        'method' => $method->getName(),
                        'middlewares' => $middlewares,
                        'path' => $route->path,
                        'compiled' => $compiledPath,
                        'params' => $this->extractParamNames($route->path),
                        'name' => $route->name,
                        'domain' => $route->domain,
                        'defaults' => $route->defaults
                    ];
                    
                    $this->routes[$httpMethod][] = $routeData;
                    
                    if ($route->name) {
                        $this->namedRoutes[$route->name] = $routeData;
                    }
                }
            }
        }
    }

    public function resolve(string $path, string $method): ?array {
        $routes = $this->routes[$method] ?? [];

        //var_dump($routes);

        foreach ($routes as $route) {
            if (preg_match($route['compiled'], $path, $matches)) {
                $params = [];
                foreach ($route['params'] as $name) {
                    if (isset($matches[$name])) {
                        $params[$name] = $matches[$name];
                    } elseif (isset($route['defaults'][$name])) {
                        $params[$name] = $route['defaults'][$name];
                    } else {
                        $params[$name] = null;
                    }
                    
                    // $params[$name] = $matches[$name] ?? null;
                }

                return [
                    'controller' => $route['controller'],
                    'method' => $route['method'],
                    'middlewares' => $route['middlewares'],
                    'params' => $params,
                ];
            }
        }

        return $this->fallbackRoute;
    }

    public function fallback(string $controllerClass, string $methodName): void {
        $this->fallbackRoute = [
            'controller' => $controllerClass,
            'method' => $methodName,
            'middlewares' => [],
            'params' => [],
        ];
    }

    public function url(string $name, array $params = []): ?string {
        if (!isset($this->namedRoutes[$name])) return null;

        $path = $this->namedRoutes[$name]['path'];
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }

        return $path;
    }
   
    private function compilePath(string $path, array $requirements): string {
        $segments = explode('/', trim($path, '/'));
        $regex = '';
        $optionalSegmentDepth = 0;

        foreach ($segments as $segment) {
            if (preg_match('/^\{(\w+)\??\}$/', $segment, $matches)) {
                $param = $matches[1];
                $isOptional = str_ends_with($segment, '?}');
                $constraint = $requirements[$param] ?? '[^/]+';

                if ($isOptional) {
                    $regex .= '(?:/(?P<' . $param . '>' . $constraint . '))?';
                    $optionalSegmentDepth++;
                } else {
                    $regex .= '/(?P<' . $param . '>' . $constraint . ')';
                }
            } else {
                $regex .= '/' . preg_quote($segment, '#');
            }
        }

        return '#^' . $regex . '/?$#';
    }



    
    private function extractParamNames(string $path): array {
        preg_match_all('/\{(\w+)\??\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    public function getRoutes(): array {
        return $this->routes;
    }

    # ============== GROUP ==========================
    public function group(array $attributes, callable $callback): void {
        $this->groupStack[] = $attributes;

        // Run the callback with current router instance
        $callback($this);

        // Remove the current group after execution
        array_pop($this->groupStack);
    }
}
?>
