<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;

/** Escape output HTML. */
function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** Base path aplikasi (mis. "/bansaopen2026/public" atau ""). */
function base_path(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    return rtrim($dir, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** URL lengkap / absolut aplikasi web Bansa Open (diambil dari .env APP_URL). */
function app_url(string $path = ''): string
{
    $envUrl = trim((string)($_ENV['APP_URL'] ?? ''));
    if ($envUrl === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'bansaopen2026.test';
        $envUrl = $scheme . '://' . $host . base_path();
    }
    $envUrl = rtrim($envUrl, '/');
    return $path !== '' ? $envUrl . '/' . ltrim($path, '/') : $envUrl;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function flash(?string $type = null, ?string $msg = null): ?array
{
    if ($type !== null && $msg !== null) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function sport_label(string $s): string
{
    return \App\Models\Sports::LIST[$s]['label'] ?? $s;
}

/** Path URL halaman login admin (rahasia, diatur lewat .env ADMIN_LOGIN_PATH). */
function admin_login_path(): string
{
    $p = trim((string)($_ENV['ADMIN_LOGIN_PATH'] ?? ''), '/');
    return $p !== '' ? $p : 'admin-bansa-login';
}

function category_label(string $cat): string
{
    return [
        'ganda_putra' => 'Ganda Putra',
        'ganda_putri' => 'Ganda Putri',
        'umum'        => 'Umum',
    ][$cat] ?? $cat;
}

/** Label babak/grup sebuah pertandingan, mis. "Semi Final (Bagan Kiri)" atau "Grand Final". */
function match_stage_label(array $m, int $knockoutRounds): string
{
    if (($m['stage'] ?? 'knockout') === 'group') {
        $prefix = (($m['sport_type'] ?? '') === 'futsal') ? 'Match ke-' : 'Hari ke-';
        return ($m['group_name'] ?? 'Grup') . ' · ' . $prefix . (int)$m['round'];
    }
    $side = $m['bracket_side'] ?? 'left';
    if ($side === 'third_place') {
        return 'Perebutan Juara 3';
    }
    if ($side === 'final') {
        return 'Grand Final';
    }
    $rName = round_name((int)$m['round'], $knockoutRounds);
    $sideLabel = match ($side) {
        'left' => 'Bagan Kiri',
        'right' => 'Bagan Kanan',
        default => '',
    };
    return $sideLabel ? "$rName ($sideLabel)" : $rName;
}

function status_label(string $s): string
{
    return ['upcoming' => 'Belum Mulai', 'live' => 'Sedang Berlangsung', 'finished' => 'Selesai'][$s] ?? $s;
}

function round_name(int $round, int $totalRounds): string
{
    return match ($totalRounds - $round) {
        0 => 'Final',
        1 => 'Semi Final',
        2 => 'Perempat Final',
        3 => 'Babak 16 Besar',
        4 => 'Babak 32 Besar',
        5 => 'Babak 64 Besar',
        default => 'Babak Penyisihan' . ($totalRounds - $round > 5 ? ' ' . $round : ''),
    };
}

/**
 * Mengembalikan nama babak pertandingan spesifik untuk jadwal & informasi urutan main.
 */
function match_round_name(array $m, int $totalRounds): string
{
    $side = $m['bracket_side'] ?? 'left';
    if ($side === 'third_place') {
        return 'Perebutan Juara 3';
    }
    if ($side === 'final') {
        return 'Grand Final';
    }
    if (($m['stage'] ?? 'knockout') === 'group') {
        return ($m['group_name'] ?? 'Fase Grup') . ' · Match ' . (int)($m['round'] ?? 1);
    }
    $diff = $totalRounds - (int)($m['round'] ?? 1);
    return match ($diff) {
        0 => 'Grand Final',
        1 => 'Semi Final',
        2 => 'Perempat Final',
        3 => 'Babak 16 Besar',
        4 => 'Babak 32 Besar',
        5 => 'Babak 64 Besar',
        default => 'Babak Penyisihan ' . (int)($m['round'] ?? 1),
    };
}

/**
 * Format nama pertandingan resmi urutan main (contoh: "Match 1-1 - Babak 16 Besar").
 */
function match_order_name(array $m, int $seqIndex, int $totalRounds): string
{
    $roundName = match_round_name($m, $totalRounds);
    $round = (int)($m['round'] ?? 1);
    $pos = (int)($m['position'] ?? 1);
    return "Match {$round}-{$pos} - {$roundName}";
}

function fmt_dt(?string $dt): string
{
    if (!$dt) {
        return 'Jadwal belum ditentukan';
    }
    $ts = strtotime($dt);
    if (!$ts) {
        return 'Jadwal belum ditentukan';
    }
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $day = $days[(int)date('w', $ts)];
    $d = date('d', $ts);
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $m = $months[(int)date('n', $ts) - 1];
    $y = date('Y', $ts);
    $time = date('H:i', $ts);

    if ($time === '00:00' && str_ends_with($dt, '00:00:00')) {
        return "$day, $d $m $y";
    }
    return "$day, $d $m $y · $time WIB";
}

/**
 * Memecah string daftar anggota tim (yang dipisahkan koma, titik koma, atau baris baru)
 * menjadi array nama anggota yang bersih untuk ditampilkan per baris.
 * @return string[]
 */
function parse_team_members(?string $members): array
{
    if ($members === null || trim($members) === '') {
        return [];
    }
    $items = preg_split('/[,\r\n;]+/', $members);
    $result = [];
    foreach ($items as $item) {
        $trimmed = trim($item);
        if ($trimmed !== '') {
            $result[] = $trimmed;
        }
    }
    return $result;
}

function is_admin(): bool
{
    return Auth::check();
}

/**
 * Mengembalikan placeholder teks nama tim jika slot tim masih kosong / belum ditentukan.
 * - Futsal / Group Playoff:
 *     - Final: Slot 1 => "Juara Grup A", Slot 2 => "Juara Grup B"
 *     - Perebutan Juara 3: Slot 1 => "Runner Up Grup A", Slot 2 => "Runner Up Grup B"
 * - Knockout (Badminton & Pingpong):
 *     - Final: Slot 1 => "Winner Semifinal 1", Slot 2 => "Winner Semifinal 2"
 *     - Perebutan Juara 3: Slot 1 => "Runner-up Semifinal 1", Slot 2 => "Runner-up Semifinal 2"
 *     - Round > 1: "Winner Match #<round-1>-<prev_pos>"
 *     - Round <= 1 / Group Biasa: "TBD"
 */
function match_team_placeholder(array $m, int $slotNum, ?int $totalRounds = null): string
{
    $sport = $m['sport_type'] ?? '';
    $isGroupSystem = ($sport === 'futsal' || \App\Models\Sports::system($sport) === 'group');
    $stage = $m['stage'] ?? 'knockout';
    $bracketSide = $m['bracket_side'] ?? 'left';

    // 1. Cabor Futsal / Sistem Group Playoff
    if ($isGroupSystem) {
        if ($bracketSide === 'final') {
            return ($slotNum === 1) ? 'Juara Grup A' : 'Juara Grup B';
        }
        if ($bracketSide === 'third_place') {
            return ($slotNum === 1) ? 'Runner Up Grup A' : 'Runner Up Grup B';
        }
        return 'TBD';
    }

    // 2. Cabor Sistem Gugur (Badminton & Tenis Meja)
    if ($stage === 'knockout') {
        if ($bracketSide === 'final') {
            return ($slotNum === 1) ? 'Winner Semifinal 1' : 'Winner Semifinal 2';
        }
        if ($bracketSide === 'third_place') {
            return ($slotNum === 1) ? 'Runner-up Semifinal 1' : 'Runner-up Semifinal 2';
        }

        $round = (int)($m['round'] ?? 1);
        if ($round <= 1) {
            return 'Menunggu penentuan dari panitia';
        }

        if ($totalRounds === null && !empty($m['sport_type'])) {
            $cat = $m['category'] ?? 'umum';
            $totalRounds = \App\Models\MatchModel::knockoutRounds($m['sport_type'], $cat);
        }

        $pos = (int)($m['position'] ?? 1);
        $prevRound = $round - 1;

        if ($bracketSide === 'left') {
            $prevPos = ($slotNum === 1) ? ($pos * 2 - 1) : ($pos * 2);
            return "Winner Match #{$prevRound}-{$prevPos}";
        }

        if ($bracketSide === 'right') {
            if ($totalRounds && $totalRounds > 1) {
                $count = (int)pow(2, $totalRounds - $round);
                $offset = intdiv($count, 2);
                $relPos = max(1, $pos - $offset);
                $prevOffset = intdiv((int)pow(2, $totalRounds - $prevRound), 2);
                $prevPos = ($slotNum === 1) ? ($prevOffset + $relPos * 2 - 1) : ($prevOffset + $relPos * 2);
                return "Winner Match #{$prevRound}-{$prevPos}";
            }
            $prevPos = ($slotNum === 1) ? ($pos * 2 - 1) : ($pos * 2);
            return "Winner Match #{$prevRound}-{$prevPos}";
        }
    }

    return 'Menunggu penentuan dari panitia';
}

/** URL Favicon web menggunakan logo turnamen Bansa Open. */
function favicon_url(): string
{
    $logo = \App\Models\Setting::logoUrl();
    if ($logo) {
        return $logo;
    }
    return asset('uploads/logo/f91942ca2c2bc329c6182f99.png');
}

