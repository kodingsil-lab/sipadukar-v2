<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureDefaultKriteriasExist extends Migration
{
    public function up()
    {
        $table = $this->db->table('kriterias');
        $now = date('Y-m-d H:i:s');

        $defaults = [
            [
                'nomor_kriteria' => 1,
                'kode' => 'K1',
                'nama_kriteria' => 'Visi, Misi, Tujuan, dan Strategi',
                'deskripsi' => 'Dokumen dan data terkait visi, misi, tujuan, dan strategi institusi/program studi.',
                'urutan' => 1,
            ],
            [
                'nomor_kriteria' => 2,
                'kode' => 'K2',
                'nama_kriteria' => 'Tata Pamong, Tata Kelola, dan Kerja Sama',
                'deskripsi' => 'Dokumen dan data terkait tata pamong, tata kelola, kepemimpinan, dan kerja sama.',
                'urutan' => 2,
            ],
            [
                'nomor_kriteria' => 3,
                'kode' => 'K3',
                'nama_kriteria' => 'Mahasiswa',
                'deskripsi' => 'Dokumen dan data terkait mahasiswa, layanan, prestasi, dan pembinaan.',
                'urutan' => 3,
            ],
            [
                'nomor_kriteria' => 4,
                'kode' => 'K4',
                'nama_kriteria' => 'Sumber Daya Manusia',
                'deskripsi' => 'Dokumen dan data terkait dosen, tenaga kependidikan, kualifikasi, dan pengembangan SDM.',
                'urutan' => 4,
            ],
            [
                'nomor_kriteria' => 5,
                'kode' => 'K5',
                'nama_kriteria' => 'Keuangan, Sarana, dan Prasarana',
                'deskripsi' => 'Dokumen dan data terkait pembiayaan, sarana, prasarana, dan sistem pendukung.',
                'urutan' => 5,
            ],
            [
                'nomor_kriteria' => 6,
                'kode' => 'K6',
                'nama_kriteria' => 'Pendidikan',
                'deskripsi' => 'Dokumen dan data terkait kurikulum, proses pembelajaran, evaluasi, dan mutu pendidikan.',
                'urutan' => 6,
            ],
            [
                'nomor_kriteria' => 7,
                'kode' => 'K7',
                'nama_kriteria' => 'Penelitian',
                'deskripsi' => 'Dokumen dan data terkait kegiatan, luaran, dan pengelolaan penelitian.',
                'urutan' => 7,
            ],
            [
                'nomor_kriteria' => 8,
                'kode' => 'K8',
                'nama_kriteria' => 'Pengabdian kepada Masyarakat',
                'deskripsi' => 'Dokumen dan data terkait kegiatan, luaran, dan pengelolaan pengabdian kepada masyarakat.',
                'urutan' => 8,
            ],
            [
                'nomor_kriteria' => 9,
                'kode' => 'K9',
                'nama_kriteria' => 'Luaran dan Capaian Tridharma',
                'deskripsi' => 'Dokumen dan data terkait luaran, capaian, prestasi, publikasi, dan hasil tridharma.',
                'urutan' => 9,
            ],
        ];

        foreach ($defaults as $row) {
            $existing = $table
                ->where('kode', $row['kode'])
                ->get()
                ->getRowArray();

            $payload = [
                'nomor_kriteria' => $row['nomor_kriteria'],
                'nama_kriteria' => $row['nama_kriteria'],
                'deskripsi' => $row['deskripsi'],
                'urutan' => $row['urutan'],
                'is_aktif' => 1,
                'updated_at' => $now,
            ];

            if (is_array($existing)) {
                $table->where('id', (int) $existing['id'])->update($payload);
                continue;
            }

            $payload['kode'] = $row['kode'];
            $payload['created_at'] = $now;
            $table->insert($payload);
        }
    }

    public function down()
    {
        $codes = ['K1', 'K2', 'K3', 'K4', 'K5', 'K6', 'K7', 'K8', 'K9'];
        $this->db->table('kriterias')->whereIn('kode', $codes)->delete();
    }
}