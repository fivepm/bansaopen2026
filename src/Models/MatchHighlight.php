<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MatchHighlight
{
    public const EVENTS = [
        'goal'               => ['label' => 'Gol',                 'emoji' => '⚽',  'badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40'],
        'yellow_card'        => ['label' => 'Kartu Kuning',        'emoji' => '🟨',  'badge' => 'bg-amber-500/20 text-amber-300 border-amber-500/40'],
        'double_yellow_card' => ['label' => 'Kartu Kuning Kedua',  'emoji' => '🟨🟥', 'badge' => 'bg-orange-500/20 text-orange-300 border-orange-500/40'],
        'red_card'           => ['label' => 'Kartu Merah',         'emoji' => '🟥',  'badge' => 'bg-rose-500/20 text-rose-400 border-rose-500/40'],
        'substitution'       => ['label' => 'Pertukaran Pemain',   'emoji' => '🔄',  'badge' => 'bg-sky-500/20 text-sky-400 border-sky-500/40'],
        'half_time'          => ['label' => 'Half Time',           'emoji' => '⏸️',  'badge' => 'bg-amber-500/20 text-amber-300 border-amber-500/50 font-bold'],
        'full_time'          => ['label' => 'Full Time',           'emoji' => '🏁',  'badge' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50 font-bold'],
    ];

    /** Ambil semua highlight untuk 1 pertandingan tertentu. */
    public static function forMatch(int $matchId): array
    {
        return Database::all(
            "SELECT h.*, t.name AS team_name
             FROM match_highlights h
             LEFT JOIN teams t ON t.id = h.team_id
             WHERE h.match_id = ?
             ORDER BY h.id ASC",
            [$matchId]
        );
    }

    /** Ambil highlights untuk kumpulan ID match sekaligus, diindeks per match_id. */
    public static function forMatches(array $matchIds): array
    {
        if (empty($matchIds)) {
            return [];
        }
        $in = implode(',', array_fill(0, count($matchIds), '?'));
        $rows = Database::all(
            "SELECT h.*, t.name AS team_name
             FROM match_highlights h
             LEFT JOIN teams t ON t.id = h.team_id
             WHERE h.match_id IN ($in)
             ORDER BY h.id ASC",
            array_values($matchIds)
        );

        $grouped = [];
        foreach ($rows as $r) {
            $mid = (int)$r['match_id'];
            $grouped[$mid][] = $r;
        }
        return $grouped;
    }

    /** Format tampilan menit dengan memperhitungkan waktu tambahan (injury time, misal 5+1' atau 10+2'). */
    public static function formatMinute(string|int $minute, int $halfDuration = 5, int $period = 1): string
    {
        $mStr = trim((string)$minute);
        if (str_contains($mStr, '+') || $mStr === 'HT' || $mStr === 'FT') {
            return $mStr;
        }
        $val = (int)$mStr;
        if ($val <= 0) {
            return '1';
        }
        if ($period === 1) {
            if ($val > $halfDuration) {
                return $halfDuration . '+' . ($val - $halfDuration);
            }
            return (string)$val;
        }
        $limit = $halfDuration * 2;
        if ($val > $limit) {
            return $limit . '+' . ($val - $limit);
        }
        return (string)$val;
    }

    /** Tambah highlight baru. */
    public static function add(
        int $matchId,
        ?int $teamId,
        string $playerName,
        string $eventType,
        string|int $minute,
        ?string $playerIn = null,
        ?string $playerOut = null,
        int $period = 1
    ): int {
        if (!isset(self::EVENTS[$eventType])) {
            $eventType = 'goal';
        }
        $minuteStr = trim((string)$minute);
        if ($minuteStr === '' || $minuteStr === '0') {
            $minuteStr = '1';
        }
        $period = in_array($period, [1, 2], true) ? $period : 1;
        $playerIn = ($playerIn !== null && trim($playerIn) !== '') ? trim($playerIn) : null;
        $playerOut = ($playerOut !== null && trim($playerOut) !== '') ? trim($playerOut) : null;

        if ($eventType === 'substitution') {
            if ($playerIn && $playerOut) {
                $playerName = "Keluar: {$playerOut} ➔ Masuk: {$playerIn}";
            } elseif ($playerIn) {
                $playerName = "Masuk: {$playerIn}";
            } elseif ($playerOut) {
                $playerName = "Keluar: {$playerOut}";
            }
        } elseif ($eventType === 'half_time') {
            $playerName = 'Half Time (Turun Minum)';
            $teamId = null;
        } elseif ($eventType === 'full_time') {
            $playerName = 'Full Time (Pertandingan Berakhir)';
            $teamId = null;
        }

        $playerName = trim($playerName);
        if ($playerName === '') {
            $playerName = ($eventType === 'substitution') ? 'Pergantian Pemain' : 'Pemain';
        }

        Database::query(
            "INSERT INTO match_highlights (match_id, team_id, player_name, player_in, player_out, event_type, minute, period)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$matchId, $teamId, $playerName, $playerIn, $playerOut, $eventType, $minuteStr, $period]
        );

        return (int)Database::connection()->lastInsertId();
    }

    /** Hapus highlight berdasarkan ID. */
    public static function delete(int $id): bool
    {
        $stmt = Database::query("DELETE FROM match_highlights WHERE id = ?", [$id]);
        return ($stmt instanceof \PDOStatement && $stmt->rowCount() > 0);
    }

    /**
     * Hitung ulang skor Futsal berdasarkan catatan highlight GOL di database.
     * Mengupdate kolom set1_a, set1_b, set2_a, set2_b, score1, score2 pada tabel matches
     * serta memperbarui klasemen jika tahap grup.
     */
    public static function recalculateFutsalScore(int $matchId): array
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [$matchId]);
        if (!$m) {
            return [
                'score1' => 0,
                'score2' => 0,
                'set1_a' => 0,
                'set1_b' => 0,
                'set2_a' => 0,
                'set2_b' => 0,
            ];
        }

        $t1 = (int)$m['team1_id'];
        $t2 = (int)$m['team2_id'];

        $set1A = (int)Database::one(
            "SELECT COUNT(*) AS c FROM match_highlights WHERE match_id = ? AND team_id = ? AND event_type = 'goal' AND (period = 1 OR period IS NULL)",
            [$matchId, $t1]
        )['c'];

        $set1B = (int)Database::one(
            "SELECT COUNT(*) AS c FROM match_highlights WHERE match_id = ? AND team_id = ? AND event_type = 'goal' AND (period = 1 OR period IS NULL)",
            [$matchId, $t2]
        )['c'];

        $set2A = (int)Database::one(
            "SELECT COUNT(*) AS c FROM match_highlights WHERE match_id = ? AND team_id = ? AND event_type = 'goal' AND period = 2",
            [$matchId, $t1]
        )['c'];

        $set2B = (int)Database::one(
            "SELECT COUNT(*) AS c FROM match_highlights WHERE match_id = ? AND team_id = ? AND event_type = 'goal' AND period = 2",
            [$matchId, $t2]
        )['c'];

        $score1 = $set1A + $set2A;
        $score2 = $set1B + $set2B;

        Database::query(
            "UPDATE matches SET set1_a = ?, set1_b = ?, set2_a = ?, set2_b = ?, score1 = ?, score2 = ? WHERE id = ?",
            [$set1A, $set1B, $set2A, $set2B, $score1, $score2, $matchId]
        );

        if (!empty($m['group_id'])) {
            Standing::recalculate((int)$m['group_id']);
        }

        return [
            'set1A'  => $set1A,
            'set1B'  => $set1B,
            'set2A'  => $set2A,
            'set2B'  => $set2B,
            'score1' => $score1,
            'score2' => $score2,
        ];
    }
}
