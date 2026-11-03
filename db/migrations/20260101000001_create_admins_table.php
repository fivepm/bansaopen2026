<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdminsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('admins')
            ->addColumn('username', 'string', ['limit' => 50])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('name', 'string', ['limit' => 100, 'default' => 'Panitia'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['username'], ['unique' => true])
            ->create();
    }
}

