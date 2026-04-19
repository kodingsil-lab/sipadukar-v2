<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DummyCleanupSeeder extends Seeder
{
    public function run()
    {
        $dokumenIds = $this->collectDummyDokumenIds();
        $subBagianIds = $this->collectDummySubBagianIds();
        $userIds = $this->collectDummyUserIds();

        if (! empty($dokumenIds)) {
            $this->db->table('riwayat_dokumen')->whereIn('dokumen_id', $dokumenIds)->delete();
            $this->db->table('review_dokumen')->whereIn('dokumen_id', $dokumenIds)->delete();
            $this->db->table('dokumen')->whereIn('id', $dokumenIds)->delete();
        }

        if (! empty($subBagianIds)) {
            $this->db->table('sub_bagian')->whereIn('id', $subBagianIds)->delete();
        }

        $this->db->table('peraturan')->like('slug', 'peraturan-dummy-', 'after')->delete();
        $this->db->table('instrumen')->like('slug', 'instrumen-dummy-', 'after')->delete();

        if (! empty($userIds)) {
            $this->db->table('user_roles')->whereIn('user_id', $userIds)->delete();
            $this->db->table('users')->whereIn('id', $userIds)->delete();
        }
    }

    /**
     * Dokumen dummy yang dibuat oleh DummyFullSeeder lama dan baru.
     */
    private function collectDummyDokumenIds(): array
    {
        $rows = $this->db->table('dokumen')
            ->select('id')
            ->groupStart()
                ->like('slug_dokumen', 'dokumen-dummy-', 'after')
                ->orWhere("slug_dokumen REGEXP '^k[0-9]+-sb[0-9]+-ps[0-9]+-d[0-9]+$'", null, false)
            ->groupEnd()
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Sub bagian dummy yang dibuat oleh DummyFullSeeder lama dan baru.
     */
    private function collectDummySubBagianIds(): array
    {
        $rows = $this->db->table('sub_bagian')
            ->select('id')
            ->groupStart()
                ->like('slug_sub_bagian', 'sub-bagian-dummy-', 'after')
                ->orWhere("slug_sub_bagian REGEXP '^k[0-9]+-sb-[0-9]+$'", null, false)
                ->orLike('nama_sub_bagian', 'Nama Sub Bagian ', 'after')
            ->groupEnd()
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function collectDummyUserIds(): array
    {
        $rows = $this->db->table('users')
            ->select('id')
            ->like('username', 'dummy-', 'after')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}
