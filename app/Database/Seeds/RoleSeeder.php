<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'nama_role'  => 'Admin',
                'slug_role'  => 'admin',
                'deskripsi'  => 'Akses penuh sistem',
                'is_aktif'   => 1,
            ],
            [
                'nama_role'  => 'LPM',
                'slug_role'  => 'lpm',
                'deskripsi'  => 'Pengelola mutu dan validator dokumen',
                'is_aktif'   => 1,
            ],
            [
                'nama_role'  => 'Dekan',
                'slug_role'  => 'dekan',
                'deskripsi'  => 'Pemantau dokumen tingkat UPPS',
                'is_aktif'   => 1,
            ],
            [
                'nama_role'  => 'Kaprodi',
                'slug_role'  => 'kaprodi',
                'deskripsi'  => 'Pemantau dokumen tingkat program studi',
                'is_aktif'   => 1,
            ],
            [
                'nama_role'  => 'Dosen',
                'slug_role'  => 'dosen',
                'deskripsi'  => 'Petugas pengunggah dokumen',
                'is_aktif'   => 1,
            ],
            [
                'nama_role'  => 'Asesor',
                'slug_role'  => 'asesor',
                'deskripsi'  => 'Pembaca dan penilai dokumen',
                'is_aktif'   => 1,
            ],
        ];

        $builder = $this->db->table('roles');

        foreach ($data as $row) {
            $existing = $builder
                ->select('id')
                ->where('slug_role', $row['slug_role'])
                ->get()
                ->getRowArray();

            if (is_array($existing)) {
                $builder->where('id', (int) $existing['id'])->update([
                    'nama_role'  => $row['nama_role'],
                    'deskripsi'  => $row['deskripsi'],
                    'is_aktif'   => $row['is_aktif'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            $builder->insert([
                'nama_role'  => $row['nama_role'],
                'slug_role'  => $row['slug_role'],
                'deskripsi'  => $row['deskripsi'],
                'is_aktif'   => $row['is_aktif'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
