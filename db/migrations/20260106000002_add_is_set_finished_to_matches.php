<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIsSetFinishedToMatches extends AbstractMigration
{
    public function change(): void
    {
        $this->table('matches')
            ->addColumn('is_set_finished', 'boolean', [
                'default' => false,
                'comment' => 'Apakah set aktif saat ini telah diselesaikan oleh wasit (1 = selesai, 0 = sedang berlangsung)',
                'after'   => 'current_set',
            ])
            ->update();
    }
}

