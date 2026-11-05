<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateMatchHighlightsAndFutsalTimer extends AbstractMigration
{
    public function change(): void
    {
        // 1. Modifikasi tabel match_highlights untuk mengakomodasi pertukaran pemain dan babak
        $this->table('match_highlights')
            ->changeColumn('event_type', 'enum', [
                'values' => ['goal', 'yellow_card', 'red_card', 'substitution'],
                'default' => 'goal',
            ])
            ->addColumn('player_in', 'string', ['limit' => 100, 'null' => true, 'after' => 'player_name'])
            ->addColumn('player_out', 'string', ['limit' => 100, 'null' => true, 'after' => 'player_in'])
            ->addColumn('period', 'integer', ['default' => 1, 'limit' => 2, 'signed' => false, 'after' => 'event_type'])
            ->update();

        // 2. Tambah kolom timer futsal pada tabel matches untuk sinkronisasi live panel dan publik
        $this->table('matches')
            ->addColumn('futsal_half_duration', 'integer', ['default' => 20, 'after' => 'scoring_system'])
            ->addColumn('futsal_elapsed_seconds', 'integer', ['default' => 0, 'after' => 'futsal_half_duration'])
            ->addColumn('futsal_timer_status', 'enum', [
                'values' => ['stopped', 'running', 'paused'],
                'default' => 'stopped',
                'after' => 'futsal_elapsed_seconds',
            ])
            ->addColumn('futsal_timer_updated_at', 'datetime', ['null' => true, 'after' => 'futsal_timer_status'])
            ->update();
    }
}

