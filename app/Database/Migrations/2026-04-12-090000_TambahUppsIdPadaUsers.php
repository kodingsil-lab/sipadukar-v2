<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TambahUppsIdPadaUsers extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldNames('users');
        if (! in_array('upps_id', $fields, true)) {
            $this->forge->addColumn('users', [
                'upps_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'program_studi_id',
                ],
            ]);
        }

        try {
            $this->db->query('ALTER TABLE users ADD INDEX idx_users_upps_id (upps_id)');
        } catch (\Throwable $e) {
            // index mungkin sudah ada
        }

        try {
            $this->db->query('ALTER TABLE users ADD CONSTRAINT fk_users_upps_id FOREIGN KEY (upps_id) REFERENCES upps(id) ON UPDATE CASCADE ON DELETE SET NULL');
        } catch (\Throwable $e) {
            // constraint mungkin sudah ada
        }

        // Backfill dasar: jika user sudah punya program_studi_id, ambil upps_id dari prodi tersebut.
        $this->db->query("
            UPDATE users u
            JOIN program_studi ps ON ps.id = u.program_studi_id
            SET u.upps_id = ps.upps_id
            WHERE u.program_studi_id IS NOT NULL
              AND (u.upps_id IS NULL OR u.upps_id = 0)
        ");
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_upps_id');
        } catch (\Throwable $e) {
            // no-op
        }

        try {
            $this->db->query('ALTER TABLE users DROP INDEX idx_users_upps_id');
        } catch (\Throwable $e) {
            // no-op
        }

        $fields = $this->db->getFieldNames('users');
        if (in_array('upps_id', $fields, true)) {
            $this->forge->dropColumn('users', 'upps_id');
        }
    }
}

