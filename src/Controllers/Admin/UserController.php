<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

final class UserController
{
    public function index(): void
    {
        $users = Database::all('SELECT id, username, name, role, created_at FROM admins ORDER BY role ASC, name ASC');
        View::render('admin/users/index', [
            'title' => 'Manajemen Pengguna & Wasit',
            'users' => $users,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        View::render('admin/users/form', [
            'title' => 'Tambah Operator / Admin',
            'user'  => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? '', ['superadmin', 'operator'], true) ? $_POST['role'] : 'operator';

        if ($username === '' || $name === '' || strlen($password) < 4) {
            flash('error', 'Semua kolom wajib diisi (password minimal 4 karakter).');
            redirect('admin/users/create');
        }

        $exists = Database::one('SELECT id FROM admins WHERE username = ?', [$username]);
        if ($exists) {
            flash('error', "Username '$username' sudah digunakan.");
            redirect('admin/users/create');
        }

        Database::query(
            'INSERT INTO admins (username, password, name, role) VALUES (?, ?, ?, ?)',
            [$username, password_hash($password, PASSWORD_DEFAULT), $name, $role]
        );

        flash('success', "Akun $role '$username' berhasil ditambahkan.");
        redirect('admin/users');
    }

    public function edit(string $id): void
    {
        $user = Database::one('SELECT id, username, name, role FROM admins WHERE id = ?', [(int)$id]);
        if (!$user) {
            flash('error', 'Pengguna tidak ditemukan.');
            redirect('admin/users');
        }
        View::render('admin/users/form', [
            'title' => 'Edit Pengguna',
            'user'  => $user,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $user = Database::one('SELECT id, username FROM admins WHERE id = ?', [(int)$id]);
        if (!$user) {
            flash('error', 'Pengguna tidak ditemukan.');
            redirect('admin/users');
        }

        $name = trim($_POST['name'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['superadmin', 'operator'], true) ? $_POST['role'] : 'operator';
        $newPass = trim($_POST['password'] ?? '');

        if ($name === '') {
            flash('error', 'Nama lengkap wajib diisi.');
            redirect("admin/users/{$user['id']}/edit");
        }

        if ($newPass !== '') {
            Database::query(
                'UPDATE admins SET name = ?, role = ?, password = ? WHERE id = ?',
                [$name, $role, password_hash($newPass, PASSWORD_DEFAULT), $user['id']]
            );
        } else {
            Database::query('UPDATE admins SET name = ?, role = ? WHERE id = ?', [$name, $role, $user['id']]);
        }

        flash('success', 'Data pengguna berhasil diperbarui.');
        redirect('admin/users');
    }

    public function destroy(string $id): void
    {
        $currentUser = Auth::user();
        $targetId = (int)$id;

        if ($currentUser && (int)$currentUser['id'] === $targetId) {
            flash('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
            redirect('admin/users');
        }

        Database::query('DELETE FROM admins WHERE id = ?', [$targetId]);
        flash('success', 'Pengguna telah dihapus.');
        redirect('admin/users');
    }
}

