<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalisasiSubBagianProgramStudiDariDokumenAktif extends Migration
{
    public function up()
    {
        $subBagianList = $this->db->table('sub_bagian')->get()->getResultArray();
        foreach ($subBagianList as $subBagian) {
            $subBagianId = (int) ($subBagian['id'] ?? 0);
            $kriteriaId = (int) ($subBagian['kriteria_id'] ?? 0);
            $slug = trim((string) ($subBagian['slug_sub_bagian'] ?? ''));
            if ($subBagianId <= 0 || $kriteriaId <= 0 || $slug === '') {
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

            $currentProdiId = (int) ($subBagian['program_studi_id'] ?? 0);
            $primaryProdiId = in_array($currentProdiId, $prodiIds, true)
                ? $currentProdiId
                : (int) $prodiIds[0];

            if ($currentProdiId !== $primaryProdiId) {
                $this->db->table('sub_bagian')
                    ->where('id', $subBagianId)
                    ->update(['program_studi_id' => $primaryProdiId]);
            }

            foreach ($prodiIds as $prodiId) {
                if ($prodiId === $primaryProdiId) {
                    $this->db->table('dokumen')
                        ->where('sub_bagian_id', $subBagianId)
                        ->where('program_studi_id', $prodiId)
                        ->where('deleted_at', null)
                        ->update(['sub_bagian_id' => $subBagianId]);
                    continue;
                }

                $target = $this->db->table('sub_bagian')
                    ->select('id')
                    ->where('kriteria_id', $kriteriaId)
                    ->where('program_studi_id', $prodiId)
                    ->where('slug_sub_bagian', $slug)
                    ->get()
                    ->getRowArray();

                $targetId = (int) ($target['id'] ?? 0);
                if ($targetId <= 0) {
                    $clone = $subBagian;
                    unset($clone['id']);
                    $clone['program_studi_id'] = $prodiId;
                    $clone['created_at'] = date('Y-m-d H:i:s');
                    $clone['updated_at'] = date('Y-m-d H:i:s');

                    $this->db->table('sub_bagian')->insert($clone);
                    $targetId = (int) $this->db->insertID();
                }

                if ($targetId <= 0) {
                    continue;
                }

                $this->db->table('dokumen')
                    ->where('sub_bagian_id', $subBagianId)
                    ->where('program_studi_id', $prodiId)
                    ->where('deleted_at', null)
                    ->update(['sub_bagian_id' => $targetId]);
            }
        }
    }

    public function down()
    {
        // Tidak melakukan rollback data normalisasi.
    }
}
