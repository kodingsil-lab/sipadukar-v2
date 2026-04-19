<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahSumberDokumenLinkPadaDokumen extends Migration
{
    public function up()
    {
        $fields = [
            'sumber_dokumen' => [
                'type'       => 'ENUM',
                'constraint' => ['file', 'link'],
                'default'    => 'file',
                'after'      => 'jenis_dokumen',
            ],
            'link_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
                'after'      => 'sumber_dokumen',
            ],
        ];

        $this->forge->addColumn('dokumen', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('dokumen', ['sumber_dokumen', 'link_dokumen']);
    }
}
