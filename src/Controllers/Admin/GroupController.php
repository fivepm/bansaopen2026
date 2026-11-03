<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\GroupModel;
use App\Models\MatchModel;
use App\Models\Sports;
use App\Models\Standing;

/** Manajemen grup (sistem liga / round robin) per cabor. */
final class GroupController
{
    public function index(string $sport): void
    {
        Sports::require($sport);
        View::render('admin/groups', [
            'title'       => 'Grup ' . sport_label($sport),
            'sport'       => $sport,
            'system'      => Sports::system($sport),
            'groups'      => GroupModel::forSport($sport),
            'teams'       => Database::all('SELECT id, name FROM teams WHERE sport_type = ? ORDER BY name', [$sport]),
            'assignments' => GroupModel::assignments($sport),
            'standings'   => Standing::forSport($sport),
            'hasFixtures' => (bool)MatchModel::groupMatches($sport),
        ], 'layouts/admin');
    }

    public function create(string $sport): void
    {
        Sports::require($sport);
        $n = (int)($_POST['count'] ?? 0);
        if ($n < 1 || $n > 16) {
            flash('error', 'Jumlah grup harus 1–16.');
            redirect("admin/$sport/groups");
        }
        GroupModel::createGroups($sport, $n);
        flash('success', "$n grup dibuat. Sekarang bagi tim ke dalam grup.");
        redirect("admin/$sport/groups");
    }

    public function assign(string $sport): void
    {
        Sports::require($sport);
        $assign = [];
        foreach ((array)($_POST['group'] ?? []) as $teamId => $groupId) {
            $assign[(int)$teamId] = (int)$groupId;
        }
        GroupModel::assign($sport, $assign);
        flash('success', 'Pembagian grup disimpan. Jadwal grup perlu di-generate ulang.');
        redirect("admin/$sport/groups");
    }

    public function auto(string $sport): void
    {
        Sports::require($sport);
        if (!GroupModel::forSport($sport)) {
            flash('error', 'Buat grup terlebih dahulu.');
            redirect("admin/$sport/groups");
        }
        GroupModel::autoAssign($sport);
        flash('success', 'Tim dibagi otomatis ke grup. Jadwal grup perlu di-generate ulang.');
        redirect("admin/$sport/groups");
    }

    /** Generate jadwal round-robin untuk semua grup cabor ini. */
    public function fixtures(string $sport): void
    {
        Sports::require($sport);
        $total = 0;
        foreach (GroupModel::forSport($sport) as $g) {
            $total += GroupModel::generateFixtures($sport, (int)$g['id']);
        }
        flash($total ? 'success' : 'error', $total ? "$total pertandingan grup dibuat." : 'Tidak ada pertandingan dibuat. Pastikan tiap grup berisi minimal 2 tim.');
        redirect("admin/$sport/groups");
    }

    public function reset(string $sport): void
    {
        Sports::require($sport);
        GroupModel::resetAll($sport);
        flash('success', 'Semua grup & pertandingan grup dihapus.');
        redirect("admin/$sport/groups");
    }
}

