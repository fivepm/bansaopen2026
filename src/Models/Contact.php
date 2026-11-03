<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Contact
{
    public const PLATFORMS = [
        'whatsapp'  => [
            'label' => 'WhatsApp',
            'icon'  => 'bi-whatsapp',
            'color' => 'text-emerald-400',
            'bg'    => 'bg-emerald-600',
            'badge' => 'border-emerald-500/30 bg-emerald-950/40 text-emerald-300',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon'  => 'bi-instagram',
            'color' => 'text-pink-400',
            'bg'    => 'bg-gradient-to-tr from-amber-600 via-pink-600 to-purple-600',
            'badge' => 'border-pink-500/30 bg-pink-950/40 text-pink-300',
        ],
        'tiktok'    => [
            'label' => 'TikTok',
            'icon'  => 'bi-tiktok',
            'color' => 'text-slate-200',
            'bg'    => 'bg-black border border-slate-700',
            'badge' => 'border-slate-700 bg-slate-800 text-slate-300',
        ],
        'email'     => [
            'label' => 'Gmail / Email',
            'icon'  => 'bi-envelope-fill',
            'color' => 'text-rose-400',
            'bg'    => 'bg-rose-600',
            'badge' => 'border-rose-500/30 bg-rose-950/40 text-rose-300',
        ],
    ];

    /**
     * Ambil semua data kontak aktif beserta URL formatnya.
     * @return array<array{
     *   id: int,
     *   platform: string,
     *   account_name: string,
     *   url: string,
     *   formatted_url: string,
     *   sort_order: int,
     *   platform_label: string,
     *   platform_icon: string,
     *   platform_color: string,
     *   platform_bg: string,
     *   platform_badge: string
     * }>
     */
    public static function all(): array
    {
        $rows = Database::all('SELECT * FROM contacts ORDER BY sort_order ASC, id ASC');
        $result = [];

        foreach ($rows as $r) {
            $platform = (string)($r['platform'] ?? 'whatsapp');
            $meta = self::PLATFORMS[$platform] ?? self::PLATFORMS['whatsapp'];
            $url = (string)($r['url'] ?? '');

            $result[] = [
                'id'             => (int)$r['id'],
                'platform'       => $platform,
                'account_name'   => (string)$r['account_name'],
                'url'            => $url,
                'formatted_url'  => self::formatUrl($platform, $url),
                'sort_order'     => (int)($r['sort_order'] ?? 0),
                'platform_label' => $meta['label'],
                'platform_icon'  => $meta['icon'],
                'platform_color' => $meta['color'],
                'platform_bg'    => $meta['bg'],
                'platform_badge' => $meta['badge'],
            ];
        }

        return $result;
    }

    /**
     * Cari kontak berdasarkan ID.
     */
    public static function find(int $id): ?array
    {
        $r = Database::one('SELECT * FROM contacts WHERE id = ?', [$id]);
        if (!$r) return null;

        $platform = (string)($r['platform'] ?? 'whatsapp');
        $meta = self::PLATFORMS[$platform] ?? self::PLATFORMS['whatsapp'];
        $url = (string)($r['url'] ?? '');

        return [
            'id'             => (int)$r['id'],
            'platform'       => $platform,
            'account_name'   => (string)$r['account_name'],
            'url'            => $url,
            'formatted_url'  => self::formatUrl($platform, $url),
            'sort_order'     => (int)($r['sort_order'] ?? 0),
            'platform_label' => $meta['label'],
            'platform_icon'  => $meta['icon'],
            'platform_color' => $meta['color'],
            'platform_bg'    => $meta['bg'],
            'platform_badge' => $meta['badge'],
        ];
    }

    /**
     * Tambah kontak baru.
     */
    public static function create(array $data): int
    {
        $platform = trim((string)($data['platform'] ?? 'whatsapp'));
        if (!isset(self::PLATFORMS[$platform])) {
            $platform = 'whatsapp';
        }

        $accountName = trim((string)($data['account_name'] ?? ''));
        $url = trim((string)($data['url'] ?? ''));
        $sortOrder = (int)($data['sort_order'] ?? 0);

        Database::query(
            'INSERT INTO contacts (platform, account_name, url, sort_order) VALUES (?, ?, ?, ?)',
            [$platform, $accountName, $url, $sortOrder]
        );

        return (int)Database::lastInsertId();
    }

    /**
     * Perbarui data kontak.
     */
    public static function update(int $id, array $data): void
    {
        $platform = trim((string)($data['platform'] ?? 'whatsapp'));
        if (!isset(self::PLATFORMS[$platform])) {
            $platform = 'whatsapp';
        }

        $accountName = trim((string)($data['account_name'] ?? ''));
        $url = trim((string)($data['url'] ?? ''));
        $sortOrder = (int)($data['sort_order'] ?? 0);

        Database::query(
            'UPDATE contacts SET platform = ?, account_name = ?, url = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?',
            [$platform, $accountName, $url, $sortOrder, $id]
        );
    }

    /**
     * Hapus akun kontak.
     */
    public static function delete(int $id): void
    {
        Database::query('DELETE FROM contacts WHERE id = ?', [$id]);
    }

    /**
     * Format URL agar siap diklik sesuai platform.
     */
    public static function formatUrl(string $platform, string $val): string
    {
        $val = trim($val);
        if ($val === '') return '';

        switch ($platform) {
            case 'whatsapp':
                if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
                    return $val;
                }
                $nums = preg_replace('/[^0-9]/', '', $val);
                if ($nums === '') return '';
                if (str_starts_with($nums, '0')) {
                    $nums = '62' . substr($nums, 1);
                } elseif (str_starts_with($nums, '8')) {
                    $nums = '62' . $nums;
                }
                return 'https://wa.me/' . $nums;

            case 'instagram':
                if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
                    return $val;
                }
                $val = ltrim($val, '@');
                return 'https://instagram.com/' . $val;

            case 'tiktok':
                if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
                    return $val;
                }
                $val = ltrim($val, '@');
                return 'https://tiktok.com/@' . $val;

            case 'email':
                if (str_starts_with($val, 'mailto:')) {
                    return $val;
                }
                return 'mailto:' . $val;

            default:
                return $val;
        }
    }
}
