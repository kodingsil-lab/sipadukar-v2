<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelKriterias extends Migration
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
            'nomor_kriteria' => [
                'type'       => 'INT',
                'constraint' => 2,
                'unsigned'   => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nama_kriteria' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 1,
            ],
            'is_aktif' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_kriteria');
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('kriterias', true);
    }

    public function down()
    {
        $this->forge->dropTable('kriterias', true);
    }
}