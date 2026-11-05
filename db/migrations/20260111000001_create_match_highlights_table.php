<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMatchHighlightsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('match_highlights')
            ->addColumn('match_id', 'integer', ['signed' => false])
            ->addColumn('team_id', 'integer', ['signed' => false])
            ->addColumn('player_name', 'string', ['limit' => 100])
            ->addColumn('event_type', 'enum', ['values' => ['goal', 'yellow_card', 'red_card']])
            ->addColumn('minute', 'integer', ['default' => 0])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['match_id'])
            ->addIndex(['team_id'])
            ->addIndex(['event_type'])
            ->addForeignKey('match_id', 'matches', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}

