<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('settings')
            ->addColumn('setting_key', 'string', ['limit' => 100])
            ->addColumn('setting_value', 'text', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['setting_key'], ['unique' => true])
            ->create();
    }
}

