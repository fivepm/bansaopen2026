<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\Sports;

final class DashboardController
{
    public function index(): void
    {
        $sports = [];
        foreach (Sports::LIST as $slug => $s) {
            $one = fn(string $sql) => (int)Database::one($sql, [$slug])['c'];
            $sports[$slug] = $s + [
                'system'   => Sports::system($slug),
                'teams'    => $one('SELECT COUNT(*) c FROM teams WHERE sport_type = ?'),
                'matches'  => $one('SELECT COUNT(*) c FROM matches WHERE sport_type = ?'),
                'live'     => $one("SELECT COUNT(*) c FROM matches WHERE sport_type = ? AND status = 'live'"),
                'finished' => $one("SELECT COUNT(*) c FROM matches WHERE sport_type = ? AND status = 'finished'"),
            ];
        }
        View::render('admin/dashboard', ['title' => 'Dashboard', 'sports' => $sports], 'layouts/admin');
    }
}
