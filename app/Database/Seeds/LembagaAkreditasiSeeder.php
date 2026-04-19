<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LembagaAkreditasiSeeder extends Seeder
{
    public function run()
    {
        $rows = [
            [
                'nama_lembaga_akreditasi' => 'Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT)',
                'nama_singkatan'          => 'BAN-PT',
                'alamat_website'          => 'https://www.banpt.or.id',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Kependidikan (LAMDIK)',
                'nama_singkatan'          => 'LAMDIK',
                'alamat_website'          => 'https://lamdik.or.id/',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Sains Alam dan Ilmu Formal (LAMSAMA)',
                'nama_singkatan'          => 'LAMSAMA',
                'alamat_website'          => 'https://lamsama.or.id/',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Ekonomi Manajemen Bisnis dan Akuntansi (LAMEMBA)',
                'nama_singkatan'          => 'LAMEMBA',
                'alamat_website'          => 'https://lamemba.or.id/',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Pendidikan Tinggi Kesehatan Indonesia (LAM-PTKes)',
                'nama_singkatan'          => 'LAM-PTKes',
                'alamat_website'          => 'https://lamptkes.org/',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Informatika dan Komputer (LAM INFOKOM)',
                'nama_singkatan'          => 'LAM INFOKOM',
                'alamat_website'          => 'https://laminfokom.or.id/official',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Program Studi Teknik (LAM Teknik)',
                'nama_singkatan'          => 'LAM Teknik',
                'alamat_website'          => 'https://lamteknik.or.id/',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Ilmu Sosial, Politik, Administrasi, dan Komunikasi (LAMSPAK)',
                'nama_singkatan'          => 'LAMSPAK',
                'alamat_website'          => 'https://www.lamspak.id/',
                'is_aktif'                => 1,
            ],
            [
                'nama_lembaga_akreditasi' => 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)',
                'nama_singkatan'          => 'LAMDEPILAR',
                'alamat_website'          => 'https://lamdepilar.or.id/',
                'is_aktif'                => 1,
            ],
        ];

        $builder = $this->db->table('lembaga_akreditasi');
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            $existing = $builder
                ->select('id')
                ->where('nama_lembaga_akreditasi', $row['nama_lembaga_akreditasi'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $builder->where('id', (int) $existing['id'])->update([
                    'nama_singkatan' => $row['nama_singkatan'],
                    'alamat_website' => $row['alamat_website'],
                    'is_aktif'       => $row['is_aktif'],
                    'updated_at'     => $now,
                ]);
                continue;
            }

            $builder->insert([
                'nama_lembaga_akreditasi' => $row['nama_lembaga_akreditasi'],
                'nama_singkatan'          => $row['nama_singkatan'],
                'alamat_website'          => $row['alamat_website'],
                'is_aktif'                => $row['is_aktif'],
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);
        }
    }
}
