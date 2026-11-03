<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Schedule
{
    /**
     * Ambil data jadwal, waktu, kategori, dan lokasi untuk suatu cabang olahraga.
     * @return array{kategori: string, date_range: string, jam_pertandingan: string, venue: string, notes: string}
     */
    public static function forSport(string $sport): array
    {
        try {
            $row = Database::one('SELECT * FROM schedules WHERE sport_type = ?', [$sport]);
            if ($row) {
                $dates = (string)($row['date_range'] ?? '');
                return [
                    'kategori'         => (string)($row['kategori'] ?? ''),
                    'date_range'       => $dates,
                    'dates'            => $dates,
                    'jam_pertandingan' => (string)($row['jam_pertandingan'] ?? ''),
                    'venue'            => (string)($row['venue'] ?? ''),
                    'notes'            => (string)($row['notes'] ?? ''),
                ];
            }
        } catch (\Throwable) {
            // Fallback jika tabel bermasalah
        }

        // Fallback kompatibilitas ganda dari settings
        $dates = Setting::get("schedule_dates_$sport", '') ?? '';
        return [
            'kategori'         => Setting::get("schedule_kategori_$sport", '') ?? '',
            'date_range'       => $dates,
            'dates'            => $dates,
            'jam_pertandingan' => Setting::get("schedule_time_$sport", '') ?? '',
            'venue'            => Setting::get("schedule_venue_$sport", '') ?? '',
            'notes'            => Setting::get("schedule_notes_$sport", '') ?? '',
        ];
    }

    /**
     * Simpan atau update data jadwal dan lokasi berdasarkan sport_type cabor.
     */
    public static function save(string $sport, array $data): void
    {
        $kategori = trim((string)($data['kategori'] ?? ''));
        $dateRange = trim((string)($data['date_range'] ?? ''));
        $jam = trim((string)($data['jam_pertandingan'] ?? ''));
        $venue = trim((string)($data['venue'] ?? ''));
        $notes = trim((string)($data['notes'] ?? ''));

        try {
            Database::query(
                'INSERT INTO schedules (sport_type, kategori, date_range, jam_pertandingan, venue, notes)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    kategori = VALUES(kategori),
                    date_range = VALUES(date_range),
                    jam_pertandingan = VALUES(jam_pertandingan),
                    venue = VALUES(venue),
                    notes = VALUES(notes),
                    updated_at = CURRENT_TIMESTAMP',
                [$sport, $kategori, $dateRange, $jam, $venue, $notes]
            );
        } catch (\Throwable) {
            // Abaikan kegagalan query
        }

        // Simpan salinan ke tabel settings untuk kompatibilitas mundur
        Setting::set("schedule_kategori_$sport", $kategori);
        Setting::set("schedule_dates_$sport", $dateRange);
        Setting::set("schedule_time_$sport", $jam);
        Setting::set("schedule_venue_$sport", $venue);
        Setting::set("schedule_notes_$sport", $notes);
    }
}
