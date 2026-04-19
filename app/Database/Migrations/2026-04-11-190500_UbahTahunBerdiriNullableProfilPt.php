<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UbahTahunBerdiriNullableProfilPt extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('profil_pt', [
            'tahun_berdiri' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('profil_pt', [
            'tahun_berdiri' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => false,
            ],
        ]);
    }
}
