<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    private static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Database::all('SELECT setting_key, setting_value FROM settings') as $r) {
                self::$cache[$r['setting_key']] = $r['setting_value'];
            }
        }
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        Database::query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache = null;
    }

    /** URL logo utama atau null jika belum diunggah. */
    public static function logoUrl(): ?string
    {
        try {
            $p = self::get('site_logo');
        } catch (\Throwable) {
            return null; // tabel belum dimigrasi
        }
        return $p ? asset($p) : null;
    }

    /** Ambil data teks rentang tanggal dan nama lokasi/lapangan per cabor. */
    public static function getSchedule(string $sport): array
    {
        return Schedule::forSport($sport);
    }

    /** Simpan data teks rentang tanggal dan nama lokasi/lapangan per cabor. */
    public static function setSchedule(string $sport, string $dates, string $venue, string $notes = ''): void
    {
        self::set("schedule_dates_$sport", trim($dates));
        self::set("schedule_venue_$sport", trim($venue));
        self::set("schedule_notes_$sport", trim($notes));
    }
}

