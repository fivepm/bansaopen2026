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
        return ($m['group_name'] ?? 'Grup') . ' · Hari ke-' . (int)$m['round'];
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
        default => 'Babak Penyisihan' . ($totalRounds - $round > 3 ? ' ' . $round : ''),
    };
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

/** URL Favicon web menggunakan logo turnamen Bansa Open. */
function favicon_url(): string
{
    $logo = \App\Models\Setting::logoUrl();
    if ($logo) {
        return $logo;
    }
    return asset('uploads/logo/f91942ca2c2bc329c6182f99.png');
}

