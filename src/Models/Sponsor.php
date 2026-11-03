<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Sponsor
{
    public static function all(): array
    {
        return Database::all('SELECT * FROM sponsors ORDER BY created_at DESC, id DESC');
    }

    public static function active(): array
    {
        try {
            return Database::all('SELECT * FROM sponsors WHERE is_active = 1 ORDER BY id');
        } catch (\Throwable) {
            return []; // tabel belum dimigrasi
        }
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM sponsors WHERE id = ?', [$id]);
    }
}

