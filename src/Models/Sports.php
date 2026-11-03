<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Daftar cabang olahraga & pengaturan sistem turnamen per cabor. */
final class Sports
{
    public const LIST = [
        'badminton' => ['label' => 'Badminton', 'emoji' => '🏸'],
        'pingpong'  => ['label' => 'Pingpong',  'emoji' => '🏓'],
        'futsal'    => ['label' => 'Futsal',    'emoji' => '⚽'],
    ];

    public const SYSTEMS = [
        'knockout' => 'Sistem Gugur (Bracket)',
        'group'    => 'Sistem Grup (Liga / Round Robin)',
    ];

    public const CATEGORIES = [
        'badminton' => [
            'ganda_putra' => 'Ganda Putra',
            'ganda_putri' => 'Ganda Putri',
        ],
        'futsal' => [
            'umum' => 'Umum',
        ],
        'pingpong' => [
            'umum' => 'Umum',
        ],
    ];

    public static function exists(string $sport): bool
    {
        return isset(self::LIST[$sport]);
    }

    /** Validasi slug cabor dari URL; 404 jika tidak dikenal. */
    public static function require(string $sport): string
    {
        if (!self::exists($sport)) {
            http_response_code(404);
            \App\Core\View::render('errors/404', ['title' => 'Tidak ditemukan']);
            exit;
        }
        return $sport;
    }

    public static function emoji(string $sport): string
    {
        return self::LIST[$sport]['emoji'] ?? '🏅';
    }

    public static function system(string $sport): string
    {
        $v = Setting::get("system_$sport", 'knockout');
        return isset(self::SYSTEMS[$v]) ? $v : 'knockout';
    }

    public static function setSystem(string $sport, string $system): void
    {
        if (isset(self::SYSTEMS[$system])) {
            Setting::set("system_$sport", $system);
        }
    }

    /** Daftar kategori yang berlaku untuk cabor tertentu. */
    public static function categoriesFor(string $sport): array
    {
        return self::CATEGORIES[$sport] ?? ['umum' => 'Umum'];
    }

    public static function defaultCategory(string $sport): string
    {
        $cats = self::categoriesFor($sport);
        return (string)array_key_first($cats);
    }

    /**
     * Cabor dianggap aktif/dimulai jika SUDAH memiliki data tim yang diinput DAN bagan/pertandingan yang diatur.
     * Jika salah satu belum ada, cabor berstatus "Belum Dimulai".
     */
    public static function isActive(string $sport): bool
    {
        try {
            $teamCount = (int)Database::one('SELECT COUNT(*) c FROM teams WHERE sport_type = ?', [$sport])['c'];
            $matchCount = (int)Database::one('SELECT COUNT(*) c FROM matches WHERE sport_type = ?', [$sport])['c'];
            return ($teamCount > 0 && $matchCount > 0);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Status spesifik turnamen cabor:
     * - not_started : Belum Dimulai (belum ada tim atau belum ada bracket)
     * - live        : Sedang Berlangsung (ada match berstatus live)
     * - finished    : Selesai (seluruh match sudah selesai)
     * - ready       : Siap Bertanding (tim dan jadwal match sudah siap)
     */
    public static function status(string $sport): string
    {
        try {
            $teamCount = (int)Database::one('SELECT COUNT(*) c FROM teams WHERE sport_type = ?', [$sport])['c'];
            $matchCount = (int)Database::one('SELECT COUNT(*) c FROM matches WHERE sport_type = ?', [$sport])['c'];
            if ($teamCount === 0 || $matchCount === 0) {
                return 'not_started';
            }
            $liveCount = (int)Database::one("SELECT COUNT(*) c FROM matches WHERE sport_type = ? AND status = 'live'", [$sport])['c'];
            if ($liveCount > 0) {
                return 'live';
            }
            $finishedCount = (int)Database::one("SELECT COUNT(*) c FROM matches WHERE sport_type = ? AND status = 'finished'", [$sport])['c'];
            if ($finishedCount > 0 && $finishedCount === $matchCount) {
                return 'finished';
            }
            return 'ready';
        } catch (\Throwable) {
            return 'not_started';
        }
    }
}
