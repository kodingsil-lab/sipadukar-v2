<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'nama_lengkap'   => 'Administrator SIPADUKAR',
            'username'       => 'admin',
            'email'          => 'admin@sipadukar.local',
            'password_hash'  => password_hash('admin123', PASSWORD_DEFAULT),
            'nip'            => null,
            'unit_kerja'     => 'LPM',
            'jabatan'        => 'Administrator',
            'foto'           => null,
            'is_aktif'       => 1,
            'terakhir_login' => null,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $userId = $this->db->insertID();

        $roleAdmin = $this->db->table('roles')
            ->where('slug_role', 'admin')
            ->get()
            ->getRow();

        if ($roleAdmin) {
            $this->db->table('user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleAdmin->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}