<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Render view di src/views/{name}.php di dalam layout (public / admin). */
    public static function render(string $name, array $data = [], string $layout = 'layouts/public'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__) . '/views/' . $name . '.php';
        $content = ob_get_clean();
        require dirname(__DIR__) . '/views/' . $layout . '.php';
        unset($_SESSION['old']);
    }

    public static function partial(string $name, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__) . '/views/' . ltrim($name, '/') . '.php';
        return (string)ob_get_clean();
    }

    public static function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

