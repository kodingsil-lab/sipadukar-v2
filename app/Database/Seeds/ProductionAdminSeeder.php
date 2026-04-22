<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class ProductionAdminSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roleTable = $this->db->table('roles');
        $userTable = $this->db->table('users');
        $userRoleTable = $this->db->table('user_roles');

        $adminRole = $roleTable
            ->where('slug_role', 'admin')
            ->get()
            ->getRowArray();

        if (! is_array($adminRole)) {
            $roleTable->insert([
                'nama_role'  => 'Admin',
                'slug_role'  => 'admin',
                'deskripsi'  => 'Akses penuh sistem',
                'is_aktif'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $adminRole = $roleTable
                ->where('slug_role', 'admin')
                ->get()
                ->getRowArray();
        }

        $adminUser = $userTable
            ->where('username', 'admin')
            ->get()
            ->getRowArray();

        if (! is_array($adminUser)) {
            $adminPassword = $this->resolveAdminPassword();

            $userTable->insert([
                'nama_lengkap'   => 'Administrator SIPADUKAR',
                'username'       => 'admin',
                'email'          => 'admin@sipadukar.local',
                'password_hash'  => password_hash($adminPassword, PASSWORD_DEFAULT),
                'nip'            => null,
                'unit_kerja'     => 'LPM',
                'jabatan'        => 'Administrator',
                'foto'           => null,
                'is_aktif'       => 1,
                'terakhir_login' => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            $adminUser = $userTable
                ->where('username', 'admin')
                ->get()
                ->getRowArray();
        }

        $adminRoleId = (int) ($adminRole['id'] ?? 0);
        $adminUserId = (int) ($adminUser['id'] ?? 0);

        if ($adminRoleId <= 0 || $adminUserId <= 0) {
            return;
        }

        $existingRelation = $userRoleTable
            ->where('user_id', $adminUserId)
            ->where('role_id', $adminRoleId)
            ->countAllResults();

        if ($existingRelation === 0) {
            $userRoleTable->insert([
                'user_id'    => $adminUserId,
                'role_id'    => $adminRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function resolveAdminPassword(): string
    {
        $password = trim((string) env('SEED_PRODUCTION_ADMIN_PASSWORD', ''));
        if ($password === '') {
            throw new RuntimeException('SEED_PRODUCTION_ADMIN_PASSWORD wajib diisi saat menjalankan ProductionAdminSeeder.');
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('SEED_PRODUCTION_ADMIN_PASSWORD minimal 12 karakter.');
        }

        return $password;
    }
}
