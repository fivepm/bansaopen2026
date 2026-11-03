<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\Sports;

final class TeamController
{
    public function index(string $sport): void
    {
        Sports::require($sport);
        $categories = Sports::categoriesFor($sport);
        $catFilter = $_GET['cat'] ?? null;
        if ($catFilter && !isset($categories[$catFilter])) {
            $catFilter = null;
        }

        $sql = 'SELECT * FROM teams WHERE sport_type = ?';
        $params = [$sport];
        if ($catFilter) {
            $sql .= ' AND category = ?';
            $params[] = $catFilter;
        }
        $sql .= ' ORDER BY category, name';

        $teams = Database::all($sql, $params);
        View::render('admin/teams/index', [
            'title'      => 'Tim ' . sport_label($sport),
            'sport'      => $sport,
            'teams'      => $teams,
            'categories' => $categories,
            'catFilter'  => $catFilter,
        ], 'layouts/admin');
    }

    public function create(string $sport): void
    {
        Sports::require($sport);
        View::render('admin/teams/form', [
            'title'      => 'Tambah Tim ' . sport_label($sport),
            'sport'      => $sport,
            'team'       => null,
            'categories' => Sports::categoriesFor($sport),
        ], 'layouts/admin');
    }

    public function store(string $sport): void
    {
        Sports::require($sport);
        $d = $this->input("admin/$sport/teams/create", $sport);
        Database::query(
            'INSERT INTO teams (sport_type, category, name, members, institution, contact) VALUES (?,?,?,?,?,?)',
            [$sport, $d['category'], $d['name'], $d['members'], $d['institution'], $d['contact']]
        );
        flash('success', 'Tim berhasil ditambahkan.');
        redirect("admin/$sport/teams");
    }

    public function edit(string $sport, string $id): void
    {
        $team = $this->find($sport, (int)$id);
        View::render('admin/teams/form', [
            'title'      => 'Edit Tim',
            'sport'      => $sport,
            'team'       => $team,
            'categories' => Sports::categoriesFor($sport),
        ], 'layouts/admin');
    }

    public function update(string $sport, string $id): void
    {
        $team = $this->find($sport, (int)$id);
        $d = $this->input("admin/$sport/teams/{$team['id']}/edit", $sport);
        Database::query(
            'UPDATE teams SET category=?, name=?, members=?, institution=?, contact=? WHERE id=?',
            [$d['category'], $d['name'], $d['members'], $d['institution'], $d['contact'], $team['id']]
        );
        flash('success', 'Tim berhasil diperbarui.');
        redirect("admin/$sport/teams");
    }

    public function destroy(string $sport, string $id): void
    {
        $team = $this->find($sport, (int)$id);
        $groups = Database::all('SELECT group_id FROM group_standings WHERE team_id = ?', [$team['id']]);
        Database::query('DELETE FROM teams WHERE id = ?', [$team['id']]);
        foreach ($groups as $g) {
            \App\Models\Standing::recalculate((int)$g['group_id']);
        }
        flash('success', 'Tim dihapus.');
        redirect("admin/$sport/teams");
    }

    private function find(string $sport, int $id): array
    {
        Sports::require($sport);
        $team = Database::one('SELECT * FROM teams WHERE id = ? AND sport_type = ?', [$id, $sport]);
        if (!$team) {
            http_response_code(404);
            exit('Tim tidak ditemukan.');
        }
        return $team;
    }

    private function input(string $backTo, string $sport): array
    {
        $d = [];
        $validCats = Sports::categoriesFor($sport);
        $cat = $_POST['category'] ?? '';
        $d['category'] = isset($validCats[$cat]) ? $cat : Sports::defaultCategory($sport);

        foreach (['name', 'members', 'institution', 'contact'] as $k) {
            $d[$k] = trim($_POST[$k] ?? '');
            $d[$k] = $d[$k] === '' ? null : mb_substr($d[$k], 0, $k === 'members' ? 255 : 120);
        }
        if ($d['name'] === null) {
            $_SESSION['old'] = $_POST;
            flash('error', 'Nama tim wajib diisi.');
            redirect($backTo);
        }
        return $d;
    }
}
