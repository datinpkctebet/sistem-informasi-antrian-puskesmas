<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueKiosk extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 2,
                'comment'    => 'A=Ibu&Anak, B=Dewasa&Lansia, C=Prioritas, D=Informasi, E=Kasir',
            ],
            'nomor_antrian' => [
                'type'       => 'INT',
                'constraint' => 11,
                'comment'    => 'Nomor urut harian per kategori, reset tiap hari',
            ],
            'full_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'comment'    => 'Gabungan kategori+nomor, contoh B0184',
            ],
            'nama_pasien' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['waiting', 'calling', 'done'],
                'default'    => 'waiting',
            ],
            'loket' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'petugas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'waktu_masuk' => [
                'type' => 'DATETIME',
            ],
            'waktu_panggil' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'waktu_selesai' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_warning' => [
                'type'   => 'BIGINT',
                'null'   => true,
            ],
            'last_called_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'call_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('kategori');
        $this->forge->addKey('status');
        $this->forge->addKey('tanggal');
        $this->forge->addKey(['kategori', 'tanggal']); // composite, untuk generate nomor harian cepat

        $this->forge->createTable('queue_kiosk');
    }

    public function down()
    {
        $this->forge->dropTable('queue_kiosk');
    }
}