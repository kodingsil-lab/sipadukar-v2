<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelReviewDokumen extends Migration
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
            'dokumen_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'reviewer_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'status_review' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'ditinjau',
                    'perlu_revisi',
                    'tervalidasi',
                    'ditolak'
                ],
                'default' => 'ditinjau',
            ],
            'catatan_review' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tanggal_review' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey('dokumen_id');
        $this->forge->addKey('reviewer_id');
        $this->forge->addKey('status_review');

        $this->forge->addForeignKey('dokumen_id', 'dokumen', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reviewer_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('review_dokumen', true);
    }

    public function down()
    {
        $this->forge->dropTable('review_dokumen', true);
    }
}