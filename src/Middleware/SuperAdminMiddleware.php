<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\View;

/**
 * Middleware untuk memastikan hanya Super Admin yang dapat mengakses halaman krusial
 * (pengaturan logo, sponsor, manajemen tim, pembuatan bracket, dll).
 * Jika Operator Wasit mencoba mengakses, dialihkan ke Live Panel.
 */
final class SuperAdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Halaman tidak ditemukan']);
            exit;
        }

        if (!Auth::isSuperAdmin()) {
            flash('error', 'Akses ditolak: Akun Operator hanya memiliki hak akses ke Live Panel Skor.');
            redirect('admin/live-panel');
        }
    }
}

