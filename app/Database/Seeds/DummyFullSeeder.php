<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DummyFullSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $this->seedRoles($now);
        $this->seedUsers($now);
        $this->seedUserRoles($now);
        $this->call('UppsProgramStudiDummySeeder');
        $this->seedKriterias($now);
        $this->seedJenisDokumen($now);
        $subBagianList = $this->seedSubBagian($now);
        $this->seedDokumen($now, $subBagianList);
        $this->seedReviewDokumen($now);
        $this->seedRiwayatDokumen($now);
        $this->seedPeraturan($now);
        $this->seedInstrumen($now);
    }

    private function seedJenisDokumen(string $now): void
    {
        if (! $this->db->tableExists('jenis_dokumen')) {
            return;
        }

        $jenisDokumenList = [
            'Surat Keputusan (SK)',
            'Laporan',
            'SOP',
            'Formulir',
            'Data',
            'Dokumen Pendukung',
            'Lainnya',
        ];

        foreach ($jenisDokumenList as $namaJenisDokumen) {
            $exists = $this->db->table('jenis_dokumen')
                ->where('nama_jenis_dokumen', $namaJenisDokumen)
                ->where('deleted_at', null)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('jenis_dokumen')->insert([
                'nama_jenis_dokumen' => $namaJenisDokumen,
                'is_aktif'           => 1,
                'created_by'         => null,
                'updated_by'         => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }

    private function seedRoles(string $now): void
    {
        $roles = [
            ['nama_role' => 'Admin', 'slug_role' => 'admin', 'deskripsi' => 'Akses penuh sistem'],
            ['nama_role' => 'LPM', 'slug_role' => 'lpm', 'deskripsi' => 'Pengelola mutu LPM'],
            ['nama_role' => 'Dekan', 'slug_role' => 'dekan', 'deskripsi' => 'Pimpinan UPPS'],
            ['nama_role' => 'Kaprodi', 'slug_role' => 'kaprodi', 'deskripsi' => 'Ketua program studi'],
            ['nama_role' => 'Dosen', 'slug_role' => 'dosen', 'deskripsi' => 'Pengunggah dokumen'],
            ['nama_role' => 'Asesor', 'slug_role' => 'asesor', 'deskripsi' => 'Reviewer eksternal'],
        ];

        foreach ($roles as $role) {
            $exists = $this->db->table('roles')
                ->where('slug_role', $role['slug_role'])
                ->countAllResults();

            if ($exists) {
                continue;
            }

            $this->db->table('roles')->insert([
                'nama_role'   => $role['nama_role'],
                'slug_role'   => $role['slug_role'],
                'deskripsi'   => $role['deskripsi'],
                'is_aktif'    => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    private function seedUsers(string $now): void
    {
        $users = [
            [
                'nama_lengkap' => 'Dummy Admin',
                'username'     => 'dummy-admin',
                'email'        => 'dummy-admin@sipadukar.local',
                'unit_kerja'   => 'LPM',
                'jabatan'      => 'Administrator',
            ],
            [
                'nama_lengkap' => 'Dummy LPM',
                'username'     => 'dummy-lpm',
                'email'        => 'dummy-lpm@sipadukar.local',
                'unit_kerja'   => 'LPM',
                'jabatan'      => 'Staf LPM',
            ],
            [
                'nama_lengkap' => 'Dummy Dosen',
                'username'     => 'dummy-dosen',
                'email'        => 'dummy-dosen@sipadukar.local',
                'unit_kerja'   => 'Program Studi',
                'jabatan'      => 'Dosen',
            ],
        ];

        foreach ($users as $user) {
            // Delete if exists, then insert
            $this->db->table('users')
                ->where('username', $user['username'])
                ->orWhere('email', $user['email'])
                ->delete();

            $this->db->table('users')->insert([
                'nama_lengkap'  => $user['nama_lengkap'],
                'username'      => $user['username'],
                'email'         => $user['email'],
                'password_hash' => password_hash('dummy123', PASSWORD_DEFAULT),
                'nip'           => null,
                'unit_kerja'    => $user['unit_kerja'],
                'jabatan'       => $user['jabatan'],
                'foto'          => null,
                'is_aktif'      => 1,
                'terakhir_login'=> null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    private function seedUserRoles(string $now): void
    {
        $users = $this->db->table('users')->select('id, username')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getResultArray();
        $roles = $this->db->table('roles')->select('id, slug_role')->orderBy('id', 'ASC')->get()->getResultArray();

        if (empty($users) || empty($roles)) {
            return;
        }

        $roleBySlug = [];
        foreach ($roles as $role) {
            $roleBySlug[$role['slug_role']] = (int) $role['id'];
        }

        $pairs = [];
        foreach ($users as $user) {
            $username = (string) $user['username'];
            $userId = (int) $user['id'];

            if (str_contains($username, 'admin') && isset($roleBySlug['admin'])) {
                $pairs[] = [$userId, $roleBySlug['admin']];
            }
            if (str_contains($username, 'lpm') && isset($roleBySlug['lpm'])) {
                $pairs[] = [$userId, $roleBySlug['lpm']];
            }
            if (str_contains($username, 'dosen') && isset($roleBySlug['dosen'])) {
                $pairs[] = [$userId, $roleBySlug['dosen']];
            }
        }

        if (empty($pairs)) {
            $firstRoleId = (int) $roles[0]['id'];
            for ($i = 0; $i < min(3, count($users)); $i++) {
                $pairs[] = [(int) $users[$i]['id'], $firstRoleId];
            }
        }

        foreach ($pairs as [$userId, $roleId]) {
            $exists = $this->db->table('user_roles')
                ->where('user_id', $userId)
                ->where('role_id', $roleId)
                ->countAllResults();

            if ($exists) {
                continue;
            }

            $this->db->table('user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedKriterias(string $now): void
    {
        $kriterias = [
            ['nomor_kriteria' => 1, 'kode' => 'K1', 'nama_kriteria' => 'Visi, Misi, Tujuan, dan Strategi', 'urutan' => 1],
            ['nomor_kriteria' => 2, 'kode' => 'K2', 'nama_kriteria' => 'Tata Pamong, Tata Kelola, dan Kerja Sama', 'urutan' => 2],
            ['nomor_kriteria' => 3, 'kode' => 'K3', 'nama_kriteria' => 'Mahasiswa', 'urutan' => 3],
            ['nomor_kriteria' => 4, 'kode' => 'K4', 'nama_kriteria' => 'Sumber Daya Manusia', 'urutan' => 4],
            ['nomor_kriteria' => 5, 'kode' => 'K5', 'nama_kriteria' => 'Keuangan, Sarana, dan Prasarana', 'urutan' => 5],
            ['nomor_kriteria' => 6, 'kode' => 'K6', 'nama_kriteria' => 'Pendidikan', 'urutan' => 6],
            ['nomor_kriteria' => 7, 'kode' => 'K7', 'nama_kriteria' => 'Penelitian', 'urutan' => 7],
            ['nomor_kriteria' => 8, 'kode' => 'K8', 'nama_kriteria' => 'Pengabdian kepada Masyarakat', 'urutan' => 8],
            ['nomor_kriteria' => 9, 'kode' => 'K9', 'nama_kriteria' => 'Luaran dan Capaian Tridharma', 'urutan' => 9],
        ];

        foreach ($kriterias as $k) {
            $exists = $this->db->table('kriterias')->where('kode', $k['kode'])->countAllResults();
            if ($exists) {
                continue;
            }

            $this->db->table('kriterias')->insert([
                'nomor_kriteria' => $k['nomor_kriteria'],
                'kode'           => $k['kode'],
                'nama_kriteria'  => $k['nama_kriteria'],
                'deskripsi'      => 'Data dummy untuk ' . $k['nama_kriteria'],
                'urutan'         => $k['urutan'],
                'is_aktif'       => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function seedSubBagian(string $now): array
    {
        $kriteriaList = $this->db->table('kriterias')
            ->select('id, kode, nama_kriteria')
            ->where('is_aktif', 1)
            ->orderBy('urutan', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($kriteriaList)) {
            return [];
        }

        $userId = (int) (($this->db->table('users')->select('id')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray()['id'] ?? 0));
        $subBagianTemplate = [
            1 => ['Nama Sub Bagian 1', 'Dokumen kebijakan, SOP, dan pedoman implementasi.'],
            2 => ['Nama Sub Bagian 2', 'Dokumen pelaksanaan, bukti kegiatan, dan rekam tindak lanjut.'],
            3 => ['Nama Sub Bagian 3', 'Dokumen evaluasi, capaian mutu, dan perbaikan berkelanjutan.'],
        ];

        foreach ($kriteriaList as $kriteria) {
            $kriteriaId = (int) ($kriteria['id'] ?? 0);
            $kode = strtolower((string) ($kriteria['kode'] ?? ('k' . $kriteriaId)));
            if ($kriteriaId <= 0) {
                continue;
            }

            foreach ($subBagianTemplate as $urut => $template) {
                [$namaSubBagian, $deskripsi] = $template;
                $slug = $kode . '-sb-' . $urut;

                $exists = $this->db->table('sub_bagian')
                    ->where('kriteria_id', $kriteriaId)
                    ->where('slug_sub_bagian', $slug)
                    ->countAllResults();
                if ($exists > 0) {
                    continue;
                }

                $this->db->table('sub_bagian')->insert([
                    'kriteria_id'      => $kriteriaId,
                    'nama_sub_bagian'  => $namaSubBagian,
                    'slug_sub_bagian'  => $slug,
                    'deskripsi'        => $deskripsi,
                    'urutan'           => $urut,
                    'dibuat_oleh'      => $userId > 0 ? $userId : null,
                    'diupdate_oleh'    => $userId > 0 ? $userId : null,
                    'is_aktif'         => 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }

        return $this->db->table('sub_bagian sb')
            ->select('sb.id, sb.kriteria_id, sb.nama_sub_bagian, sb.slug_sub_bagian, sb.urutan, k.kode as kode_kriteria')
            ->join('kriterias k', 'k.id = sb.kriteria_id', 'inner')
            ->where('sb.deleted_at', null)
            ->like('sb.slug_sub_bagian', 'k', 'after')
            ->like('sb.slug_sub_bagian', '-sb-', 'both')
            ->orderBy('k.urutan', 'ASC')
            ->orderBy('sb.urutan', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function seedDokumen(string $now, array $subBagians = []): void
    {
        if (empty($subBagians)) {
            return;
        }

        $users = $this->db->table('users')
            ->select('id')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        if (empty($users)) {
            return;
        }

        $prodiAktif = $this->db->table('program_studi')
            ->select('id, nama_program_studi, jenjang')
            ->where('is_aktif_akreditasi', 1)
            ->orderBy('nama_program_studi', 'ASC')
            ->get()
            ->getResultArray();
        if (empty($prodiAktif)) {
            $prodiAktif = $this->db->table('program_studi')
                ->select('id, nama_program_studi, jenjang')
                ->orderBy('nama_program_studi', 'ASC')
                ->limit(4)
                ->get()
                ->getResultArray();
        }
        if (empty($prodiAktif)) {
            return;
        }

        $statusList = ['draft', 'perlu_revisi', 'tervalidasi', 'ditolak'];
        $finalizedStatuses = ['perlu_revisi', 'tervalidasi', 'ditolak'];
        $jenisDokumenList = [
            'Surat Keputusan (SK)',
            'Laporan',
            'SOP',
            'Formulir',
            'Data',
            'Dokumen Pendukung',
            'Lainnya',
        ];
        $docCounter = 0;
        $docsPerProdiPerSubBagian = 2;
        $tahun = date('Y');
        $supportsProgramStudiId = $this->tableHasField('dokumen', 'program_studi_id');
        $lpmReviewerIds = array_map(
            static fn ($row) => (int) ($row['id'] ?? 0),
            $this->db->table('users u')
                ->select('u.id')
                ->join('user_roles ur', 'ur.user_id = u.id', 'inner')
                ->join('roles r', 'r.id = ur.role_id', 'inner')
                ->where('u.deleted_at', null)
                ->where('r.slug_role', 'lpm')
                ->orderBy('u.id', 'ASC')
                ->get()
                ->getResultArray()
        );

        if (empty($lpmReviewerIds)) {
            $lpmReviewerIds = array_map(static fn ($row) => (int) $row['id'], $users);
        }

        foreach ($subBagians as $subBagian) {
            $subBagianId = (int) ($subBagian['id'] ?? 0);
            $kriteriaId = (int) ($subBagian['kriteria_id'] ?? 0);
            $kodeKriteria = strtoupper((string) ($subBagian['kode_kriteria'] ?? ('K' . $kriteriaId)));
            $subUrutan = (int) ($subBagian['urutan'] ?? 1);
            $namaSubBagian = trim((string) ($subBagian['nama_sub_bagian'] ?? 'Sub Bagian'));

            if ($subBagianId <= 0 || $kriteriaId <= 0) {
                continue;
            }

            foreach ($prodiAktif as $prodiIndex => $prodi) {
                $programStudiId = (int) ($prodi['id'] ?? 0);
                $namaProdi = trim((string) ($prodi['nama_program_studi'] ?? 'Program Studi'));
                $jenjang = trim((string) ($prodi['jenjang'] ?? ''));

                if ($programStudiId <= 0) {
                    continue;
                }

                for ($i = 1; $i <= $docsPerProdiPerSubBagian; $i++) {
                    $docCounter++;
                    $status = $statusList[($docCounter - 1) % count($statusList)];
                    $slug = strtolower($kodeKriteria) . '-sb' . $subUrutan . '-ps' . $programStudiId . '-d' . $i;

                    $exists = $this->db->table('dokumen')
                        ->where('slug_dokumen', $slug)
                        ->countAllResults();
                    if ($exists > 0) {
                        continue;
                    }

                    $uploader = (int) $users[($docCounter - 1) % count($users)]['id'];
                    $isFinalizedStatus = in_array($status, $finalizedStatuses, true);
                    $reviewer = $isFinalizedStatus
                        ? (int) $lpmReviewerIds[($docCounter - 1) % count($lpmReviewerIds)]
                        : null;
                    $prefixKodeProdi = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($namaProdi)), 0, 4));
                    $kodeDokumen = $kodeKriteria . '-SB' . str_pad((string) $subUrutan, 2, '0', STR_PAD_LEFT) . '-' . ($prefixKodeProdi !== '' ? $prefixKodeProdi : 'PROD') . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                    $nomorDokumen = 'ND/' . $kodeKriteria . '/' . str_pad((string) $subUrutan, 2, '0', STR_PAD_LEFT) . '/' . $programStudiId . '/' . $i . '/' . $tahun;
                    $judul = $kodeKriteria . ' - ' . $namaSubBagian . ' - ' . $namaProdi . ($jenjang !== '' ? ' (' . $jenjang . ')' : '') . ' - Dokumen ' . $i;

                    $dataInsert = [
                        'kriteria_id'      => $kriteriaId,
                        'sub_bagian_id'    => $subBagianId,
                        'kode_dokumen'     => $kodeDokumen,
                        'judul_dokumen'    => $judul,
                        'slug_dokumen'     => $slug,
                        'deskripsi'        => 'Dokumen dummy untuk simulasi akreditasi pada ' . $namaSubBagian . ' - ' . $namaProdi . '.',
                        'nomor_dokumen'    => $nomorDokumen,
                        'jenis_dokumen'    => $jenisDokumenList[($docCounter - 1) % count($jenisDokumenList)],
                        'tahun_dokumen'    => (int) $tahun,
                        'nama_file'        => $slug . '.pdf',
                        'path_file'        => 'dummy/dokumen/' . $slug . '.pdf',
                        'ekstensi_file'    => 'pdf',
                        'mime_type'        => 'application/pdf',
                        'ukuran_file'      => 102400 + ($docCounter * 1024),
                        'versi'            => 1,
                        'status_dokumen'   => $status,
                        'catatan_terakhir' => $isFinalizedStatus
                            ? 'Catatan finalisasi LPM dengan status ' . $status . '.'
                            : 'Dokumen draft, menunggu finalisasi LPM.',
                        'tanggal_upload'   => $now,
                        'tanggal_submit'   => $isFinalizedStatus ? $now : null,
                        'tanggal_validasi' => $status === 'tervalidasi' ? $now : null,
                        'uploaded_by'      => $uploader,
                        'reviewer_id'      => $reviewer,
                        'unit_kerja'       => $namaProdi,
                        'is_aktif'         => 1,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];

                    if ($supportsProgramStudiId) {
                        $dataInsert['program_studi_id'] = $programStudiId;
                    }

                    $this->db->table('dokumen')->insert($dataInsert);
                }
            }
        }
    }

    private function seedReviewDokumen(string $now): void
    {
        $dokumenList = $this->db->table('dokumen')
            ->select('id, status_dokumen')
            ->where('deleted_at', null)
            ->like('slug_dokumen', 'k', 'after')
            ->like('slug_dokumen', '-sb', 'both')
            ->like('slug_dokumen', '-ps', 'both')
            ->like('slug_dokumen', '-d', 'both')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $dokumenIds = array_map(
            static fn ($row) => (int) $row['id'],
            $dokumenList
        );
        $reviewerIds = array_map(
            static fn ($row) => (int) $row['id'],
            $this->db->table('users u')
                ->select('u.id')
                ->join('user_roles ur', 'ur.user_id = u.id', 'inner')
                ->join('roles r', 'r.id = ur.role_id', 'inner')
                ->where('u.deleted_at', null)
                ->where('r.slug_role', 'lpm')
                ->orderBy('u.id', 'ASC')
                ->get()
                ->getResultArray()
        );

        if (empty($dokumenIds) || empty($reviewerIds)) {
            return;
        }

        foreach ($dokumenList as $i => $dokumen) {
            $dokumenId = (int) ($dokumen['id'] ?? 0);
            if ($dokumenId <= 0) {
                continue;
            }

            $exists = $this->db->table('review_dokumen')
                ->where('dokumen_id', $dokumenId)
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $statusDokumen = (string) ($dokumen['status_dokumen'] ?? 'draft');
            if (! in_array($statusDokumen, ['perlu_revisi', 'tervalidasi', 'ditolak'], true)) {
                continue;
            }

            $statusReview = $statusDokumen;

            $this->db->table('review_dokumen')->insert([
                'dokumen_id'     => $dokumenId,
                'reviewer_id'    => $reviewerIds[$i % count($reviewerIds)],
                'status_review'  => $statusReview,
                'catatan_review' => 'Catatan review otomatis untuk dokumen dummy.',
                'tanggal_review' => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function seedRiwayatDokumen(string $now): void
    {
        $dokumenList = $this->db->table('dokumen')
            ->select('id, versi, status_dokumen, nama_file, path_file, ekstensi_file, mime_type, ukuran_file, uploaded_by, tanggal_upload')
            ->where('deleted_at', null)
            ->like('slug_dokumen', 'k', 'after')
            ->like('slug_dokumen', '-sb', 'both')
            ->like('slug_dokumen', '-ps', 'both')
            ->like('slug_dokumen', '-d', 'both')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $dokumenIds = array_map(
            static fn ($row) => (int) $row['id'],
            $dokumenList
        );
        $uploaderIds = array_map(
            static fn ($row) => (int) $row['id'],
            $this->db->table('users')->select('id')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getResultArray()
        );

        if (empty($dokumenIds) || empty($uploaderIds)) {
            return;
        }

        foreach ($dokumenList as $i => $dokumen) {
            $dokumenId = (int) ($dokumen['id'] ?? 0);
            if ($dokumenId <= 0) {
                continue;
            }

            $exists = $this->db->table('riwayat_dokumen')
                ->where('dokumen_id', $dokumenId)
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $uploadedBy = (int) ($dokumen['uploaded_by'] ?? 0);
            if ($uploadedBy <= 0) {
                $uploadedBy = $uploaderIds[$i % count($uploaderIds)];
            }

            $this->db->table('riwayat_dokumen')->insert([
                'dokumen_id'      => $dokumenId,
                'versi'           => (int) ($dokumen['versi'] ?? 1),
                'status_saat_itu' => (string) ($dokumen['status_dokumen'] ?? 'draft'),
                'keterangan'      => 'Riwayat awal otomatis untuk dokumen dummy.',
                'nama_file'       => (string) ($dokumen['nama_file'] ?? ('dokumen-' . $dokumenId . '.pdf')),
                'path_file'       => (string) ($dokumen['path_file'] ?? ('dummy/riwayat/dokumen-' . $dokumenId . '.pdf')),
                'ekstensi_file'   => (string) ($dokumen['ekstensi_file'] ?? 'pdf'),
                'mime_type'       => (string) ($dokumen['mime_type'] ?? 'application/pdf'),
                'ukuran_file'     => (int) ($dokumen['ukuran_file'] ?? 102400),
                'diunggah_oleh'   => $uploadedBy,
                'waktu_upload'    => (string) ($dokumen['tanggal_upload'] ?? $now),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    private function seedPeraturan(string $now): void
    {
        $count = $this->db->table('peraturan')->where('deleted_at', null)->countAllResults();
        if ($count >= 3) {
            return;
        }

        $userId = (int) (($this->db->table('users')->select('id')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray()['id'] ?? 0));

        for ($i = $count + 1; $i <= 3; $i++) {
            $slug = 'peraturan-dummy-' . $i;
            $exists = $this->db->table('peraturan')->where('slug', $slug)->countAllResults();
            if ($exists) {
                continue;
            }

            $this->db->table('peraturan')->insert([
                'judul'           => 'Peraturan Dummy ' . $i,
                'slug'            => $slug,
                'kategori'        => 'Regulasi Internal',
                'nomor_peraturan' => 'PR/' . $i . '/2026',
                'tahun'           => '2026',
                'deskripsi'       => 'Deskripsi peraturan dummy ' . $i,
                'nama_file'       => 'peraturan-dummy-' . $i . '.pdf',
                'path_file'       => 'dummy/peraturan/peraturan-dummy-' . $i . '.pdf',
                'ekstensi_file'   => 'pdf',
                'ukuran_file'     => 153600 + ($i * 1024),
                'tanggal_terbit'  => date('Y-m-d'),
                'is_aktif'        => 1,
                'dibuat_oleh'     => $userId > 0 ? $userId : null,
                'diupdate_oleh'   => $userId > 0 ? $userId : null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    private function seedInstrumen(string $now): void
    {
        $count = $this->db->table('instrumen')->where('deleted_at', null)->countAllResults();
        if ($count >= 3) {
            return;
        }

        $userId = (int) (($this->db->table('users')->select('id')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray()['id'] ?? 0));

        for ($i = $count + 1; $i <= 3; $i++) {
            $slug = 'instrumen-dummy-' . $i;
            $exists = $this->db->table('instrumen')->where('slug', $slug)->countAllResults();
            if ($exists) {
                continue;
            }

            $this->db->table('instrumen')->insert([
                'judul'           => 'Instrumen Dummy ' . $i,
                'slug'            => $slug,
                'kategori'        => 'Instrumen Evaluasi',
                'deskripsi'       => 'Deskripsi instrumen dummy ' . $i,
                'versi_instrumen' => 'v' . $i . '.0',
                'nama_file'       => 'instrumen-dummy-' . $i . '.pdf',
                'path_file'       => 'dummy/instrumen/instrumen-dummy-' . $i . '.pdf',
                'ekstensi_file'   => 'pdf',
                'ukuran_file'     => 176400 + ($i * 1024),
                'tanggal_berlaku' => date('Y-m-d'),
                'is_aktif'        => 1,
                'dibuat_oleh'     => $userId > 0 ? $userId : null,
                'diupdate_oleh'   => $userId > 0 ? $userId : null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    private function tableHasField(string $tableName, string $fieldName): bool
    {
        static $fieldMap = [];

        if (! isset($fieldMap[$tableName])) {
            $fieldMap[$tableName] = $this->db->getFieldNames($tableName);
        }

        return in_array($fieldName, $fieldMap[$tableName], true);
    }
}
