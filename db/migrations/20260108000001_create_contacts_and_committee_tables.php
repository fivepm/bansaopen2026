<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrasi untuk tabel contacts, committees, committee_roles, dan committee_has_roles.
 */
final class CreateContactsAndCommitteeTables extends AbstractMigration
{
    public function change(): void
    {
        // 1. Tabel contacts untuk menyimpan tautan sosial media & kontak
        if (!$this->hasTable('contacts')) {
            $table = $this->table('contacts');
            $table->addColumn('instagram', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Username atau link Instagram'])
                  ->addColumn('whatsapp', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Nomor WhatsApp atau wa.me link'])
                  ->addColumn('tiktok', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Username atau link TikTok'])
                  ->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Alamat Gmail / Email'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                  ->create();

            $this->execute("INSERT INTO contacts (id, instagram, whatsapp, tiktok, email) VALUES (1, '', '', '', '')");
        }

        // 2. Tabel committee_roles untuk daftar jabatan & divisi
        if (!$this->hasTable('committee_roles')) {
            $roles = $this->table('committee_roles');
            $roles->addColumn('name', 'string', ['limit' => 100, 'comment' => 'Nama jabatan'])
                  ->addColumn('category', 'string', ['limit' => 50, 'default' => 'divisi', 'comment' => 'inti, divisi, cabor'])
                  ->addColumn('division', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Pengelompokan divisi'])
                  ->addColumn('sort_order', 'integer', ['default' => 0])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['name'], ['unique' => true])
                  ->addIndex(['sort_order'])
                  ->create();

            $defaultRoles = [
                // Panitia Inti
                ['name' => 'Penanggung Jawab', 'category' => 'inti', 'division' => 'Penanggung Jawab', 'sort_order' => 1],
                ['name' => 'Ketua Panitia', 'category' => 'inti', 'division' => 'BPH', 'sort_order' => 2],
                ['name' => 'Wakil Ketua', 'category' => 'inti', 'division' => 'BPH', 'sort_order' => 3],
                ['name' => 'Sekretaris', 'category' => 'inti', 'division' => 'BPH', 'sort_order' => 4],
                ['name' => 'Bendahara', 'category' => 'inti', 'division' => 'BPH', 'sort_order' => 5],

                // Divisi Acara
                ['name' => 'Koordinator Divisi Acara', 'category' => 'divisi', 'division' => 'Divisi Acara', 'sort_order' => 10],
                ['name' => 'Anggota Divisi Acara', 'category' => 'divisi', 'division' => 'Divisi Acara', 'sort_order' => 11],

                // Divisi Konsumsi
                ['name' => 'Koordinator Divisi Konsumsi', 'category' => 'divisi', 'division' => 'Divisi Konsumsi', 'sort_order' => 20],
                ['name' => 'Anggota Divisi Konsumsi', 'category' => 'divisi', 'division' => 'Divisi Konsumsi', 'sort_order' => 21],

                // Divisi Kesehatan
                ['name' => 'Koordinator Divisi Kesehatan', 'category' => 'divisi', 'division' => 'Divisi Kesehatan', 'sort_order' => 30],
                ['name' => 'Anggota Divisi Kesehatan', 'category' => 'divisi', 'division' => 'Divisi Kesehatan', 'sort_order' => 31],

                // Divisi Sponsorship
                ['name' => 'Koordinator Divisi Sponsorship', 'category' => 'divisi', 'division' => 'Divisi Sponsorship', 'sort_order' => 40],
                ['name' => 'Anggota Divisi Sponsorship', 'category' => 'divisi', 'division' => 'Divisi Sponsorship', 'sort_order' => 41],

                // Divisi Perlengkapan
                ['name' => 'Koordinator Divisi Perlengkapan', 'category' => 'divisi', 'division' => 'Divisi Perlengkapan', 'sort_order' => 50],
                ['name' => 'Anggota Divisi Perlengkapan', 'category' => 'divisi', 'division' => 'Divisi Perlengkapan', 'sort_order' => 51],

                // Divisi Publikasi dan Dokumentasi
                ['name' => 'Koordinator Divisi Publikasi dan Dokumentasi', 'category' => 'divisi', 'division' => 'Divisi Publikasi dan Dokumentasi', 'sort_order' => 60],
                ['name' => 'Anggota Divisi Publikasi dan Dokumentasi', 'category' => 'divisi', 'division' => 'Divisi Publikasi dan Dokumentasi', 'sort_order' => 61],

                // Divisi Keamanan
                ['name' => 'Koordinator Divisi Keamanan', 'category' => 'divisi', 'division' => 'Divisi Keamanan', 'sort_order' => 70],
                ['name' => 'Anggota Divisi Keamanan', 'category' => 'divisi', 'division' => 'Divisi Keamanan', 'sort_order' => 71],

                // PJ Cabor
                ['name' => 'PJ Cabor Badminton', 'category' => 'cabor', 'division' => 'Penanggung Jawab Cabor', 'sort_order' => 80],
                ['name' => 'PJ Cabor Pingpong', 'category' => 'cabor', 'division' => 'Penanggung Jawab Cabor', 'sort_order' => 81],
                ['name' => 'PJ Cabor Futsal', 'category' => 'cabor', 'division' => 'Penanggung Jawab Cabor', 'sort_order' => 82],
            ];

            $this->table('committee_roles')->insert($defaultRoles)->saveData();
        }

        // 3. Tabel committees (daftar orang panitia)
        if (!$this->hasTable('committees')) {
            $committees = $this->table('committees');
            $committees->addColumn('name', 'string', ['limit' => 150, 'comment' => 'Nama anggota panitia'])
                       ->addColumn('phone', 'string', ['limit' => 50, 'null' => true, 'comment' => 'Nomor HP/WA'])
                       ->addColumn('sort_order', 'integer', ['default' => 0])
                       ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                       ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                       ->create();
        }

        // 4. Tabel junction committee_has_roles (many-to-many: satu orang bisa banyak jabatan)
        if (!$this->hasTable('committee_has_roles')) {
            $junction = $this->table('committee_has_roles');
            $junction->addColumn('committee_id', 'integer', ['signed' => false])
                     ->addColumn('role_id', 'integer', ['signed' => false])
                     ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                     ->addIndex(['committee_id', 'role_id'], ['unique' => true])
                     ->addForeignKey('committee_id', 'committees', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                     ->addForeignKey('role_id', 'committee_roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                     ->create();
        }
    }
}

