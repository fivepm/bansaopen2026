<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AdminSeeder extends AbstractSeed
{
    public function run(): void
    {
        $superUser = $_ENV['ADMIN_USERNAME'] ?? 'admin';
        $superPass = $_ENV['ADMIN_PASSWORD'] ?? 'admin12345';

        $existing = array_column($this->fetchAll('SELECT username FROM admins'), 'username');

        // 1. Akun Super Admin
        if (!in_array($superUser, $existing, true)) {
            $this->table('admins')->insert([
                'username' => $superUser,
                'password' => password_hash($superPass, PASSWORD_DEFAULT),
                'name'     => 'Super Administrator',
                'role'     => 'superadmin',
            ])->saveData();
        }

        // 2. Akun Operator Skor / Wasit
        if (!in_array('operator', $existing, true)) {
            $this->table('admins')->insert([
                'username' => 'operator',
                'password' => password_hash('operator123', PASSWORD_DEFAULT),
                'name'     => 'Operator Wasit Lapangan',
                'role'     => 'operator',
            ])->saveData();
        }
    }
}
