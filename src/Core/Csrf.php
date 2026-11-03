<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_token'] ??= bin2hex(random_bytes(32));
    }

    public static function verify(): void
    {
        $sent = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['_token'] ?? '', (string)$sent)) {
            http_response_code(419);
            exit('Token CSRF tidak valid. Muat ulang halaman dan coba lagi.');
        }
    }
}

