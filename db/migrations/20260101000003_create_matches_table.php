<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMatchesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('matches')
            ->addColumn('sport', 'string', ['limit' => 20, 'default' => 'badminton'])
            ->addColumn('round', 'integer', ['comment' => '1 = babak pertama'])
            ->addColumn('position', 'integer', ['comment' => 'Urutan pertandingan dalam babak (mulai 1)'])
            ->addColumn('team1_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('team2_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('score1', 'integer', ['default' => 0])
            ->addColumn('score2', 'integer', ['default' => 0])
            ->addColumn('winner_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('status', 'enum', ['values' => ['upcoming', 'live', 'finished'], 'default' => 'upcoming'])
            ->addColumn('scheduled_at', 'datetime', ['null' => true])
            ->addColumn('court', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['sport', 'round', 'position'], ['unique' => true])
            ->addIndex(['status'])
            ->addForeignKey('team1_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('team2_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('winner_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}

