<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateMasterDokumenKriteriaSubBagianOpsional extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('nama_sub_bagian', 'master_dokumen_kriteria')) {
            $this->forge->addColumn('master_dokumen_kriteria', [
                'nama_sub_bagian' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'sub_bagian_id',
                ],
            ]);
        }

        $this->forge->modifyColumn('master_dokumen_kriteria', [
            'sub_bagian_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('nama_sub_bagian', 'master_dokumen_kriteria')) {
            $this->forge->dropColumn('master_dokumen_kriteria', 'nama_sub_bagian');
        }

        $this->forge->modifyColumn('master_dokumen_kriteria', [
            'sub_bagian_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => false,
            ],
        ]);
    }
}
