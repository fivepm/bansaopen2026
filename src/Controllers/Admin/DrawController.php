<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\Sports;

/**
 * Undian (Spin) tim untuk slot Babak Penyisihan (Round 1) — khusus Badminton & Pingpong.
 */
final class DrawController
{
    public const SPORTS = ['badminton', 'pingpong'];

    public static function supports(string $sport): bool
    {
        return in_array($sport, self::SPORTS, true) && Sports::system($sport) === 'knockout';
    }

    private function guard(string $sport): string
    {
        Sports::require($sport);
        if (!self::supports($sport)) {
            flash('error', 'Fitur undian hanya tersedia untuk Badminton dan Pingpong (sistem gugur).');
            redirect("admin/$sport");
        }
        $categories = Sports::categoriesFor($sport);
        $cat = $_GET['cat'] ?? $_POST['category'] ?? Sports::defaultCategory($sport);
        return isset($categories[$cat]) ? (string)$cat : (string)Sports::defaultCategory($sport);
    }

    /** Slot Round 1 (bukan final / juara 3) yang kedua timnya masih kosong. */
    private function emptySlots(string $sport, string $cat): array
    {
        return Database::all(
            "SELECT id, round, position, bracket_side FROM matches
             WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 1
               AND bracket_side IN ('left','right') AND team1_id IS NULL AND team2_id IS NULL
             ORDER BY position ASC",
            [$sport, $cat]
        );
    }

    /** Tim yang belum masuk ke pertandingan manapun (kategori ini). */
    private function availableTeams(string $sport, string $cat): array
    {
        return Database::all(
            "SELECT t.id, t.name, t.members FROM teams t
             WHERE t.sport_type = ? AND (t.category = ? OR t.category = 'umum')
               AND NOT EXISTS (
                 SELECT 1 FROM matches m
                 WHERE m.sport_type = t.sport_type AND (m.team1_id = t.id OR m.team2_id = t.id)
               )
             ORDER BY t.name ASC",
            [$sport, $cat]
        );
    }

    public function index(string $sport): void
    {
        $cat = $this->guard($sport);
        $categories = Sports::categoriesFor($sport);
        $totalRounds = \App\Models\MatchModel::knockoutRounds($sport, $cat);

        View::render('admin/matches/draw', [
            'title'      => 'Undian Tim ' . sport_label($sport),
            'sport'      => $sport,
            'categories' => $categories,
            'catFilter'  => $cat,
            'hasBracket' => $totalRounds > 0,
            'slots'      => $this->emptySlots($sport, $cat),
            'teams'      => $this->availableTeams($sport, $cat),
        ], 'layouts/admin');
    }

    /** AJAX: simpan hasil undian ke slot. POST team1_id, team2_id */
    public function assign(string $sport, string $id): void
    {
        $cat = $this->guard($sport);
        $t1 = (int)($_POST['team1_id'] ?? 0);
        $t2 = (int)($_POST['team2_id'] ?? 0);

        if ($t1 <= 0 || $t2 <= 0 || $t1 === $t2) {
            View::json(['ok' => false, 'error' => 'Dua tim berbeda diperlukan.'], 422);
        }

        $slot = Database::one(
            "SELECT id FROM matches WHERE id = ? AND sport_type = ? AND category = ? AND stage = 'knockout'
               AND round = 1 AND bracket_side IN ('left','right') AND team1_id IS NULL AND team2_id IS NULL",
            [(int)$id, $sport, $cat]
        );
        if (!$slot) {
            View::json(['ok' => false, 'error' => 'Slot tidak valid atau sudah terisi.'], 409);
        }

        $ok = array_map('intval', array_column($this->availableTeams($sport, $cat), 'id'));
        if (!in_array($t1, $ok, true) || !in_array($t2, $ok, true)) {
            View::json(['ok' => false, 'error' => 'Salah satu tim sudah terjadwal atau tidak valid.'], 409);
        }

        $stmt = Database::query(
            'UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ? AND team1_id IS NULL AND team2_id IS NULL',
            [$t1, $t2, (int)$id]
        );
        if ($stmt instanceof \PDOStatement && $stmt->rowCount() === 0) {
            View::json(['ok' => false, 'error' => 'Slot sudah terisi oleh proses lain.'], 409);
        }

        View::json(['ok' => true]);
    }
}

