<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelUserProgramStudiAssignments extends Migration
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
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'program_studi_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('program_studi_id');
        $this->forge->addUniqueKey(['user_id', 'program_studi_id'], 'uniq_user_program_studi_assignment');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('program_studi_id', 'program_studi', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_program_studi_assignments', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_program_studi_assignments', true);
    }
}