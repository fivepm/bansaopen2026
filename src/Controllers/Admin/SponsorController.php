<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Upload;
use App\Core\View;
use App\Models\Sponsor;

final class SponsorController
{
    public function index(): void
    {
        View::render('admin/sponsors/index', ['title' => 'Sponsor', 'sponsors' => Sponsor::all()], 'layouts/admin');
    }

    public function create(): void
    {
        View::render('admin/sponsors/form', ['title' => 'Tambah Sponsor', 'sponsor' => null], 'layouts/admin');
    }

    public function store(): void
    {
        $name = $this->name('admin/sponsors/create');
        try {
            $logo = Upload::image($_FILES['logo'] ?? [], 'sponsors');
        } catch (\RuntimeException $e) {
            $_SESSION['old'] = $_POST;
            flash('error', $e->getMessage());
            redirect('admin/sponsors/create');
        }
        Database::query(
            'INSERT INTO sponsors (name, logo_path, is_active) VALUES (?, ?, ?)',
            [$name, $logo, isset($_POST['is_active']) ? 1 : 0]
        );
        flash('success', 'Sponsor ditambahkan.');
        redirect('admin/sponsors');
    }

    public function edit(string $id): void
    {
        View::render('admin/sponsors/form', ['title' => 'Edit Sponsor', 'sponsor' => $this->find((int)$id)], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $s = $this->find((int)$id);
        $back = "admin/sponsors/{$s['id']}/edit";
        $name = $this->name($back);
        $logo = $s['logo_path'];

        // Logo opsional saat edit
        if (($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $logo = Upload::image($_FILES['logo'], 'sponsors');
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect($back);
            }
            Upload::delete($s['logo_path']);
        }
        Database::query(
            'UPDATE sponsors SET name = ?, logo_path = ?, is_active = ? WHERE id = ?',
            [$name, $logo, isset($_POST['is_active']) ? 1 : 0, $s['id']]
        );
        flash('success', 'Sponsor diperbarui.');
        redirect('admin/sponsors');
    }

    public function toggle(string $id): void
    {
        $s = $this->find((int)$id);
        Database::query('UPDATE sponsors SET is_active = ? WHERE id = ?', [$s['is_active'] ? 0 : 1, $s['id']]);
        flash('success', $s['is_active'] ? 'Sponsor dinonaktifkan.' : 'Sponsor diaktifkan.');
        redirect('admin/sponsors');
    }

    public function destroy(string $id): void
    {
        $s = $this->find((int)$id);
        Upload::delete($s['logo_path']);
        Database::query('DELETE FROM sponsors WHERE id = ?', [$s['id']]);
        flash('success', 'Sponsor dihapus.');
        redirect('admin/sponsors');
    }

    private function find(int $id): array
    {
        $s = Sponsor::find($id);
        if (!$s) {
            http_response_code(404);
            exit('Sponsor tidak ditemukan.');
        }
        return $s;
    }

    private function name(string $backTo): string
    {
        $name = mb_substr(trim($_POST['name'] ?? ''), 0, 120);
        if ($name === '') {
            $_SESSION['old'] = $_POST;
            flash('error', 'Nama sponsor wajib diisi.');
            redirect($backTo);
        }
        return $name;
    }
}

