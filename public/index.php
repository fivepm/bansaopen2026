<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Router;

$router = new Router();
require dirname(__DIR__) . '/src/routes.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $e) {
    http_response_code(500);
    if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL)) {
        echo '<pre>' . htmlspecialchars((string)$e) . '</pre>';
    } else {
        echo 'Terjadi kesalahan pada server.';
    }
}

