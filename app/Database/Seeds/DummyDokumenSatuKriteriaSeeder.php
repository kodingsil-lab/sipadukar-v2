<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DummyDokumenSatuKriteriaSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $kriteria = $this->db->table('kriterias')
            ->select('id, kode, nama_kriteria')
            ->where('is_aktif', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! $kriteria) {
            return;
        }

        $kriteriaId = (int) ($kriteria['id'] ?? 0);
        if ($kriteriaId <= 0) {
            return;
        }

        $userId = (int) (($this->db->table('users')
            ->select('id')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray()['id'] ?? 0));

        $reviewerId = (int) (($this->db->table('users u')
            ->select('u.id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'inner')
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->where('u.deleted_at', null)
            ->where('r.slug_role', 'lpm')
            ->orderBy('u.id', 'ASC')
            ->get()
            ->getRowArray()['id'] ?? $userId));

        $programStudi = $this->db->table('program_studi')
            ->select('id, nama_program_studi')
            ->orderBy('is_aktif_akreditasi', 'DESC')
            ->orderBy('nama_program_studi', 'ASC')
            ->get()
            ->getRowArray();

        $programStudiId = (int) ($programStudi['id'] ?? 0);
        $namaProgramStudi = trim((string) ($programStudi['nama_program_studi'] ?? 'Program Studi'));

        $kodeKriteria = strtoupper(trim((string) ($kriteria['kode'] ?? ('K' . $kriteriaId))));
        $subBagianTemplates = [
            ['nama' => 'Sub Bagian 1', 'deskripsi' => 'Dokumen kebijakan dan pedoman.'],
            ['nama' => 'Sub Bagian 2', 'deskripsi' => 'Dokumen pelaksanaan dan bukti kegiatan.'],
            ['nama' => 'Sub Bagian 3', 'deskripsi' => 'Dokumen evaluasi dan tindak lanjut.'],
        ];

        foreach ($subBagianTemplates as $index => $template) {
            $urutan = $index + 1;
            $slugSubBagian = strtolower($kodeKriteria) . '-dummy-sb-' . $urutan;

            $subBagian = $this->db->table('sub_bagian')
                ->select('id')
                ->where('kriteria_id', $kriteriaId)
                ->where('slug_sub_bagian', $slugSubBagian)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (! $subBagian) {
                $this->db->table('sub_bagian')->insert([
                    'kriteria_id'     => $kriteriaId,
                    'nama_sub_bagian' => $template['nama'],
                    'slug_sub_bagian' => $slugSubBagian,
                    'deskripsi'       => $template['deskripsi'],
                    'urutan'          => $urutan,
                    'dibuat_oleh'     => $userId > 0 ? $userId : null,
                    'diupdate_oleh'   => $userId > 0 ? $userId : null,
                    'is_aktif'        => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                $subBagianId = (int) $this->db->insertID();
            } else {
                $subBagianId = (int) ($subBagian['id'] ?? 0);
            }

            if ($subBagianId <= 0) {
                continue;
            }

            $slugDokumen = strtolower($kodeKriteria) . '-dummy-doc-sb-' . $urutan;
            $existsDokumen = $this->db->table('dokumen')
                ->where('slug_dokumen', $slugDokumen)
                ->where('deleted_at', null)
                ->countAllResults();

            if ($existsDokumen > 0) {
                continue;
            }

            $dataDokumen = [
                'kriteria_id'      => $kriteriaId,
                'sub_bagian_id'    => $subBagianId,
                'kode_dokumen'     => $kodeKriteria . '-DUMMY-SB' . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT),
                'judul_dokumen'    => 'Dokumen Dummy ' . $kodeKriteria . ' - ' . $template['nama'],
                'slug_dokumen'     => $slugDokumen,
                'deskripsi'        => 'Dokumen dummy untuk pengujian 1 kriteria dengan sub bagian.',
                'nomor_dokumen'    => 'DUMMY/' . $kodeKriteria . '/SB' . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT) . '/' . date('Y'),
                'jenis_dokumen'    => 'Dokumen Pendukung',
                'tahun_dokumen'    => (int) date('Y'),
                'nama_file'        => $slugDokumen . '.pdf',
                'path_file'        => 'dummy/dokumen/' . $slugDokumen . '.pdf',
                'ekstensi_file'    => 'pdf',
                'mime_type'        => 'application/pdf',
                'ukuran_file'      => 102400,
                'versi'            => 1,
                'status_dokumen'   => 'tervalidasi',
                'catatan_terakhir' => 'Data dummy otomatis untuk pengujian.',
                'tanggal_upload'   => $now,
                'tanggal_submit'   => $now,
                'tanggal_validasi' => $now,
                'uploaded_by'      => $userId > 0 ? $userId : null,
                'reviewer_id'      => $reviewerId > 0 ? $reviewerId : null,
                'is_aktif'         => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if ($this->tableHasField('dokumen', 'sumber_dokumen')) {
                $dataDokumen['sumber_dokumen'] = 'file';
                $dataDokumen['link_dokumen'] = null;
            }

            if ($this->tableHasField('dokumen', 'program_studi_id')) {
                $dataDokumen['program_studi_id'] = $programStudiId > 0 ? $programStudiId : null;
            }

            if ($this->tableHasField('dokumen', 'unit_kerja')) {
                $dataDokumen['unit_kerja'] = $namaProgramStudi;
            }

            $this->db->table('dokumen')->insert($dataDokumen);
        }
    }

    private function tableHasField(string $table, string $field): bool
    {
        $fields = $this->db->getFieldData($table);
        foreach ($fields as $column) {
            if (strcasecmp((string) ($column->name ?? ''), $field) === 0) {
                return true;
            }
        }

        return false;
    }
}
