<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler, array $options = []): void
    {
        $this->add('POST', $path, $handler, $options);
    }

    private function add(string $method, string $path, array $handler, array $options = []): void
    {
        $pattern = '#^' . preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', rtrim($path, '/') ?: '/') . '$#';
        $this->routes[] = ['method' => $method, 'pattern' => $pattern, 'handler' => $handler, 'options' => $options];
    }

    public function dispatch(string $method, string $path): void
    {
        $path = rtrim($path, '/') ?: '/';
        $allowed = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path, $m)) {
                continue;
            }
            $allowed = true;
            if ($route['method'] !== $method) {
                continue;
            }
            if ($method === 'POST' && ($route['options']['csrf'] ?? true)) {
                // PHP drops the whole form when it's over post_max_size, which would look like an expired session.
                if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > Upload::maxPostBytes()) {
                    flash('error', 'That was too much to upload at once. This server accepts up to ' . Upload::mb(Upload::maxPostBytes()) . ' per form, so use fewer or smaller photos.');
                    back();
                }
                Csrf::verify();
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->$action(...array_values($params));
            return;
        }
        if ($allowed) {
            http_response_code(405);
            exit('Method not allowed');
        }
        abort(404);
    }
}
