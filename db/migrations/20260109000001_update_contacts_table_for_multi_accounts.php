<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrasi untuk tabel contacts: mendukung multi-akun per platform sosial media dan kolom account_name.
 */
final class UpdateContactsTableForMultiAccounts extends AbstractMigration
{
    public function up(): void
    {
        $existingItems = [];

        if ($this->hasTable('contacts')) {
            $table = $this->table('contacts');
            if ($table->hasColumn('instagram')) {
                // Ambil data kontak lama jika ada
                try {
                    $rows = $this->fetchAll('SELECT * FROM contacts');
                    foreach ($rows as $r) {
                        if (!empty($r['whatsapp'])) {
                            $existingItems[] = [
                                'platform'     => 'whatsapp',
                                'account_name' => (string)$r['whatsapp'],
                                'url'          => (string)$r['whatsapp'],
                                'sort_order'   => 1,
                            ];
                        }
                        if (!empty($r['instagram'])) {
                            $existingItems[] = [
                                'platform'     => 'instagram',
                                'account_name' => (string)$r['instagram'],
                                'url'          => (string)$r['instagram'],
                                'sort_order'   => 2,
                            ];
                        }
                        if (!empty($r['tiktok'])) {
                            $existingItems[] = [
                                'platform'     => 'tiktok',
                                'account_name' => (string)$r['tiktok'],
                                'url'          => (string)$r['tiktok'],
                                'sort_order'   => 3,
                            ];
                        }
                        if (!empty($r['email'])) {
                            $existingItems[] = [
                                'platform'     => 'email',
                                'account_name' => (string)$r['email'],
                                'url'          => (string)$r['email'],
                                'sort_order'   => 4,
                            ];
                        }
                    }
                } catch (\Throwable) {}

                $this->table('contacts')->drop()->save();
            }
        }

        if (!$this->hasTable('contacts')) {
            $contacts = $this->table('contacts');
            $contacts->addColumn('platform', 'string', ['limit' => 50, 'comment' => 'whatsapp, instagram, tiktok, email'])
                     ->addColumn('account_name', 'string', ['limit' => 150, 'comment' => 'Nama Akun / Nomor (misal: @bansaopen atau 0812-3456-7890)'])
                     ->addColumn('url', 'string', ['limit' => 255, 'comment' => 'Tautan / URL atau nomor'])
                     ->addColumn('sort_order', 'integer', ['default' => 0])
                     ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                     ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                     ->addIndex(['platform'])
                     ->addIndex(['sort_order'])
                     ->create();

            if (!empty($existingItems)) {
                $this->table('contacts')->insert($existingItems)->saveData();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('contacts')) {
            $this->table('contacts')->drop()->save();
        }
    }
}

