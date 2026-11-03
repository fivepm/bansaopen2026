<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Models\MatchModel;
use App\Models\Sponsor;
use App\Models\Sports;
use App\Models\Standing;

final class PublicController
{
    public function home(): void
    {
        $sports = [];
        $totalTeams = 0;
        $totalLive = 0;
        $totalFinished = 0;

        foreach (Sports::LIST as $slug => $s) {
            $isActive = Sports::isActive($slug);
            $sportStatus = Sports::status($slug);
            $teamCount = (int)Database::one('SELECT COUNT(*) c FROM teams WHERE sport_type = ?', [$slug])['c'];
            $matchCount = (int)Database::one('SELECT COUNT(*) c FROM matches WHERE sport_type = ?', [$slug])['c'];
            $liveCount = (int)Database::one("SELECT COUNT(*) c FROM matches WHERE sport_type = ? AND status = 'live'", [$slug])['c'];
            $finishedCount = (int)Database::one("SELECT COUNT(*) c FROM matches WHERE sport_type = ? AND status = 'finished'", [$slug])['c'];
            $schedule = \App\Models\Schedule::forSport($slug);

            $sports[$slug] = $s + [
                'active'           => $isActive,
                'status'           => $sportStatus,
                'teams'            => $teamCount,
                'matches'          => $matchCount,
                'live'             => $liveCount,
                'finished'         => $finishedCount,
                'system'           => Sports::system($slug),
                'schedule'         => $schedule,
                'kategori'         => $schedule['kategori'] ?? '',
                'date_range'       => $schedule['date_range'] ?? '',
                'jam_pertandingan' => $schedule['jam_pertandingan'] ?? '',
                'venue'            => $schedule['venue'] ?? '',
            ];

            $totalTeams += $teamCount;
            $totalLive += $liveCount;
            $totalFinished += $finishedCount;
        }

        $coreCommittees = \App\Models\Committee::getCoreCommittees();

        View::render('public/home', [
            'title'          => 'Beranda',
            'sports'         => $sports,
            'coreCommittees' => $coreCommittees,
            'sponsors'       => Sponsor::active(),
            'overview'       => [
                'sports'   => count(Sports::LIST),
                'teams'    => $totalTeams,
                'live'     => $totalLive,
                'finished' => $totalFinished,
            ],
        ]);
    }

    /**
     * Halaman Publik Susunan Kepanitiaan Turnamen Bansa Open.
     */
    public function committees(): void
    {
        $coreCommittees = \App\Models\Committee::getCoreCommittees();
        $divisionCommittees = \App\Models\Committee::getDivisionCommittees();
        $structure = \App\Models\Committee::getStructure();
        $totalCommittees = (int)Database::one('SELECT COUNT(*) c FROM committees')['c'];

        View::render('public/committees', [
            'title'              => 'Susunan Kepanitiaan',
            'coreCommittees'     => $coreCommittees,
            'divisionCommittees' => $divisionCommittees,
            'structure'          => $structure,
            'totalCommittees'    => $totalCommittees,
        ]);
    }

    public function sport(string $sport): void
    {
        if (!$this->guard($sport)) {
            return;
        }
        $system = Sports::system($sport);
        $categories = Sports::categoriesFor($sport);
        $schedule = \App\Models\Schedule::forSport($sport);

        $teams = Database::all('SELECT * FROM teams WHERE sport_type = ? ORDER BY category, name ASC', [$sport]);
        $liveMatches = MatchModel::byStatus($sport, 'live', 'm.round, m.position');
        $finishedMatches = MatchModel::byStatus($sport, 'finished', 'm.updated_at DESC');

        $brackets = [];
        $standings = [];
        $groupMatches = [];

        if ($system === 'knockout') {
            foreach ($categories as $catKey => $catLabel) {
                $brackets[$catKey] = MatchModel::twoSidedBracket($sport, $catKey);
            }
        } else {
            $standings = Standing::forSport($sport);
            $groupMatches = MatchModel::groupMatches($sport);
        }

        View::render('public/sport', [
            'title'            => sport_label($sport),
            'sport'            => $sport,
            'system'           => $system,
            'categories'       => $categories,
            'schedule'         => $schedule,
            'kategori'         => $schedule['kategori'] ?? '',
            'date_range'       => $schedule['date_range'] ?? '',
            'jam_pertandingan' => $schedule['jam_pertandingan'] ?? '',
            'venue'            => $schedule['venue'] ?? '',
            'schedule_notes'   => $schedule['notes'] ?? '',
            'teams'            => $teams,
            'liveMatches'      => $liveMatches,
            'finishedMatches'  => $finishedMatches,
            'brackets'         => $brackets,
            'standings'        => $standings,
            'groupMatches'     => $groupMatches,
            'hideTabs'         => true,
            'fullWidth'        => true,
        ]);
    }

    /**
     * Endpoint API JSON untuk polling real-time data cabor setiap 1 detik.
     * Mengembalikan data skor terkini, HTML komponen Live, Hasil, dan Bagan Bracket secara utuh.
     */
    public function liveData(string $sport): void
    {
        if (!Sports::exists($sport)) {
            View::json(['ok' => false, 'error' => 'Cabang olahraga tidak ditemukan.'], 404);
        }

        $system = Sports::system($sport);
        $categories = Sports::categoriesFor($sport);

        // 1. Ambil pertandingan yang sedang LIVE
        $liveMatches = Database::all(
            MatchModel::SELECT . " WHERE m.sport_type = ? AND m.status = 'live' ORDER BY m.round ASC, m.position ASC",
            [$sport]
        );

        // 2. Ambil pertandingan yang sudah SELESAI
        $finishedMatches = Database::all(
            MatchModel::SELECT . " WHERE m.sport_type = ? AND m.status = 'finished' ORDER BY m.updated_at DESC",
            [$sport]
        );

        // 3. Render HTML tab Live & tab Hasil
        $liveHtml = View::partial('partials/live_matches_content', [
            'liveMatches' => $liveMatches,
            'sport'       => $sport,
        ]);

        $finishedHtml = View::partial('partials/finished_matches_content', [
            'finishedMatches' => $finishedMatches,
            'sport'           => $sport,
        ]);

        // 4. Render HTML Bagan Pertandingan per kategori (Knockout)
        $bracketsHtml = [];
        if ($system === 'knockout') {
            foreach ($categories as $catKey => $catLabel) {
                $bracket = MatchModel::twoSidedBracket($sport, $catKey);
                $bracketsHtml[$catKey] = View::partial('partials/two_sided_bracket', [
                    'bracket' => $bracket,
                    'catKey'  => $catKey,
                    'sport'   => $sport,
                ]);
            }
        }

        // 5. Identifikasi status dan hash untuk sinkronisasi DOM presisi
        $liveIds = array_map(static fn($m) => (int)$m['id'], $liveMatches);
        $finishedIds = array_map(static fn($m) => (int)$m['id'], $finishedMatches);

        $statusHash = md5(json_encode([$liveIds, $finishedIds], JSON_THROW_ON_ERROR));
        $liveScoreHash = md5(json_encode($liveMatches, JSON_THROW_ON_ERROR));
        $bracketHash = md5(json_encode($bracketsHtml, JSON_THROW_ON_ERROR));

        View::json([
            'ok'              => true,
            'sport'           => $sport,
            'live_count'      => count($liveMatches),
            'finished_count'  => count($finishedMatches),
            'live_ids'        => $liveIds,
            'finished_ids'    => $finishedIds,
            'status_hash'     => $statusHash,
            'live_score_hash' => $liveScoreHash,
            'bracket_hash'    => $bracketHash,
            'live_html'       => $liveHtml,
            'finished_html'   => $finishedHtml,
            'brackets_html'   => $bracketsHtml,
            'matches'         => $liveMatches,
            'timestamp'       => time(),
        ]);
    }

    public function bracket(string $sport): void
    {
        redirect("$sport#bracket");
    }

    public function standings(string $sport): void
    {
        redirect("$sport#klasemen");
    }

    public function live(string $sport): void
    {
        redirect("$sport#live");
    }

    public function results(string $sport): void
    {
        redirect("$sport#hasil");
    }

    /** Validasi cabor; tampilkan "Segera Hadir" bila cabor belum punya data. */
    private function guard(string $sport): bool
    {
        Sports::require($sport);
        if (!Sports::isActive($sport)) {
            View::render('public/coming_soon', ['title' => sport_label($sport), 'sport' => $sport, 'hideTabs' => true]);
            return false;
        }
        return true;
    }
}
