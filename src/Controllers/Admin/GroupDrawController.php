<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\GroupModel;
use App\Models\Sports;
use App\Models\Standing;

/**
 * Undian Tim ke dalam Grup (Futsal / Sistem Liga).
 */
final class GroupDrawController
{
    public static function supports(string $sport): bool
    {
        return Sports::system($sport) === 'group';
    }

    private function guard(string $sport): void
    {
        Sports::require($sport);
        if (!self::supports($sport)) {
            flash('error', 'Fitur undian grup hanya untuk cabang olahraga sistem grup (futsal/liga).');
            redirect("admin/$sport");
        }
    }

    public function index(string $sport): void
    {
        $this->guard($sport);

        $groups = GroupModel::forSport($sport);
        $unassignedTeams = Database::all(
            "SELECT t.id, t.name, t.members, t.institution
             FROM teams t
             WHERE t.sport_type = ?
               AND NOT EXISTS (
                 SELECT 1 FROM group_standings gs WHERE gs.team_id = t.id
               )
             ORDER BY t.name ASC",
            [$sport]
        );

        $standings = Standing::forSport($sport);

        View::render('admin/groups/draw', [
            'title'           => 'Undian Grup ' . sport_label($sport),
            'sport'           => $sport,
            'groups'          => $groups,
            'unassignedTeams' => $unassignedTeams,
            'standings'       => $standings,
        ], 'layouts/admin');
    }

    public function assign(string $sport): void
    {
        $this->guard($sport);

        $teamId = (int)($_POST['team_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);

        if ($teamId <= 0 || $groupId <= 0) {
            View::json(['ok' => false, 'error' => 'Tim dan target grup harus dipilih.'], 422);
        }

        // Validasi grup
        $group = GroupModel::find($groupId, $sport);
        if (!$group) {
            View::json(['ok' => false, 'error' => 'Grup tidak valid.'], 422);
        }

        // Validasi tim
        $team = Database::one(
            "SELECT t.id, t.name, t.members, t.institution
             FROM teams t
             WHERE t.id = ? AND t.sport_type = ?",
            [$teamId, $sport]
        );
        if (!$team) {
            View::json(['ok' => false, 'error' => 'Tim tidak ditemukan.'], 404);
        }

        // Cek apakah tim sudah punya grup
        $existing = Database::one(
            "SELECT group_id FROM group_standings WHERE team_id = ?",
            [$teamId]
        );
        if ($existing) {
            View::json(['ok' => false, 'error' => 'Tim ini sudah dimasukkan ke dalam grup.'], 409);
        }

        Database::query(
            "INSERT INTO group_standings (group_id, team_id, played, won, drawn, lost, goals_for, goals_against, points)
             VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0)",
            [$groupId, $teamId]
        );

        // Jika ada jadwal grup lama, hapus agar konsisten
        Database::query("DELETE FROM matches WHERE group_id = ? AND stage = 'group'", [$groupId]);

        View::json([
            'ok'         => true,
            'team_id'    => $teamId,
            'team_name'  => $team['name'],
            'group_id'   => $groupId,
            'group_name' => $group['name'],
        ]);
    }
}

