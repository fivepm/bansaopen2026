<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCurrentSetToMatches extends AbstractMigration
{
    public function change(): void
    {
        $this->table('matches')
            ->addColumn('current_set', 'integer', [
                'default' => 0,
                'comment' => 'Nomor set yang sedang aktif dimainkan (0 = belum ada set aktif, 1, 2, 3)',
                'after'   => 'scoring_system',
            ])
            ->update();
    }
}

