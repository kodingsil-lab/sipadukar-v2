<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRoleDemoSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'nama_lengkap'  => 'Petugas LPM',
                'username'      => 'lpm',
                'email'         => 'lpm@sipadukar.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'unit_kerja'    => 'LPM',
                'jabatan'       => 'LPM',
                'role_slug'     => 'lpm',
            ],
            [
                'nama_lengkap'  => 'Dekan UPPS',
                'username'      => 'dekan',
                'email'         => 'dekan@sipadukar.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'unit_kerja'    => 'UPPS',
                'jabatan'       => 'Dekan',
                'role_slug'     => 'dekan',
            ],
            [
                'nama_lengkap'  => 'Ketua Program Studi',
                'username'      => 'kaprodi',
                'email'         => 'kaprodi@sipadukar.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'unit_kerja'    => 'Program Studi',
                'jabatan'       => 'Kaprodi',
                'role_slug'     => 'kaprodi',
            ],
            [
                'nama_lengkap'  => 'Dosen Petugas Dokumen',
                'username'      => 'dosen',
                'email'         => 'dosen@sipadukar.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'unit_kerja'    => 'Program Studi',
                'jabatan'       => 'Dosen',
                'role_slug'     => 'dosen',
            ],
            [
                'nama_lengkap'  => 'Asesor',
                'username'      => 'asesor',
                'email'         => 'asesor@sipadukar.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'unit_kerja'    => 'Eksternal',
                'jabatan'       => 'Asesor',
                'role_slug'     => 'asesor',
            ],
        ];

        foreach ($users as $user) {
            $existingUser = $this->db->table('users')
                ->where('username', $user['username'])
                ->get()
                ->getRow();

            if ($existingUser) {
                continue;
            }

            $roleSlug = $user['role_slug'];
            unset($user['role_slug']);

            $user['is_aktif'] = 1;
            $user['created_at'] = date('Y-m-d H:i:s');
            $user['updated_at'] = date('Y-m-d H:i:s');

            $this->db->table('users')->insert($user);
            $userId = $this->db->insertID();

            $role = $this->db->table('roles')
                ->where('slug_role', $roleSlug)
                ->get()
                ->getRow();

            if ($role) {
                $this->db->table('user_roles')->insert([
                    'user_id'    => $userId,
                    'role_id'    => $role->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
