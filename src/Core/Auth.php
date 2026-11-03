<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $admin = Database::one('SELECT * FROM admins WHERE username = ?', [$username]);
        if (!$admin || !password_verify($password, $admin['password'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'       => (int)$admin['id'],
            'username' => $admin['username'],
            'name'     => $admin['name'],
            'role'     => $admin['role'] ?? 'superadmin',
        ];
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin']);
    }

    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function role(): string
    {
        return (string)($_SESSION['admin']['role'] ?? 'operator');
    }

    public static function isSuperAdmin(): bool
    {
        return self::role() === 'superadmin';
    }

    public static function isOperator(): bool
    {
        return self::role() === 'operator';
    }

    public static function roleLabel(): string
    {
        return self::isSuperAdmin() ? 'Super Admin' : 'Operator Skor';
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
