<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrasi untuk tabel committees: menambahkan kolom photo untuk foto profil anggota panitia.
 */
final class AddPhotoToCommittees extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('committees')) {
            $table = $this->table('committees');
            if (!$table->hasColumn('photo')) {
                $table->addColumn('photo', 'string', [
                    'limit'   => 255,
                    'null'    => true,
                    'after'   => 'phone',
                    'comment' => 'Path file foto profil anggota panitia'
                ])->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('committees')) {
            $table = $this->table('committees');
            if ($table->hasColumn('photo')) {
                $table->removeColumn('photo')->update();
            }
        }
    }
}

