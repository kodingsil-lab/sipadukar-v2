<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahLogoPadaLembagaAkreditasi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('lembaga_akreditasi', [
            'logo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'alamat_website',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('lembaga_akreditasi', 'logo_path');
    }
}
