<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahUnitKerjaPadaDokumen extends Migration
{
    public function up()
    {
        $fields = [
            'unit_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'reviewer_id',
            ],
        ];

        $this->forge->addColumn('dokumen', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('dokumen', 'unit_kerja');
    }
}