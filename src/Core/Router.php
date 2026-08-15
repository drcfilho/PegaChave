<?php
namespace App\Core;

class Router {
    private array $routes = [];
    private array $dependencies = [];

    public function setDependencies(array $deps) {
        $this->dependencies = $deps;
    }

    public function add($method, $path, $controller, $action) {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => "#^" . $path . "$#",
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function get($path, $controller, $action = 'index') {
        $this->add('GET', $path, $controller, $action);
    }

    public function post($path, $controller, $action = 'index') {
        $this->add('POST', $path, $controller, $action);
    }

    public function dispatch($requestUri, $requestMethod) {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if (!defined('BASE_URL')) {
            define('BASE_URL', $basePath);
        }
        
        $uri = parse_url($requestUri, PHP_URL_PATH);
        
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($requestMethod) && preg_match($route['path'], $uri, $matches)) {
                $controllerClass = "App\\Controllers\\" . $route['controller'];
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass($this->dependencies['pdo'] ?? null, $this->dependencies['config'] ?? []);
                    $action = $route['action'];
                    if (method_exists($controllerInstance, $action)) {
                        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        call_user_func_array([$controllerInstance, $action], $params);
                        return;
                    }
                }
            }
        }
        
        http_response_code(404);
        echo "404 - Página não encontrada. (URI: " . htmlspecialchars($uri) . ")";
    }
}
