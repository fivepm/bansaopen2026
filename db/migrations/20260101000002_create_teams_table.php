<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTeamsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('teams')
            ->addColumn('sport', 'string', ['limit' => 20, 'default' => 'badminton'])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('members', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Nama pemain, pisahkan dengan koma'])
            ->addColumn('institution', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('contact', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['sport'])
            ->create();
    }
}

