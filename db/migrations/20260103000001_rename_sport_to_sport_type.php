<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** Ganti nama kolom `sport` menjadi `sport_type` pada teams & matches (data lama tetap aman). */
final class RenameSportToSportType extends AbstractMigration
{
    public function change(): void
    {
        $this->table('teams')->renameColumn('sport', 'sport_type')->update();
        $this->table('matches')->renameColumn('sport', 'sport_type')->update();
    }
}

