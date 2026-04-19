<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ProgramStudiModel;

class DokumenModel extends Model
{
    private ?bool $cachedActiveProdiFilterEnabled = null;
    private ?array $cachedActiveProdiUnits = null;

    protected $table            = 'dokumen';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kriteria_id',
        'sub_bagian_id',
        'kode_dokumen',
        'judul_dokumen',
        'slug_dokumen',
        'deskripsi',
        'nomor_dokumen',
        'jenis_dokumen',
        'sumber_dokumen',
        'link_dokumen',
        'tahun_dokumen',
        'nama_file',
        'path_file',
        'ekstensi_file',
        'mime_type',
        'ukuran_file',
        'versi',
        'status_dokumen',
        'catatan_terakhir',
        'tanggal_upload',
        'tanggal_submit',
        'tanggal_validasi',
        'uploaded_by',
        'reviewer_id',
        'program_studi_id',
        'unit_kerja',
        'is_aktif',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'            => 'integer',
        'kriteria_id'   => 'integer',
        'sub_bagian_id' => 'integer',
        'tahun_dokumen' => '?integer',
        'ukuran_file'   => '?integer',
        'versi'         => 'integer',
        'uploaded_by'   => '?integer',
        'reviewer_id'   => '?integer',
        'program_studi_id' => '?integer',
        'is_aktif'      => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['siapkanSlugDokumen'];
    protected $beforeUpdate = ['siapkanSlugDokumen'];

    protected function siapkanSlugDokumen(array $data): array
    {
        if (empty($data['data']['judul_dokumen']) && empty($data['data']['slug_dokumen'])) {
            return $data;
        }

        $slugAwal = trim((string) ($data['data']['slug_dokumen'] ?? ''));
        if ($slugAwal === '') {
            $slugAwal = buat_slug((string) $data['data']['judul_dokumen']);
        }

        $ignoreId = null;
        if (isset($data['id'])) {
            if (is_array($data['id']) && ! empty($data['id'])) {
                $ignoreId = (int) reset($data['id']);
            } elseif (is_numeric($data['id'])) {
                $ignoreId = (int) $data['id'];
            }
        }

        $data['data']['slug_dokumen'] = $this->buatSlugDokumenUnik($slugAwal, $ignoreId);
        return $data;
    }

    private function buatSlugDokumenUnik(string $slugAwal, ?int $ignoreId = null): string
    {
        $slugDasar = trim($slugAwal);
        if ($slugDasar === '') {
            $slugDasar = 'dokumen';
        }

        $kandidat = $slugDasar;
        $counter = 2;
        while ($this->slugDokumenDipakai($kandidat, $ignoreId)) {
            $kandidat = $slugDasar . '-' . $counter;
            $counter++;
        }

        return $kandidat;
    }

    private function slugDokumenDipakai(string $slug, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()
            ->select('id')
            ->where('slug_dokumen', $slug)
            ->limit(1);

        if (($ignoreId ?? 0) > 0) {
            $builder->where('id !=', (int) $ignoreId);
        }

        return $builder->get()->getFirstRow() !== null;
    }

    protected function applyUnitScope($builder)
    {
        if (! is_login()) {
            $builder->where('1 = 0');
            return $builder;
        }

        if (has_role(['admin', 'lpm'])) {
            return $builder;
        }

        $userId   = (int) (session()->get('user_id') ?? 0);
        $userUppsId = (int) (session()->get('upps_id') ?? 0);
        $userProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
        $accessibleProgramStudiIds = user_accessible_program_studi_ids();

        if (has_role('dekan')) {
            if (! empty($accessibleProgramStudiIds)) {
                $builder->whereIn('dokumen.program_studi_id', $accessibleProgramStudiIds);
                return $builder;
            }

            if ($userUppsId > 0) {
                $builder->whereIn(
                    'dokumen.program_studi_id',
                    static function ($sub) use ($userUppsId) {
                        $sub->select('id')
                            ->from('program_studi')
                            ->where('upps_id', $userUppsId);
                    }
                );
                return $builder;
            }

            $builder->where('1 = 0');
            return $builder;
        }

        if (has_role('kaprodi')) {
            if (! empty($accessibleProgramStudiIds)) {
                $builder->whereIn('dokumen.program_studi_id', $accessibleProgramStudiIds);
                return $builder;
            }

            $builder->where('1 = 0');
            return $builder;
        }

        if (has_role('dosen')) {
            if (! empty($accessibleProgramStudiIds)) {
                $builder->groupStart()
                    ->where('dokumen.uploaded_by', $userId)
                    ->orWhereIn('dokumen.program_studi_id', $accessibleProgramStudiIds)
                    ->groupEnd();
                return $builder;
            }

            $builder->where('dokumen.uploaded_by', $userId);
            return $builder;
        }

        $builder->where('1 = 0');
        return $builder;
    }

    protected function applyActiveProdiScope($builder, bool $onlyActiveProdi = false)
    {
        if (! $onlyActiveProdi) {
            return $builder;
        }

        $activeIds = $this->getActiveProdiIdList();
        if (empty($activeIds)) {
            return $builder;
        }

        $builder->whereIn('dokumen.program_studi_id', $activeIds);
        return $builder;
    }

    protected function getActiveProdiIdList(): array
    {
        if (is_array($this->cachedActiveProdiUnits)) {
            return $this->cachedActiveProdiUnits;
        }

        $rows = (new ProgramStudiModel())
            ->select('id')
            ->where('is_aktif_akreditasi', 1)
            ->findAll();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        $this->cachedActiveProdiUnits = array_values(array_unique($ids));
        return $this->cachedActiveProdiUnits;
    }

    public function isFilterProdiAktifEnabled(): bool
    {
        if ($this->cachedActiveProdiFilterEnabled !== null) {
            return $this->cachedActiveProdiFilterEnabled;
        }

        $count = (new ProgramStudiModel())
            ->where('is_aktif_akreditasi', 1)
            ->countAllResults();

        $this->cachedActiveProdiFilterEnabled = $count > 0;
        return $this->cachedActiveProdiFilterEnabled;
    }

    protected function baseLaporanQuery(bool $onlyActiveProdi = false)
    {
        $builder = $this->db->table('dokumen')
            ->select('
                dokumen.*,
                kriterias.nama_kriteria,
                kriterias.kode as kode_kriteria,
                sub_bagian.nama_sub_bagian,
                uploader.nama_lengkap as nama_pengunggah,
                reviewer.nama_lengkap as nama_reviewer,
                program_studi.nama_program_studi as nama_program_studi
            ')
            ->join('kriterias', 'kriterias.id = dokumen.kriteria_id', 'left')
            ->join('sub_bagian', 'sub_bagian.id = dokumen.sub_bagian_id', 'left')
            ->join('users as uploader', 'uploader.id = dokumen.uploaded_by', 'left')
            ->join('users as reviewer', 'reviewer.id = dokumen.reviewer_id', 'left')
            ->join('program_studi', 'program_studi.id = dokumen.program_studi_id', 'left')
            ->where('dokumen.deleted_at', null);

        $builder = $this->applyUnitScope($builder);
        return $this->applyActiveProdiScope($builder, $onlyActiveProdi);
    }

    public function getLengkap(bool $onlyActiveProdi = false): array
    {
        return $this->baseLaporanQuery($onlyActiveProdi)
            ->orderBy('dokumen.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getDetailById(int $id): ?array
    {
        return $this->baseLaporanQuery()
            ->select('
                dokumen.*,
                kriterias.nama_kriteria,
                kriterias.kode as kode_kriteria,
                sub_bagian.nama_sub_bagian,
                sub_bagian.slug_sub_bagian,
                uploader.nama_lengkap as nama_pengunggah,
                reviewer.nama_lengkap as nama_reviewer,
                program_studi.nama_program_studi as nama_program_studi
            ')
            ->where('dokumen.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getByKriteria(int $kriteriaId, bool $onlyActiveProdi = false, ?int $programStudiId = null, bool $includeUnassignedWhenAll = false): array
    {
        $builder = $this->baseLaporanQuery(false)
            ->where('dokumen.kriteria_id', $kriteriaId);

        $programStudiId = (int) ($programStudiId ?? 0);
        if ($programStudiId > 0) {
            $builder->where('dokumen.program_studi_id', $programStudiId);
        } elseif ($onlyActiveProdi) {
            $activeIds = $this->getActiveProdiIdList();
            if (! empty($activeIds)) {
                if ($includeUnassignedWhenAll) {
                    $builder->groupStart()
                        ->whereIn('dokumen.program_studi_id', $activeIds)
                        ->orWhere('dokumen.program_studi_id', null)
                        ->groupEnd();
                } else {
                    $builder->whereIn('dokumen.program_studi_id', $activeIds);
                }
            }
        }

        return $builder
            ->orderBy('sub_bagian.urutan', 'ASC')
            ->orderBy('dokumen.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getBySubBagian(int $subBagianId, bool $onlyActiveProdi = false): array
    {
        return $this->baseLaporanQuery($onlyActiveProdi)
            ->where('dokumen.sub_bagian_id', $subBagianId)
            ->orderBy('dokumen.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getByStatus(string $status, int $limit = 10, bool $onlyActiveProdi = false): array
    {
        return $this->baseLaporanQuery($onlyActiveProdi)
            ->where('dokumen.status_dokumen', $status)
            ->orderBy('dokumen.updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getTerbaru(int $limit = 10, bool $onlyActiveProdi = false): array
    {
        return $this->baseLaporanQuery($onlyActiveProdi)
            ->orderBy('dokumen.updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getRingkasanPerKriteria(bool $onlyActiveProdi = false, ?int $programStudiId = null, ?int $kriteriaId = null): array
    {
        $programStudiId = (int) ($programStudiId ?? 0);
        $kriteriaId = (int) ($kriteriaId ?? 0);
        $dokumenRingkasanBuilder = $this->db->table('dokumen')
            ->select(" 
                dokumen.kriteria_id,
                COUNT(dokumen.id) as total_dokumen,
                SUM(CASE WHEN dokumen.status_dokumen = 'tervalidasi' THEN 1 ELSE 0 END) as total_tervalidasi,
                SUM(CASE WHEN dokumen.status_dokumen = 'perlu_revisi' THEN 1 ELSE 0 END) as total_revisi,
                SUM(CASE WHEN dokumen.status_dokumen = 'draft' THEN 1 ELSE 0 END) as total_draft
            ", false)
            ->where('dokumen.deleted_at', null);

        if ($programStudiId > 0) {
            $dokumenRingkasanBuilder->where('dokumen.program_studi_id', $programStudiId);
        } elseif ($onlyActiveProdi) {
            $activeIds = $this->getActiveProdiIdList();
            if (! empty($activeIds)) {
                $safeIds = array_map(static fn ($id) => (int) $id, $activeIds);
                $dokumenRingkasanBuilder->whereIn('dokumen.program_studi_id', $safeIds);
            } else {
                $dokumenRingkasanBuilder->where('1 = 0', null, false);
            }
        }

        if (! has_role(['admin', 'lpm'])) {
            $userId   = (int) (session()->get('user_id') ?? 0);
            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            $userProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();

            if (has_role('dekan')) {
                if (! empty($accessibleProgramStudiIds)) {
                    $dokumenRingkasanBuilder->whereIn('dokumen.program_studi_id', $accessibleProgramStudiIds);
                } elseif ($userUppsId > 0) {
                    $dokumenRingkasanBuilder->whereIn(
                        'dokumen.program_studi_id',
                        static function ($sub) use ($userUppsId) {
                            $sub->select('id')
                                ->from('program_studi')
                                ->where('upps_id', $userUppsId);
                        }
                    );
                } else {
                    $dokumenRingkasanBuilder->where('1 = 0', null, false);
                }
            } elseif (has_role('kaprodi')) {
                if (! empty($accessibleProgramStudiIds)) {
                    $dokumenRingkasanBuilder->whereIn('dokumen.program_studi_id', $accessibleProgramStudiIds);
                } else {
                    $dokumenRingkasanBuilder->where('1 = 0', null, false);
                }
            } elseif (has_role('dosen')) {
                if (! empty($accessibleProgramStudiIds)) {
                    $dokumenRingkasanBuilder->groupStart()
                        ->where('dokumen.uploaded_by', $userId)
                        ->orWhereIn('dokumen.program_studi_id', $accessibleProgramStudiIds)
                        ->groupEnd();
                } else {
                    $dokumenRingkasanBuilder->where('dokumen.uploaded_by', $userId);
                }
            } else {
                $dokumenRingkasanBuilder->where('1 = 0', null, false);
            }
        }

        $dokumenRingkasanBuilder->groupBy('dokumen.kriteria_id');

        $builder = $this->db->table('kriterias')
            ->select(" 
                kriterias.id,
                kriterias.kode,
                kriterias.nama_kriteria,
                COALESCE(dokumen_ringkasan.total_dokumen, 0) as total_dokumen,
                COALESCE(dokumen_ringkasan.total_tervalidasi, 0) as total_tervalidasi,
                COALESCE(dokumen_ringkasan.total_revisi, 0) as total_revisi,
                COALESCE(dokumen_ringkasan.total_draft, 0) as total_draft
            ", false)
            ->join(
                '(' . $dokumenRingkasanBuilder->getCompiledSelect() . ') dokumen_ringkasan',
                'dokumen_ringkasan.kriteria_id = kriterias.id',
                'left',
                false
            );

        if ($kriteriaId > 0) {
            $builder->where('kriterias.id', $kriteriaId);
        }

        return $builder
            ->orderBy('kriterias.urutan', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function hitungTotalDokumen(bool $onlyActiveProdi = false): int
    {
        $builder = $this->db->table('dokumen')
            ->where('dokumen.deleted_at', null);

        $builder = $this->applyUnitScope($builder);
        $builder = $this->applyActiveProdiScope($builder, $onlyActiveProdi);

        return (int) $builder->countAllResults();
    }

    public function hitungPerStatus(bool $onlyActiveProdi = false): array
    {
        $builder = $this->db->table('dokumen')
            ->select('status_dokumen, COUNT(*) as total')
            ->where('dokumen.deleted_at', null);

        $builder = $this->applyUnitScope($builder);
        $builder = $this->applyActiveProdiScope($builder, $onlyActiveProdi);

        $rows = $builder->groupBy('status_dokumen')->get()->getResultArray();

        $hasil = [
            'draft'          => 0,
            'diajukan'       => 0,
            'ditinjau'       => 0,
            'perlu_revisi'   => 0,
            'disubmit_ulang' => 0,
            'tervalidasi'    => 0,
            'ditolak'        => 0,
        ];

        foreach ($rows as $row) {
            $hasil[$row['status_dokumen']] = (int) $row['total'];
        }

        return $hasil;
    }

    public function getLaporan(array $filter = [], bool $onlyActiveProdi = false): array
    {
        $builder = $this->baseLaporanQuery($onlyActiveProdi);
        $builder = $this->applyModeFilter($builder, $filter['mode'] ?? null);

        if (! empty($filter['kriteria_id'])) {
            $builder->where('dokumen.kriteria_id', (int) $filter['kriteria_id']);
        }

        if (! empty($filter['sub_bagian_id'])) {
            $builder->where('dokumen.sub_bagian_id', (int) $filter['sub_bagian_id']);
        }

        if (! empty($filter['status_dokumen'])) {
            $builder->where('dokumen.status_dokumen', $filter['status_dokumen']);
        }

        if (! empty($filter['tahun_dokumen'])) {
            $builder->where('dokumen.tahun_dokumen', (int) $filter['tahun_dokumen']);
        }

        if (! empty($filter['program_studi_id'])) {
            $builder->where('dokumen.program_studi_id', (int) $filter['program_studi_id']);
        }

        if (! empty($filter['keyword'])) {
            $keyword = trim((string) $filter['keyword']);
            $builder->groupStart()
                ->like('dokumen.judul_dokumen', $keyword)
                ->orLike('dokumen.kode_dokumen', $keyword)
                ->orLike('dokumen.nomor_dokumen', $keyword)
                ->orLike('dokumen.jenis_dokumen', $keyword)
                ->orLike('sub_bagian.nama_sub_bagian', $keyword)
                ->orLike('kriterias.nama_kriteria', $keyword)
                ->orLike('program_studi.nama_program_studi', $keyword)
                ->groupEnd();
        }

        $allowedSortMap = [
            'judul'      => 'dokumen.judul_dokumen',
            'kriteria'   => 'kriterias.kode',
            'sub_bagian' => 'sub_bagian.nama_sub_bagian',
            'program_studi' => 'program_studi.nama_program_studi',
            'pengunggah' => 'uploader.nama_lengkap',
            'status'     => 'dokumen.status_dokumen',
            'tahun'      => 'dokumen.tahun_dokumen',
            'versi'      => 'dokumen.versi',
            'created_at' => 'dokumen.created_at',
            'updated_at' => 'dokumen.updated_at',
            'tanggal'    => 'dokumen.updated_at',
        ];

        $sortBy  = strtolower(trim((string) ($filter['sort_by'] ?? '')));
        $sortDirRaw = strtoupper(trim((string) ($filter['sort_dir'] ?? '')));

        if ($sortBy === 'prioritas_tindakan') {
            return $builder
                ->orderBy("FIELD(dokumen.status_dokumen, 'perlu_revisi', 'ditolak', 'draft', 'disubmit_ulang', 'ditinjau', 'diajukan', 'tervalidasi')", '', false)
                ->orderBy('dokumen.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        if ($sortBy !== '' && isset($allowedSortMap[$sortBy])) {
            $sortDir = $sortDirRaw;
            if ($sortDir === '') {
                $sortDir = ($sortBy === 'judul') ? 'ASC' : 'DESC';
            }
            if (! in_array($sortDir, ['ASC', 'DESC'], true)) {
                $sortDir = ($sortBy === 'judul') ? 'ASC' : 'DESC';
            }

            return $builder
                ->orderBy($allowedSortMap[$sortBy], $sortDir)
                ->orderBy('dokumen.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        $mode = trim((string) ($filter['mode'] ?? ''));
        if ($mode === 'antrian_final') {
            return $builder
                ->orderBy('dokumen.tanggal_submit', 'DESC')
                ->orderBy('dokumen.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        if ($mode === 'perlu_tindakan') {
            return $builder
                ->orderBy("FIELD(dokumen.status_dokumen, 'perlu_revisi', 'ditolak', 'draft')", '', false)
                ->orderBy('dokumen.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        return $builder
            ->orderBy('kriterias.urutan', 'ASC')
            ->orderBy('sub_bagian.urutan', 'ASC')
            ->orderBy('dokumen.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getRekapStatus(array $filter = [], bool $onlyActiveProdi = false): array
    {
        $builder = $this->db->table('dokumen')
            ->select('status_dokumen, COUNT(*) as total')
            ->where('dokumen.deleted_at', null);

        $builder = $this->applyUnitScope($builder);
        $builder = $this->applyActiveProdiScope($builder, $onlyActiveProdi);
        $builder = $this->applyModeFilter($builder, $filter['mode'] ?? null);

        if (! empty($filter['kriteria_id'])) {
            $builder->where('dokumen.kriteria_id', (int) $filter['kriteria_id']);
        }

        if (! empty($filter['sub_bagian_id'])) {
            $builder->where('dokumen.sub_bagian_id', (int) $filter['sub_bagian_id']);
        }

        if (! empty($filter['tahun_dokumen'])) {
            $builder->where('dokumen.tahun_dokumen', (int) $filter['tahun_dokumen']);
        }

        if (! empty($filter['program_studi_id'])) {
            $builder->where('dokumen.program_studi_id', (int) $filter['program_studi_id']);
        }

        return $builder->groupBy('status_dokumen')->get()->getResultArray();
    }

    public function getRekapKriteria(array $filter = [], bool $onlyActiveProdi = false): array
    {
        $builder = $this->db->table('kriterias')
            ->select("
                kriterias.id,
                kriterias.kode,
                kriterias.nama_kriteria,
                COUNT(dokumen.id) as total_dokumen,
                SUM(CASE WHEN dokumen.status_dokumen = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN dokumen.status_dokumen = 'diajukan' THEN 1 ELSE 0 END) as diajukan,
                SUM(CASE WHEN dokumen.status_dokumen = 'ditinjau' THEN 1 ELSE 0 END) as ditinjau,
                SUM(CASE WHEN dokumen.status_dokumen = 'perlu_revisi' THEN 1 ELSE 0 END) as perlu_revisi,
                SUM(CASE WHEN dokumen.status_dokumen = 'disubmit_ulang' THEN 1 ELSE 0 END) as disubmit_ulang,
                SUM(CASE WHEN dokumen.status_dokumen = 'tervalidasi' THEN 1 ELSE 0 END) as tervalidasi,
                SUM(CASE WHEN dokumen.status_dokumen = 'ditolak' THEN 1 ELSE 0 END) as ditolak
            ", false)
            ->join('dokumen', 'dokumen.kriteria_id = kriterias.id AND dokumen.deleted_at IS NULL', 'left');

        if (! has_role(['admin', 'lpm'])) {
            $userId   = (int) (session()->get('user_id') ?? 0);
            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            $userProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();

            if (has_role('dekan')) {
                if (! empty($accessibleProgramStudiIds)) {
                    $builder->whereIn('dokumen.program_studi_id', $accessibleProgramStudiIds);
                } elseif ($userUppsId > 0) {
                    $builder->whereIn(
                        'dokumen.program_studi_id',
                        static function ($sub) use ($userUppsId) {
                            $sub->select('id')
                                ->from('program_studi')
                                ->where('upps_id', $userUppsId);
                        }
                    );
                } else {
                    $builder->where('1 = 0');
                }
            } elseif (has_role('kaprodi')) {
                if (! empty($accessibleProgramStudiIds)) {
                    $builder->whereIn('dokumen.program_studi_id', $accessibleProgramStudiIds);
                } else {
                    $builder->where('1 = 0');
                }
            } elseif (has_role('dosen')) {
                if (! empty($accessibleProgramStudiIds)) {
                    $builder->groupStart()
                        ->where('dokumen.uploaded_by', $userId)
                        ->orWhereIn('dokumen.program_studi_id', $accessibleProgramStudiIds)
                        ->groupEnd();
                } else {
                    $builder->where('dokumen.uploaded_by', $userId);
                }
            } else {
                $builder->where('1 = 0');
            }
        }

        $builder = $this->applyActiveProdiScope($builder, $onlyActiveProdi);
        $builder = $this->applyModeFilter($builder, $filter['mode'] ?? null);

        if (! empty($filter['tahun_dokumen'])) {
            $builder->where('dokumen.tahun_dokumen', (int) $filter['tahun_dokumen']);
        }

        if (! empty($filter['status_dokumen'])) {
            $builder->where('dokumen.status_dokumen', $filter['status_dokumen']);
        }

        if (! empty($filter['program_studi_id'])) {
            $builder->where('dokumen.program_studi_id', (int) $filter['program_studi_id']);
        }

        return $builder
            ->groupBy('kriterias.id')
            ->orderBy('kriterias.urutan', 'ASC')
            ->get()
            ->getResultArray();
    }

    protected function applyModeFilter($builder, ?string $mode)
    {
        $mode = trim((string) $mode);
        if ($mode === '') {
            return $builder;
        }

        if ($mode === 'antrian_final') {
            $builder->whereIn('dokumen.status_dokumen', ['diajukan', 'ditinjau', 'disubmit_ulang']);
            return $builder;
        }

        if ($mode === 'perlu_tindakan') {
            $builder->whereIn('dokumen.status_dokumen', ['draft', 'perlu_revisi', 'ditolak']);
            return $builder;
        }

        return $builder;
    }
}
