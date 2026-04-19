<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelSubBagian extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kriteria_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_sub_bagian' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'slug_sub_bagian' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 1,
            ],
            'dibuat_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'diupdate_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('kriteria_id');
        $this->forge->addKey('dibuat_oleh');
        $this->forge->addKey('diupdate_oleh');
        $this->forge->addUniqueKey(['kriteria_id', 'slug_sub_bagian']);

        $this->forge->addForeignKey('kriteria_id', 'kriterias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('diupdate_oleh', 'users', 'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('sub_bagian', true);
    }

    public function down()
    {
        $this->forge->dropTable('sub_bagian', true);
    }
}