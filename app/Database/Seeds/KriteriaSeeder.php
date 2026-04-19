<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'nomor_kriteria' => 1,
                'kode'           => 'K1',
                'nama_kriteria'  => 'Visi, Misi, Tujuan, dan Strategi',
                'deskripsi'      => 'Dokumen dan data terkait visi, misi, tujuan, dan strategi institusi/program studi.',
                'urutan'         => 1,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 2,
                'kode'           => 'K2',
                'nama_kriteria'  => 'Tata Pamong, Tata Kelola, dan Kerja Sama',
                'deskripsi'      => 'Dokumen dan data terkait tata pamong, tata kelola, kepemimpinan, dan kerja sama.',
                'urutan'         => 2,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 3,
                'kode'           => 'K3',
                'nama_kriteria'  => 'Mahasiswa',
                'deskripsi'      => 'Dokumen dan data terkait mahasiswa, layanan, prestasi, dan pembinaan.',
                'urutan'         => 3,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 4,
                'kode'           => 'K4',
                'nama_kriteria'  => 'Sumber Daya Manusia',
                'deskripsi'      => 'Dokumen dan data terkait dosen, tenaga kependidikan, kualifikasi, dan pengembangan SDM.',
                'urutan'         => 4,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 5,
                'kode'           => 'K5',
                'nama_kriteria'  => 'Keuangan, Sarana, dan Prasarana',
                'deskripsi'      => 'Dokumen dan data terkait pembiayaan, sarana, prasarana, dan sistem pendukung.',
                'urutan'         => 5,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 6,
                'kode'           => 'K6',
                'nama_kriteria'  => 'Pendidikan',
                'deskripsi'      => 'Dokumen dan data terkait kurikulum, proses pembelajaran, evaluasi, dan mutu pendidikan.',
                'urutan'         => 6,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 7,
                'kode'           => 'K7',
                'nama_kriteria'  => 'Penelitian',
                'deskripsi'      => 'Dokumen dan data terkait kegiatan, luaran, dan pengelolaan penelitian.',
                'urutan'         => 7,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 8,
                'kode'           => 'K8',
                'nama_kriteria'  => 'Pengabdian kepada Masyarakat',
                'deskripsi'      => 'Dokumen dan data terkait kegiatan, luaran, dan pengelolaan pengabdian kepada masyarakat.',
                'urutan'         => 8,
                'is_aktif'       => 1,
            ],
            [
                'nomor_kriteria' => 9,
                'kode'           => 'K9',
                'nama_kriteria'  => 'Luaran dan Capaian Tridharma',
                'deskripsi'      => 'Dokumen dan data terkait luaran, capaian, prestasi, publikasi, dan hasil tridharma.',
                'urutan'         => 9,
                'is_aktif'       => 1,
            ],
        ];

        $builder = $this->db->table('kriterias');

        foreach ($data as $row) {
            $existing = $builder
                ->select('id')
                ->where('kode', $row['kode'])
                ->get()
                ->getRowArray();

            if (is_array($existing)) {
                $builder->where('id', (int) $existing['id'])->update([
                    'nomor_kriteria' => $row['nomor_kriteria'],
                    'nama_kriteria'  => $row['nama_kriteria'],
                    'deskripsi'      => $row['deskripsi'],
                    'urutan'         => $row['urutan'],
                    'is_aktif'       => $row['is_aktif'],
                    'updated_at'     => $now,
                ]);
                continue;
            }

            $builder->insert([
                'nomor_kriteria' => $row['nomor_kriteria'],
                'kode'           => $row['kode'],
                'nama_kriteria'  => $row['nama_kriteria'],
                'deskripsi'      => $row['deskripsi'],
                'urutan'         => $row['urutan'],
                'is_aktif'       => $row['is_aktif'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}