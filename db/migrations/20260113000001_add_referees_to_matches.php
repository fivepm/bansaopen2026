<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRefereesToMatches extends AbstractMigration
{
    public function change(): void
    {
        $this->table('matches')
            ->addColumn('referee_main', 'string', [
                'limit'   => 100,
                'null'    => true,
                'default' => null,
                'comment' => 'Nama Wasit Utama / Umpire',
                'after'   => 'court',
            ])
            ->addColumn('referee_line1', 'string', [
                'limit'   => 100,
                'null'    => true,
                'default' => null,
                'comment' => 'Nama Wasit Garis 1 / Hakim Garis 1',
                'after'   => 'referee_main',
            ])
            ->addColumn('referee_line2', 'string', [
                'limit'   => 100,
                'null'    => true,
                'default' => null,
                'comment' => 'Nama Wasit Garis 2 / Hakim Garis 2',
                'after'   => 'referee_line1',
            ])
            ->update();
    }
}

