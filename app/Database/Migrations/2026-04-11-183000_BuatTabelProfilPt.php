<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelProfilPt extends Migration
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
            'nama_pt' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nama_singkatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status_pt' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'badan_penyelenggara' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'kode_pt_pddikti' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'tahun_berdiri' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'alamat_lengkap' => [
                'type' => 'TEXT',
            ],
            'website_resmi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'email_resmi_pt' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nomor_telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'status_akreditasi_pt' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'nomor_sk_akreditasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'tanggal_sk' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_berlaku_akreditasi' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_berakhir_akreditasi' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'file_sk_akreditasi_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_sertifikat_akreditasi_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'lembaga_akreditasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('updated_by');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('profil_pt', true);
    }

    public function down()
    {
        $this->forge->dropTable('profil_pt', true);
    }
}

