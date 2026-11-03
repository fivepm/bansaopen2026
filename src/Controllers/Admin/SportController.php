<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\View;
use App\Models\Sports;

/** Halaman pusat per cabor + pilihan sistem turnamen (gugur / grup). */
final class SportController
{
    public function hub(string $sport): void
    {
        Sports::require($sport);
        View::render('admin/sport', [
            'title'  => sport_label($sport),
            'sport'  => $sport,
            'system' => Sports::system($sport),
        ], 'layouts/admin');
    }

    public function setSystem(string $sport): void
    {
        Sports::require($sport);
        $system = $_POST['system'] ?? '';
        if (!isset(Sports::SYSTEMS[$system])) {
            flash('error', 'Sistem turnamen tidak valid.');
            redirect("admin/$sport");
        }
        Sports::setSystem($sport, $system);
        flash('success', 'Sistem turnamen diubah ke: ' . Sports::SYSTEMS[$system]);
        redirect("admin/$sport");
    }
}

