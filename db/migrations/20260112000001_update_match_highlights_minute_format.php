<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateMatchHighlightsMinuteFormat extends AbstractMigration
{
    public function change(): void
    {
        // Ubah kolom minute menjadi string agar bisa menampung format injury time seperti "5+1", "10+2", dll.
        $this->table('match_highlights')
            ->changeColumn('minute', 'string', ['limit' => 20, 'default' => '1'])
            ->update();
    }
}

