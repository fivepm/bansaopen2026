<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRolesAndScoringSystem extends AbstractMigration
{
    public function change(): void
    {
        // 1. Tambah kolom role pada tabel admins (superadmin / operator)
        $this->table('admins')
            ->addColumn('role', 'enum', [
                'values'  => ['superadmin', 'operator'],
                'default' => 'superadmin',
                'after'   => 'password',
            ])
            ->addIndex(['role'])
            ->update();

        // 2. Tambah kolom scoring_system dan skor per set pada tabel matches
        $this->table('matches')
            ->addColumn('scoring_system', 'enum', [
                'values'  => ['poin_lanjut', 'rubber_set'],
                'default' => 'rubber_set',
                'after'   => 'category',
            ])
            ->addColumn('set1_a', 'integer', ['default' => 0, 'after' => 'team2_id'])
            ->addColumn('set1_b', 'integer', ['default' => 0, 'after' => 'set1_a'])
            ->addColumn('set2_a', 'integer', ['default' => 0, 'after' => 'set1_b'])
            ->addColumn('set2_b', 'integer', ['default' => 0, 'after' => 'set2_a'])
            ->addColumn('set3_a', 'integer', ['default' => 0, 'after' => 'set2_b'])
            ->addColumn('set3_b', 'integer', ['default' => 0, 'after' => 'set3_a'])
            ->update();
    }
}

