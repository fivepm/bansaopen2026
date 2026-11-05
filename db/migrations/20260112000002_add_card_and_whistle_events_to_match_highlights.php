<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCardAndWhistleEventsToMatchHighlights extends AbstractMigration
{
    public function change(): void
    {
        $this->table('match_highlights')
            ->changeColumn('event_type', 'enum', [
                'values' => ['goal', 'yellow_card', 'double_yellow_card', 'red_card', 'substitution', 'half_time', 'full_time'],
                'default' => 'goal',
            ])
            ->update();
    }
}

