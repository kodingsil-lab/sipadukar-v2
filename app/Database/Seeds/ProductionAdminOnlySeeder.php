<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class ProductionAdminOnlySeeder extends Seeder
{
    public function run()
    {
        $adminRole = $this->db->table('roles')
            ->select('id')
            ->where('slug_role', 'admin')
            ->get()
            ->getRowArray();

        if (! is_array($adminRole) || (int) ($adminRole['id'] ?? 0) <= 0) {
            throw new RuntimeException('Role admin tidak ditemukan. Proses cleanup dibatalkan.');
        }

        $adminRoleId = (int) $adminRole['id'];

        $adminRows = $this->db->table('user_roles ur')
            ->select('ur.user_id')
            ->join('users u', 'u.id = ur.user_id', 'inner')
            ->where('ur.role_id', $adminRoleId)
            ->where('u.deleted_at', null)
            ->groupBy('ur.user_id')
            ->get()
            ->getResultArray();

        $adminUserIds = array_values(array_unique(array_map(
            static fn (array $row): int => (int) ($row['user_id'] ?? 0),
            $adminRows
        )));
        $adminUserIds = array_values(array_filter($adminUserIds, static fn (int $id): bool => $id > 0));

        if (empty($adminUserIds)) {
            throw new RuntimeException('Tidak ada user admin aktif. Proses cleanup dibatalkan untuk mencegah lockout.');
        }

        $targetRows = $this->db->table('users')
            ->select('id')
            ->whereNotIn('id', $adminUserIds)
            ->get()
            ->getResultArray();

        $targetUserIds = array_values(array_unique(array_map(
            static fn (array $row): int => (int) ($row['id'] ?? 0),
            $targetRows
        )));
        $targetUserIds = array_values(array_filter($targetUserIds, static fn (int $id): bool => $id > 0));

        if (empty($targetUserIds)) {
            return;
        }

        $this->db->transStart();

        // Hapus relasi role user non-admin terlebih dahulu.
        $this->db->table('user_roles')->whereIn('user_id', $targetUserIds)->delete();

        // Hapus user non-admin. FK dokumen/audit menggunakan SET NULL atau CASCADE sesuai skema.
        $this->db->table('users')->whereIn('id', $targetUserIds)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Cleanup admin-only gagal dan transaction di-rollback.');
        }
    }
}
