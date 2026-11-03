<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\View;
use App\Models\Schedule;
use App\Models\Sports;

final class ScheduleController
{
    /**
     * Tampilkan formulir pengaturan Jadwal & Lokasi untuk semua cabor.
     */
    public function index(): void
    {
        $schedules = [];
        foreach (Sports::LIST as $slug => $s) {
            $schedules[$slug] = array_merge($s, Schedule::forSport($slug));
        }

        View::render('admin/schedules/index', [
            'title'     => 'Pengaturan Jadwal & Lokasi',
            'schedules' => $schedules,
        ], 'layouts/admin');
    }

    /**
     * Simpan pembaruan kategori, rentang tanggal, jam pertandingan, lokasi, dan catatan per cabor.
     */
    public function save(): void
    {
        $kategoriList = (array)($_POST['kategori'] ?? []);
        $datesList    = (array)($_POST['dates'] ?? []);
        $jamList      = (array)($_POST['jam'] ?? []);
        $venuesList   = (array)($_POST['venues'] ?? []);
        $notesList    = (array)($_POST['notes'] ?? []);

        foreach (Sports::LIST as $slug => $s) {
            $kategori = (string)($kategoriList[$slug] ?? '');
            $dates    = (string)($datesList[$slug] ?? '');
            $jam      = (string)($jamList[$slug] ?? '');
            $venue    = (string)($venuesList[$slug] ?? '');
            $notes    = (string)($notesList[$slug] ?? '');

            Schedule::save($slug, [
                'kategori'         => $kategori,
                'date_range'       => $dates,
                'jam_pertandingan' => $jam,
                'venue'            => $venue,
                'notes'            => $notes,
            ]);
        }

        flash('success', 'Pengaturan Jadwal, Kategori, & Lokasi cabang olahraga berhasil disimpan.');
        redirect('admin/schedules');
    }
}

