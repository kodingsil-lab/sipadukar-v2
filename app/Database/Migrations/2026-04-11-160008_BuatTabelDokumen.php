<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelDokumen extends Migration
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
            'kriteria_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sub_bagian_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'kode_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'judul_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'slug_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'nomor_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'jenis_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tahun_dokumen' => [
                'type'       => 'YEAR',
                'null'       => true,
            ],
            'nama_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'path_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ekstensi_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ukuran_file' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'versi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'status_dokumen' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'draft',
                    'diajukan',
                    'ditinjau',
                    'perlu_revisi',
                    'disubmit_ulang',
                    'tervalidasi',
                    'ditolak'
                ],
                'default' => 'draft',
            ],
            'catatan_terakhir' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tanggal_upload' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'tanggal_submit' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'tanggal_validasi' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'uploaded_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewer_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_aktif' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug_dokumen');
        $this->forge->addKey('kriteria_id');
        $this->forge->addKey('sub_bagian_id');
        $this->forge->addKey('kode_dokumen');
        $this->forge->addKey('status_dokumen');
        $this->forge->addKey('uploaded_by');
        $this->forge->addKey('reviewer_id');

        $this->forge->addForeignKey('kriteria_id', 'kriterias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sub_bagian_id', 'sub_bagian', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('reviewer_id', 'users', 'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('dokumen', true);
    }

    public function down()
    {
        $this->forge->dropTable('dokumen', true);
    }
}