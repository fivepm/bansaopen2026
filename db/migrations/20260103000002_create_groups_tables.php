<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** Tabel sistem grup: `groups` (per cabor) dan `group_standings` (anggota grup + statistik klasemen). */
final class CreateGroupsTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('groups')
            ->addColumn('sport_type', 'string', ['limit' => 20])
            ->addColumn('name', 'string', ['limit' => 50, 'comment' => 'mis. Grup A'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['sport_type'])
            ->create();

        $this->table('group_standings')
            ->addColumn('group_id', 'integer', ['signed' => false])
            ->addColumn('team_id', 'integer', ['signed' => false])
            ->addColumn('played', 'integer', ['default' => 0])
            ->addColumn('won', 'integer', ['default' => 0])
            ->addColumn('drawn', 'integer', ['default' => 0])
            ->addColumn('lost', 'integer', ['default' => 0])
            ->addColumn('goals_for', 'integer', ['default' => 0, 'comment' => 'total skor/gol dicetak'])
            ->addColumn('goals_against', 'integer', ['default' => 0, 'comment' => 'total skor/gol kemasukan'])
            ->addColumn('points', 'integer', ['default' => 0])
            ->addIndex(['team_id'], ['unique' => true])
            ->addIndex(['group_id', 'points'])
            ->addForeignKey('group_id', 'groups', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}

