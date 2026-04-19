<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\KriteriaModel;
use App\Models\ProfilPtModel;
use App\Models\ProgramStudiModel;
use App\Models\LembagaAkreditasiModel;
use App\Models\SubBagianModel;

class PublicController extends BaseController
{
    private const DEFAULT_SUB_BAGIAN_SLUG = '__default_kriteria_tanpa_subbagian__';

    protected $dokumenModel;
    protected $kriteriaModel;
    protected $profilPtModel;
    protected $programStudiModel;
    protected $lembagaAkreditasiModel;
    protected $subBagianModel;

    public function __construct()
    {
        $this->dokumenModel = new DokumenModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->profilPtModel = new ProfilPtModel();
        $this->programStudiModel = new ProgramStudiModel();
        $this->lembagaAkreditasiModel = new LembagaAkreditasiModel();
        $this->subBagianModel = new SubBagianModel();
    }

    /**
     * Beranda / Homepage Public
     * Menampilkan ringkasan dashboard dengan kartu statistik dan progres dokumen
     */
    public function index()
    {
        $profilData = $this->profilPtModel->getSingleton();
        $profilInstitusi = array_merge([
            'nama_pt' => 'Universitas San Pedro',
            'nama_singkatan' => 'UNISAP',
            'badan_penyelenggara' => '-',
            'kode_pt_pddikti' => '-',
            'status_akreditasi_pt' => '-',
            'lembaga_akreditasi' => '-',
            'nomor_sk_akreditasi' => '-',
            'tanggal_berlaku_akreditasi' => '-',
            'tanggal_berakhir_akreditasi' => '-',
            'nomor_sk' => '-',
            'mulai_berlaku' => '-',
            'berlaku_sampai' => '-',
            'peringkat' => '-',
            'logo_lembaga_path' => '',
        ], $profilData ?? []);

        $profilInstitusi['nomor_sk'] = trim((string) ($profilInstitusi['nomor_sk_akreditasi'] ?? $profilInstitusi['nomor_sk'] ?? '-')) ?: '-';
        $profilInstitusi['mulai_berlaku'] = trim((string) ($profilInstitusi['tanggal_berlaku_akreditasi'] ?? $profilInstitusi['mulai_berlaku'] ?? '-')) ?: '-';
        $profilInstitusi['berlaku_sampai'] = trim((string) ($profilInstitusi['tanggal_berakhir_akreditasi'] ?? $profilInstitusi['berlaku_sampai'] ?? '-')) ?: '-';
        $profilInstitusi['peringkat'] = trim((string) ($profilInstitusi['status_akreditasi_pt'] ?? $profilInstitusi['peringkat'] ?? '-')) ?: '-';

        $kriterias = $this->kriteriaModel->getAktif();
        $prodiAktifAkreditasi = $this->programStudiModel
            ->select('program_studi.*, upps.nama_upps, upps.nama_singkatan AS nama_singkatan_upps')
            ->join('upps', 'upps.id = program_studi.upps_id', 'left')
            ->where('program_studi.is_aktif_akreditasi', 1)
            ->orderBy('program_studi.nama_program_studi', 'ASC')
            ->findAll();

        [
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'programStudiFilterLocked' => $programStudiFilterLocked,
            'showProgramStudiAllOption' => $showProgramStudiAllOption,
        ] = $this->resolveSelectedProgramStudiFilter($prodiAktifAkreditasi);

        $requestedId = (int) ($this->request->getGet('kriteria_id') ?? 0);
        $kriteriaId = $requestedId > 0 ? $requestedId : ($kriterias[0]['id'] ?? null);
        $selectedKriteria = null;
        foreach ($kriterias as $item) {
            if ((int) ($item['id'] ?? 0) === (int) $kriteriaId) {
                $selectedKriteria = $item;
                break;
            }
        }

        if ($selectedKriteria === null) {
            $kriteriaId = $kriterias[0]['id'] ?? null;
            $selectedKriteria = $kriterias[0] ?? null;
        }

        $dokumen = [];
        if ($kriteriaId) {
            $dokumen = $this->getDokumenPublicByKriteria($kriteriaId, $selectedProgramStudiId);
        }

        $totalKriteria = count($kriterias);
        $totalDokumenPublic = $this->getTotalDokumenPublik(false, $selectedProgramStudiId);
        $ringkasanKriteria = $this->getRingkasanPublikPerKriteria($totalDokumenPublic, $selectedProgramStudiId);
        $kriteriaTerisi = count(array_filter($ringkasanKriteria, static fn ($row) => (int) ($row['total_dokumen'] ?? 0) > 0));
        $progressPersen = $totalKriteria > 0 ? (int) round(($kriteriaTerisi / $totalKriteria) * 100) : 0;

        $singkatanLembaga = trim((string) ($profilInstitusi['lembaga_akreditasi'] ?? ''));
        if ($singkatanLembaga !== '' && $singkatanLembaga !== '-') {
            $lembagaTerpilih = $this->lembagaAkreditasiModel
                ->select('logo_path')
                ->where('nama_singkatan', $singkatanLembaga)
                ->first();

            $profilInstitusi['logo_lembaga_path'] = trim((string) ($lembagaTerpilih['logo_path'] ?? ''));
        }

        $singkatanLembagaProdi = [];
        foreach ($prodiAktifAkreditasi as $prodiRow) {
            $singkatan = trim((string) ($prodiRow['lembaga_akreditasi'] ?? ''));
            if ($singkatan !== '') {
                $singkatanLembagaProdi[$singkatan] = true;
            }
        }

        $logoBySingkatan = [];
        $namaLembagaBySingkatan = [];
        if (! empty($singkatanLembagaProdi)) {
            $listLembaga = $this->lembagaAkreditasiModel
                ->select('nama_singkatan, nama_lembaga_akreditasi, logo_path')
                ->whereIn('nama_singkatan', array_keys($singkatanLembagaProdi))
                ->findAll();

            foreach ($listLembaga as $lembaga) {
                $singkatan = trim((string) ($lembaga['nama_singkatan'] ?? ''));
                if ($singkatan === '') {
                    continue;
                }

                if (! array_key_exists($singkatan, $logoBySingkatan)) {
                    $logoBySingkatan[$singkatan] = trim((string) ($lembaga['logo_path'] ?? ''));
                }

                if (! array_key_exists($singkatan, $namaLembagaBySingkatan)) {
                    $namaLembagaBySingkatan[$singkatan] = trim((string) ($lembaga['nama_lembaga_akreditasi'] ?? ''));
                }
            }
        }

        foreach ($prodiAktifAkreditasi as $idx => $prodiRow) {
            $singkatan = trim((string) ($prodiRow['lembaga_akreditasi'] ?? ''));
            $prodiAktifAkreditasi[$idx]['logo_lembaga_path'] = $logoBySingkatan[$singkatan] ?? '';
            $prodiAktifAkreditasi[$idx]['nama_lembaga_akreditasi'] = $namaLembagaBySingkatan[$singkatan] ?? ($prodiRow['lembaga_akreditasi'] ?? '');
        }

        $dokumenTerbaruPublik = $this->getDokumenTerbaruPublik(1);
        $tanggalPembaruanTerakhir = $dokumenTerbaruPublik[0]['tanggal_validasi'] ?? '';

        $data = [
            'title' => 'SIPADUKAR',
            'menuActive' => 'beranda',
            'profilInstitusi' => $profilInstitusi,
            'prodiAktifAkreditasi' => $prodiAktifAkreditasi,
            'totalDokumenPublic' => $totalDokumenPublic,
            'totalKriteria' => $totalKriteria,
            'progressPersen' => $progressPersen,
            'kriteriaTerisi' => $kriteriaTerisi,
            'kriteriaActive' => $kriteriaId,
            'selectedKriteria' => $selectedKriteria,
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'programStudiFilterLocked' => $programStudiFilterLocked,
            'showProgramStudiAllOption' => $showProgramStudiAllOption,
            'dokumen' => $dokumen,
            'ringkasanKriteria' => $ringkasanKriteria,
            'kriterias' => $kriterias,
            'tanggalPembaruanTerakhir' => $tanggalPembaruanTerakhir,
        ];

        return view('public/beranda', $data);
    }

    /**
     * Halaman Profil Institusi
     * Menampilkan profil PT, program studi, lembaga akreditasi
     */
    public function profil()
    {
        $profilData = $this->profilPtModel->getSingleton();
        $profilPt = array_merge([
            'nama_pt' => 'Universitas San Pedro',
            'nama_singkatan' => 'UNISAP',
            'status_pt' => '-',
            'badan_penyelenggara' => '-',
            'kode_pt_pddikti' => '-',
            'tahun_berdiri' => '-',
            'alamat_lengkap' => '-',
            'website_resmi' => '-',
            'email_resmi_pt' => '-',
            'nomor_telepon' => '-',
            'status_akreditasi_pt' => '-',
            'lembaga_akreditasi' => '-',
            'nomor_sk_akreditasi' => '-',
            'tanggal_berlaku_akreditasi' => '-',
            'tanggal_berakhir_akreditasi' => '-',
        ], $profilData ?? []);

        $programStudi = $this->programStudiModel
            ->select('program_studi.*, upps.nama_upps')
            ->join('upps', 'upps.id = program_studi.upps_id', 'left')
            ->where('program_studi.is_aktif_akreditasi', 1)
            ->orderBy('program_studi.nama_program_studi', 'ASC')
            ->findAll();

        $lembagas = $this->lembagaAkreditasiModel
            ->where('is_aktif', 1)
            ->orderBy('nama_lembaga_akreditasi', 'ASC')
            ->findAll();

        $summaryCards = [
            [
                'label' => 'Prodi Aktif',
                'count' => count($programStudi),
                'note' => 'Program studi yang saat ini masuk ruang lingkup akreditasi',
                'variant' => 'stat-prodi',
                'icon' => 'bi-mortarboard-fill',
                'percent' => 100,
            ],
            [
                'label' => 'Lembaga Referensi',
                'count' => count($lembagas),
                'note' => 'Daftar lembaga akreditasi aktif yang tercatat pada sistem',
                'variant' => 'stat-primary',
                'icon' => 'bi-building-check',
                'percent' => 100,
            ],
            [
                'label' => 'Dokumen Final Public',
                'count' => $this->getTotalDokumenPublik(),
                'note' => 'Dokumen tervalidasi yang dapat dibuka untuk kebutuhan asesmen',
                'variant' => 'stat-dokumen',
                'icon' => 'bi-file-earmark-check-fill',
                'percent' => 100,
            ],
            [
                'label' => 'Status Akreditasi PT',
                'count' => $profilPt['status_akreditasi_pt'] ?: '-',
                'note' => 'Status terakhir perguruan tinggi yang ditampilkan pada portal public',
                'variant' => 'stat-persiapan',
                'icon' => 'bi-award-fill',
                'percent' => 100,
            ],
        ];

        $data = [
            'title' => 'Profil Institusi',
            'menuActive' => 'profil',
            'profilPt' => $profilPt,
            'programStudi' => $programStudi,
            'lembagas' => $lembagas,
            'summaryCards' => $summaryCards,
        ];

        return view('public/profil', $data);
    }

    /**
     * Halaman Daftar Kriteria dan Filter Dokumen
     * Menampilkan daftar K1-K9 dan dokumen per kriteria
     */
    public function kriteria()
    {
        $kriterias = $this->kriteriaModel->getAktif();
        $prodiAktifAkreditasi = $this->programStudiModel
            ->where('is_aktif_akreditasi', 1)
            ->orderBy('nama_program_studi', 'ASC')
            ->findAll();

        [
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'programStudiFilterLocked' => $programStudiFilterLocked,
            'showProgramStudiAllOption' => $showProgramStudiAllOption,
        ] = $this->resolveSelectedProgramStudiFilter($prodiAktifAkreditasi);

        $requestedId = (int) ($this->request->getGet('kriteria_id') ?? 0);
        $kriteriaId = $requestedId > 0 ? $requestedId : ($kriterias[0]['id'] ?? null);
        $selectedKriteria = null;
        foreach ($kriterias as $item) {
            if ((int) ($item['id'] ?? 0) === (int) $kriteriaId) {
                $selectedKriteria = $item;
                break;
            }
        }

        $dokumen = [];
        if ($kriteriaId) {
            $dokumen = $this->getDokumenPublicByKriteria($kriteriaId, $selectedProgramStudiId);
        }

        $totalDokumenPublic = $this->getTotalDokumenPublik(false, $selectedProgramStudiId);
        $ringkasanKriteria = $this->getRingkasanPublikPerKriteria($totalDokumenPublic, $selectedProgramStudiId);
        $kriteriaPanels = $this->buildPublicKriteriaPanels($kriterias, $selectedProgramStudiId);

        $data = [
            'title' => 'Dokumen Kriteria',
            'menuActive' => 'kriteria',
            'kriterias' => $kriterias,
            'kriteriaActive' => $kriteriaId,
            'selectedKriteria' => $selectedKriteria,
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'programStudiFilterLocked' => $programStudiFilterLocked,
            'showProgramStudiAllOption' => $showProgramStudiAllOption,
            'programStudiAktifList' => $prodiAktifAkreditasi,
            'dokumen' => $dokumen,
            'kriteriaPanels' => $kriteriaPanels,
            'ringkasanKriteria' => $ringkasanKriteria,
            'totalDokumenPublic' => $totalDokumenPublic,
        ];

        return view('public/kriteria', $data);
    }

    /**
     * Detail Kriteria - Load dokumen untuk kriteria spesifik (via AJAX atau langsung)
     */
    public function kriteriaDetail($id)
    {
        $id = (int) $id;

        // Validasi kriteria
        $kriteria = $this->kriteriaModel
            ->where('is_aktif', 1)
            ->find($id);

        if (!$kriteria) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $kriterias = $this->kriteriaModel->getAktif();

        $prodiAktifAkreditasi = $this->programStudiModel
            ->where('is_aktif_akreditasi', 1)
            ->orderBy('nama_program_studi', 'ASC')
            ->findAll();

        [
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'programStudiFilterLocked' => $programStudiFilterLocked,
            'showProgramStudiAllOption' => $showProgramStudiAllOption,
        ] = $this->resolveSelectedProgramStudiFilter($prodiAktifAkreditasi);

        $dokumen = $this->getDokumenPublicByKriteria($id, $selectedProgramStudiId);
        $totalDokumenPublic = $this->getTotalDokumenPublik(false, $selectedProgramStudiId);
        $ringkasanKriteria = $this->getRingkasanPublikPerKriteria($totalDokumenPublic, $selectedProgramStudiId);
        $kriteriaPanels = $this->buildPublicKriteriaPanels($kriterias, $selectedProgramStudiId);

        // Jika request AJAX, return JSON
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'data' => $dokumen,
                'meta' => [
                    'selectedProgramStudiId' => $selectedProgramStudiId,
                    'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
                    'ringkasanKriteria' => $ringkasanKriteria,
                    'totalDokumenPublic' => $totalDokumenPublic,
                ],
            ]);
        }

        $query = ['kriteria_id' => $id];
        if ($selectedProgramStudiId > 0) {
            $query['program_studi_id'] = $selectedProgramStudiId;
        }

        return redirect()->to(site_url('portal/kriteria') . '?' . http_build_query($query));
    }

    /**
     * Halaman Dokumen Penting
     * Menampilkan dokumen strategis/utama: LED, LKPS, Renstra, Statuta, Kurikulum, SK penting
     */
    public function dokumenPenting()
    {
        $dokumen = $this->getDokumenPentingPublik(24);
        $dokumenGrouped = [];
        foreach ($dokumen as $doc) {
            $jenis = $doc['jenis_dokumen'];
            if (!isset($dokumenGrouped[$jenis])) {
                $dokumenGrouped[$jenis] = [];
            }
            $dokumenGrouped[$jenis][] = $doc;
        }

        $tanggalPembaruanTerakhir = $dokumen[0]['tanggal_validasi'] ?? '';

        $data = [
            'title' => 'Dokumen Penting',
            'menuActive' => 'penting',
            'dokumen' => $dokumen,
            'dokumenGrouped' => $dokumenGrouped,
            'totalDokumenPenting' => count($dokumen),
            'totalKategoriPenting' => count($dokumenGrouped),
            'tanggalPembaruanTerakhir' => $tanggalPembaruanTerakhir,
        ];

        return view('public/dokumen_penting', $data);
    }

    /**
     * Halaman Pencarian Dokumen Public
     * Pencarian dengan kata kunci dan filter kriteria, kategori, tahun
     */
    public function pencarian()
    {
        // Sanitasi & validasi parameter GET
        $keyword = substr(trim((string) ($this->request->getGet('q') ?? '')), 0, 200);

        $kriteriaIdRaw = $this->request->getGet('kriteria_id');
        $kriteriaId = ($kriteriaIdRaw !== null && ctype_digit((string) $kriteriaIdRaw) && (int) $kriteriaIdRaw > 0)
            ? (int) $kriteriaIdRaw
            : null;

        // kategori dipass via Query Builder (parameterized), cukup dibatasi panjangnya
        $kategoriRaw = $this->request->getGet('kategori');
        $kategori = ($kategoriRaw !== null && strlen((string) $kategoriRaw) <= 100)
            ? trim((string) $kategoriRaw)
            : null;

        $tahunRaw = $this->request->getGet('tahun');
        $tahun = null;
        if ($tahunRaw !== null && ctype_digit((string) $tahunRaw)) {
            $tahunInt = (int) $tahunRaw;
            if ($tahunInt >= 1900 && $tahunInt <= 2100) {
                $tahun = $tahunInt;
            }
        }

        $programStudiAktif = $this->programStudiModel
            ->select('id')
            ->where('is_aktif_akreditasi', 1)
            ->findAll();
        $activeProgramStudiIds = array_values(array_unique(array_map(
            static fn ($row) => (int) ($row['id'] ?? 0),
            $programStudiAktif
        )));
        $activeProgramStudiIds = array_values(array_filter($activeProgramStudiIds, static fn ($id) => $id > 0));
        $hasActiveProgramStudi = ! empty($activeProgramStudiIds);

        $dokumen = [];

        if (!empty($keyword) || $kriteriaId || $kategori || $tahun) {
            $query = $this->dokumenModel
                ->select('dokumen.*, kriterias.nomor_kriteria, kriterias.kode AS kode_kriteria, kriterias.nama_kriteria')
                ->join('kriterias', 'kriterias.id = dokumen.kriteria_id', 'left')
                ->where('dokumen.status_dokumen', 'tervalidasi')
                ->where('dokumen.is_aktif', 1)
                ->where('dokumen.deleted_at', null);

            if (! empty($activeProgramStudiIds)) {
                $query->whereIn('dokumen.program_studi_id', $activeProgramStudiIds);
            } else {
                $query->where('1 = 0', null, false);
            }

            if (!empty($keyword)) {
                $keyword = trim($keyword);
                $query->groupStart()
                    ->like('dokumen.judul_dokumen', $keyword)
                    ->orLike('dokumen.deskripsi', $keyword)
                    ->orLike('dokumen.kode_dokumen', $keyword)
                    ->groupEnd();
            }

            if ($kriteriaId) {
                $query->where('dokumen.kriteria_id', (int) $kriteriaId);
            }

            if ($kategori) {
                $query->where('dokumen.jenis_dokumen', $kategori);
            }

            if ($tahun) {
                $query->where('dokumen.tahun_dokumen', (int) $tahun);
            }

            $dokumen = $query
                ->orderBy('dokumen.tanggal_validasi', 'DESC')
                ->orderBy('dokumen.judul_dokumen', 'ASC')
                ->findAll();
        }

        $kategoriList = $this->dokumenModel
            ->distinct()
            ->select('jenis_dokumen')
            ->where('status_dokumen', 'tervalidasi')
            ->where('is_aktif', 1)
            ->where('deleted_at', null);
        if (! empty($activeProgramStudiIds)) {
            $kategoriList = $kategoriList->whereIn('program_studi_id', $activeProgramStudiIds);
        } else {
            $kategoriList = $kategoriList->where('1 = 0', null, false);
        }
        $kategoriList = $kategoriList
            ->orderBy('jenis_dokumen', 'ASC')
            ->findAll();

        $tahunList = $this->dokumenModel
            ->distinct()
            ->select('tahun_dokumen')
            ->where('status_dokumen', 'tervalidasi')
            ->where('is_aktif', 1)
            ->where('deleted_at', null);
        if (! empty($activeProgramStudiIds)) {
            $tahunList = $tahunList->whereIn('program_studi_id', $activeProgramStudiIds);
        } else {
            $tahunList = $tahunList->where('1 = 0', null, false);
        }
        $tahunList = $tahunList
            ->orderBy('tahun_dokumen', 'DESC')
            ->findAll();

        $kriterias = $this->kriteriaModel->getAktif();

        $hasFilter = !empty($keyword) || !empty($kriteriaId) || !empty($kategori) || !empty($tahun);
        $filterAktif = count(array_filter([
            trim((string) $keyword),
            $kriteriaId,
            $kategori,
            $tahun,
        ], static fn ($value) => $value !== null && $value !== ''));

        $totalDokumenPublic = 0;
        if (! empty($activeProgramStudiIds)) {
            $totalDokumenPublic = (int) db_connect()->table('dokumen')
                ->where('deleted_at', null)
                ->where('status_dokumen', 'tervalidasi')
                ->where('is_aktif', 1)
                ->whereIn('program_studi_id', $activeProgramStudiIds)
                ->countAllResults();
        }

        $data = [
            'title' => 'Pencarian Dokumen',
            'menuActive' => 'pencarian',
            'keyword' => $keyword,
            'kriteriaId' => $kriteriaId,
            'kategori' => $kategori,
            'tahun' => $tahun,
            'dokumen' => $dokumen,
            'kategoriList' => $kategoriList,
            'tahunList' => $tahunList,
            'kriterias' => $kriterias,
            'hasFilter' => $hasFilter,
            'filterAktif' => $filterAktif,
            'totalDokumenPublic' => $totalDokumenPublic,
            'hasActiveProgramStudi' => $hasActiveProgramStudi,
        ];

        return view('public/pencarian', $data);
    }

    /**
     * Helper: Ambil dokumen public berdasarkan kriteria
     */
    private function getDokumenPublicByKriteria($kriteriaId, ?int $programStudiId = null)
    {
        $builder = $this->dokumenModel
            ->select('dokumen.*, kriterias.nomor_kriteria, kriterias.nama_kriteria')
            ->join('kriterias', 'kriterias.id = dokumen.kriteria_id', 'left')
            ->where('dokumen.kriteria_id', (int) $kriteriaId)
            ->where('dokumen.status_dokumen', 'tervalidasi')
            ->where('dokumen.is_aktif', 1);

        $programStudiId = (int) ($programStudiId ?? 0);
        if ($programStudiId > 0) {
            $builder->where('dokumen.program_studi_id', $programStudiId);
        }

        return $builder
            ->orderBy('dokumen.tanggal_validasi', 'DESC')
            ->findAll();
    }

    private function getTotalDokumenPublik(bool $withFileOnly = false, ?int $programStudiId = null): int
    {
        $builder = db_connect()->table('dokumen')
            ->where('deleted_at', null)
            ->where('status_dokumen', 'tervalidasi')
            ->where('is_aktif', 1);

        $programStudiId = (int) ($programStudiId ?? 0);
        if ($programStudiId > 0) {
            $builder->where('program_studi_id', $programStudiId);
        }

        if ($withFileOnly) {
            $builder->where('path_file IS NOT NULL', null, false)
                ->where("TRIM(COALESCE(path_file, '')) <> ''", null, false);
        }

        return (int) $builder->countAllResults();
    }

    private function getRingkasanPublikPerKriteria(int $totalDokumenPublic, ?int $programStudiId = null): array
    {
        $programStudiId = (int) ($programStudiId ?? 0);
        $db = db_connect();
        $dokumenRingkasanBuilder = $db->table('dokumen')
            ->select([
                'dokumen.kriteria_id',
                'COUNT(dokumen.id) AS total_dokumen',
                'MAX(dokumen.tanggal_validasi) AS tanggal_terakhir',
            ])
            ->where('dokumen.deleted_at', null)
            ->where('dokumen.status_dokumen', 'tervalidasi')
            ->where('dokumen.is_aktif', 1)
            ->groupBy('dokumen.kriteria_id');

        if ($programStudiId > 0) {
            $dokumenRingkasanBuilder->where('dokumen.program_studi_id', $programStudiId);
        }

        $rows = $db->table('kriterias')
            ->select([
                'kriterias.id',
                'kriterias.kode',
                'kriterias.nomor_kriteria',
                'kriterias.nama_kriteria',
                'COALESCE(dokumen_ringkasan.total_dokumen, 0) AS total_dokumen',
                'dokumen_ringkasan.tanggal_terakhir',
            ])
            ->join(
                '(' . $dokumenRingkasanBuilder->getCompiledSelect() . ') dokumen_ringkasan',
                'dokumen_ringkasan.kriteria_id = kriterias.id',
                'left',
                false
            )
            ->where('kriterias.is_aktif', 1)
            ->orderBy('kriterias.urutan', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['persentase'] = $totalDokumenPublic > 0
                ? (int) round((((int) ($row['total_dokumen'] ?? 0)) / $totalDokumenPublic) * 100)
                : 0;
        }
        unset($row);

        return $rows;
    }

    private function buildPublicKriteriaPanels(array $kriterias, ?int $programStudiId = null): array
    {
        $panels = [];
        foreach ($kriterias as $kriteria) {
            $kriteriaId = (int) ($kriteria['id'] ?? 0);
            if ($kriteriaId <= 0) {
                continue;
            }

            $structured = $this->getDokumenPublikTerstrukturByKriteria($kriteriaId, $programStudiId);
            $kriteria['direct_docs'] = $structured['direct_docs'];
            $kriteria['sub_bagian_list'] = $structured['sub_bagian_list'];
            $kriteria['total_dokumen'] = count($structured['direct_docs']);
            foreach ($structured['sub_bagian_list'] as $subBagian) {
                $kriteria['total_dokumen'] += count($subBagian['dokumen_list'] ?? []);
            }

            $panels[] = $kriteria;
        }

        return $panels;
    }

    private function getDokumenPublikTerstrukturByKriteria(int $kriteriaId, ?int $programStudiId = null): array
    {
        $subBagianListRaw = $this->subBagianModel->getByKriteria($kriteriaId, $programStudiId);
        $builder = db_connect()->table('dokumen')
            ->select([
                'dokumen.id',
                'dokumen.kriteria_id',
                'dokumen.sub_bagian_id',
                'dokumen.kode_dokumen',
                'dokumen.judul_dokumen',
                'dokumen.deskripsi',
                'dokumen.jenis_dokumen',
                'dokumen.status_dokumen',
                'dokumen.sumber_dokumen',
                'dokumen.link_dokumen',
                'dokumen.tahun_dokumen',
                'dokumen.path_file',
                'dokumen.versi',
                'dokumen.tanggal_validasi',
                'dokumen.updated_at',
                'dokumen.created_at',
                'sub_bagian.nama_sub_bagian',
                'sub_bagian.slug_sub_bagian',
                'sub_bagian.urutan AS urutan_sub_bagian',
                'uploader.nama_lengkap AS nama_pengunggah',
            ])
            ->join('sub_bagian', 'sub_bagian.id = dokumen.sub_bagian_id', 'left')
            ->join('users uploader', 'uploader.id = dokumen.uploaded_by', 'left')
            ->where('dokumen.kriteria_id', $kriteriaId)
            ->where('dokumen.deleted_at', null)
            ->where('dokumen.status_dokumen', 'tervalidasi')
            ->where('dokumen.is_aktif', 1);

        $programStudiId = (int) ($programStudiId ?? 0);
        if ($programStudiId > 0) {
            $builder->where('dokumen.program_studi_id', $programStudiId);
        }

        $dokumenRows = $builder
            ->orderBy('sub_bagian.urutan', 'ASC')
            ->orderBy('dokumen.tanggal_validasi', 'DESC')
            ->orderBy('dokumen.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        $defaultSubBagianId = 0;
        $subBagianList = [];
        foreach ($subBagianListRaw as $subBagian) {
            if (($subBagian['slug_sub_bagian'] ?? '') === self::DEFAULT_SUB_BAGIAN_SLUG) {
                $defaultSubBagianId = (int) ($subBagian['id'] ?? 0);
                continue;
            }

            $subBagian['dokumen_list'] = [];
            $subBagianList[(int) ($subBagian['id'] ?? 0)] = $subBagian;
        }

        $directDocs = [];
        foreach ($dokumenRows as $dokumen) {
            $dokumen = $this->decorateWaktuDokumenPublik($dokumen);
            $subBagianId = (int) ($dokumen['sub_bagian_id'] ?? 0);
            if ($subBagianId <= 0 || ($defaultSubBagianId > 0 && $subBagianId === $defaultSubBagianId)) {
                $directDocs[] = $dokumen;
                continue;
            }

            if (! isset($subBagianList[$subBagianId])) {
                $directDocs[] = $dokumen;
                continue;
            }

            $subBagianList[$subBagianId]['dokumen_list'][] = $dokumen;
        }

        return [
            'direct_docs' => $directDocs,
            'sub_bagian_list' => array_values($subBagianList),
        ];
    }

    private function decorateWaktuDokumenPublik(array $dokumen): array
    {
        $tanggal = $dokumen['tanggal_validasi'] ?? ($dokumen['updated_at'] ?? ($dokumen['created_at'] ?? ''));
        $dokumen['waktu_tampil'] = '-';
        if (! empty($tanggal)) {
            $timestamp = strtotime((string) $tanggal);
            if ($timestamp !== false) {
                $dokumen['waktu_tampil'] = date('d-m-Y | H.i', $timestamp) . ' WITA';
            }
        }

        return $dokumen;
    }

    private function resolveSelectedProgramStudiFilter(array $programStudiList): array
    {
        $selectedProgramStudiLabelAll = 'Prodi Persiapan Akreditasi';
        $activeIds = [];
        foreach ($programStudiList as $prodi) {
            $prodiId = (int) ($prodi['id'] ?? 0);
            if ($prodiId > 0) {
                $activeIds[] = $prodiId;
            }
        }

        $activeIds = array_values(array_unique($activeIds));
        $programStudiFilterLocked = count($activeIds) === 1;
        $selectedProgramStudiId = $programStudiFilterLocked
            ? (int) ($activeIds[0] ?? 0)
            : (int) ($this->request->getGet('program_studi_id') ?? 0);

        if ($selectedProgramStudiId > 0 && ! in_array($selectedProgramStudiId, $activeIds, true)) {
            $selectedProgramStudiId = $programStudiFilterLocked ? (int) ($activeIds[0] ?? 0) : 0;
        }

        $selectedProgramStudiLabel = $selectedProgramStudiLabelAll;
        if ($selectedProgramStudiId > 0) {
            foreach ($programStudiList as $prodi) {
                if ((int) ($prodi['id'] ?? 0) !== $selectedProgramStudiId) {
                    continue;
                }

                $selectedProgramStudiLabel = trim((string) ($prodi['nama_program_studi'] ?? ''));
                if (! empty($prodi['jenjang'])) {
                    $selectedProgramStudiLabel .= ' (' . trim((string) $prodi['jenjang']) . ')';
                }
                break;
            }
        }

        return [
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'programStudiFilterLocked' => $programStudiFilterLocked,
            'showProgramStudiAllOption' => count($activeIds) > 1,
        ];
    }

    private function getDokumenTerbaruPublik(int $limit = 6): array
    {
        return $this->dokumenModel
            ->select('dokumen.id, dokumen.judul_dokumen, dokumen.jenis_dokumen, dokumen.tahun_dokumen, dokumen.tanggal_validasi, dokumen.path_file, kriterias.kode AS kode_kriteria, program_studi.nama_program_studi')
            ->join('kriterias', 'kriterias.id = dokumen.kriteria_id', 'left')
            ->join('program_studi', 'program_studi.id = dokumen.program_studi_id', 'left')
            ->where('dokumen.status_dokumen', 'tervalidasi')
            ->where('dokumen.is_aktif', 1)
            ->where('dokumen.deleted_at', null)
            ->orderBy('dokumen.tanggal_validasi', 'DESC')
            ->orderBy('dokumen.updated_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    private function getDokumenPentingPublik(int $limit = 6): array
    {
        $prioritasJenis = [
            'Laporan',
            'Surat Keputusan (SK)',
            'SOP',
            'Data',
            'Dokumen Pendukung',
            'Formulir',
            'LED',
            'LKPS',
            'Renstra',
            'Statuta',
            'Kurikulum',
        ];

        $query = $this->dokumenModel
            ->select('dokumen.id, dokumen.judul_dokumen, dokumen.jenis_dokumen, dokumen.tahun_dokumen, dokumen.tanggal_validasi, dokumen.path_file, kriterias.kode AS kode_kriteria, program_studi.nama_program_studi')
            ->join('kriterias', 'kriterias.id = dokumen.kriteria_id', 'left')
            ->join('program_studi', 'program_studi.id = dokumen.program_studi_id', 'left')
            ->where('dokumen.status_dokumen', 'tervalidasi')
            ->where('dokumen.is_aktif', 1)
            ->where('dokumen.deleted_at', null)
            ->whereIn('dokumen.jenis_dokumen', $prioritasJenis)
            ->orderBy("FIELD(dokumen.jenis_dokumen, 'Laporan', 'Surat Keputusan (SK)', 'SOP', 'Data', 'Dokumen Pendukung', 'Formulir', 'LED', 'LKPS', 'Renstra', 'Statuta', 'Kurikulum')", '', false)
            ->orderBy('dokumen.tanggal_validasi', 'DESC')
            ->limit($limit)
            ->findAll();

        if ($query !== []) {
            return $query;
        }

        return $this->getDokumenTerbaruPublik($limit);
    }
}
