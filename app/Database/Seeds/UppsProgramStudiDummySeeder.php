<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UppsProgramStudiDummySeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $userId = $this->getFirstUserId();
        $lembagaCodes = $this->getLembagaCodes();

        $uppsTemplates = [
            [
                'nama_upps' => 'Fakultas Keguruan dan Ilmu Pendidikan',
                'nama_singkatan' => 'FKIP',
                'jenis_unit' => 'Fakultas',
                'nama_pimpinan_upps' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                'nutpk' => 'NUTPK-FKIP-001',
                'email_resmi_upps' => 'fkip@sanpedro.ac.id',
                'nomor_telepon' => '(021) 551-1101',
            ],
            [
                'nama_upps' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam',
                'nama_singkatan' => 'FMIPA',
                'jenis_unit' => 'Fakultas',
                'nama_pimpinan_upps' => 'Dr. Rina Kartika, M.Si.',
                'nutpk' => 'NUTPK-FMIPA-002',
                'email_resmi_upps' => 'fmipa@sanpedro.ac.id',
                'nomor_telepon' => '(021) 551-1102',
            ],
            [
                'nama_upps' => 'Fakultas Ekonomi dan Bisnis',
                'nama_singkatan' => 'FEB',
                'jenis_unit' => 'Fakultas',
                'nama_pimpinan_upps' => 'Dr. Sinta Maharani, M.M.',
                'nutpk' => 'NUTPK-FEB-003',
                'email_resmi_upps' => 'feb@sanpedro.ac.id',
                'nomor_telepon' => '(021) 551-1103',
            ],
            [
                'nama_upps' => 'Fakultas Teknik dan Informatika',
                'nama_singkatan' => 'FTI',
                'jenis_unit' => 'Fakultas',
                'nama_pimpinan_upps' => 'Dr. Bambang Prasetyo, S.T., M.T.',
                'nutpk' => 'NUTPK-FTI-004',
                'email_resmi_upps' => 'fti@sanpedro.ac.id',
                'nomor_telepon' => '(021) 551-1104',
            ],
        ];

        $this->completeExistingUpps($uppsTemplates, $userId, $now);
        $this->ensureTemplateUppsExists($uppsTemplates, $userId, $now);

        $uppsBySingkatan = $this->getUppsBySingkatan();
        $fallbackUppsId = $this->getFallbackUppsId();

        $prodiTemplates = [
            ['nama_program_studi' => 'Pendidikan Bahasa Inggris', 'nama_singkatan' => 'PBI', 'kode_program_studi_pddikti' => '14201', 'jenjang' => 'S1', 'upps' => 'FKIP', 'website' => 'https://pbi.sanpedro.ac.id', 'email' => 'pbi@sanpedro.ac.id', 'telepon' => '(021) 551-2101', 'ketua' => 'Dr. Nur Aisyah, M.Pd.', 'nuptk' => 'NUPTK-PBI-001', 'status' => 'Baik Sekali', 'nomor_sk' => '001/SK-PBI/AKR/2025', 'tanggal_sk' => '2025-01-15', 'mulai' => '2025-02-01', 'berakhir' => '2030-02-01', 'aktif' => 1],
            ['nama_program_studi' => 'Pendidikan Fisika', 'nama_singkatan' => 'PFIS', 'kode_program_studi_pddikti' => '14202', 'jenjang' => 'S1', 'upps' => 'FMIPA', 'website' => 'https://pfis.sanpedro.ac.id', 'email' => 'pfis@sanpedro.ac.id', 'telepon' => '(021) 551-2102', 'ketua' => 'Dr. Satria Wijaya, M.Si.', 'nuptk' => 'NUPTK-PFIS-002', 'status' => 'Baik', 'nomor_sk' => '002/SK-PFIS/AKR/2025', 'tanggal_sk' => '2025-01-20', 'mulai' => '2025-02-05', 'berakhir' => '2030-02-05', 'aktif' => 1],
            ['nama_program_studi' => 'Pendidikan Guru Sekolah Dasar', 'nama_singkatan' => 'PGSD', 'kode_program_studi_pddikti' => '14203', 'jenjang' => 'S1', 'upps' => 'FKIP', 'website' => 'https://pgsd.sanpedro.ac.id', 'email' => 'pgsd@sanpedro.ac.id', 'telepon' => '(021) 551-2103', 'ketua' => 'Dr. Heni Pratiwi, M.Pd.', 'nuptk' => 'NUPTK-PGSD-003', 'status' => 'Unggul', 'nomor_sk' => '003/SK-PGSD/AKR/2025', 'tanggal_sk' => '2025-02-01', 'mulai' => '2025-02-15', 'berakhir' => '2030-02-15', 'aktif' => 1],
            ['nama_program_studi' => 'Pendidikan Matematika', 'nama_singkatan' => 'PMAT', 'kode_program_studi_pddikti' => '14204', 'jenjang' => 'S1', 'upps' => 'FMIPA', 'website' => 'https://pmat.sanpedro.ac.id', 'email' => 'pmat@sanpedro.ac.id', 'telepon' => '(021) 551-2104', 'ketua' => 'Dr. Rudi Hartono, M.Pd.', 'nuptk' => 'NUPTK-PMAT-004', 'status' => 'Baik Sekali', 'nomor_sk' => '004/SK-PMAT/AKR/2025', 'tanggal_sk' => '2025-02-10', 'mulai' => '2025-02-25', 'berakhir' => '2030-02-25', 'aktif' => 1],
            ['nama_program_studi' => 'Sistem Informasi', 'nama_singkatan' => 'SI', 'kode_program_studi_pddikti' => '55201', 'jenjang' => 'S1', 'upps' => 'FTI', 'website' => 'https://si.sanpedro.ac.id', 'email' => 'si@sanpedro.ac.id', 'telepon' => '(021) 551-2105', 'ketua' => 'Dr. Mega Lestari, S.Kom., M.Kom.', 'nuptk' => 'NUPTK-SI-005', 'status' => 'Baik', 'nomor_sk' => '005/SK-SI/AKR/2025', 'tanggal_sk' => '2025-02-15', 'mulai' => '2025-03-01', 'berakhir' => '2030-03-01', 'aktif' => 0],
            ['nama_program_studi' => 'Teknik Informatika', 'nama_singkatan' => 'TI', 'kode_program_studi_pddikti' => '55202', 'jenjang' => 'S1', 'upps' => 'FTI', 'website' => 'https://ti.sanpedro.ac.id', 'email' => 'ti@sanpedro.ac.id', 'telepon' => '(021) 551-2106', 'ketua' => 'Dr. Farhan Hidayat, S.T., M.Kom.', 'nuptk' => 'NUPTK-TI-006', 'status' => 'Baik Sekali', 'nomor_sk' => '006/SK-TI/AKR/2025', 'tanggal_sk' => '2025-02-20', 'mulai' => '2025-03-05', 'berakhir' => '2030-03-05', 'aktif' => 0],
            ['nama_program_studi' => 'Manajemen', 'nama_singkatan' => 'MNJ', 'kode_program_studi_pddikti' => '61201', 'jenjang' => 'S1', 'upps' => 'FEB', 'website' => 'https://manajemen.sanpedro.ac.id', 'email' => 'manajemen@sanpedro.ac.id', 'telepon' => '(021) 551-2107', 'ketua' => 'Dr. Lia Permata, S.E., M.M.', 'nuptk' => 'NUPTK-MNJ-007', 'status' => 'Unggul', 'nomor_sk' => '007/SK-MNJ/AKR/2025', 'tanggal_sk' => '2025-03-01', 'mulai' => '2025-03-15', 'berakhir' => '2030-03-15', 'aktif' => 0],
            ['nama_program_studi' => 'Akuntansi', 'nama_singkatan' => 'AKT', 'kode_program_studi_pddikti' => '61202', 'jenjang' => 'S1', 'upps' => 'FEB', 'website' => 'https://akuntansi.sanpedro.ac.id', 'email' => 'akuntansi@sanpedro.ac.id', 'telepon' => '(021) 551-2108', 'ketua' => 'Dr. Nanda Putri, S.E., M.Ak.', 'nuptk' => 'NUPTK-AKT-008', 'status' => 'Baik Sekali', 'nomor_sk' => '008/SK-AKT/AKR/2025', 'tanggal_sk' => '2025-03-05', 'mulai' => '2025-03-20', 'berakhir' => '2030-03-20', 'aktif' => 0],
            ['nama_program_studi' => 'Magister Pendidikan Bahasa Inggris', 'nama_singkatan' => 'MPBI', 'kode_program_studi_pddikti' => '88201', 'jenjang' => 'S2', 'upps' => 'FKIP', 'website' => 'https://mpbi.sanpedro.ac.id', 'email' => 'mpbi@sanpedro.ac.id', 'telepon' => '(021) 551-2109', 'ketua' => 'Dr. Sari Melati, M.Pd.', 'nuptk' => 'NUPTK-MPBI-009', 'status' => 'Baik', 'nomor_sk' => '009/SK-MPBI/AKR/2025', 'tanggal_sk' => '2025-03-10', 'mulai' => '2025-03-25', 'berakhir' => '2030-03-25', 'aktif' => 0],
            ['nama_program_studi' => 'Magister Manajemen', 'nama_singkatan' => 'MM', 'kode_program_studi_pddikti' => '88202', 'jenjang' => 'S2', 'upps' => 'FEB', 'website' => 'https://mm.sanpedro.ac.id', 'email' => 'mm@sanpedro.ac.id', 'telepon' => '(021) 551-2110', 'ketua' => 'Dr. Yoga Prabowo, S.E., M.M.', 'nuptk' => 'NUPTK-MM-010', 'status' => 'Baik', 'nomor_sk' => '010/SK-MM/AKR/2025', 'tanggal_sk' => '2025-03-15', 'mulai' => '2025-04-01', 'berakhir' => '2030-04-01', 'aktif' => 0],
        ];

        $this->completeExistingProdi($prodiTemplates, $uppsBySingkatan, $fallbackUppsId, $lembagaCodes, $userId, $now);
        $this->ensureTemplateProdiExists($prodiTemplates, $uppsBySingkatan, $fallbackUppsId, $lembagaCodes, $userId, $now);
    }

    private function getFirstUserId(): ?int
    {
        $row = $this->db->table('users')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
        $id = (int) ($row['id'] ?? 0);
        return $id > 0 ? $id : null;
    }

    private function getLembagaCodes(): array
    {
        $rows = $this->db->table('lembaga_akreditasi')
            ->select('nama_singkatan')
            ->where('nama_singkatan IS NOT NULL', null, false)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $codes = [];
        foreach ($rows as $row) {
            $code = trim((string) ($row['nama_singkatan'] ?? ''));
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        if (empty($codes)) {
            $codes = ['BAN-PT', 'LAMDIK', 'LAMEMBA', 'LAM INFOKOM'];
        }

        return array_values(array_unique($codes));
    }

    private function getUppsBySingkatan(): array
    {
        $rows = $this->db->table('upps')->select('id,nama_singkatan,nama_upps')->orderBy('id', 'ASC')->get()->getResultArray();
        $result = [];
        foreach ($rows as $row) {
            $key = strtoupper(trim((string) ($row['nama_singkatan'] ?? '')));
            if ($key !== '') {
                $result[$key] = (int) $row['id'];
            }
        }

        return $result;
    }

    private function getFallbackUppsId(): int
    {
        $row = $this->db->table('upps')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
        return (int) ($row['id'] ?? 0);
    }

    private function completeExistingUpps(array $templates, ?int $userId, string $now): void
    {
        $rows = $this->db->table('upps')->orderBy('id', 'ASC')->get()->getResultArray();
        foreach ($rows as $idx => $row) {
            $template = $templates[$idx % count($templates)];
            $update = [];

            $fields = ['nama_singkatan', 'jenis_unit', 'nama_pimpinan_upps', 'nutpk', 'email_resmi_upps', 'nomor_telepon'];
            foreach ($fields as $field) {
                $current = trim((string) ($row[$field] ?? ''));
                if ($current === '') {
                    $update[$field] = $template[$field];
                }
            }

            if (! empty($update)) {
                $update['updated_at'] = $now;
                $update['updated_by'] = $userId;
                $this->db->table('upps')->where('id', (int) $row['id'])->update($update);
            }
        }
    }

    private function ensureTemplateUppsExists(array $templates, ?int $userId, string $now): void
    {
        foreach ($templates as $template) {
            $exists = $this->db->table('upps')->where('nama_upps', $template['nama_upps'])->countAllResults();
            if ($exists) {
                continue;
            }

            $this->db->table('upps')->insert([
                'nama_upps' => $template['nama_upps'],
                'nama_singkatan' => $template['nama_singkatan'],
                'jenis_unit' => $template['jenis_unit'],
                'nama_pimpinan_upps' => $template['nama_pimpinan_upps'],
                'nutpk' => $template['nutpk'],
                'email_resmi_upps' => $template['email_resmi_upps'],
                'nomor_telepon' => $template['nomor_telepon'],
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function completeExistingProdi(
        array $templates,
        array $uppsBySingkatan,
        int $fallbackUppsId,
        array $lembagaCodes,
        ?int $userId,
        string $now
    ): void {
        $rows = $this->db->table('program_studi')->orderBy('id', 'ASC')->get()->getResultArray();
        foreach ($rows as $idx => $row) {
            $template = $templates[$idx % count($templates)];
            $update = [];

            $uppsKey = strtoupper(trim((string) ($template['upps'] ?? '')));
            $templateUppsId = (int) ($uppsBySingkatan[$uppsKey] ?? $fallbackUppsId);
            if ((int) ($row['upps_id'] ?? 0) <= 0 && $templateUppsId > 0) {
                $update['upps_id'] = $templateUppsId;
            }

            $mapFields = [
                'nama_singkatan' => 'nama_singkatan',
                'kode_program_studi_pddikti' => 'kode_program_studi_pddikti',
                'jenjang' => 'jenjang',
                'website_resmi' => 'website',
                'email_resmi_program_studi' => 'email',
                'nomor_telepon' => 'telepon',
                'nama_ketua_program_studi' => 'ketua',
                'nuptk' => 'nuptk',
                'status_akreditasi' => 'status',
                'nomor_sk_akreditasi' => 'nomor_sk',
                'tanggal_sk' => 'tanggal_sk',
                'tanggal_mulai_berlaku' => 'mulai',
                'tanggal_berakhir' => 'berakhir',
            ];

            foreach ($mapFields as $dbField => $templateField) {
                $current = trim((string) ($row[$dbField] ?? ''));
                if ($current === '') {
                    $update[$dbField] = $template[$templateField];
                }
            }

            $lembagaCurrent = trim((string) ($row['lembaga_akreditasi'] ?? ''));
            if ($lembagaCurrent === '') {
                $update['lembaga_akreditasi'] = $lembagaCodes[$idx % count($lembagaCodes)];
            }

            if (! isset($row['is_aktif_akreditasi']) || (int) $row['is_aktif_akreditasi'] === 0) {
                $update['is_aktif_akreditasi'] = (int) ($template['aktif'] ?? 0);
            }

            if (! empty($update)) {
                $update['updated_at'] = $now;
                $update['updated_by'] = $userId;
                $this->db->table('program_studi')->where('id', (int) $row['id'])->update($update);
            }
        }
    }

    private function ensureTemplateProdiExists(
        array $templates,
        array $uppsBySingkatan,
        int $fallbackUppsId,
        array $lembagaCodes,
        ?int $userId,
        string $now
    ): void {
        foreach ($templates as $idx => $template) {
            $exists = $this->db->table('program_studi')->where('nama_program_studi', $template['nama_program_studi'])->countAllResults();
            if ($exists) {
                continue;
            }

            $uppsKey = strtoupper(trim((string) ($template['upps'] ?? '')));
            $uppsId = (int) ($uppsBySingkatan[$uppsKey] ?? $fallbackUppsId);
            if ($uppsId <= 0) {
                continue;
            }

            $this->db->table('program_studi')->insert([
                'upps_id' => $uppsId,
                'nama_program_studi' => $template['nama_program_studi'],
                'nama_singkatan' => $template['nama_singkatan'],
                'kode_program_studi_pddikti' => $template['kode_program_studi_pddikti'],
                'jenjang' => $template['jenjang'],
                'website_resmi' => $template['website'],
                'email_resmi_program_studi' => $template['email'],
                'nomor_telepon' => $template['telepon'],
                'nama_ketua_program_studi' => $template['ketua'],
                'nuptk' => $template['nuptk'],
                'status_akreditasi' => $template['status'],
                'nomor_sk_akreditasi' => $template['nomor_sk'],
                'tanggal_sk' => $template['tanggal_sk'],
                'tanggal_mulai_berlaku' => $template['mulai'],
                'tanggal_berakhir' => $template['berakhir'],
                'lembaga_akreditasi' => $lembagaCodes[$idx % count($lembagaCodes)],
                'is_aktif_akreditasi' => (int) ($template['aktif'] ?? 0),
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

