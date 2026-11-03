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

