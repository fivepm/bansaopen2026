<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** matches: tambah `stage` (knockout|group) & `group_id`; unique (round, position) diganti index biasa. */
final class AddStageAndGroupToMatches extends AbstractMigration
{
    public function up(): void
    {
        $t = $this->table('matches');
        $t->removeIndexByName($this->uniqueIndexName())->update();

        $this->table('matches')
            ->addColumn('stage', 'enum', ['values' => ['knockout', 'group'], 'default' => 'knockout', 'after' => 'sport_type'])
            ->addColumn('group_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'stage'])
            ->addIndex(['sport_type', 'stage', 'round', 'position'])
            ->addForeignKey('group_id', 'groups', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->update();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM matches WHERE stage = 'group'");
        $this->table('matches')
            ->dropForeignKey('group_id')
            ->removeIndex(['sport_type', 'stage', 'round', 'position'])
            ->removeColumn('group_id')
            ->removeColumn('stage')
            ->addIndex(['sport_type', 'round', 'position'], ['unique' => true])
            ->update();
    }

    /** Nama index unik lama (sport/round/position) diambil dari information_schema agar tidak bergantung pada nama otomatis. */
    private function uniqueIndexName(): string
    {
        $row = $this->fetchRow(
            "SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'matches' AND NON_UNIQUE = 0 AND INDEX_NAME <> 'PRIMARY'
             GROUP BY INDEX_NAME HAVING COUNT(*) = 3 LIMIT 1"
        );
        return $row['INDEX_NAME'] ?? 'sport_round_position_missing';
    }
}

