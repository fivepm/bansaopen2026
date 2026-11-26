<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MatchModel
{
    public const SELECT = 'SELECT m.*, t1.name AS team1_name, t1.members AS team1_members, t2.name AS team2_name, t2.members AS team2_members, w.name AS winner_name, g.name AS group_name
        FROM matches m
        LEFT JOIN teams t1 ON t1.id = m.team1_id
        LEFT JOIN teams t2 ON t2.id = m.team2_id
        LEFT JOIN teams w  ON w.id  = m.winner_id
        LEFT JOIN `groups` g ON g.id = m.group_id';

    // ---------- Query ----------

    /**
     * Mengambil struktur bagan dua sisi (Two-Sided Bracket) untuk cabor & kategori tertentu.
     * @return array{totalRounds: int, left: array<int, array>, right: array<int, array>, final: ?array, thirdPlace: ?array, isRepechage20: bool, bestLoserLeft: ?array, bestLoserRight: ?array}
     */
    public static function twoSidedBracket(string $sport, string $category = 'umum'): array
    {
        $matches = Database::all(
            self::SELECT . " WHERE m.sport_type = ? AND m.category = ? AND m.stage = 'knockout' ORDER BY m.round ASC, m.position ASC",
            [$sport, $category]
        );

        $totalRounds = 0;
        $left = [];
        $right = [];
        $final = null;
        $thirdPlace = null;

        foreach ($matches as $m) {
            $r = (int)$m['round'];
            if ($r > $totalRounds) {
                $totalRounds = $r;
            }

            $side = $m['bracket_side'] ?? 'left';
            if ($side === 'final') {
                $final = $m;
            } elseif ($side === 'third_place') {
                $thirdPlace = $m;
            } elseif ($side === 'right') {
                $right[$r][] = $m;
            } else {
                $left[$r][] = $m;
            }
        }

        $isRepechage20 = ($totalRounds === 6 && count($left[1] ?? []) === 5 && count($right[1] ?? []) === 5);
        $bestLoserLeft = $isRepechage20 ? self::computeBestLoser($left[1] ?? []) : null;
        $bestLoserRight = $isRepechage20 ? self::computeBestLoser($right[1] ?? []) : null;

        return compact('totalRounds', 'left', 'right', 'final', 'thirdPlace', 'isRepechage20', 'bestLoserLeft', 'bestLoserRight');
    }

    /**
     * Menghitung peringkat tim kalah dari 5 laga Babak Pendahuluan (format 20 tim).
     * Urutan: 1) Set yang dimenangkan (misal 1-2 > 0-2), 2) Selisih poin, 3) Total poin dicetak.
     * Mengembalikan daftar terurut (index 0 = Kalah Terbaik / BYE) jika seluruh 5 laga sudah selesai, atau null jika belum.
     */
    private static function rankRound1Losers(array $round1Matches): ?array
    {
        if (count($round1Matches) !== 5) {
            return null;
        }
        $losers = [];
        foreach ($round1Matches as $m) {
            if (($m['status'] ?? '') !== 'finished' || empty($m['winner_id']) || empty($m['team1_id']) || empty($m['team2_id'])) {
                return null;
            }
            $winnerId = (int)$m['winner_id'];
            $t1 = (int)$m['team1_id'];
            $t2 = (int)$m['team2_id'];
            $isT1Loser = ($winnerId === $t2);
            $loserId = $isT1Loser ? $t1 : $t2;
            $loserName = $isT1Loser ? ($m['team1_name'] ?? 'Tim') : ($m['team2_name'] ?? 'Tim');

            $system = $m['scoring_system'] ?? 'rubber_set';
            $s1A = (int)($m['set1_t1'] ?? 0);
            $s1B = (int)($m['set1_t2'] ?? 0);
            $s2A = (int)($m['set2_t1'] ?? 0);
            $s2B = (int)($m['set2_t2'] ?? 0);
            $s3A = (int)($m['set3_t1'] ?? 0);
            $s3B = (int)($m['set3_t2'] ?? 0);

            if ($system === 'poin_lanjut') {
                $pts1 = (int)($m['score1'] ?? 0);
                $pts2 = (int)($m['score2'] ?? 0);
                $setsWon = 0;
            } else {
                $setsWon = $isT1Loser ? (int)($m['score1'] ?? 0) : (int)($m['score2'] ?? 0);
                $pts1 = $s1A + $s2A + $s3A;
                $pts2 = $s1B + $s2B + $s3B;
                if ($pts1 === 0 && $pts2 === 0) {
                    $pts1 = (int)($m['score1'] ?? 0);
                    $pts2 = (int)($m['score2'] ?? 0);
                }
            }

            $ptsFor = $isT1Loser ? $pts1 : $pts2;
            $ptsAgainst = $isT1Loser ? $pts2 : $pts1;
            $pointDiff = $ptsFor - $ptsAgainst;

            $losers[] = [
                'team_id'    => $loserId,
                'team_name'  => $loserName,
                'sets_won'   => $setsWon,
                'point_diff' => $pointDiff,
                'points_for' => $ptsFor,
                'pos'        => (int)($m['position'] ?? 0),
            ];
        }

        usort($losers, static function (array $a, array $b): int {
            return ($b['sets_won'] <=> $a['sets_won'])
                ?: ($b['point_diff'] <=> $a['point_diff'])
                ?: ($b['points_for'] <=> $a['points_for'])
                ?: ($a['pos'] <=> $b['pos']);
        });

        return $losers;
    }

    private static function computeBestLoser(array $round1Matches): ?array
    {
        $ranked = self::rankRound1Losers($round1Matches);
        return $ranked ? $ranked[0] : null;
    }

    /** Pertandingan sistem gugur per babak (kompatibilitas backward / tampilan datar). */
    public static function knockout(string $sport, ?string $category = null): array
    {
        $sql = self::SELECT . " WHERE m.sport_type = ? AND m.stage = 'knockout'";
        $params = [$sport];
        if ($category !== null) {
            $sql .= ' AND m.category = ?';
            $params[] = $category;
        }
        $sql .= ' ORDER BY m.round, m.position';

        $rounds = [];
        foreach (Database::all($sql, $params) as $m) {
            $rounds[(int)$m['round']][] = $m;
        }
        return $rounds;
    }

    /** Pertandingan fase grup, dikelompokkan per group_id. @return array<int, array> */
    public static function groupMatches(string $sport): array
    {
        $out = [];
        foreach (Database::all(self::SELECT . " WHERE m.sport_type = ? AND m.stage = 'group' ORDER BY g.name, m.round, m.position", [$sport]) as $m) {
            $out[(int)$m['group_id']][] = $m;
        }
        return $out;
    }

    public static function find(int $id, ?string $sport = null): ?array
    {
        $sql = self::SELECT . ' WHERE m.id = ?';
        $params = [$id];
        if ($sport !== null) {
            $sql .= ' AND m.sport_type = ?';
            $params[] = $sport;
        }
        return Database::one($sql, $params);
    }

    public static function byStatus(string $sport, string $status, string $order, ?string $category = null): array
    {
        $sql = self::SELECT . ' WHERE m.sport_type = ? AND m.status = ?';
        $params = [$sport, $status];
        if ($category !== null) {
            $sql .= ' AND m.category = ?';
            $params[] = $category;
        }
        $sql .= " ORDER BY $order";
        return Database::all($sql, $params);
    }

    /**
     * Mengambil daftar urutan jadwal pertandingan cabor secara kronologis (Ascending).
     * Diurutkan dari pagi hingga malam (waktu pertandingan paling awal ke akhir).
     * Pertandingan tanpa jadwal (TBD) diurutkan setelahnya secara berurutan babak & posisi.
     * @return array<int, array>
     */
    public static function chronologicalSchedule(string $sport, ?string $category = null): array
    {
        $sql = self::SELECT . ' WHERE m.sport_type = ?';
        $params = [$sport];
        if ($category !== null) {
            $sql .= ' AND m.category = ?';
            $params[] = $category;
        }
        $sql .= ' ORDER BY 
            CASE WHEN m.scheduled_at IS NULL THEN 1 ELSE 0 END ASC,
            m.scheduled_at ASC,
            m.round ASC,
            m.position ASC';

        return Database::all($sql, $params);
    }

    public static function knockoutRounds(string $sport, ?string $category = null): int
    {
        $sql = "SELECT MAX(round) AS r FROM matches WHERE sport_type = ? AND stage = 'knockout'";
        $params = [$sport];
        if ($category !== null) {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }
        return (int)(Database::one($sql, $params)['r'] ?? 0);
    }

    /**
     * Cek apakah bagan cabor & kategori ini menggunakan format 20 tim (Round 1 = 10 laga, totalRounds = 6).
     */
    public static function isRepechage20(string $sport, string $category = 'umum'): bool
    {
        static $cache = [];
        $key = $sport . ':' . $category;
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $row = Database::one(
            "SELECT MAX(round) AS max_r, SUM(CASE WHEN round = 1 THEN 1 ELSE 0 END) AS r1_cnt
             FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout'",
            [$sport, $category]
        );
        return $cache[$key] = ((int)($row['max_r'] ?? 0) === 6 && (int)($row['r1_cnt'] ?? 0) === 10);
    }

    // ---------- Bracket Generator (Two-Sided + Third Place) ----------

    /**
     * Membuat bagan two-sided knockout untuk $slots peserta (4, 8, 16, 20, 32).
     * Untuk 20 tim: 8 tim babak pendahuluan (play-in) + 12 tim bye langsung ke 16 besar.
     * Bagian kiri dan kanan maju ke tengah, dengan pertandingan Final dan Perebutan Juara 3.
     */
    public static function generateTwoSidedKnockout(string $sport, string $category, int $slots): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query(
                "DELETE FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout'",
                [$sport, $category]
            );

            if ($slots === 20) {
                // ===== FORMAT KHUSUS 20 TIM (PENDAHULUAN 5+5 + PENYISIHAN 1 BYE + 2 MATCH PER SISI) =====
                // Round 1 (Babak Pendahuluan): 5 laga kiri (pos 1-5) + 5 laga kanan (pos 6-10)
                // Round 2 (Babak Penyisihan): 2 laga kiri (pos 1-2) + 2 laga kanan (pos 3-4) + 1 Kalah Terbaik BYE per sisi
                // Round 3 (Babak 16 Besar): 4 laga kiri (pos 1-4) + 4 laga kanan (pos 5-8)
                // Round 4 (Perempat Final): 2 laga kiri (pos 1-2) + 2 laga kanan (pos 3-4)
                // Round 5 (Semi Final): 1 laga kiri (pos 1) + 1 laga kanan (pos 2)
                // Round 6 (Puncak): final + third_place
                $totalRounds = 6;

                // Round 1: Babak Pendahuluan (5 kiri + 5 kanan)
                for ($pos = 1; $pos <= 5; $pos++) {
                    Database::query(
                        "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', 1, ?, 'left')",
                        [$sport, $category, $pos]
                    );
                }
                for ($pos = 6; $pos <= 10; $pos++) {
                    Database::query(
                        "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', 1, ?, 'right')",
                        [$sport, $category, $pos]
                    );
                }

                // Round 2: Babak Penyisihan (2 kiri + 2 kanan)
                for ($pos = 1; $pos <= 2; $pos++) {
                    Database::query(
                        "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', 2, ?, 'left')",
                        [$sport, $category, $pos]
                    );
                }
                for ($pos = 3; $pos <= 4; $pos++) {
                    Database::query(
                        "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', 2, ?, 'right')",
                        [$sport, $category, $pos]
                    );
                }

                // Round 3..5: Babak 16 Besar, Perempat Final, Semi Final
                for ($round = 3; $round < $totalRounds; $round++) {
                    $matchCount = (int)pow(2, $totalRounds - $round); // r3:8, r4:4, r5:2
                    $half = intdiv($matchCount, 2);

                    for ($pos = 1; $pos <= $half; $pos++) {
                        Database::query(
                            "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, ?, 'left')",
                            [$sport, $category, $round, $pos]
                        );
                    }
                    for ($pos = $half + 1; $pos <= $matchCount; $pos++) {
                        Database::query(
                            "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, ?, 'right')",
                            [$sport, $category, $round, $pos]
                        );
                    }
                }

                // Final & Juara 3 (Round 6)
                Database::query(
                    "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, 1, 'final')",
                    [$sport, $category, $totalRounds]
                );
                Database::query(
                    "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, 2, 'third_place')",
                    [$sport, $category, $totalRounds]
                );
            } else {
                // ===== FORMAT STANDAR (4, 8, 16, 32) =====
                // Hitung total ronde hingga final: misal 8 slot => R = 3 (R1: 4 matches, R2: 2 matches, R3: Final & Juara 3)
                $totalRounds = (int)ceil(log($slots, 2));

                // Generate babak penyisihan hingga semifinal (Round 1 s.d. R - 1)
                for ($round = 1; $round < $totalRounds; $round++) {
                    $matchCount = (int)pow(2, $totalRounds - $round);
                    $half = intdiv($matchCount, 2);

                    // Blok Kiri
                    for ($pos = 1; $pos <= $half; $pos++) {
                        Database::query(
                            "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, ?, 'left')",
                            [$sport, $category, $round, $pos]
                        );
                    }

                    // Blok Kanan
                    for ($pos = $half + 1; $pos <= $matchCount; $pos++) {
                        Database::query(
                            "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, ?, 'right')",
                            [$sport, $category, $round, $pos]
                        );
                    }
                }

                // Babak Puncak (Round R): Final & Perebutan Juara 3
                Database::query(
                    "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, 1, 'final')",
                    [$sport, $category, $totalRounds]
                );
                Database::query(
                    "INSERT INTO matches (sport_type, category, stage, round, position, bracket_side) VALUES (?, ?, 'knockout', ?, 2, 'third_place')",
                    [$sport, $category, $totalRounds]
                );
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function resetKnockout(string $sport, ?string $category = null): void
    {
        $sql = "DELETE FROM matches WHERE sport_type = ? AND stage = 'knockout'";
        $params = [$sport];
        if ($category !== null) {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }
        Database::query($sql, $params);
    }

    // ---------- Update Skor & Advance Otomatis ----------

    /**
     * Menghitung pemenang (winner_id), yang kalah (loser_id), dan skor ringkasan (score1, score2)
     * berdasarkan sistem pertandingan (poin_lanjut vs rubber_set).
     * 
     * @param array $m Record pertandingan
     * @return array{winner_id: ?int, loser_id: ?int, score1: int, score2: int, error: ?string}
     */
    public static function determineOutcome(array $m): array
    {
        $t1 = !empty($m['team1_id']) ? (int)$m['team1_id'] : null;
        $t2 = !empty($m['team2_id']) ? (int)$m['team2_id'] : null;

        if (!$t1 || !$t2) {
            return [
                'winner_id' => null,
                'loser_id'  => null,
                'score1'    => 0,
                'score2'    => 0,
                'error'     => 'Kedua tim harus ditentukan terlebih dahulu.',
            ];
        }

        $system = $m['scoring_system'] ?? 'rubber_set';
        $set1A = (int)($m['set1_a'] ?? 0);
        $set1B = (int)($m['set1_b'] ?? 0);
        $set2A = (int)($m['set2_a'] ?? 0);
        $set2B = (int)($m['set2_b'] ?? 0);
        $set3A = (int)($m['set3_a'] ?? 0);
        $set3B = (int)($m['set3_b'] ?? 0);

        if (($m['sport_type'] ?? '') === 'futsal') {
            // Format Futsal: Waktu Normal (Babak 1 dan Babak 2)
            // Total gol = Babak 1 + Babak 2
            $finalA = $set1A + $set2A;
            $finalB = $set1B + $set2B;

            // Fallback jika nilai babak 0 tetapi score1/score2 sudah ada
            if ($finalA === 0 && $finalB === 0 && ((int)($m['score1'] ?? 0) > 0 || (int)($m['score2'] ?? 0) > 0)) {
                $finalA = (int)($m['score1'] ?? 0);
                $finalB = (int)($m['score2'] ?? 0);
            }

            if ($finalA === $finalB) {
                // Pada fase grup, hasil SERI diperbolehkan dan sah (masing-masing 1 poin klasemen)
                if (($m['stage'] ?? '') === 'group') {
                    return [
                        'winner_id' => null,
                        'loser_id'  => null,
                        'score1'    => $finalA,
                        'score2'    => $finalB,
                        'error'     => null,
                    ];
                }
                // Jika pada babak final / perebutan juara 3 sistem gugur, harus ada pemenang (misal adu penalti)
                return [
                    'winner_id' => null,
                    'loser_id'  => null,
                    'score1'    => $finalA,
                    'score2'    => $finalB,
                    'error'     => "Skor waktu normal masih imbang ($finalA - $finalB). Babak final harus memiliki pemenang (adu penalti).",
                ];
            }

            return [
                'winner_id' => ($finalA > $finalB) ? $t1 : $t2,
                'loser_id'  => ($finalA > $finalB) ? $t2 : $t1,
                'score1'    => $finalA,
                'score2'    => $finalB,
                'error'     => null,
            ];
        }

        if ($system === 'poin_lanjut') {
            // Sistem Poin Lanjut: Set 2 melanjutkan skor terakhir dari Set 1 (Carry-Over Score).
            // Nilai Set 2 adalah skor akumulasi paling akhir sebagai penentu pemenang.
            // Kolom Set 1 adalah titik tengah, dan Set 2 adalah skor akhir akumulasi.
            $finalA = ($set2A > 0) ? $set2A : $set1A;
            $finalB = ($set2B > 0) ? $set2B : $set1B;

            // Fallback jika nilai set belum terisi tetapi score1/score2 sudah ada
            if ($finalA === 0 && $finalB === 0 && ((int)($m['score1'] ?? 0) > 0 || (int)($m['score2'] ?? 0) > 0)) {
                $finalA = (int)($m['score1'] ?? 0);
                $finalB = (int)($m['score2'] ?? 0);
            }

            if ($finalA === $finalB) {
                return [
                    'winner_id' => null,
                    'loser_id'  => null,
                    'score1'    => $finalA,
                    'score2'    => $finalB,
                    'error'     => "Skor akhir akumulasi poin lanjut masih seri ($finalA - $finalB). Sistem gugur harus memiliki tim pemenang.",
                ];
            }

            return [
                'winner_id' => ($finalA > $finalB) ? $t1 : $t2,
                'loser_id'  => ($finalA > $finalB) ? $t2 : $t1,
                'score1'    => $finalA,
                'score2'    => $finalB,
                'error'     => null,
            ];
        }

        // Sistem Rubber Set (Best of 3): Pemenang adalah yang memenangkan 2 set lebih dulu.
        $setsA = ($set1A > $set1B ? 1 : 0) + ($set2A > $set2B ? 1 : 0) + ($set3A > $set3B ? 1 : 0);
        $setsB = ($set1B > $set1A ? 1 : 0) + ($set2B > $set2A ? 1 : 0) + ($set3B > $set3A ? 1 : 0);

        // Fallback jika nilai set 0 tetapi score1/score2 sudah terisi
        if ($setsA === 0 && $setsB === 0 && ((int)($m['score1'] ?? 0) > 0 || (int)($m['score2'] ?? 0) > 0)) {
            $setsA = (int)($m['score1'] ?? 0);
            $setsB = (int)($m['score2'] ?? 0);
        }

        if ($setsA === $setsB) {
            return [
                'winner_id' => null,
                'loser_id'  => null,
                'score1'    => $setsA,
                'score2'    => $setsB,
                'error'     => "Skor set masih seimbang ($setsA - $setsB). Harus ada tim pemenang.",
            ];
        }

        if ($setsA < 2 && $setsB < 2 && ($m['stage'] ?? '') === 'knockout') {
            if ($setsA !== $setsB) {
                return [
                    'winner_id' => ($setsA > $setsB) ? $t1 : $t2,
                    'loser_id'  => ($setsA > $setsB) ? $t2 : $t1,
                    'score1'    => $setsA,
                    'score2'    => $setsB,
                    'error'     => null,
                ];
            }
            return [
                'winner_id' => null,
                'loser_id'  => null,
                'score1'    => $setsA,
                'score2'    => $setsB,
                'error'     => "Pertandingan Rubber Set belum selesai ($setsA - $setsB). Salah satu tim harus memenangkan minimal 2 set.",
            ];
        }

        return [
            'winner_id' => ($setsA > $setsB) ? $t1 : $t2,
            'loser_id'  => ($setsA > $setsB) ? $t2 : $t1,
            'score1'    => $setsA,
            'score2'    => $setsB,
            'error'     => null,
        ];
    }

    /**
     * Simpan hasil pertandingan.
     * - Sistem Gugur:
     *   - Pemenang blok kiri/kanan maju ke babak selanjutnya menuju tengah.
     *   - Pemenang Semifinal Kiri & Kanan maju ke Grand Final.
     *   - Tim yang kalah di Semifinal Kiri & Kanan otomatis masuk ke Perebutan Juara 3.
     * - Sistem Grup:
     *   - Klasemen grup dihitung ulang otomatis.
     */
    public static function update(int $id, array $d): void
    {
        $m = self::find($id);
        if (!$m) {
            return;
        }

        $winner = null;
        $loser = null;
        if ($d['status'] === 'finished' && !empty($d['team1_id']) && !empty($d['team2_id'])) {
            if (!empty($d['winner_id'])) {
                $winner = (int)$d['winner_id'];
                $loser = ($winner === (int)$d['team1_id']) ? (int)$d['team2_id'] : (int)$d['team1_id'];
            } elseif ($d['score1'] > $d['score2']) {
                $winner = (int)$d['team1_id'];
                $loser = (int)$d['team2_id'];
            } elseif ($d['score2'] > $d['score1']) {
                $winner = (int)$d['team2_id'];
                $loser = (int)$d['team1_id'];
            }
        }

        $refMain = array_key_exists('referee_main', $d) ? $d['referee_main'] : ($m['referee_main'] ?? null);
        $refLine1 = array_key_exists('referee_line1', $d) ? $d['referee_line1'] : ($m['referee_line1'] ?? null);
        $refLine2 = array_key_exists('referee_line2', $d) ? $d['referee_line2'] : ($m['referee_line2'] ?? null);

        Database::query(
            'UPDATE matches SET team1_id=?, team2_id=?, score1=?, score2=?, status=?, winner_id=?, scheduled_at=?, court=?, referee_main=?, referee_line1=?, referee_line2=? WHERE id=?',
            [$d['team1_id'], $d['team2_id'], $d['score1'], $d['score2'], $d['status'], $winner, $d['scheduled_at'], $d['court'], $refMain, $refLine1, $refLine2, $id]
        );

        if ($m['stage'] === 'group') {
            Standing::recalculate((int)$m['group_id']);
        } else {
            self::advance(
                $m['sport_type'],
                $m['category'] ?? 'umum',
                (int)$m['round'],
                (int)$m['position'],
                $m['bracket_side'] ?? 'left',
                $winner,
                $loser
            );
        }
    }

    /**
     * Memajukan pemenang dan peserta perebutan juara 3 ke pertandingan selanjutnya.
     */
    private static function advance(
        string $sport,
        string $category,
        int $round,
        int $pos,
        string $bracketSide,
        ?int $winner,
        ?int $loser
    ): void {
        $totalRounds = self::knockoutRounds($sport, $category);
        if ($totalRounds < 2) {
            return;
        }

        // Babak Semifinal (Round R - 1)
        if ($round === $totalRounds - 1) {
            $slot = ($bracketSide === 'left') ? 'team1_id' : 'team2_id';

            // 1. Pemenang semifinal masuk ke Grand Final
            $final = Database::one(
                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND bracket_side = 'final'",
                [$sport, $category]
            );
            if ($final && $final['status'] === 'upcoming') {
                Database::query("UPDATE matches SET $slot = ? WHERE id = ?", [$winner, $final['id']]);
            }

            // 2. Yang kalah di semifinal masuk ke Perebutan Juara 3
            $third = Database::one(
                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND bracket_side = 'third_place'",
                [$sport, $category]
            );
            if ($third && $third['status'] === 'upcoming') {
                Database::query("UPDATE matches SET $slot = ? WHERE id = ?", [$loser, $third['id']]);
            }
            return;
        }

        // Babak sebelum semifinal (Round 1 s.d. R - 2)
        if ($round < $totalRounds - 1) {
            // Cek khusus format 20 tim (totalRounds = 6, Round 1 = 10 laga: 5 kiri + 5 kanan)
            if ($totalRounds === 6 && ($round === 1 || $round === 2)) {
                $r1Count = (int)(Database::one(
                    "SELECT COUNT(*) AS c FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 1",
                    [$sport, $category]
                )['c'] ?? 0);

                if ($r1Count === 10) {
                    if ($round === 1) {
                        // 1. Pemenang Round 1 (Babak Pendahuluan) langsung ke Round 3 (Babak 16 Besar)
                        $r3Map = [
                            1  => [1, 'team1_id'],
                            2  => [1, 'team2_id'],
                            3  => [2, 'team1_id'],
                            4  => [2, 'team2_id'],
                            5  => [3, 'team1_id'],
                            6  => [5, 'team1_id'],
                            7  => [5, 'team2_id'],
                            8  => [6, 'team1_id'],
                            9  => [6, 'team2_id'],
                            10 => [7, 'team1_id'],
                        ];
                        if (isset($r3Map[$pos])) {
                            [$tgtPos, $tgtSlot] = $r3Map[$pos];
                            $next = Database::one(
                                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 3 AND position = ? AND bracket_side = ?",
                                [$sport, $category, $tgtPos, $bracketSide]
                            );
                            if ($next && $next['status'] === 'upcoming') {
                                Database::query("UPDATE matches SET $tgtSlot = ? WHERE id = ?", [$winner, $next['id']]);
                            }
                        }

                        // 2. Jika seluruh 5 laga Round 1 di sisi ini selesai, tentukan 1 Kalah Terbaik (BYE) + 4 tim ke Round 2 (Babak Penyisihan)
                        $sideR1 = Database::all(
                            self::SELECT . " WHERE m.sport_type = ? AND m.category = ? AND m.stage = 'knockout' AND m.round = 1 AND m.bracket_side = ? ORDER BY m.position ASC",
                            [$sport, $category, $bracketSide]
                        );
                        $rankedLosers = self::rankRound1Losers($sideR1);
                        if ($rankedLosers !== null && count($rankedLosers) === 5) {
                            // Rank 0 = Kalah Terbaik (BYE langsung ke Round 3: 3-3 untuk Kiri, 3-7 untuk Kanan)
                            $byePos = ($bracketSide === 'left') ? 3 : 7;
                            $byeMatch = Database::one(
                                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 3 AND position = ? AND bracket_side = ?",
                                [$sport, $category, $byePos, $bracketSide]
                            );
                            if ($byeMatch && $byeMatch['status'] === 'upcoming') {
                                Database::query('UPDATE matches SET team2_id = ? WHERE id = ?', [$rankedLosers[0]['team_id'], $byeMatch['id']]);
                            }

                            // Rank 1..4 = 4 tim kalah lainnya bertanding di 2 laga Round 2 (Babak Penyisihan)
                            $r2Pos1 = ($bracketSide === 'left') ? 1 : 3;
                            $r2Pos2 = ($bracketSide === 'left') ? 2 : 4;
                            $m2a = Database::one(
                                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 2 AND position = ? AND bracket_side = ?",
                                [$sport, $category, $r2Pos1, $bracketSide]
                            );
                            if ($m2a && $m2a['status'] === 'upcoming') {
                                Database::query('UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ?', [$rankedLosers[1]['team_id'], $rankedLosers[4]['team_id'], $m2a['id']]);
                            }
                            $m2b = Database::one(
                                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 2 AND position = ? AND bracket_side = ?",
                                [$sport, $category, $r2Pos2, $bracketSide]
                            );
                            if ($m2b && $m2b['status'] === 'upcoming') {
                                Database::query('UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ?', [$rankedLosers[2]['team_id'], $rankedLosers[3]['team_id'], $m2b['id']]);
                            }
                        }
                        return;
                    }

                    if ($round === 2) {
                        // Pemenang Round 2 (Babak Penyisihan) masuk ke 3-4 (Kiri) & 3-8 (Kanan)
                        $r2ToR3 = [
                            1 => [4, 'team1_id'],
                            2 => [4, 'team2_id'],
                            3 => [8, 'team1_id'],
                            4 => [8, 'team2_id'],
                        ];
                        if (isset($r2ToR3[$pos])) {
                            [$tgtPos, $tgtSlot] = $r2ToR3[$pos];
                            $next = Database::one(
                                "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = 3 AND position = ? AND bracket_side = ?",
                                [$sport, $category, $tgtPos, $bracketSide]
                            );
                            if ($next && $next['status'] === 'upcoming') {
                                Database::query("UPDATE matches SET $tgtSlot = ? WHERE id = ?", [$winner, $next['id']]);
                            }
                        }
                        return;
                    }
                }
            }

            if ($bracketSide === 'left') {
                $nextPos = (int)ceil($pos / 2);
                $slot = ($pos % 2 === 1) ? 'team1_id' : 'team2_id';

                $next = Database::one(
                    "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = ? AND position = ? AND bracket_side = 'left'",
                    [$sport, $category, $round + 1, $nextPos]
                );
                if ($next && $next['status'] === 'upcoming') {
                    Database::query("UPDATE matches SET $slot = ? WHERE id = ?", [$winner, $next['id']]);
                }
            } elseif ($bracketSide === 'right') {
                // Posisi di sisi kanan dimulai dari offset
                $count = (int)pow(2, $totalRounds - $round);
                $offset = intdiv($count, 2);
                $relPos = $pos - $offset; // 1, 2, ...
                $nextRelPos = (int)ceil($relPos / 2);

                $nextCount = (int)pow(2, $totalRounds - ($round + 1));
                $nextOffset = intdiv($nextCount, 2);
                $nextPos = $nextOffset + $nextRelPos;

                $slot = ($relPos % 2 === 1) ? 'team1_id' : 'team2_id';

                $next = Database::one(
                    "SELECT id, status FROM matches WHERE sport_type = ? AND category = ? AND stage = 'knockout' AND round = ? AND position = ? AND bracket_side = 'right'",
                    [$sport, $category, $round + 1, $nextPos]
                );
                if ($next && $next['status'] === 'upcoming') {
                    Database::query("UPDATE matches SET $slot = ? WHERE id = ?", [$winner, $next['id']]);
                }
            }
        }
    }
}
