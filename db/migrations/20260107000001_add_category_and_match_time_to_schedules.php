<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migrasi untuk tabel schedules cabor: menambahkan kolom kategori dan jam_pertandingan.
 */
final class AddCategoryAndMatchTimeToSchedules extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('schedules')) {
            $table = $this->table('schedules');
            $table->addColumn('sport_type', 'string', ['limit' => 30, 'comment' => 'Slug cabor (badminton, futsal, pingpong)'])
                  ->addColumn('kategori', 'string', ['limit' => 150, 'null' => true, 'comment' => 'Kategori pertandingan (contoh: Ganda Putra & Putri, Umum, U-19)'])
                  ->addColumn('date_range', 'string', ['limit' => 150, 'null' => true, 'comment' => 'Rentang tanggal pelaksanaan (contoh: 12 - 16 Agustus 2026)'])
                  ->addColumn('jam_pertandingan', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Jam pertandingan (contoh: 08:00 WIB - Selesai)'])
                  ->addColumn('venue', 'string', ['limit' => 150, 'null' => true, 'comment' => 'Nama tempat / lokasi lapangan'])
                  ->addColumn('notes', 'text', ['null' => true, 'comment' => 'Catatan tambahan'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['sport_type'], ['unique' => true])
                  ->create();
        } else {
            $table = $this->table('schedules');
            if (!$table->hasColumn('kategori')) {
                $table->addColumn('kategori', 'string', ['limit' => 150, 'null' => true, 'after' => 'sport_type', 'comment' => 'Kategori pertandingan']);
            }
            if (!$table->hasColumn('jam_pertandingan')) {
                $table->addColumn('jam_pertandingan', 'string', ['limit' => 100, 'null' => true, 'after' => 'date_range', 'comment' => 'Jam pertandingan']);
            }
            $table->update();
        }
    }
}

