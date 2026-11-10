<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\GroupModel;
use App\Models\MatchModel;
use App\Models\Sports;

final class MatchController
{
    private const STATUSES = ['upcoming', 'live', 'finished'];

    public function index(string $sport): void
    {
        Sports::require($sport);
        $system = Sports::system($sport);
        $categories = Sports::categoriesFor($sport);
        $catFilter = $_GET['cat'] ?? Sports::defaultCategory($sport);
        if (!isset($categories[$catFilter])) {
            $catFilter = Sports::defaultCategory($sport);
        }

        $twoSided = ($system === 'knockout')
            ? MatchModel::twoSidedBracket($sport, $catFilter)
            : ['totalRounds' => 0, 'left' => [], 'right' => [], 'final' => null, 'thirdPlace' => null];

        $teams = Database::all(
            'SELECT id, name FROM teams WHERE sport_type = ? AND (category = ? OR category = "umum") ORDER BY name ASC',
            [$sport, $catFilter]
        );

        $refereesList = Database::all('
            SELECT DISTINCT referee_name FROM (
                SELECT referee_main AS referee_name FROM matches WHERE referee_main IS NOT NULL AND referee_main != ""
                UNION
                SELECT referee_line1 AS referee_name FROM matches WHERE referee_line1 IS NOT NULL AND referee_line1 != ""
                UNION
                SELECT referee_line2 AS referee_name FROM matches WHERE referee_line2 IS NOT NULL AND referee_line2 != ""
            ) AS ref_pool ORDER BY referee_name ASC
        ');
        $refereesList = array_values(array_filter(array_column($refereesList, 'referee_name')));

        View::render('admin/matches/index', [
            'title'        => 'Pertandingan ' . sport_label($sport),
            'sport'        => $sport,
            'system'       => $system,
            'categories'   => $categories,
            'catFilter'    => $catFilter,
            'twoSided'     => $twoSided,
            'total'        => $twoSided['totalRounds'],
            'teams'        => $teams,
            'refereesList' => $refereesList,
            'groups'       => $system === 'group' ? GroupModel::forSport($sport) : [],
            'groupMatches' => $system === 'group' ? MatchModel::groupMatches($sport) : [],
            'playoffs'     => $system === 'group' ? \App\Models\Standing::getPlayoffMatches($sport) : [],
        ], 'layouts/admin');
    }

    public function generate(string $sport): void
    {
        Sports::require($sport);
        $slots = (int)($_POST['slots'] ?? 0);
        if (!in_array($slots, [4, 8, 16, 32], true)) {
            flash('error', 'Ukuran bracket tidak valid (pilih 4, 8, 16, atau 32 peserta).');
            redirect("admin/$sport/matches");
        }

        $categories = Sports::categoriesFor($sport);
        $cat = $_POST['category'] ?? Sports::defaultCategory($sport);
        if (!isset($categories[$cat])) {
            $cat = Sports::defaultCategory($sport);
        }

        MatchModel::generateTwoSidedKnockout($sport, $cat, $slots);
        flash('success', "Bagan Kiri-Kanan $slots peserta untuk kategori {$categories[$cat]} berhasil dibuat.");
        redirect("admin/$sport/matches?cat=$cat");
    }

    public function reset(string $sport): void
    {
        Sports::require($sport);
        $cat = $_POST['category'] ?? null;
        MatchModel::resetKnockout($sport, $cat);
        flash('success', 'Bagan pertandingan sistem gugur telah dihapus.');
        redirect("admin/$sport/matches" . ($cat ? "?cat=$cat" : ''));
    }

    public function edit(string $sport, string $id): void
    {
        $match = $this->find($sport, (int)$id);
        $cat = $match['category'] ?? 'umum';
        
        // Filter tim sesuai cabor & kategori pertandingan
        $teams = Database::all(
            'SELECT id, name FROM teams WHERE sport_type = ? AND (category = ? OR category = "umum") ORDER BY name',
            [$sport, $cat]
        );

        $refereesList = Database::all('
            SELECT DISTINCT referee_name FROM (
                SELECT referee_main AS referee_name FROM matches WHERE referee_main IS NOT NULL AND referee_main != ""
                UNION
                SELECT referee_line1 AS referee_name FROM matches WHERE referee_line1 IS NOT NULL AND referee_line1 != ""
                UNION
                SELECT referee_line2 AS referee_name FROM matches WHERE referee_line2 IS NOT NULL AND referee_line2 != ""
            ) AS ref_pool ORDER BY referee_name ASC
        ');
        $refereesList = array_values(array_filter(array_column($refereesList, 'referee_name')));

        View::render('admin/matches/edit', [
            'title'        => 'Atur Pertandingan',
            'sport'        => $sport,
            'match'        => $match,
            'teams'        => $teams,
            'refereesList' => $refereesList,
            'total'        => MatchModel::knockoutRounds($sport, $cat),
        ], 'layouts/admin');
    }

    public function update(string $sport, string $id): void
    {
        $match = $this->find($sport, (int)$id);
        $isGroup = ($match['stage'] === 'group');

        // Pertandingan grup: susunan tim ditetapkan oleh jadwal round-robin
        $t1 = $isGroup ? (int)$match['team1_id'] : ((int)($_POST['team1_id'] ?? 0) ?: null);
        $t2 = $isGroup ? (int)$match['team2_id'] : ((int)($_POST['team2_id'] ?? 0) ?: null);
        if (!$isGroup) {
            $ok = Database::all('SELECT id FROM teams WHERE sport_type = ?', [$sport]);
            $ok = array_map('intval', array_column($ok, 'id'));
            foreach ([&$t1, &$t2] as &$t) {
                if ($t && !in_array($t, $ok, true)) {
                    $t = null;
                }
            }
            unset($t);
        }

        $status = in_array($_POST['status'] ?? '', self::STATUSES, true) ? $_POST['status'] : 'upcoming';
        $s1 = max(0, (int)($_POST['score1'] ?? 0));
        $s2 = max(0, (int)($_POST['score2'] ?? 0));
        $back = "admin/$sport/matches/{$match['id']}/edit";

        if ($t1 && $t1 === $t2) {
            flash('error', 'Tim 1 dan Tim 2 tidak boleh sama.');
            redirect($back);
        }
        if ($status !== 'upcoming' && (!$t1 || !$t2)) {
            flash('error', 'Kedua tim harus ditentukan sebelum pertandingan dimulai.');
            redirect($back);
        }
        if ($status === 'finished' && $s1 === $s2 && !$isGroup) {
            flash('error', 'Skor akhir sistem gugur tidak boleh seri; harus ada pemenang.');
            redirect($back);
        }

        $sched = trim($_POST['scheduled_at'] ?? '');
        $court = trim($_POST['court'] ?? '');
        $refMain  = trim($_POST['referee_main'] ?? '');
        $refLine1 = trim($_POST['referee_line1'] ?? '');
        $refLine2 = trim($_POST['referee_line2'] ?? '');
        $refMain  = $refMain !== '' ? mb_substr($refMain, 0, 100) : null;
        $refLine1 = $refLine1 !== '' ? mb_substr($refLine1, 0, 100) : null;
        $refLine2 = $refLine2 !== '' ? mb_substr($refLine2, 0, 100) : null;

        $scoringSystem = in_array($_POST['scoring_system'] ?? '', ['poin_lanjut', 'rubber_set'], true)
            ? $_POST['scoring_system']
            : ($match['scoring_system'] ?? 'rubber_set');

        $set1A = max(0, (int)($_POST['set1_a'] ?? $match['set1_a'] ?? 0));
        $set1B = max(0, (int)($_POST['set1_b'] ?? $match['set1_b'] ?? 0));
        $set2A = max(0, (int)($_POST['set2_a'] ?? $match['set2_a'] ?? 0));
        $set2B = max(0, (int)($_POST['set2_b'] ?? $match['set2_b'] ?? 0));
        $set3A = max(0, (int)($_POST['set3_a'] ?? $match['set3_a'] ?? 0));
        $set3B = max(0, (int)($_POST['set3_b'] ?? $match['set3_b'] ?? 0));

        Database::query(
            'UPDATE matches SET scoring_system=?, set1_a=?, set1_b=?, set2_a=?, set2_b=?, set3_a=?, set3_b=? WHERE id=?',
            [$scoringSystem, $set1A, $set1B, $set2A, $set2B, $set3A, $set3B, $match['id']]
        );

        MatchModel::update((int)$match['id'], [
            'team1_id'      => $t1,
            'team2_id'      => $t2,
            'score1'        => $s1,
            'score2'        => $s2,
            'status'        => $status,
            'scheduled_at'  => $sched !== '' ? date('Y-m-d H:i:s', strtotime($sched)) : null,
            'court'         => $court !== '' ? mb_substr($court, 0, 30) : null,
            'referee_main'  => $refMain,
            'referee_line1' => $refLine1,
            'referee_line2' => $refLine2,
        ]);

        $catParam = !empty($match['category']) ? "?cat={$match['category']}" : '';
        flash('success', 'Pertandingan diperbarui.');
        redirect("admin/$sport/matches$catParam");
    }

    /**
     * Modal Form: Ubah status, sistem set, dan jadwal pertandingan (Hari, Tanggal, Jam, Lapangan).
     * Opsi status dalam modal ini: 'upcoming' (Belum Mulai) dan 'finished' (Selesai).
     * Opsi 'live' dipisahkan ke tombol mandiri "Set Live" di kotak pertandingan.
     */
    public function updateStatusAndSystem(string $sport, string $id): void
    {
        Sports::require($sport);
        $m = $this->find($sport, (int)$id);
        $catParam = !empty($m['category']) ? "?cat={$m['category']}" : '';

        // Status yang diizinkan lewat modal: 'upcoming' dan 'finished' (jika ada 'live' tetap diproses)
        $status = in_array($_POST['status'] ?? '', ['upcoming', 'finished', 'live'], true) ? $_POST['status'] : 'upcoming';
        $scoringSystem = in_array($_POST['scoring_system'] ?? '', ['poin_lanjut', 'rubber_set'], true)
            ? $_POST['scoring_system']
            : ($m['scoring_system'] ?? 'rubber_set');

        $futsalHalfDuration = isset($_POST['futsal_half_duration']) ? max(1, min(60, (int)$_POST['futsal_half_duration'])) : (int)($m['futsal_half_duration'] ?? 5);
        $stage = $m['stage'] ?? 'group';
        if ($sport === 'futsal' && isset($_POST['stage'])) {
            $stage = ($_POST['stage'] === 'playoff') ? 'knockout' : 'group';
        }

        // Pengaturan Jadwal Pertandingan (Hari, Tanggal, dan Jam) & Lapangan
        $schedDate = trim($_POST['schedule_date'] ?? '');
        $schedTime = trim($_POST['schedule_time'] ?? '');
        $scheduledAt = null;
        if ($schedDate !== '') {
            $timePart = $schedTime !== '' ? $schedTime : '00:00';
            $scheduledAt = date('Y-m-d H:i:s', strtotime("$schedDate $timePart"));
        }
        $court = trim($_POST['court'] ?? '');
        $court = $court !== '' ? mb_substr($court, 0, 30) : null;

        // Pengaturan Wasit Pertandingan (Wasit Utama 1 orang, Wasit Garis 2 orang)
        $refMain  = isset($_POST['referee_main']) ? trim((string)$_POST['referee_main']) : null;
        $refLine1 = isset($_POST['referee_line1']) ? trim((string)$_POST['referee_line1']) : null;
        $refLine2 = isset($_POST['referee_line2']) ? trim((string)$_POST['referee_line2']) : null;

        $refMain  = ($refMain !== null && $refMain !== '') ? mb_substr($refMain, 0, 100) : ($refMain !== null ? null : ($m['referee_main'] ?? null));
        $refLine1 = ($refLine1 !== null && $refLine1 !== '') ? mb_substr($refLine1, 0, 100) : ($refLine1 !== null ? null : ($m['referee_line1'] ?? null));
        $refLine2 = ($refLine2 !== null && $refLine2 !== '') ? mb_substr($refLine2, 0, 100) : ($refLine2 !== null ? null : ($m['referee_line2'] ?? null));

        // Jika Live (jika dikirim): kedua tim harus ditentukan
        if ($status === 'live') {
            if (empty($m['team1_id']) || empty($m['team2_id'])) {
                flash('error', 'Kedua tim harus ditentukan terlebih dahulu sebelum mengubah status menjadi Live.');
                redirect("admin/$sport/matches$catParam");
            }

            Database::query('UPDATE matches SET status = ?, scoring_system = ?, stage = ?, futsal_half_duration = ?, scheduled_at = ?, court = ?, referee_main = ?, referee_line1 = ?, referee_line2 = ?, current_set = 0, is_set_finished = 0 WHERE id = ?', [
                $status,
                $scoringSystem,
                $stage,
                $futsalHalfDuration,
                $scheduledAt,
                $court,
                $refMain,
                $refLine1,
                $refLine2,
                $m['id']
            ]);

            flash('success', "Pertandingan #{$m['round']}-{$m['position']} diubah menjadi LIVE! Silakan atur skor di Live Panel.");
            redirect('admin/live-panel');
        }

        // Jika Selesai: simpan set skor jika ada, lalu hitung skor & pemenang
        if ($status === 'finished') {
            if (empty($m['team1_id']) || empty($m['team2_id'])) {
                flash('error', 'Kedua tim harus ditentukan terlebih dahulu sebelum menyelesaikan pertandingan.');
                redirect("admin/$sport/matches$catParam");
            }

            if ($sport === 'futsal') {
                // Futsal: skor mutlak berasal dari highlight gol
                $refreshed = $this->find($sport, (int)$id);
                $s1 = (int)($refreshed['score1'] ?? 0);
                $s2 = (int)($refreshed['score2'] ?? 0);
                $winnerId = ($s1 > $s2) ? (int)$m['team1_id'] : (($s2 > $s1) ? (int)$m['team2_id'] : null);

                Database::query(
                    'UPDATE matches SET stage = ?, futsal_half_duration = ?, scheduled_at = ?, court = ?, referee_main = ?, referee_line1 = ?, referee_line2 = ?, status = ?, score1 = ?, score2 = ?, winner_id = ? WHERE id = ?',
                    [$stage, $futsalHalfDuration, $scheduledAt, $court, $refMain, $refLine1, $refLine2, 'finished', $s1, $s2, $winnerId, $m['id']]
                );

                if (!empty($m['group_id'])) {
                    \App\Models\Standing::recalculate((int)$m['group_id']);
                }

                flash('success', "Pertandingan Futsal diselesaikan dengan skor $s1 - $s2.");
                redirect("admin/$sport/matches$catParam");
            }

            $set1A = isset($_POST['set1_a']) ? max(0, (int)$_POST['set1_a']) : (int)($m['set1_a'] ?? 0);
            $set1B = isset($_POST['set1_b']) ? max(0, (int)$_POST['set1_b']) : (int)($m['set1_b'] ?? 0);
            $set2A = isset($_POST['set2_a']) ? max(0, (int)$_POST['set2_a']) : (int)($m['set2_a'] ?? 0);
            $set2B = isset($_POST['set2_b']) ? max(0, (int)$_POST['set2_b']) : (int)($m['set2_b'] ?? 0);
            $set3A = isset($_POST['set3_a']) ? max(0, (int)$_POST['set3_a']) : (int)($m['set3_a'] ?? 0);
            $set3B = isset($_POST['set3_b']) ? max(0, (int)$_POST['set3_b']) : (int)($m['set3_b'] ?? 0);

            Database::query(
                'UPDATE matches SET scoring_system = ?, scheduled_at = ?, court = ?, referee_main = ?, referee_line1 = ?, referee_line2 = ?, set1_a = ?, set1_b = ?, set2_a = ?, set2_b = ?, set3_a = ?, set3_b = ? WHERE id = ?',
                [$scoringSystem, $scheduledAt, $court, $refMain, $refLine1, $refLine2, $set1A, $set1B, $set2A, $set2B, $set3A, $set3B, $m['id']]
            );

            // Muat ulang data terbaru untuk kalkulasi pemenang
            $refreshed = $this->find($sport, (int)$id);
            $outcome = MatchModel::determineOutcome($refreshed);

            if ($outcome['error']) {
                flash('error', $outcome['error']);
                redirect("admin/$sport/matches$catParam");
            }

            // Simpan status finished lewat MatchModel::update() agar logika advance bracket berjalan otomatis
            MatchModel::update((int)$m['id'], [
                'team1_id'      => $m['team1_id'],
                'team2_id'      => $m['team2_id'],
                'score1'        => $outcome['score1'],
                'score2'        => $outcome['score2'],
                'status'        => 'finished',
                'winner_id'     => $outcome['winner_id'],
                'scheduled_at'  => $scheduledAt,
                'court'         => $court,
                'referee_main'  => $refMain,
                'referee_line1' => $refLine1,
                'referee_line2' => $refLine2,
            ]);

            flash('success', "Pertandingan diselesaikan! Pemenang berhasil melaju otomatis ke babak berikutnya.");
            redirect("admin/$sport/matches$catParam");
        }

        // Jika Belum Mulai (upcoming):
        Database::query('UPDATE matches SET scoring_system = ?, stage = ?, futsal_half_duration = ?, scheduled_at = ?, court = ?, referee_main = ?, referee_line1 = ?, referee_line2 = ? WHERE id = ?', [
            $scoringSystem,
            $stage,
            $futsalHalfDuration,
            $scheduledAt,
            $court,
            $refMain,
            $refLine1,
            $refLine2,
            $m['id']
        ]);

        MatchModel::update((int)$m['id'], [
            'team1_id'      => $m['team1_id'],
            'team2_id'      => $m['team2_id'],
            'score1'        => 0,
            'score2'        => 0,
            'status'        => 'upcoming',
            'winner_id'     => null,
            'scheduled_at'  => $scheduledAt,
            'court'         => $court,
            'referee_main'  => $refMain,
            'referee_line1' => $refLine1,
            'referee_line2' => $refLine2,
        ]);

        flash('success', 'Pengaturan jadwal, sistem set, status, dan wasit berhasil disimpan.');
        redirect("admin/$sport/matches$catParam");
    }

    /**
     * Modal Form: Atur Wasit Pertandingan (Wasit Utama 1 orang, Wasit Garis 2 orang).
     */
    public function updateReferees(string $sport, string $id): void
    {
        Sports::require($sport);
        $m = $this->find($sport, (int)$id);
        $catParam = !empty($m['category']) ? "?cat={$m['category']}" : '';

        $refMain  = trim($_POST['referee_main'] ?? '');
        $refLine1 = trim($_POST['referee_line1'] ?? '');
        $refLine2 = trim($_POST['referee_line2'] ?? '');

        $refMain  = $refMain !== '' ? mb_substr($refMain, 0, 100) : null;
        $refLine1 = $refLine1 !== '' ? mb_substr($refLine1, 0, 100) : null;
        $refLine2 = $refLine2 !== '' ? mb_substr($refLine2, 0, 100) : null;

        Database::query(
            'UPDATE matches SET referee_main = ?, referee_line1 = ?, referee_line2 = ? WHERE id = ?',
            [$refMain, $refLine1, $refLine2, $m['id']]
        );

        if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax'])) {
            View::json([
                'ok' => true,
                'message' => "Susunan wasit untuk Pertandingan #{$m['round']}-{$m['position']} berhasil disimpan.",
                'referee_main' => $refMain,
                'referee_line1' => $refLine1,
                'referee_line2' => $refLine2,
            ]);
            return;
        }

        $redirectTo = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : "admin/$sport/matches$catParam";
        flash('success', "Susunan wasit untuk Pertandingan #{$m['round']}-{$m['position']} berhasil disimpan.");
        redirect($redirectTo);
    }

    /**
     * Modal Form: Atur Tim (Khusus Babak Penyisihan / First Round).
     * Babak lanjutan (Perempat Final, Semifinal, Final, Perebutan Juara 3)
     * TIDAK BOLEH diinput manual dan diisi otomatis dari pemenang babak sebelumnya.
     */
    public function updateTeams(string $sport, string $id): void
    {
        Sports::require($sport);
        $m = $this->find($sport, (int)$id);
        $catParam = !empty($m['category']) ? "?cat={$m['category']}" : '';

        // Validasi ketat babak penyisihan
        if ($m['stage'] === 'knockout') {
            $isFirstRound = ((int)$m['round'] === 1 && !in_array($m['bracket_side'], ['final', 'third_place'], true));
            if (!$isFirstRound) {
                flash('error', 'Tim hanya dapat diatur manual pada Babak Penyisihan (First Round). Babak lanjutan diisi otomatis oleh pemenang babak sebelumnya.');
                redirect("admin/$sport/matches$catParam");
            }
        }

        $t1 = (int)($_POST['team1_id'] ?? 0) ?: null;
        $t2 = (int)($_POST['team2_id'] ?? 0) ?: null;

        if ($t1 && $t1 === $t2) {
            flash('error', 'Tim A dan Tim B tidak boleh sama.');
            redirect("admin/$sport/matches$catParam");
        }

        Database::query('UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ?', [$t1, $t2, $m['id']]);

        flash('success', 'Susunan tim babak penyisihan berhasil disimpan.');
        redirect("admin/$sport/matches$catParam");
    }

    /**
     * Mengubah status pertandingan menjadi Live agar bisa diproses langsung di Live Panel.
     */
    public function setLive(string $sport, string $id): void
    {
        Sports::require($sport);
        $m = $this->find($sport, (int)$id);

        if (!$m['team1_id'] || !$m['team2_id']) {
            flash('error', 'Kedua tim harus ditentukan terlebih dahulu sebelum memulai pertandingan.');
            redirect("admin/$sport/matches");
        }

        Database::query("UPDATE matches SET status = 'live', current_set = 0, is_set_finished = 0 WHERE id = ?", [$m['id']]);
        flash('success', "Pertandingan telah diubah menjadi LIVE! Silakan buka Live Panel untuk mengupdate skor.");
        redirect('admin/live-panel');
    }

    /** AJAX: tambah/kurangi skor langsung dari daftar. POST side=1|2, delta=+1|-1 */
    public function quickScore(string $sport, string $id): void
    {
        $m = $this->find($sport, (int)$id);
        $side = (int)($_POST['side'] ?? 0);
        $delta = (int)($_POST['delta'] ?? 0) >= 0 ? 1 : -1;
        if (!in_array($side, [1, 2], true) || $m['status'] !== 'live') {
            View::json(['ok' => false, 'error' => 'Skor hanya bisa diubah saat pertandingan berlangsung.'], 422);
        }
        $col = "score$side";
        $new = max(0, (int)$m[$col] + $delta);
        Database::query("UPDATE matches SET $col = ? WHERE id = ?", [$new, $m['id']]);
        $m[$col] = $new;
        View::json(['ok' => true, 'score1' => (int)$m['score1'], 'score2' => (int)$m['score2']]);
    }

    private function find(string $sport, int $id): array
    {
        Sports::require($sport);
        $m = MatchModel::find($id, $sport);
        if (!$m) {
            http_response_code(404);
            exit('Pertandingan tidak ditemukan.');
        }
        return $m;
    }
}
