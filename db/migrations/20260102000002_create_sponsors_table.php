<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSponsorsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('sponsors')
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('logo_path', 'string', ['limit' => 255])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['is_active'])
            ->create();
    }
}

