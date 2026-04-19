<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahProgramStudiIdPadaUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'program_studi_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'unit_kerja',
            ],
        ]);

        $this->forge->addKey('program_studi_id');
        $this->forge->addForeignKey('program_studi_id', 'program_studi', 'id', 'SET NULL', 'SET NULL');

        $sql = "
            UPDATE users u
            LEFT JOIN program_studi ps
              ON LOWER(TRIM(u.unit_kerja)) = LOWER(TRIM(ps.nama_program_studi))
              OR LOWER(TRIM(u.unit_kerja)) = LOWER(TRIM(ps.nama_singkatan))
              OR LOWER(TRIM(u.unit_kerja)) = LOWER(TRIM(ps.kode_program_studi_pddikti))
            SET u.program_studi_id = ps.id
            WHERE u.program_studi_id IS NULL
        ";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->forge->dropForeignKey('users', 'users_program_studi_id_foreign');
        $this->forge->dropColumn('users', 'program_studi_id');
    }
}

