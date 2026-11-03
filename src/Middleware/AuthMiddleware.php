<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\View;

/** Halaman admin disembunyikan: pengunjung yang belum login mendapat 404 (bukan redirect ke login). */
final class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Halaman tidak ditemukan']);
            exit;
        }
    }
}
