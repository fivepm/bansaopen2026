<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class GroupModel
{
    public static function forSport(string $sport): array
    {
        return Database::all('SELECT * FROM `groups` WHERE sport_type = ? ORDER BY name', [$sport]);
    }

    public static function find(int $id, string $sport): ?array
    {
        return Database::one('SELECT * FROM `groups` WHERE id = ? AND sport_type = ?', [$id, $sport]);
    }

    /** Hapus semua grup (beserta klasemen & pertandingan grup via FK cascade), lalu buat $count grup baru. */
    public static function createGroups(string $sport, int $count): void
    {
        self::resetAll($sport);
        for ($i = 0; $i < $count; $i++) {
            Database::query('INSERT INTO `groups` (sport_type, name) VALUES (?, ?)', [$sport, 'Grup ' . self::letter($i)]);
        }
    }

    public static function resetAll(string $sport): void
    {
        Database::query("DELETE FROM matches WHERE sport_type = ? AND stage = 'group'", [$sport]);
        Database::query('DELETE FROM `groups` WHERE sport_type = ?', [$sport]);
    }

    /** Anggota grup saat ini: team_id => group_id */
    public static function assignments(string $sport): array
    {
        $map = [];
        foreach (Database::all(
            'SELECT s.team_id, s.group_id FROM group_standings s JOIN `groups` g ON g.id = s.group_id WHERE g.sport_type = ?',
            [$sport]
        ) as $r) {
            $map[(int)$r['team_id']] = (int)$r['group_id'];
        }
        return $map;
    }

    /**
     * Simpan pembagian tim ke grup. Jadwal grup lama dihapus (harus di-generate ulang) agar data tetap konsisten.
     * @param array<int,int> $assign team_id => group_id (0 = tanpa grup)
     */
    public static function assign(string $sport, array $assign): void
    {
        $validGroups = array_column(self::forSport($sport), 'id');
        $validTeams = array_column(Database::all('SELECT id FROM teams WHERE sport_type = ?', [$sport]), 'id');

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query("DELETE FROM matches WHERE sport_type = ? AND stage = 'group'", [$sport]);
            Database::query('DELETE s FROM group_standings s JOIN `groups` g ON g.id = s.group_id WHERE g.sport_type = ?', [$sport]);
            foreach ($assign as $teamId => $groupId) {
                if (in_array($teamId, $validTeams) && in_array($groupId, $validGroups)) {
                    Database::query('INSERT INTO group_standings (group_id, team_id) VALUES (?, ?)', [$groupId, $teamId]);
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Bagi semua tim cabor secara acak & merata ke grup yang ada. */
    public static function autoAssign(string $sport): void
    {
        $groups = array_column(self::forSport($sport), 'id');
        if (!$groups) {
            return;
        }
        $teams = array_column(Database::all('SELECT id FROM teams WHERE sport_type = ?', [$sport]), 'id');
        shuffle($teams);
        $assign = [];
        foreach ($teams as $i => $teamId) {
            $assign[(int)$teamId] = (int)$groups[$i % count($groups)];
        }
        self::assign($sport, $assign);
    }

    /**
     * Buat jadwal round-robin (semua bertemu semua satu kali) dengan metode lingkaran.
     * round = hari pertandingan (matchday), position = urutan di dalam grup.
     * @return int jumlah pertandingan yang dibuat
     */
    public static function generateFixtures(string $sport, int $groupId): int
    {
        $teams = array_map('intval', array_column(
            Database::all('SELECT team_id FROM group_standings WHERE group_id = ? ORDER BY team_id', [$groupId]),
            'team_id'
        ));
        Database::query("DELETE FROM matches WHERE group_id = ? AND stage = 'group'", [$groupId]);
        if (count($teams) < 2) {
            Standing::recalculate($groupId);
            return 0;
        }

        $list = $teams;
        if (count($list) % 2 === 1) {
            $list[] = null; // bye
        }
        $n = count($list);
        $created = 0;
        $pos = 1;
        for ($round = 1; $round < $n; $round++) {
            for ($i = 0; $i < $n / 2; $i++) {
                $a = $list[$i];
                $b = $list[$n - 1 - $i];
                if ($a === null || $b === null) {
                    continue;
                }
                Database::query(
                    "INSERT INTO matches (sport_type, stage, group_id, round, position, team1_id, team2_id) VALUES (?, 'group', ?, ?, ?, ?, ?)",
                    [$sport, $groupId, $round, $pos++, $a, $b]
                );
                $created++;
            }
            // rotasi: elemen pertama tetap, sisanya berputar
            $last = array_pop($list);
            array_splice($list, 1, 0, [$last]);
        }
        Standing::recalculate($groupId);
        return $created;
    }

    private static function letter(int $i): string
    {
        return $i < 26 ? chr(65 + $i) : (string)($i + 1);
    }
}
