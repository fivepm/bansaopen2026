<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Upload;
use App\Core\View;
use App\Models\Setting;

final class SettingController
{
    public function index(): void
    {
        View::render('admin/settings', [
            'title' => 'Pengaturan Turnamen',
            'logo'  => Setting::get('site_logo'),
        ], 'layouts/admin');
    }

    public function updateLogo(): void
    {
        try {
            $new = Upload::image($_FILES['logo'] ?? [], 'logo');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('admin/settings');
        }
        Upload::delete(Setting::get('site_logo'));
        Setting::set('site_logo', $new);
        flash('success', 'Logo berhasil diperbarui.');
        redirect('admin/settings');
    }

    public function deleteLogo(): void
    {
        Upload::delete(Setting::get('site_logo'));
        Setting::set('site_logo', null);
        flash('success', 'Logo dihapus.');
        redirect('admin/settings');
    }
}

