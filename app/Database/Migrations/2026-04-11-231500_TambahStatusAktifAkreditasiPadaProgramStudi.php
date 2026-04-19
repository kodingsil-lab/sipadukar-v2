<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahStatusAktifAkreditasiPadaProgramStudi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('program_studi', [
            'is_aktif_akreditasi' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'lembaga_akreditasi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('program_studi', 'is_aktif_akreditasi');
    }
}

