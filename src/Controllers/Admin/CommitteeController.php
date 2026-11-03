<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Upload;
use App\Core\View;
use App\Models\Committee;
use App\Models\CommitteeRole;

final class CommitteeController
{
    /**
     * Tampilkan daftar susunan panitia.
     */
    public function index(): void
    {
        $committees = Committee::allWithRoles();
        $rolesGrouped = CommitteeRole::grouped();

        View::render('admin/committees/index', [
            'title'        => 'Manajemen Susunan Kepanitiaan',
            'committees'   => $committees,
            'rolesGrouped' => $rolesGrouped,
        ], 'layouts/admin');
    }

    /**
     * Formulir tambah panitia baru.
     */
    public function create(): void
    {
        $coreRoles = CommitteeRole::coreRoles();
        $divisionGrouped = CommitteeRole::divisionGrouped();

        View::render('admin/committees/create', [
            'title'           => 'Tambah Anggota Panitia',
            'coreRoles'       => $coreRoles,
            'divisionGrouped' => $divisionGrouped,
        ], 'layouts/admin');
    }

    /**
     * Simpan panitia baru.
     */
    public function store(): void
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $coreRole = (string)($_POST['core_role'] ?? 'other');
        $roleIds = (array)($_POST['role_ids'] ?? []);

        if ($name === '') {
            flash('error', 'Nama anggota panitia wajib diisi.');
            redirect('admin/committees/create');
        }

        // Jika memilih radio button jabatan inti (Penanggung Jawab / BPH)
        if (in_array($coreRole, ['1', '2', '3', '4', '5'], true)) {
            $finalRoleIds = [(int)$coreRole];
        } elseif ($coreRole === 'other') {
            $finalRoleIds = array_filter(array_map('intval', $roleIds));
        } else {
            $finalRoleIds = array_filter(array_map('intval', $roleIds));
        }

        if (empty($finalRoleIds)) {
            flash('error', 'Pilih salah satu jabatan inti atau minimal satu divisi operasional.');
            redirect('admin/committees/create');
        }

        $photo = null;
        if (!empty($_POST['cropped_photo']) && str_starts_with((string)$_POST['cropped_photo'], 'data:image/')) {
            try {
                $photo = Upload::base64((string)$_POST['cropped_photo'], 'committees');
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('admin/committees/create');
            }
        } elseif (isset($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $photo = Upload::image($_FILES['photo'], 'committees');
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('admin/committees/create');
            }
        }

        Committee::create([
            'name'  => $name,
            'phone' => $phone,
            'photo' => $photo,
        ], $finalRoleIds);

        flash('success', "Anggota panitia \"$name\" berhasil ditambahkan.");
        redirect('admin/committees');
    }

    /**
     * Formulir edit panitia.
     */
    public function edit(string $id): void
    {
        $committee = Committee::find((int)$id);
        if (!$committee) {
            flash('error', 'Data panitia tidak ditemukan.');
            redirect('admin/committees');
        }

        $coreRoles = CommitteeRole::coreRoles();
        $divisionGrouped = CommitteeRole::divisionGrouped();

        View::render('admin/committees/edit', [
            'title'           => 'Edit Panitia: ' . $committee['name'],
            'committee'       => $committee,
            'coreRoles'       => $coreRoles,
            'divisionGrouped' => $divisionGrouped,
        ], 'layouts/admin');
    }

    /**
     * Update data panitia dan jabatannya.
     */
    public function update(string $id): void
    {
        $cid = (int)$id;
        $committee = Committee::find($cid);
        if (!$committee) {
            flash('error', 'Data panitia tidak ditemukan.');
            redirect('admin/committees');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $coreRole = (string)($_POST['core_role'] ?? 'other');
        $roleIds = (array)($_POST['role_ids'] ?? []);

        if ($name === '') {
            flash('error', 'Nama anggota panitia wajib diisi.');
            redirect("admin/committees/$cid/edit");
        }

        // Jika memilih radio button jabatan inti (Penanggung Jawab / BPH)
        if (in_array($coreRole, ['1', '2', '3', '4', '5'], true)) {
            $finalRoleIds = [(int)$coreRole];
        } elseif ($coreRole === 'other') {
            $finalRoleIds = array_filter(array_map('intval', $roleIds));
        } else {
            $finalRoleIds = array_filter(array_map('intval', $roleIds));
        }

        if (empty($finalRoleIds)) {
            flash('error', 'Pilih salah satu jabatan inti atau minimal satu divisi operasional.');
            redirect("admin/committees/$cid/edit");
        }

        $photo = $committee['photo'] ?? null;

        // Opsi hapus foto jika dicentang
        if (!empty($_POST['remove_photo'])) {
            if ($photo) {
                Upload::delete($photo);
            }
            $photo = null;
        }

        // Upload foto baru jika ada file yang dipilih atau hasil crop
        if (!empty($_POST['cropped_photo']) && str_starts_with((string)$_POST['cropped_photo'], 'data:image/')) {
            try {
                $newPhoto = Upload::base64((string)$_POST['cropped_photo'], 'committees');
                // Hapus file lama jika ada
                if ($photo) {
                    Upload::delete($photo);
                }
                $photo = $newPhoto;
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect("admin/committees/$cid/edit");
            }
        } elseif (isset($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $newPhoto = Upload::image($_FILES['photo'], 'committees');
                // Hapus file lama jika ada
                if ($photo) {
                    Upload::delete($photo);
                }
                $photo = $newPhoto;
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect("admin/committees/$cid/edit");
            }
        }

        Committee::update($cid, [
            'name'  => $name,
            'phone' => $phone,
            'photo' => $photo,
        ], $finalRoleIds);

        flash('success', "Data panitia \"$name\" berhasil diperbarui.");
        redirect('admin/committees');
    }

    /**
     * Hapus data panitia.
     */
    public function destroy(string $id): void
    {
        $cid = (int)$id;
        $committee = Committee::find($cid);
        if ($committee) {
            Committee::delete($cid);
            flash('success', "Anggota panitia \"{$committee['name']}\" berhasil dihapus.");
        } else {
            flash('error', 'Data panitia tidak ditemukan.');
        }

        redirect('admin/committees');
    }
}

