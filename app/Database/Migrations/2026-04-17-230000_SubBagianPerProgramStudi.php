<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SubBagianPerProgramStudi extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('program_studi_id', 'sub_bagian')) {
            $this->forge->addColumn('sub_bagian', [
                'program_studi_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'kriteria_id',
                ],
            ]);
        }

        $this->migrateDataPerProgramStudi();

        if ($this->indexExists('sub_bagian', 'kriteria_id_slug_sub_bagian')) {
            $this->db->query('ALTER TABLE sub_bagian DROP INDEX kriteria_id_slug_sub_bagian');
        }

        if (! $this->indexExists('sub_bagian', 'sub_bagian_program_studi_id_index')) {
            $this->db->query('ALTER TABLE sub_bagian ADD INDEX sub_bagian_program_studi_id_index (program_studi_id)');
        }

        if (! $this->indexExists('sub_bagian', 'uniq_subbagian_kriteria_prodi_slug')) {
            $this->db->query('ALTER TABLE sub_bagian ADD UNIQUE INDEX uniq_subbagian_kriteria_prodi_slug (kriteria_id, program_studi_id, slug_sub_bagian)');
        }

        if (! $this->foreignKeyExists('sub_bagian', 'sub_bagian_program_studi_id_foreign')) {
            $this->db->query('ALTER TABLE sub_bagian ADD CONSTRAINT sub_bagian_program_studi_id_foreign FOREIGN KEY (program_studi_id) REFERENCES program_studi(id) ON DELETE SET NULL ON UPDATE SET NULL');
        }
    }

    public function down()
    {
        if ($this->foreignKeyExists('sub_bagian', 'sub_bagian_program_studi_id_foreign')) {
            $this->db->query('ALTER TABLE sub_bagian DROP FOREIGN KEY sub_bagian_program_studi_id_foreign');
        }

        if ($this->indexExists('sub_bagian', 'uniq_subbagian_kriteria_prodi_slug')) {
            $this->db->query('ALTER TABLE sub_bagian DROP INDEX uniq_subbagian_kriteria_prodi_slug');
        }

        if (! $this->indexExists('sub_bagian', 'kriteria_id_slug_sub_bagian')) {
            $this->db->query('ALTER TABLE sub_bagian ADD UNIQUE INDEX kriteria_id_slug_sub_bagian (kriteria_id, slug_sub_bagian)');
        }

        if ($this->indexExists('sub_bagian', 'sub_bagian_program_studi_id_index')) {
            $this->db->query('ALTER TABLE sub_bagian DROP INDEX sub_bagian_program_studi_id_index');
        }

        if ($this->db->fieldExists('program_studi_id', 'sub_bagian')) {
            $this->forge->dropColumn('sub_bagian', 'program_studi_id');
        }
    }

    private function migrateDataPerProgramStudi(): void
    {
        $subBagianList = $this->db->table('sub_bagian')->get()->getResultArray();
        foreach ($subBagianList as $subBagian) {
            $subBagianId = (int) ($subBagian['id'] ?? 0);
            if ($subBagianId <= 0) {
                continue;
            }

            $prodiRows = $this->db->table('dokumen')
                ->select('program_studi_id')
                ->where('sub_bagian_id', $subBagianId)
                ->where('deleted_at', null)
                ->where('program_studi_id IS NOT NULL', null, false)
                ->groupBy('program_studi_id')
                ->orderBy('program_studi_id', 'ASC')
                ->get()
                ->getResultArray();

            $prodiIds = [];
            foreach ($prodiRows as $row) {
                $prodiId = (int) ($row['program_studi_id'] ?? 0);
                if ($prodiId > 0) {
                    $prodiIds[] = $prodiId;
                }
            }

            if (empty($prodiIds)) {
                continue;
            }

            $primaryProdiId = (int) array_shift($prodiIds);
            $this->db->table('sub_bagian')
                ->where('id', $subBagianId)
                ->update(['program_studi_id' => $primaryProdiId]);

            foreach ($prodiIds as $prodiId) {
                $clone = $subBagian;
                unset($clone['id']);
                $clone['program_studi_id'] = $prodiId;
                $clone['created_at'] = date('Y-m-d H:i:s');
                $clone['updated_at'] = date('Y-m-d H:i:s');

                $this->db->table('sub_bagian')->insert($clone);
                $newSubBagianId = (int) $this->db->insertID();
                if ($newSubBagianId <= 0) {
                    continue;
                }

                $this->db->table('dokumen')
                    ->where('sub_bagian_id', $subBagianId)
                    ->where('program_studi_id', $prodiId)
                    ->update(['sub_bagian_id' => $newSubBagianId]);
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = $this->db->query('SHOW INDEX FROM ' . $this->db->escapeIdentifiers($table) . ' WHERE Key_name = ' . $this->db->escape($indexName))->getResultArray();
        return ! empty($result);
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = (string) $this->db->getDatabase();
        $result = $this->db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ' . $this->db->escape($database) . ' AND TABLE_NAME = ' . $this->db->escape($table) . ' AND CONSTRAINT_TYPE = \'FOREIGN KEY\' AND CONSTRAINT_NAME = ' . $this->db->escape($constraintName)
        )->getResultArray();

        return ! empty($result);
    }
}
