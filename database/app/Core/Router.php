<?php

class Router {
    protected $routes = [];

    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Dynamic base path detection
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); 
        $projectName = str_replace('\\', '/', dirname($scriptName)); 
        
        $path = str_replace('\\', '/', $path);
        
        // Remove either /thrift_pos/public or /thrift_pos from the start of the path
        if (strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        } elseif (strpos($path, $projectName) === 0) {
            $path = substr($path, strlen($projectName));
        }
        
        // Ensure path starts with / and is not empty
        if (empty($path) || $path === '') $path = '/';
        if ($path[0] !== '/') $path = '/' . $path;

        foreach ($this->routes as $route) {
            $pattern = "#^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $route['path']) . "$#";
            
            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                $handler = explode('@', $route['handler']);
                $controllerName = $handler[0];
                $action = $handler[1];

                require_once __DIR__ . "/../Controllers/$controllerName.php";
                $controller = new $controllerName();
                
                // Pass named parameters to action
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return call_user_func_array([$controller, $action], $params);
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
