<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahProgramStudiIdPadaDokumen extends Migration
{
    public function up()
    {
        $this->forge->addColumn('dokumen', [
            'program_studi_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'reviewer_id',
            ],
        ]);

        $this->forge->addKey('program_studi_id');
        $this->forge->addForeignKey('program_studi_id', 'program_studi', 'id', 'SET NULL', 'SET NULL');

        $this->db->query("
            UPDATE dokumen d
            LEFT JOIN users u ON u.id = d.uploaded_by
            SET d.program_studi_id = u.program_studi_id
            WHERE d.program_studi_id IS NULL AND u.program_studi_id IS NOT NULL
        ");

        $this->db->query("
            UPDATE dokumen d
            LEFT JOIN program_studi ps
              ON LOWER(TRIM(d.unit_kerja)) = LOWER(TRIM(ps.nama_program_studi))
              OR LOWER(TRIM(d.unit_kerja)) = LOWER(TRIM(ps.nama_singkatan))
              OR LOWER(TRIM(d.unit_kerja)) = LOWER(TRIM(ps.kode_program_studi_pddikti))
            SET d.program_studi_id = ps.id
            WHERE d.program_studi_id IS NULL
        ");
    }

    public function down()
    {
        $this->forge->dropForeignKey('dokumen', 'dokumen_program_studi_id_foreign');
        $this->forge->dropColumn('dokumen', 'program_studi_id');
    }
}

