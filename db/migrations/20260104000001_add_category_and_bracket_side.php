<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCategoryAndBracketSide extends AbstractMigration
{
    public function change(): void
    {
        // 1. Tambah kolom `category` pada tabel `teams`
        $this->table('teams')
            ->addColumn('category', 'enum', [
                'values'  => ['ganda_putra', 'ganda_putri', 'umum'],
                'default' => 'umum',
                'after'   => 'sport_type',
            ])
            ->addIndex(['sport_type', 'category'])
            ->update();

        // 2. Tambah kolom `category` dan `bracket_side` pada tabel `matches`
        $this->table('matches')
            ->addColumn('category', 'enum', [
                'values'  => ['ganda_putra', 'ganda_putri', 'umum'],
                'default' => 'umum',
                'after'   => 'sport_type',
            ])
            ->addColumn('bracket_side', 'enum', [
                'values'  => ['left', 'right', 'final', 'third_place'],
                'default' => 'left',
                'after'   => 'stage',
            ])
            ->addIndex(['sport_type', 'category', 'stage', 'bracket_side'])
            ->update();
    }
}

