<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\View;
use App\Models\Contact;

final class ContactController
{
    /**
     * Tampilkan daftar akun kontak & sosial media turnamen.
     */
    public function index(): void
    {
        $contacts = Contact::all();

        View::render('admin/contacts/index', [
            'title'     => 'Pengaturan Kontak & Sosial Media',
            'contacts'  => $contacts,
            'platforms' => Contact::PLATFORMS,
        ], 'layouts/admin');
    }

    /**
     * Tampilkan formulir tambah akun kontak baru.
     */
    public function create(): void
    {
        View::render('admin/contacts/create', [
            'title'     => 'Tambah Akun Kontak / Sosmed',
            'platforms' => Contact::PLATFORMS,
        ], 'layouts/admin');
    }

    /**
     * Simpan akun kontak baru.
     */
    public function store(): void
    {
        $platform = trim((string)($_POST['platform'] ?? 'whatsapp'));
        $accountName = trim((string)($_POST['account_name'] ?? ''));
        $url = trim((string)($_POST['url'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($accountName === '') {
            flash('error', 'Nama Akun / Nomor kontak wajib diisi.');
            redirect('admin/contacts/create');
        }

        if ($url === '') {
            flash('error', 'Tautan / URL atau nomor kontak wajib diisi.');
            redirect('admin/contacts/create');
        }

        Contact::create([
            'platform'     => $platform,
            'account_name' => $accountName,
            'url'          => $url,
            'sort_order'   => $sortOrder,
        ]);

        flash('success', "Akun kontak \"$accountName\" berhasil ditambahkan.");
        redirect('admin/contacts');
    }

    /**
     * Tampilkan formulir edit akun kontak.
     */
    public function edit(string $id): void
    {
        $contact = Contact::find((int)$id);
        if (!$contact) {
            flash('error', 'Akun kontak tidak ditemukan.');
            redirect('admin/contacts');
        }

        View::render('admin/contacts/edit', [
            'title'     => 'Edit Akun Kontak: ' . $contact['account_name'],
            'contact'   => $contact,
            'platforms' => Contact::PLATFORMS,
        ], 'layouts/admin');
    }

    /**
     * Perbarui akun kontak.
     */
    public function update(string $id): void
    {
        $cid = (int)$id;
        $contact = Contact::find($cid);
        if (!$contact) {
            flash('error', 'Akun kontak tidak ditemukan.');
            redirect('admin/contacts');
        }

        $platform = trim((string)($_POST['platform'] ?? 'whatsapp'));
        $accountName = trim((string)($_POST['account_name'] ?? ''));
        $url = trim((string)($_POST['url'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($accountName === '') {
            flash('error', 'Nama Akun / Nomor kontak wajib diisi.');
            redirect("admin/contacts/$cid/edit");
        }

        if ($url === '') {
            flash('error', 'Tautan / URL atau nomor kontak wajib diisi.');
            redirect("admin/contacts/$cid/edit");
        }

        Contact::update($cid, [
            'platform'     => $platform,
            'account_name' => $accountName,
            'url'          => $url,
            'sort_order'   => $sortOrder,
        ]);

        flash('success', "Akun kontak \"$accountName\" berhasil diperbarui.");
        redirect('admin/contacts');
    }

    /**
     * Hapus akun kontak.
     */
    public function destroy(string $id): void
    {
        $cid = (int)$id;
        $contact = Contact::find($cid);
        if ($contact) {
            Contact::delete($cid);
            flash('success', "Akun kontak \"{$contact['account_name']}\" berhasil dihapus.");
        } else {
            flash('error', 'Akun kontak tidak ditemukan.');
        }

        redirect('admin/contacts');
    }
}
