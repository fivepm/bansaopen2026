<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router sederhana. Contoh:
 *   $router->get('/teams/{id}', [TeamController::class, 'show']);
 *   $router->post('/login', fn() => ...);
 *   $router->get('/admin', [DashboardController::class, 'index'], [AuthMiddleware::class]);
 */
final class Router
{
    /** @var array<string, array<int, array{regex:string, handler:mixed, middleware:array}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void
    {
        // "/teams/{id}" => "#^/teams/(?P<id>[^/]+)$#"
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[$method][] = compact('regex', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = base_path();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['regex'], $path, $m)) {
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $mw) {
                (new $mw())->handle();
            }
            if ($method === 'POST') {
                Csrf::verify();
            }

            $h = $route['handler'];
            if (is_array($h)) {
                $h = [new $h[0](), $h[1]];
            }
            $h(...$params);
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Halaman tidak ditemukan']);
    }
}

