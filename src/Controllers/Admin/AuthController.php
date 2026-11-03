<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\View;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(Auth::isOperator() ? 'admin/live-panel' : 'admin');
        }
        View::render('admin/login', ['title' => 'Login Admin'], 'layouts/public');
    }

    public function login(): void
    {
        $u = trim($_POST['username'] ?? '');
        if (Auth::attempt($u, $_POST['password'] ?? '')) {
            if (Auth::isOperator()) {
                flash('success', 'Selamat datang! Anda masuk sebagai Operator Skor Wasit.');
                redirect('admin/live-panel');
            }
            redirect('admin');
        }
        flash('error', 'Username atau password salah.');
        redirect(admin_login_path());
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        flash('success', 'Anda sudah keluar.');
        redirect(admin_login_path());
    }
}
