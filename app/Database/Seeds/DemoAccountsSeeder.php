<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoAccountsSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            [
                'nama_lengkap' => 'Administrator SIPADUKAR',
                'username'     => 'admin-sipadukar',
                'email'        => 'admin@sipadukar.local',
                'unit_kerja'   => 'LPM',
                'jabatan'      => 'Administrator',
                'role_slug'    => 'admin',
            ],
            [
                'nama_lengkap' => 'Petugas LPM',
                'username'     => 'lpm-sipadukar',
                'email'        => 'lpm@sipadukar.local',
                'unit_kerja'   => 'LPM',
                'jabatan'      => 'LPM',
                'role_slug'    => 'lpm',
            ],
            [
                'nama_lengkap' => 'Dekan UPPS',
                'username'     => 'dekan-sipadukar',
                'email'        => 'dekan@sipadukar.local',
                'unit_kerja'   => 'UPPS',
                'jabatan'      => 'Dekan',
                'role_slug'    => 'dekan',
            ],
            [
                'nama_lengkap' => 'Ketua Program Studi',
                'username'     => 'kaprodi-sipadukar',
                'email'        => 'kaprodi@sipadukar.local',
                'unit_kerja'   => 'Program Studi',
                'jabatan'      => 'Kaprodi',
                'role_slug'    => 'kaprodi',
            ],
            [
                'nama_lengkap' => 'Dosen Petugas Dokumen',
                'username'     => 'dosen-sipadukar',
                'email'        => 'dosen@sipadukar.local',
                'unit_kerja'   => 'Program Studi',
                'jabatan'      => 'Dosen',
                'role_slug'    => 'dosen',
            ],
            [
                'nama_lengkap' => 'Asesor',
                'username'     => 'asesor-sipadukar',
                'email'        => 'asesor@sipadukar.local',
                'unit_kerja'   => 'Eksternal',
                'jabatan'      => 'Asesor',
                'role_slug'    => 'asesor',
            ],
        ];

        foreach ($accounts as $account) {
            $role = $this->db->table('roles')
                ->where('slug_role', $account['role_slug'])
                ->get()
                ->getRow();

            if (! $role) {
                continue;
            }

            $existingUser = $this->db->table('users')
                ->where('username', $account['username'])
                ->get()
                ->getRow();

            $userData = [
                'nama_lengkap'  => $account['nama_lengkap'],
                'username'      => $account['username'],
                'email'         => $account['email'],
                'password_hash' => password_hash('sipadukar123', PASSWORD_DEFAULT),
                'unit_kerja'    => $account['unit_kerja'],
                'jabatan'       => $account['jabatan'],
                'is_aktif'      => 1,
                'deleted_at'    => null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            if ($existingUser) {
                $this->db->table('users')
                    ->where('id', $existingUser->id)
                    ->update($userData);
                $userId = (int) $existingUser->id;
            } else {
                $userData['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('users')->insert($userData);
                $userId = (int) $this->db->insertID();
            }

            $roleExists = $this->db->table('user_roles')
                ->where('user_id', $userId)
                ->where('role_id', (int) $role->id)
                ->countAllResults();

            if (! $roleExists) {
                $this->db->table('user_roles')->insert([
                    'user_id'    => $userId,
                    'role_id'    => (int) $role->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
