<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class CommitteeRole
{
    /**
     * Ambil semua daftar jabatan terurut.
     */
    public static function all(): array
    {
        return Database::all('SELECT * FROM committee_roles ORDER BY sort_order ASC, id ASC');
    }

    /**
     * Ambil role yang dikelompokkan berdasarkan divisi untuk form checkbox.
     * @return array<string, array<array{id: int, name: string, category: string, division: string, sort_order: int}>>
     */
    public static function grouped(): array
    {
        $all = self::all();
        $grouped = [];
        foreach ($all as $role) {
            $division = $role['division'] ?: 'Lainnya';
            $grouped[$division][] = $role;
        }
        return $grouped;
    }

    /**
     * Ambil daftar jabatan inti (Penanggung Jawab dan BPH) untuk opsi Radio Button.
     */
    public static function coreRoles(): array
    {
        return Database::all("SELECT * FROM committee_roles WHERE category = 'inti' ORDER BY sort_order ASC, id ASC");
    }

    /**
     * Ambil daftar jabatan operasional/divisi/cabor dikelompokkan berdasarkan divisi untuk opsi Checkbox.
     * @return array<string, array<array{id: int, name: string, category: string, division: string, sort_order: int}>>
     */
    public static function divisionGrouped(): array
    {
        $rows = Database::all("SELECT * FROM committee_roles WHERE category != 'inti' ORDER BY sort_order ASC, id ASC");
        $grouped = [];
        foreach ($rows as $role) {
            $division = $role['division'] ?: 'Lainnya';
            $grouped[$division][] = $role;
        }
        return $grouped;
    }

    /**
     * Cari role berdasarkan ID.
     */
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM committee_roles WHERE id = ?', [$id]);
    }
}

