<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\MatchModel;
use App\Models\Sports;

final class LivePanelController
{
    public function index(): void
    {
        $sportFilter = $_GET['sport'] ?? null;
        if ($sportFilter && !Sports::exists($sportFilter)) {
            $sportFilter = null;
        }

        $sql = "SELECT m.*, t1.name AS team1_name, t1.members AS team1_members, t2.name AS team2_name, t2.members AS team2_members, w.name AS winner_name, g.name AS group_name
                FROM matches m
                LEFT JOIN teams t1 ON t1.id = m.team1_id
                LEFT JOIN teams t2 ON t2.id = m.team2_id
                LEFT JOIN teams w  ON w.id  = m.winner_id
                LEFT JOIN `groups` g ON g.id = m.group_id
                WHERE m.status = 'live'";
        $params = [];
        if ($sportFilter) {
            $sql .= ' AND m.sport_type = ?';
            $params[] = $sportFilter;
        }
        $sql .= ' ORDER BY m.sport_type ASC, m.round ASC, m.position ASC';

        $liveMatches = Database::all($sql, $params);
        $highlights = \App\Models\MatchHighlight::forMatches(array_column($liveMatches, 'id'));

        View::render('admin/live_panel', [
            'title'        => 'Live Panel / Operator Skor',
            'liveMatches'  => $liveMatches,
            'highlights'   => $highlights,
            'sportFilter'  => $sportFilter,
            'sports'       => Sports::LIST,
        ], 'layouts/admin');
    }

    /**
     * Mengatur set mana yang sedang aktif dimainkan (1, 2, atau 3).
     * POST params: set (1, 2, 3)
     */
    public function startSet(string $id): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak aktif / bukan berstatus Live.'], 422);
        }

        $set = (int)($_POST['set'] ?? 1);
        $system = $m['scoring_system'] ?? 'rubber_set';

        $curr = (int)($m['current_set'] ?? 0);
        $isFinished = (bool)($m['is_set_finished'] ?? false);

        // Jika ada set yang sedang berjalan dan belum ditekan tombol "Selesai Set", tidak boleh memilih set lain
        if ($curr > 0 && !$isFinished && $set !== $curr) {
            $term = (($m['sport_type'] ?? '') === 'futsal') ? 'Babak' : 'Set';
            View::json([
                'ok'    => false,
                'error' => "$term $curr masih berlangsung! Selesaikan $term $curr terlebih dahulu dengan menekan tombol 'Selesai $term $curr' sebelum memilih babak/set lain."
            ], 422);
        }

        $isFutsal = (($m['sport_type'] ?? '') === 'futsal');
        if ($isFutsal) {
            if (!in_array($set, [1, 2], true)) {
                View::json(['ok' => false, 'error' => 'Format Futsal hanya memiliki Babak 1 dan Babak 2.'], 422);
            }
            $halfDuration = (int)($m['futsal_half_duration'] ?? 5);
            $elapsed = (int)($m['futsal_elapsed_seconds'] ?? 0);
            if ($set === 2 && $curr < 2) {
                $elapsed = $halfDuration * 60;
            } elseif ($set === 1 && $curr === 0) {
                $elapsed = 0;
            }
            Database::query(
                "UPDATE matches SET
                    current_set = ?,
                    is_set_finished = 0,
                    futsal_elapsed_seconds = ?,
                    futsal_timer_status = 'running',
                    futsal_timer_updated_at = NOW()
                 WHERE id = ?",
                [$set, $elapsed, $m['id']]
            );
            $m['current_set'] = $set;
            $m['is_set_finished'] = 0;
            $m['futsal_elapsed_seconds'] = $elapsed;
            $m['futsal_timer_status'] = 'running';

            $set1A = (int)$m['set1_a'];
            $set1B = (int)$m['set1_b'];
            $set2A = (int)$m['set2_a'];
            $set2B = (int)$m['set2_b'];
            $set3A = 0;
            $set3B = 0;
            $score1 = $set1A + $set2A;
            $score2 = $set1B + $set2B;

            if ($score1 > $score2) {
                $winnerPreview = 'Tim 1 Unggul';
            } elseif ($score2 > $score1) {
                $winnerPreview = 'Tim 2 Unggul';
            } else {
                $winnerPreview = 'Skor Imbang';
            }
            $canFinish = ($set === 2);
        } elseif ($system === 'poin_lanjut') {
            if (!in_array($set, [1, 2], true)) {
                View::json(['ok' => false, 'error' => 'Sistem Poin Lanjut hanya memiliki maksimal 2 set.'], 422);
            }

            if ($set === 2) {
                // Di Poin Lanjut: Ketika wasit klik "Mulai Set 2", sistem mengunci Set 1,
                // mengambil skor akhir dari Set 1, dan menempatkannya sebagai skor awal untuk Set 2.
                $baseA = (int)($m['set1_a'] ?? 0);
                $baseB = (int)($m['set1_b'] ?? 0);
                $set2A = ((int)($m['set2_a'] ?? 0) > 0) ? max($baseA, (int)$m['set2_a']) : $baseA;
                $set2B = ((int)($m['set2_b'] ?? 0) > 0) ? max($baseB, (int)$m['set2_b']) : $baseB;

                Database::query(
                    "UPDATE matches SET current_set = 2, is_set_finished = 0, set2_a = ?, set2_b = ?, score1 = ?, score2 = ? WHERE id = ?",
                    [$set2A, $set2B, $set2A, $set2B, $m['id']]
                );
                $m['current_set'] = 2;
                $m['is_set_finished'] = 0;
                $m['set2_a'] = $set2A;
                $m['set2_b'] = $set2B;
                $m['score1'] = $set2A;
                $m['score2'] = $set2B;
            } else {
                // Set 1
                Database::query("UPDATE matches SET current_set = 1, is_set_finished = 0 WHERE id = ?", [$m['id']]);
                $m['current_set'] = 1;
                $m['is_set_finished'] = 0;
            }

            $set1A = (int)$m['set1_a'];
            $set1B = (int)$m['set1_b'];
            $set2A = (int)$m['set2_a'];
            $set2B = (int)$m['set2_b'];
            $set3A = 0;
            $set3B = 0;
            $score1 = ($set2A > 0) ? $set2A : $set1A;
            $score2 = ($set2B > 0) ? $set2B : $set1B;

            if ($score1 > $score2) {
                $winnerPreview = 'Tim 1 Unggul';
                $canFinish = true;
            } elseif ($score2 > $score1) {
                $winnerPreview = 'Tim 2 Unggul';
                $canFinish = true;
            } else {
                $winnerPreview = 'Poin Seri';
                $canFinish = false;
            }
        } else {
            // Rubber Set
            if (!in_array($set, [1, 2, 3], true)) {
                View::json(['ok' => false, 'error' => 'Pilihan set tidak valid.'], 422);
            }

            Database::query("UPDATE matches SET current_set = ?, is_set_finished = 0 WHERE id = ?", [$set, $m['id']]);
            $m['current_set'] = $set;
            $m['is_set_finished'] = 0;

            $set1A = (int)$m['set1_a'];
            $set1B = (int)$m['set1_b'];
            $set2A = (int)$m['set2_a'];
            $set2B = (int)$m['set2_b'];
            $set3A = (int)$m['set3_a'];
            $set3B = (int)$m['set3_b'];

            $setsA = ($set1A > $set1B ? 1 : 0) + ($set2A > $set2B ? 1 : 0) + ($set3A > $set3B ? 1 : 0);
            $setsB = ($set1B > $set1A ? 1 : 0) + ($set2B > $set2A ? 1 : 0) + ($set3B > $set3A ? 1 : 0);
            $score1 = $setsA;
            $score2 = $setsB;

            if ($setsA >= 2) {
                $winnerPreview = 'Tim 1 Menang 2 Set';
                $canFinish = true;
            } elseif ($setsB >= 2) {
                $winnerPreview = 'Tim 2 Menang 2 Set';
                $canFinish = true;
            } elseif ($setsA === 1 && $setsB === 1) {
                $winnerPreview = 'Rubber Set (Lanjut Set 3)';
                $canFinish = false;
            } else {
                $winnerPreview = 'Sedang Berlangsung';
                $canFinish = false;
            }
        }

        View::json([
            'ok'                     => true,
            'current_set'            => $set,
            'scoring_system'         => $system,
            'set1_a'                 => $set1A,
            'set1_b'                 => $set1B,
            'set2_a'                 => $set2A,
            'set2_b'                 => $set2B,
            'set3_a'                 => $set3A,
            'set3_b'                 => $set3B,
            'score1'                 => $score1,
            'score2'                 => $score2,
            'winner_preview'         => $winnerPreview,
            'can_finish'             => $canFinish,
            'futsal_half_duration'   => (int)($m['futsal_half_duration'] ?? 5),
            'futsal_elapsed_seconds' => (int)($m['futsal_elapsed_seconds'] ?? 0),
            'futsal_timer_status'    => $m['futsal_timer_status'] ?? 'stopped',
            'message'                => "Set $set telah diaktifkan.",
        ]);
    }

    /**
     * Menandai set aktif telah selesai sehingga wasit dapat memilih set berikutnya.
     */
    public function finishSet(string $id): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak aktif / bukan berstatus Live.'], 422);
        }

        $currentSet = (int)($m['current_set'] ?? 0);
        if ($currentSet === 0) {
            View::json(['ok' => false, 'error' => 'Belum ada set yang sedang aktif dimainkan.'], 422);
        }

        $system = $m['scoring_system'] ?? 'rubber_set';
        $isFutsal = (($m['sport_type'] ?? '') === 'futsal');

        // Tandai set aktif telah selesai
        if ($isFutsal) {
            $halfDur = (int)($m['futsal_half_duration'] ?? 5);
            $half1End = $halfDur * 60;
            $maxTime = $halfDur * 120;
            if ($currentSet === 1) {
                Database::query(
                    "UPDATE matches SET
                        is_set_finished = 1,
                        futsal_elapsed_seconds = ?,
                        futsal_timer_status = 'paused',
                        futsal_timer_updated_at = NOW()
                     WHERE id = ?",
                    [$half1End, $m['id']]
                );
                $m['futsal_elapsed_seconds'] = $half1End;

                // Tambahkan penanda Half Time ke highlight jika belum ada
                $hasHt = Database::one("SELECT id FROM match_highlights WHERE match_id = ? AND event_type = 'half_time'", [$m['id']]);
                if (!$hasHt) {
                    \App\Models\MatchHighlight::add((int)$m['id'], null, 'Half Time (Turun Minum)', 'half_time', (string)$halfDur, null, null, 1);
                }
            } elseif ($currentSet === 2) {
                // Ketika babak 2 / pertandingan selesai, waktu otomatis menjadi batas waktu maksimal yang ditentukan admin
                Database::query(
                    "UPDATE matches SET
                        is_set_finished = 1,
                        futsal_elapsed_seconds = ?,
                        futsal_timer_status = 'paused',
                        futsal_timer_updated_at = NOW()
                     WHERE id = ?",
                    [$maxTime, $m['id']]
                );
                $m['futsal_elapsed_seconds'] = $maxTime;

                // Tambahkan penanda Full Time ke highlight jika belum ada
                $hasFt = Database::one("SELECT id FROM match_highlights WHERE match_id = ? AND event_type = 'full_time'", [$m['id']]);
                if (!$hasFt) {
                    \App\Models\MatchHighlight::add((int)$m['id'], null, 'Full Time (Pertandingan Berakhir)', 'full_time', (string)($halfDur * 2), null, null, 2);
                }
            } else {
                Database::query("UPDATE matches SET is_set_finished = 1, futsal_timer_status = 'paused', futsal_timer_updated_at = NOW() WHERE id = ?", [$m['id']]);
            }
            $m['futsal_timer_status'] = 'paused';
        } else {
            Database::query("UPDATE matches SET is_set_finished = 1 WHERE id = ?", [$m['id']]);
        }
        $m['is_set_finished'] = 1;

        // Jika Poin Lanjut dan Set 1 selesai, sinkronkan skor awal Set 2
        $newSet2A = null;
        $newSet2B = null;
        if ($system === 'poin_lanjut' && $currentSet === 1) {
            $baseA = (int)$m['set1_a'];
            $baseB = (int)$m['set1_b'];
            $newSet2A = ((int)$m['set2_a'] > 0) ? max($baseA, (int)$m['set2_a']) : $baseA;
            $newSet2B = ((int)$m['set2_b'] > 0) ? max($baseB, (int)$m['set2_b']) : $baseB;

            Database::query(
                "UPDATE matches SET set2_a = ?, set2_b = ?, score1 = ?, score2 = ? WHERE id = ?",
                [$newSet2A, $newSet2B, $newSet2A, $newSet2B, $m['id']]
            );
            $m['set2_a'] = $newSet2A;
            $m['set2_b'] = $newSet2B;
            $m['score1'] = $newSet2A;
            $m['score2'] = $newSet2B;
        }

        $set1A = (int)$m['set1_a'];
        $set1B = (int)$m['set1_b'];
        $set2A = (int)($newSet2A ?? $m['set2_a']);
        $set2B = (int)($newSet2B ?? $m['set2_b']);
        $set3A = (int)$m['set3_a'];
        $set3B = (int)$m['set3_b'];

        $nextSet = null;
        $canFinishMatch = false;

        $isFutsal = (($m['sport_type'] ?? '') === 'futsal');
        if ($isFutsal) {
            $score1 = $set1A + $set2A;
            $score2 = $set1B + $set2B;
            if ($currentSet === 1) {
                $nextSet = 2;
                $winnerPreview = 'Babak 1 Selesai · Siap Lanjut Babak 2';
                $canFinishMatch = false;
            } else {
                $nextSet = null;
                $canFinishMatch = true;
                $winnerPreview = ($score1 > $score2) ? 'Tim 1 Unggul (Selesai)' : (($score2 > $score1) ? 'Tim 2 Unggul (Selesai)' : 'Skor Imbang (Selesai)');
            }
        } elseif ($system === 'poin_lanjut') {
            $score1 = ($set2A > 0) ? $set2A : $set1A;
            $score2 = ($set2B > 0) ? $set2B : $set1B;
            if ($currentSet === 1) {
                $nextSet = 2;
                $winnerPreview = 'Set 1 Selesai · Siap Mulai Set 2';
            } else {
                $canFinishMatch = ($score1 !== $score2);
                $winnerPreview = ($score1 > $score2) ? 'Tim 1 Unggul (Selesai)' : (($score2 > $score1) ? 'Tim 2 Unggul (Selesai)' : 'Poin Seri');
            }
        } else {
            // Rubber Set
            $setsA = ($set1A > $set1B ? 1 : 0) + ($set2A > $set2B ? 1 : 0) + ($set3A > $set3B ? 1 : 0);
            $setsB = ($set1B > $set1A ? 1 : 0) + ($set2B > $set2A ? 1 : 0) + ($set3B > $set3A ? 1 : 0);
            $score1 = $setsA;
            $score2 = $setsB;

            if ($currentSet === 1) {
                $nextSet = 2;
                $winnerPreview = 'Set 1 Selesai · Siap Mulai Set 2';
            } elseif ($currentSet === 2) {
                if ($setsA === 1 && $setsB === 1) {
                    $nextSet = 3;
                    $winnerPreview = 'Seri 1-1 · Siap Mulai Set 3 (Rubber Game)';
                } else {
                    $canFinishMatch = true;
                    $winnerPreview = ($setsA >= 2) ? 'Tim 1 Menang 2-0' : 'Tim 2 Menang 2-0';
                }
            } else {
                $canFinishMatch = true;
                $winnerPreview = ($setsA > $setsB) ? 'Tim 1 Menang Rubber Set' : 'Tim 2 Menang Rubber Set';
            }
        }

        View::json([
            'ok'                  => true,
            'current_set'         => $currentSet,
            'is_set_finished'     => 1,
            'next_set'            => $nextSet,
            'scoring_system'      => $system,
            'set1_a'              => $set1A,
            'set1_b'              => $set1B,
            'set2_a'              => $set2A,
            'set2_b'              => $set2B,
            'set3_a'              => $set3A,
            'set3_b'              => $set3B,
            'score1'              => $score1,
            'score2'              => $score2,
            'winner_preview'      => $winnerPreview,
            'can_finish_match'    => $canFinishMatch,
            'futsal_elapsed_seconds' => (int)($m['futsal_elapsed_seconds'] ?? 0),
            'futsal_timer_status' => $m['futsal_timer_status'] ?? 'paused',
            'highlights'          => ($isFutsal ? \App\Models\MatchHighlight::forMatch((int)$m['id']) : []),
            'message'             => "Set $currentSet telah selesai! Anda sekarang dapat memilih set berikutnya.",
        ]);
    }

    /**
     * Membuka kembali set aktif untuk mengoreksi skor jika ada kesalahan input.
     */
    public function reopenSet(string $id): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak aktif / bukan berstatus Live.'], 422);
        }

        $currentSet = (int)($m['current_set'] ?? 0);
        if ($currentSet === 0) {
            View::json(['ok' => false, 'error' => 'Belum ada set yang aktif.'], 422);
        }

        Database::query("UPDATE matches SET is_set_finished = 0 WHERE id = ?", [$m['id']]);

        $isFutsal = (($m['sport_type'] ?? '') === 'futsal');
        if ($isFutsal) {
            if ($currentSet === 1) {
                Database::query("DELETE FROM match_highlights WHERE match_id = ? AND event_type = 'half_time'", [$m['id']]);
            } elseif ($currentSet === 2) {
                Database::query("DELETE FROM match_highlights WHERE match_id = ? AND event_type = 'full_time'", [$m['id']]);
            }
        }

        View::json([
            'ok'              => true,
            'current_set'     => $currentSet,
            'is_set_finished' => 0,
            'highlights'      => ($isFutsal ? \App\Models\MatchHighlight::forMatch((int)$m['id']) : []),
            'message'         => "Set $currentSet dibuka kembali untuk koreksi skor.",
        ]);
    }

    /**
     * Endpoint AJAX / Fetch API untuk menambah atau mengurangi poin per set secara real-time.
     * POST params: set (1, 2, 3), team ('a' atau 'b'), delta (1 atau -1)
     */
    public function point(string $id): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak aktif / bukan berstatus Live.'], 422);
        }

        $set = (int)($_POST['set'] ?? 1);
        if (!in_array($set, [1, 2, 3], true)) {
            $set = 1;
        }

        // Penguncian: hanya set yang sedang aktif dimainkan yang boleh diedit skornya
        $currentSet = (int)($m['current_set'] ?? 0);
        if ($currentSet === 0) {
            View::json(['ok' => false, 'error' => 'Belum ada set yang aktif. Wasit wajib menekan tombol "Mulai Set" terlebih dahulu.'], 422);
        }
        if ($set !== $currentSet) {
            View::json(['ok' => false, 'error' => "Set $set sedang terkunci. Hanya Set $currentSet yang sedang aktif dan dapat diubah skornya."], 422);
        }

        // Jika set aktif sudah ditandai selesai, wasit harus mengklik "Koreksi Skor" sebelum bisa edit lagi
        if ((bool)($m['is_set_finished'] ?? false)) {
            View::json([
                'ok'    => false,
                'error' => "Set $currentSet telah diselesaikan. Klik tombol 'Koreksi Skor' jika ingin mengubah perolehan skor."
            ], 422);
        }

        $team = strtolower((string)($_POST['team'] ?? 'a'));
        if (!in_array($team, ['a', 'b'], true)) {
            $team = 'a';
        }

        $delta = ((int)($_POST['delta'] ?? 0) >= 0) ? 1 : -1;
        $system = $m['scoring_system'] ?? 'rubber_set';
        $column = "set{$set}_{$team}";

        $isFutsal = (($m['sport_type'] ?? '') === 'futsal');
        if ($isFutsal) {
            $currentVal = (int)($m[$column] ?? 0);
            $newVal = max(0, $currentVal + $delta);
            Database::query("UPDATE matches SET $column = ? WHERE id = ?", [$newVal, $m['id']]);
            $m[$column] = $newVal;

            $set1A = (int)$m['set1_a'];
            $set1B = (int)$m['set1_b'];
            $set2A = (int)$m['set2_a'];
            $set2B = (int)$m['set2_b'];
            $set3A = 0;
            $set3B = 0;

            $score1 = $set1A + $set2A;
            $score2 = $set1B + $set2B;
            Database::query("UPDATE matches SET score1 = ?, score2 = ? WHERE id = ?", [$score1, $score2, $m['id']]);

            if ($score1 > $score2) {
                $winnerPreview = 'Tim 1 Unggul';
            } elseif ($score2 > $score1) {
                $winnerPreview = 'Tim 2 Unggul';
            } else {
                $winnerPreview = 'Skor Imbang';
            }
            $canFinish = ($currentSet === 2);
        } elseif ($system === 'poin_lanjut') {
            $set1Val = (int)($m["set1_{$team}"] ?? 0);
            $set2Val = (int)($m["set2_{$team}"] ?? 0);

            if ($set === 2) {
                // Di Poin Lanjut, nilai Set 2 melanjutkan skor akhir dari Set 1
                $base = $set1Val;
                // Jika set 2 masih 0, nilai awalnya adalah sama persis dengan Set 1
                $currentVal = ($set2Val > 0) ? $set2Val : $base;
                $newVal = max($base, $currentVal + $delta);
            } else {
                // Set 1
                $currentVal = (int)($m[$column] ?? 0);
                $newVal = max(0, $currentVal + $delta);
            }

            Database::query("UPDATE matches SET $column = ? WHERE id = ?", [$newVal, $m['id']]);
            $m[$column] = $newVal;

            // Jika Set 1 diupdate dan Set 2 belum dimainkan (nilainya masih <= nilai Set 1 lama atau 0),
            // sinkronkan juga nilai awal Set 2 agar tidak pernah kembali ke 0-0
            if ($set === 1) {
                $col2 = "set2_{$team}";
                if ((int)$m[$col2] <= $currentVal) {
                    Database::query("UPDATE matches SET $col2 = ? WHERE id = ?", [$newVal, $m['id']]);
                    $m[$col2] = $newVal;
                }
            }

            $set1A = (int)$m['set1_a'];
            $set1B = (int)$m['set1_b'];
            $set2A = (int)$m['set2_a'];
            $set2B = (int)$m['set2_b'];
            $set3A = 0;
            $set3B = 0;

            // Skor ringkasan akumulasi penentu pemenang
            $score1 = ($set2A > 0) ? $set2A : $set1A;
            $score2 = ($set2B > 0) ? $set2B : $set1B;

            if ($score1 > $score2) {
                $winnerPreview = 'Tim 1 Unggul';
                $canFinish = true;
            } elseif ($score2 > $score1) {
                $winnerPreview = 'Tim 2 Unggul';
                $canFinish = true;
            } else {
                $winnerPreview = 'Poin Seri';
                $canFinish = false;
            }
        } else {
            // Rubber Set (Best of 3)
            $currentVal = (int)($m[$column] ?? 0);
            $newVal = max(0, $currentVal + $delta);

            Database::query("UPDATE matches SET $column = ? WHERE id = ?", [$newVal, $m['id']]);
            $m[$column] = $newVal;

            $set1A = (int)$m['set1_a'];
            $set1B = (int)$m['set1_b'];
            $set2A = (int)$m['set2_a'];
            $set2B = (int)$m['set2_b'];
            $set3A = (int)$m['set3_a'];
            $set3B = (int)$m['set3_b'];

            $setsA = ($set1A > $set1B ? 1 : 0) + ($set2A > $set2B ? 1 : 0) + ($set3A > $set3B ? 1 : 0);
            $setsB = ($set1B > $set1A ? 1 : 0) + ($set2B > $set2A ? 1 : 0) + ($set3B > $set3A ? 1 : 0);
            $score1 = $setsA;
            $score2 = $setsB;

            if ($setsA >= 2) {
                $winnerPreview = 'Tim 1 Menang 2 Set';
                $canFinish = true;
            } elseif ($setsB >= 2) {
                $winnerPreview = 'Tim 2 Menang 2 Set';
                $canFinish = true;
            } elseif ($setsA === 1 && $setsB === 1) {
                $winnerPreview = 'Rubber Set (Lanjut Set 3)';
                $canFinish = false;
            } else {
                $winnerPreview = 'Sedang Berlangsung';
                $canFinish = false;
            }
        }

        // Simpan juga score1 & score2 yang terupdate
        Database::query("UPDATE matches SET score1 = ?, score2 = ? WHERE id = ?", [$score1, $score2, $m['id']]);

        View::json([
            'ok'             => true,
            'set'            => $set,
            'team'           => $team,
            'value'          => $newVal,
            'set1_a'         => $set1A,
            'set1_b'         => $set1B,
            'set2_a'         => $set2A,
            'set2_b'         => $set2B,
            'set3_a'         => $set3A,
            'set3_b'         => $set3B,
            'score1'         => $score1,
            'score2'         => $score2,
            'scoring_system' => $system,
            'winner_preview' => $winnerPreview,
            'can_finish'     => $canFinish,
        ]);
    }

    /**
     * Menyelesaikan pertandingan live, menghitung pemenang akhir sesuai sistem scoring,
     * dan otomatis memajukan bracket pemenang/pecundang.
     */
    public function finish(string $id): void
    {
        $m = MatchModel::find((int)$id);
        if (!$m || $m['status'] !== 'live') {
            flash('error', 'Pertandingan tidak ditemukan atau bukan berstatus live.');
            redirect('admin/live-panel');
        }

        $system = $m['scoring_system'] ?? 'rubber_set';
        $set1A = (int)$m['set1_a'];
        $set1B = (int)$m['set1_b'];
        $set2A = (int)$m['set2_a'];
        $set2B = (int)$m['set2_b'];
        $set3A = (int)$m['set3_a'];
        $set3B = (int)$m['set3_b'];

        $outcome = MatchModel::determineOutcome($m);
        if ($outcome['error']) {
            flash('error', $outcome['error']);
            redirect('admin/live-panel');
        }

        // Simpan status finished lewat MatchModel::update() agar logika advance bracket berjalan otomatis
        MatchModel::update((int)$m['id'], [
            'team1_id'     => $m['team1_id'],
            'team2_id'     => $m['team2_id'],
            'score1'       => $outcome['score1'],
            'score2'       => $outcome['score2'],
            'status'       => 'finished',
            'winner_id'    => $outcome['winner_id'],
            'scheduled_at' => $m['scheduled_at'],
            'court'        => $m['court'],
        ]);

        if (($m['sport_type'] ?? '') === 'futsal') {
            $halfDur = (int)($m['futsal_half_duration'] ?? 5);
            $maxTime = $halfDur * 120;
            Database::query("UPDATE matches SET futsal_elapsed_seconds = ?, futsal_timer_status = 'paused', futsal_timer_updated_at = NOW() WHERE id = ?", [$maxTime, $m['id']]);

            $hasFt = Database::one("SELECT id FROM match_highlights WHERE match_id = ? AND event_type = 'full_time'", [$m['id']]);
            if (!$hasFt) {
                \App\Models\MatchHighlight::add((int)$m['id'], null, 'Full Time (Pertandingan Berakhir)', 'full_time', (string)($halfDur * 2), null, null, 2);
            }
        }

        Database::query("UPDATE matches SET current_set = 0, is_set_finished = 0 WHERE id = ?", [$m['id']]);

        flash('success', "Pertandingan telah diselesaikan! Skor akhir: {$outcome['score1']} - {$outcome['score2']}. Pemenang melaju ke babak selanjutnya.");
        redirect('admin/live-panel');
    }

    /**
     * Membatalkan status Live dan mengembalikannya ke Upcoming jika salah klik.
     */
    public function resetStatus(string $id): void
    {
        $m = Database::one('SELECT id FROM matches WHERE id = ?', [(int)$id]);
        if ($m) {
            Database::query("UPDATE matches SET status = 'upcoming', current_set = 0, is_set_finished = 0 WHERE id = ?", [$m['id']]);
            flash('success', 'Status pertandingan dikembalikan ke "Belum Mulai".');
        }
        redirect('admin/live-panel');
    }

    /**
     * Memperbarui durasi, detik berjalan, dan status timer futsal (AJAX).
     */
    public function updateTimer(string $id): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak aktif / bukan berstatus Live.'], 422);
        }

        $halfDuration = max(1, min(120, (int)($_POST['half_duration'] ?? $m['futsal_half_duration'] ?? 20)));
        $elapsedSeconds = max(0, min(14400, (int)($_POST['elapsed_seconds'] ?? $m['futsal_elapsed_seconds'] ?? 0)));
        $timerStatus = in_array($_POST['timer_status'] ?? '', ['running', 'paused', 'stopped'], true)
            ? $_POST['timer_status']
            : 'stopped';

        Database::query(
            "UPDATE matches SET
                futsal_half_duration = ?,
                futsal_elapsed_seconds = ?,
                futsal_timer_status = ?,
                futsal_timer_updated_at = NOW()
             WHERE id = ?",
            [$halfDuration, $elapsedSeconds, $timerStatus, $m['id']]
        );

        View::json([
            'ok'              => true,
            'half_duration'   => $halfDuration,
            'elapsed_seconds' => $elapsedSeconds,
            'timer_status'    => $timerStatus,
        ]);
    }

    /**
     * Menambahkan highlight pertandingan (Gol, Kartu Kuning, Kartu Merah, Pertukaran Pemain) via AJAX.
     */
    public function addHighlight(string $id): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak aktif / bukan berstatus Live.'], 422);
        }

        $teamId = (int)($_POST['team_id'] ?? 0);
        $eventType = trim((string)($_POST['event_type'] ?? 'goal'));
        $playerName = trim((string)($_POST['player_name'] ?? ''));
        $playerIn = isset($_POST['player_in']) ? trim((string)$_POST['player_in']) : null;
        $playerOut = isset($_POST['player_out']) ? trim((string)$_POST['player_out']) : null;
        $minute = trim((string)($_POST['minute'] ?? '1'));
        if ($minute === '' || $minute === '0') {
            $minute = '1';
        }

        $currentSet = (int)($m['current_set'] ?? 1);
        $period = isset($_POST['period']) && in_array((int)$_POST['period'], [1, 2], true)
            ? (int)$_POST['period']
            : (in_array($currentSet, [1, 2], true) ? $currentSet : 1);

        $isWhistle = in_array($eventType, ['half_time', 'full_time'], true);
        if (!$isWhistle && $teamId !== (int)$m['team1_id'] && $teamId !== (int)$m['team2_id']) {
            View::json(['ok' => false, 'error' => 'Tim tidak valid untuk pertandingan ini.'], 422);
        }
        $targetTeamId = $isWhistle ? null : $teamId;

        $hId = \App\Models\MatchHighlight::add((int)$m['id'], $targetTeamId, $playerName, $eventType, $minute, $playerIn, $playerOut, $period);

        if (($m['sport_type'] ?? '') === 'futsal') {
            \App\Models\MatchHighlight::recalculateFutsalScore((int)$m['id']);
        }

        $highlights = \App\Models\MatchHighlight::forMatch((int)$m['id']);
        $refreshed = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$m['id']]);

        View::json([
            'ok'         => true,
            'highlight'  => [
                'id'          => $hId,
                'team_id'     => $teamId,
                'event_type'  => $eventType,
                'player_name' => $playerName,
                'player_in'   => $playerIn,
                'player_out'  => $playerOut,
                'minute'      => $minute,
                'period'      => $period,
            ],
            'highlights' => $highlights,
            'score1'     => (int)($refreshed['score1'] ?? 0),
            'score2'     => (int)($refreshed['score2'] ?? 0),
            'set1_a'     => (int)($refreshed['set1_a'] ?? 0),
            'set1_b'     => (int)($refreshed['set1_b'] ?? 0),
            'set2_a'     => (int)($refreshed['set2_a'] ?? 0),
            'set2_b'     => (int)($refreshed['set2_b'] ?? 0),
        ]);
    }

    /**
     * Menghapus catatan highlight (Cancel / Undo) via AJAX.
     * Jika highlight yang dihapus adalah Gol futsal, skor tim otomatis berkurang.
     */
    public function deleteHighlight(string $id, string $highlightId): void
    {
        $m = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$id]);
        if (!$m) {
            View::json(['ok' => false, 'error' => 'Pertandingan tidak ditemukan.'], 404);
        }

        $hl = Database::one('SELECT * FROM match_highlights WHERE id = ? AND match_id = ?', [(int)$highlightId, $m['id']]);
        if ($hl) {
            \App\Models\MatchHighlight::delete((int)$highlightId);
        }

        if (($m['sport_type'] ?? '') === 'futsal') {
            \App\Models\MatchHighlight::recalculateFutsalScore((int)$m['id']);
        }

        $highlights = \App\Models\MatchHighlight::forMatch((int)$m['id']);
        $refreshed = Database::one('SELECT * FROM matches WHERE id = ?', [(int)$m['id']]);

        View::json([
            'ok'         => true,
            'deleted_id' => (int)$highlightId,
            'highlights' => $highlights,
            'score1'     => (int)($refreshed['score1'] ?? 0),
            'score2'     => (int)($refreshed['score2'] ?? 0),
            'set1_a'     => (int)($refreshed['set1_a'] ?? 0),
            'set1_b'     => (int)($refreshed['set1_b'] ?? 0),
            'set2_a'     => (int)($refreshed['set2_a'] ?? 0),
            'set2_b'     => (int)($refreshed['set2_b'] ?? 0),
        ]);
    }
}

