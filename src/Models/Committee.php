<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Committee
{
    /**
     * Ambil semua data panitia beserta daftar nama dan ID jabatan yang dipegang.
     */
    public static function allWithRoles(): array
    {
        $sql = "
            SELECT c.*,
                   GROUP_CONCAT(r.name ORDER BY r.sort_order ASC SEPARATOR ', ') AS role_names,
                   GROUP_CONCAT(r.id ORDER BY r.sort_order ASC) AS role_ids
            FROM committees c
            LEFT JOIN committee_has_roles chr ON chr.committee_id = c.id
            LEFT JOIN committee_roles r ON r.id = chr.role_id
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.id ASC
        ";
        return Database::all($sql);
    }

    /**
     * Cari panitia berdasarkan ID lengkap dengan array role_id.
     */
    public static function find(int $id): ?array
    {
        $c = Database::one('SELECT * FROM committees WHERE id = ?', [$id]);
        if (!$c) {
            return null;
        }

        $roles = Database::all(
            'SELECT role_id FROM committee_has_roles WHERE committee_id = ?',
            [$id]
        );
        $c['role_ids'] = array_map(fn($r) => (int)$r['role_id'], $roles);
        return $c;
    }

    /**
     * Tambah data panitia baru dan relasi jabatannya.
     * @param array{name: string, phone?: string, photo?: string|null} $data
     * @param int[] $roleIds
     */
    public static function create(array $data, array $roleIds): int
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $photo = !empty($data['photo']) ? (string)$data['photo'] : null;

        Database::query(
            'INSERT INTO committees (name, phone, photo) VALUES (?, ?, ?)',
            [$name, $phone, $photo]
        );
        $id = (int)Database::lastInsertId();

        self::syncRoles($id, $roleIds);
        return $id;
    }

    /**
     * Perbarui data panitia dan relasi jabatannya.
     * @param array{name: string, phone?: string, photo?: string|null} $data
     * @param int[] $roleIds
     */
    public static function update(int $id, array $data, array $roleIds): void
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $photo = !empty($data['photo']) ? (string)$data['photo'] : null;

        Database::query(
            'UPDATE committees SET name = ?, phone = ?, photo = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?',
            [$name, $phone, $photo, $id]
        );

        self::syncRoles($id, $roleIds);
    }

    /**
     * Hapus panitia (relasi di committee_has_roles terhapus otomatis oleh foreign key cascade).
     */
    public static function delete(int $id): void
    {
        $c = self::find($id);
        if ($c && !empty($c['photo'])) {
            \App\Core\Upload::delete($c['photo']);
        }
        Database::query('DELETE FROM committees WHERE id = ?', [$id]);
    }

    /**
     * Sinkronisasi relasi many-to-many jabatan panitia.
     * @param int[] $roleIds
     */
    private static function syncRoles(int $committeeId, array $roleIds): void
    {
        Database::query('DELETE FROM committee_has_roles WHERE committee_id = ?', [$committeeId]);

        $roleIds = array_unique(array_filter(array_map('intval', $roleIds)));
        foreach ($roleIds as $rId) {
            if ($rId > 0) {
                Database::query(
                    'INSERT IGNORE INTO committee_has_roles (committee_id, role_id) VALUES (?, ?)',
                    [$committeeId, $rId]
                );
            }
        }
    }

    /**
     * Ambil daftar 5 Panitia Inti untuk ditampilkan di Beranda.
     * Jabatan yang dipanggil: Penanggung Jawab, Ketua Panitia, Wakil Ketua, Sekretaris, Bendahara.
     * Jika satu nama merangkap jabatan inti (misal: Sekretaris & Bendahara), nama tidak diulang,
     * melainkan jabatannya digabung (contoh: "Sekretaris & Bendahara").
     *
     * @return array<array{id: int, name: string, role_title: string, phone: string|null}>
     */
    public static function getCoreCommittees(): array
    {
        $sql = "
            SELECT c.id, c.name, c.phone, c.photo, r.id AS role_id, r.name AS role_name, r.sort_order AS role_sort
            FROM committees c
            JOIN committee_has_roles chr ON chr.committee_id = c.id
            JOIN committee_roles r ON r.id = chr.role_id
            WHERE r.name IN ('Penanggung Jawab', 'Ketua Panitia', 'Wakil Ketua', 'Sekretaris', 'Bendahara')
            ORDER BY r.sort_order ASC, c.id ASC
        ";

        $rows = Database::all($sql);
        if (empty($rows)) {
            return [];
        }

        // Kelompokkan per committee_id
        $grouped = [];
        foreach ($rows as $row) {
            $cid = (int)$row['id'];
            if (!isset($grouped[$cid])) {
                $grouped[$cid] = [
                    'id'        => $cid,
                    'name'      => $row['name'],
                    'phone'     => $row['phone'],
                    'photo'     => $row['photo'],
                    'min_sort'  => (int)$row['role_sort'],
                    'roles'     => [],
                ];
            }
            $grouped[$cid]['roles'][] = $row['role_name'];
            if ((int)$row['role_sort'] < $grouped[$cid]['min_sort']) {
                $grouped[$cid]['min_sort'] = (int)$row['role_sort'];
            }
        }

        // Susun nama jabatan gabungan (misal: "Sekretaris & Bendahara")
        $result = [];
        foreach ($grouped as $person) {
            $uniqueRoles = array_unique($person['roles']);
            $person['role_title'] = implode(' & ', $uniqueRoles);
            unset($person['roles']);
            $result[] = $person;
        }

        // Urutkan berdasarkan jabatan inti tertinggi
        usort($result, fn($a, $b) => $a['min_sort'] <=> $b['min_sort']);

        return $result;
    }

    /**
     * Ambil daftar panitia divisi & cabor non-inti dengan penggabungan jabatan rangkap.
     * Jika satu orang memegang lebih dari satu jabatan (mis. Divisi Acara & PJ Cabor Futsal),
     * jabatannya digabungkan: "Koordinator Divisi Acara & PJ Cabor Futsal".
     *
     * Aturan Hierarki Pengurutan (Sorting Rules):
     * - Prioritas 1: Koordinator (dari divisi mana pun, tampil paling awal)
     * - Prioritas 2: Anggota Divisi (tampil setelah semua koordinator)
     * - Prioritas 3: PJ Cabor (tampil paling akhir)
     *
     * Jika satu orang memiliki multi-jabatan (misalnya sebagai Anggota Divisi dan juga PJ Cabor),
     * penentuan urutan card-nya didasarkan pada jabatan dengan prioritas tertinggi yang dia miliki.
     *
     * @return array<array{id: int, name: string, photo: string|null, role_title: string, phone: string|null, priority: int, priority_label: string, is_koord: bool}>
     */
    public static function getDivisionCommittees(): array
    {
        $sql = "
            SELECT c.id, c.name, c.phone, c.photo, r.id AS role_id, r.name AS role_name, r.category, r.division, r.sort_order AS role_sort,
                   CASE
                       WHEN r.name LIKE '%Koordinator%' THEN 1
                       WHEN r.name LIKE '%Anggota%' OR r.category = 'divisi' THEN 2
                       WHEN r.name LIKE 'PJ%' OR r.category = 'cabor' THEN 3
                       ELSE 4
                   END AS role_priority
            FROM committees c
            JOIN committee_has_roles chr ON chr.committee_id = c.id
            JOIN committee_roles r ON r.id = chr.role_id
            WHERE r.category IN ('divisi', 'cabor')
            ORDER BY role_priority ASC, r.sort_order ASC, c.id ASC
        ";

        $rows = Database::all($sql);
        if (empty($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $cid = (int)$row['id'];
            $rolePriority = (int)($row['role_priority'] ?? 4);

            if (!isset($grouped[$cid])) {
                $grouped[$cid] = [
                    'id'            => $cid,
                    'name'          => $row['name'],
                    'phone'         => $row['phone'],
                    'photo'         => $row['photo'],
                    'min_sort'      => (int)$row['role_sort'],
                    'roles'         => [],
                    'priority'      => $rolePriority,
                    'is_koord'      => false,
                ];
            }

            $grouped[$cid]['roles'][] = $row['role_name'];

            // Jika role ini memiliki prioritas lebih tinggi (angka prioritas lebih kecil), ambil yang tertinggi
            if ($rolePriority < $grouped[$cid]['priority']) {
                $grouped[$cid]['priority'] = $rolePriority;
            }

            if ((int)$row['role_sort'] < $grouped[$cid]['min_sort']) {
                $grouped[$cid]['min_sort'] = (int)$row['role_sort'];
            }

            if ($rolePriority === 1 || str_contains($row['role_name'], 'Koordinator')) {
                $grouped[$cid]['is_koord'] = true;
            }
        }

        $result = [];
        foreach ($grouped as $person) {
            $uniqueRoles = array_unique($person['roles']);
            $person['role_title'] = implode(' & ', $uniqueRoles);
            $person['priority_label'] = match ($person['priority']) {
                1 => 'Koordinator',
                2 => 'Anggota Divisi',
                3 => 'PJ Cabor',
                default => 'Panitia',
            };
            unset($person['roles']);
            $result[] = $person;
        }

        // Pengurutan Hierarki:
        // 1. Hierarki Utama: Prioritas 1 (Koordinator) -> Prioritas 2 (Anggota Divisi) -> Prioritas 3 (PJ Cabor)
        // 2. Hierarki Sekunder: Urutan sort_order divisi/jabatan di DB (min_sort ASC)
        // 3. Hierarki Tersier: Nama panitia (alfabetis ASC)
        usort($result, function(array $a, array $b): int {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }
            if ($a['min_sort'] !== $b['min_sort']) {
                return $a['min_sort'] <=> $b['min_sort'];
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $result;
    }

    /**
     * Ambil struktur lengkap susunan kepanitiaan dikelompokkan berdasarkan divisi & jabatan.
     * Jika satu nama merangkap jabatan lintas divisi, nama tersebut muncul di setiap posisi yang ia pegang.
     *
     * @return array<string, array{division: string, roles: array<array{role_name: string, members: array<array{id: int, name: string, phone: string|null, photo: string|null}>}>}>
     */
    public static function getStructure(): array
    {
        $sql = "
            SELECT r.id AS role_id, r.name AS role_name, r.category, r.division, r.sort_order AS role_sort,
                   c.id AS committee_id, c.name AS committee_name, c.phone, c.photo
            FROM committee_roles r
            LEFT JOIN committee_has_roles chr ON chr.role_id = r.id
            LEFT JOIN committees c ON c.id = chr.committee_id
            ORDER BY r.sort_order ASC, c.name ASC
        ";

        $rows = Database::all($sql);
        $divisions = [];

        foreach ($rows as $row) {
            $divKey = (string)($row['division'] ?: 'Lainnya');
            $roleName = (string)$row['role_name'];

            if (!isset($divisions[$divKey])) {
                $divisions[$divKey] = [
                    'division' => $divKey,
                    'category' => $row['category'],
                    'roles'    => [],
                ];
            }

            if (!isset($divisions[$divKey]['roles'][$roleName])) {
                $divisions[$divKey]['roles'][$roleName] = [
                    'role_name' => $roleName,
                    'members'   => [],
                ];
            }

            if (!empty($row['committee_id'])) {
                $divisions[$divKey]['roles'][$roleName]['members'][] = [
                    'id'    => (int)$row['committee_id'],
                    'name'  => (string)$row['committee_name'],
                    'phone' => $row['phone'],
                    'photo' => $row['photo'],
                ];
            }
        }

        return $divisions;
    }
}

