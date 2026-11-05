<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Perhitungan & query klasemen grup. */
final class Standing
{
    public const POINTS_WIN = 3;
    public const POINTS_DRAW = 1;
    public const POINTS_LOSS = 0;

    /**
     * Hitung ulang statistik semua anggota grup dari pertandingan fase grup yang berstatus "finished",
     * lalu simpan ke tabel group_standings. Dipanggil setiap skor/status pertandingan grup berubah.
     */
    public static function recalculate(int $groupId): void
    {
        $stats = [];
        foreach (Database::all('SELECT team_id FROM group_standings WHERE group_id = ?', [$groupId]) as $r) {
            $stats[(int)$r['team_id']] = ['played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'gf' => 0, 'ga' => 0, 'points' => 0];
        }

        $matches = Database::all(
            "SELECT team1_id, team2_id, score1, score2 FROM matches
             WHERE group_id = ? AND status = 'finished' AND team1_id IS NOT NULL AND team2_id IS NOT NULL",
            [$groupId]
        );
        foreach ($matches as $m) {
            $a = (int)$m['team1_id'];
            $b = (int)$m['team2_id'];
            if (!isset($stats[$a], $stats[$b])) {
                continue;
            }
            $sa = (int)$m['score1'];
            $sb = (int)$m['score2'];

            foreach ([[$a, $sa, $sb], [$b, $sb, $sa]] as [$team, $for, $against]) {
                $s = &$stats[$team];
                $s['played']++;
                $s['gf'] += $for;
                $s['ga'] += $against;
                if ($for > $against) {
                    $s['won']++;
                    $s['points'] += self::POINTS_WIN;
                } elseif ($for === $against) {
                    $s['drawn']++;
                    $s['points'] += self::POINTS_DRAW;
                } else {
                    $s['lost']++;
                    $s['points'] += self::POINTS_LOSS;
                }
                unset($s);
            }
        }

        foreach ($stats as $teamId => $s) {
            Database::query(
                'UPDATE group_standings SET played=?, won=?, drawn=?, lost=?, goals_for=?, goals_against=?, points=? WHERE group_id=? AND team_id=?',
                [$s['played'], $s['won'], $s['drawn'], $s['lost'], $s['gf'], $s['ga'], $s['points'], $groupId, $teamId]
            );
        }

        // Otomatis sinkronisasi kelolosan playoff grup ke final & juara 3 jika cabor liga
        $g = Database::one('SELECT sport_type FROM `groups` WHERE id = ?', [$groupId]);
        if ($g && !empty($g['sport_type'])) {
            self::syncPlayoffs((string)$g['sport_type']);
        }
    }

    /**
     * Otomatisasi kelolosan fase grup ke babak final & perebutan juara 3 (Playoff).
     * Jika satu grup sudah menyelesaikan seluruh pertandingannya:
     * - Juara Grup langsung mengisi slot Grand Final (Juara 1 & 2)
     * - Runner-Up Grup langsung mengisi slot Perebutan Juara 3
     * Tidak perlu menunggu grup lain selesai.
     * Jika 1 grup: Peringkat 1 vs 2 -> Final, Peringkat 3 vs 4 -> Juara 3.
     */
    public static function syncPlayoffs(string $sport): void
    {
        // 1. Pastikan kedua slot pertandingan playoff (Final & Perebutan Juara 3) sudah ada
        $finalMatch = Database::one(
            "SELECT id, status, team1_id, team2_id FROM matches
             WHERE sport_type = ? AND stage = 'knockout' AND bracket_side = 'final' LIMIT 1",
            [$sport]
        );
        if (!$finalMatch) {
            Database::query(
                "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side)
                 VALUES (?, 'umum', 'knockout', 1, 1, 'final')",
                [$sport]
            );
            $finalMatch = Database::one(
                "SELECT id, status, team1_id, team2_id FROM matches
                 WHERE sport_type = ? AND stage = 'knockout' AND bracket_side = 'final' LIMIT 1",
                [$sport]
            );
        }

        $thirdMatch = Database::one(
            "SELECT id, status, team1_id, team2_id FROM matches
             WHERE sport_type = ? AND stage = 'knockout' AND bracket_side = 'third_place' LIMIT 1",
            [$sport]
        );
        if (!$thirdMatch) {
            Database::query(
                "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side)
                 VALUES (?, 'umum', 'knockout', 1, 2, 'third_place')",
                [$sport]
            );
            $thirdMatch = Database::one(
                "SELECT id, status, team1_id, team2_id FROM matches
                 WHERE sport_type = ? AND stage = 'knockout' AND bracket_side = 'third_place' LIMIT 1",
                [$sport]
            );
        }

        $groups = GroupModel::forSport($sport);
        if (empty($groups)) {
            return;
        }

        $champ1 = null;
        $champ2 = null;
        $runner1 = null;
        $runner2 = null;

        if (count($groups) >= 2) {
            // >= 2 Grup: Tentukan kelolosan masing-masing grup secara independen!
            // Grup 1 (Grup A)
            $statA = Database::one(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) AS finished
                 FROM matches WHERE group_id = ? AND stage = 'group'",
                [(int)$groups[0]['id']]
            );
            $isFinA = ((int)($statA['total'] ?? 0) > 0 && (int)($statA['total'] ?? 0) === (int)($statA['finished'] ?? 0));
            if ($isFinA) {
                $rowsA = self::forGroup((int)$groups[0]['id']);
                $champ1  = !empty($rowsA[0]['team_id']) ? (int)$rowsA[0]['team_id'] : null;
                $runner1 = !empty($rowsA[1]['team_id']) ? (int)$rowsA[1]['team_id'] : null;
            }

            // Grup 2 (Grup B)
            $statB = Database::one(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) AS finished
                 FROM matches WHERE group_id = ? AND stage = 'group'",
                [(int)$groups[1]['id']]
            );
            $isFinB = ((int)($statB['total'] ?? 0) > 0 && (int)($statB['total'] ?? 0) === (int)($statB['finished'] ?? 0));
            if ($isFinB) {
                $rowsB = self::forGroup((int)$groups[1]['id']);
                $champ2  = !empty($rowsB[0]['team_id']) ? (int)$rowsB[0]['team_id'] : null;
                $runner2 = !empty($rowsB[1]['team_id']) ? (int)$rowsB[1]['team_id'] : null;
            }
        } else {
            // 1 Grup: Peringkat 1 vs 2 (Final), Peringkat 3 vs 4 (Juara 3)
            $stat = Database::one(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) AS finished
                 FROM matches WHERE group_id = ? AND stage = 'group'",
                [(int)$groups[0]['id']]
            );
            $isFin = ((int)($stat['total'] ?? 0) > 0 && (int)($stat['total'] ?? 0) === (int)($stat['finished'] ?? 0));
            if ($isFin) {
                $rows = self::forGroup((int)$groups[0]['id']);
                $champ1  = !empty($rows[0]['team_id']) ? (int)$rows[0]['team_id'] : null;
                $champ2  = !empty($rows[1]['team_id']) ? (int)$rows[1]['team_id'] : null;
                $runner1 = !empty($rows[2]['team_id']) ? (int)$rows[2]['team_id'] : null;
                $runner2 = !empty($rows[3]['team_id']) ? (int)$rows[3]['team_id'] : null;
            }
        }

        // Update pertandingan Final (jika status masih upcoming)
        if ($finalMatch && $finalMatch['status'] === 'upcoming') {
            Database::query(
                "UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ?",
                [$champ1, $champ2, $finalMatch['id']]
            );
        }

        // Update pertandingan Perebutan Juara 3 (jika status masih upcoming)
        if ($thirdMatch && $thirdMatch['status'] === 'upcoming') {
            Database::query(
                "UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ?",
                [$runner1, $runner2, $thirdMatch['id']]
            );
        }
    }

    /** Ambil data 2 pertandingan playoff (Final & Perebutan Juara 3) untuk cabor grup */
    public static function getPlayoffMatches(string $sport): array
    {
        self::syncPlayoffs($sport);

        $final = Database::one(
            MatchModel::SELECT . " WHERE m.sport_type = ? AND m.stage = 'knockout' AND m.bracket_side = 'final' LIMIT 1",
            [$sport]
        );
        $third = Database::one(
            MatchModel::SELECT . " WHERE m.sport_type = ? AND m.stage = 'knockout' AND m.bracket_side = 'third_place' LIMIT 1",
            [$sport]
        );

        return [
            'final'      => $final,
            'thirdPlace' => $third,
        ];
    }

    /**
     * Klasemen satu grup, urut: poin tertinggi → selisih skor → skor dicetak → nama.
     */
    public static function forGroup(int $groupId): array
    {
        return Database::all(
            'SELECT s.*, t.name AS team_name, (s.goals_for - s.goals_against) AS goal_diff
             FROM group_standings s
             JOIN teams t ON t.id = s.team_id
             WHERE s.group_id = ?
             ORDER BY s.points DESC, goal_diff DESC, s.goals_for DESC, t.name ASC',
            [$groupId]
        );
    }

    /** Semua grup suatu cabor beserta klasemennya. @return array<int, array{group: array, rows: array}> */
    public static function forSport(string $sport): array
    {
        $out = [];
        foreach (GroupModel::forSport($sport) as $g) {
            $out[(int)$g['id']] = ['group' => $g, 'rows' => self::forGroup((int)$g['id'])];
        }
        return $out;
    }
}

