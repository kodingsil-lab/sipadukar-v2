<?php

namespace App\Database\Seeds;

use App\Models\UserProgramStudiAssignmentModel;
use CodeIgniter\Database\Seeder;

class DosenMultiProdiSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $role = $this->db->table('roles')
            ->select('id')
            ->where('slug_role', 'dosen')
            ->get()
            ->getRowArray();

        if (! $role) {
            return;
        }

        $programStudiList = $this->db->table('program_studi')
            ->select('id, nama_program_studi')
            ->orderBy('is_aktif_akreditasi', 'DESC')
            ->orderBy('nama_program_studi', 'ASC')
            ->limit(3)
            ->get()
            ->getResultArray();

        if (count($programStudiList) < 2) {
            return;
        }

        $primaryProgramStudiId = (int) ($programStudiList[0]['id'] ?? 0);
        $additionalProgramStudiIds = [];
        foreach (array_slice($programStudiList, 1) as $programStudi) {
            $programStudiId = (int) ($programStudi['id'] ?? 0);
            if ($programStudiId > 0) {
                $additionalProgramStudiIds[] = $programStudiId;
            }
        }

        if ($primaryProgramStudiId <= 0 || empty($additionalProgramStudiIds)) {
            return;
        }

        $user = $this->db->table('users')
            ->select('id')
            ->where('username', 'demo-dosen-multiprodi')
            ->get()
            ->getRowArray();

        if ($user) {
            $userId = (int) ($user['id'] ?? 0);
            $this->db->table('users')
                ->where('id', $userId)
                ->update([
                    'program_studi_id' => $primaryProgramStudiId,
                    'updated_at' => $now,
                ]);
        } else {
            $primaryProgramStudi = $programStudiList[0];
            $this->db->table('users')->insert([
                'nama_lengkap' => 'Demo Dosen Multi Prodi',
                'username' => 'demo-dosen-multiprodi',
                'email' => 'demo-dosen-multiprodi@sipadukar.local',
                'password_hash' => password_hash('dosen123', PASSWORD_DEFAULT),
                'nip' => 'DMP-001',
                'unit_kerja' => (string) ($primaryProgramStudi['nama_program_studi'] ?? 'Program Studi'),
                'program_studi_id' => $primaryProgramStudiId,
                'jabatan' => 'Dosen',
                'is_aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $userId = (int) $this->db->insertID();
        }

        if ($userId <= 0) {
            return;
        }

        $roleExists = $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', (int) $role['id'])
            ->countAllResults();

        if (! $roleExists) {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => (int) $role['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        (new UserProgramStudiAssignmentModel())->syncAssignments($userId, $additionalProgramStudiIds);
    }
}