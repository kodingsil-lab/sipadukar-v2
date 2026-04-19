<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\KriteriaModel;
use App\Models\ProgramStudiModel;
use App\Models\SubBagianModel;

class LaporanController extends BaseController
{
    protected DokumenModel $dokumenModel;
    protected KriteriaModel $kriteriaModel;
    protected ProgramStudiModel $programStudiModel;
    protected SubBagianModel $subBagianModel;

    public function __construct()
    {
        $this->dokumenModel   = new DokumenModel();
        $this->kriteriaModel  = new KriteriaModel();
        $this->programStudiModel = new ProgramStudiModel();
        $this->subBagianModel = new SubBagianModel();
    }

    public function index()
    {
        $filter = [
            'kriteria_id'    => $this->request->getGet('kriteria_id'),
            'sub_bagian_id'  => $this->request->getGet('sub_bagian_id'),
            'status_dokumen' => $this->request->getGet('status_dokumen'),
            'tahun_dokumen'  => $this->request->getGet('tahun_dokumen'),
            'program_studi_id' => $this->request->getGet('program_studi_id'),
            'mode'           => $this->request->getGet('mode'),
            'sort_by'        => $this->request->getGet('sort_by'),
            'sort_dir'       => $this->request->getGet('sort_dir'),
        ];
        $filter['sort_by'] = trim((string) ($filter['sort_by'] ?? '')) ?: 'updated_at';
        $sortDir = strtolower(trim((string) ($filter['sort_dir'] ?? '')));
        $filter['sort_dir'] = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $programStudiList = $this->getAccessibleProgramStudiList();
        $filter['program_studi_id'] = $this->resolveProgramStudiFilter($filter['program_studi_id'], $programStudiList);

        $pakaiFilterProdiAktif = $this->dokumenModel->isFilterProdiAktifEnabled();
        $laporanListFull   = $this->dokumenModel->getLaporan($filter, $pakaiFilterProdiAktif);
        $rekapStatus   = $this->dokumenModel->getRekapStatus($filter, $pakaiFilterProdiAktif);
        $rekapKriteria = $this->dokumenModel->getRekapKriteria($filter, $pakaiFilterProdiAktif);
        $kriteriaList  = $this->kriteriaModel->getAktif();

        $subBagianList = [];
        if (! empty($filter['kriteria_id'])) {
            $subBagianList = $this->subBagianModel->getByKriteria((int) $filter['kriteria_id'], (int) ($filter['program_studi_id'] ?? 0));
        } else {
            $subBagianBuilder = $this->subBagianModel->where('deleted_at', null);
            if (! empty($filter['program_studi_id'])) {
                $subBagianBuilder->where('program_studi_id', (int) $filter['program_studi_id']);
            }

            $subBagianList = $subBagianBuilder->orderBy('nama_sub_bagian', 'ASC')->findAll();
        }

        $perPage = 25;
        $currentPage = max(1, (int) ($this->request->getGet('page_laporan') ?? 1));
        $totalData = count($laporanListFull);
        $totalPages = max(1, (int) ceil($totalData / $perPage));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $perPage;
        $laporanList = array_slice($laporanListFull, $offset, $perPage);
        $laporanPagination = $this->buildPaginationMeta($currentPage, $totalPages, $filter);

        return view('laporan/index', [
            'title'         => 'Laporan Dokumen',
            'filter'        => $filter,
            'laporanList'   => $laporanList,
            'laporanTotalData' => $totalData,
            'laporanOffset' => $offset,
            'laporanPagination' => $laporanPagination,
            'rekapStatus'   => $rekapStatus,
            'rekapKriteria' => $rekapKriteria,
            'kriteriaList'  => $kriteriaList,
            'programStudiList' => $programStudiList,
            'subBagianList' => $subBagianList,
            'pakaiFilterProdiAktif' => $pakaiFilterProdiAktif,
            'canSelectProgramStudi' => has_role(['admin', 'lpm', 'dekan']) && count($programStudiList) > 1,
        ]);
    }

    private function getAccessibleProgramStudiList(): array
    {
        $builder = $this->programStudiModel->orderBy('nama_program_studi', 'ASC');

        if (has_role(['admin', 'lpm'])) {
            return $builder->findAll();
        }

        if (has_role('dekan')) {
            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            if ($userUppsId <= 0) {
                return [];
            }

            return $builder->where('upps_id', $userUppsId)->findAll();
        }

        $userProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
        if ($userProgramStudiId <= 0) {
            return [];
        }

        return $builder->where('id', $userProgramStudiId)->findAll();
    }

    private function resolveProgramStudiFilter(mixed $rawProgramStudiId, array $programStudiList): string
    {
        $programStudiId = (int) $rawProgramStudiId;
        if ($programStudiId <= 0) {
            if (has_role('kaprodi')) {
                $sessionProdiId = (int) (session()->get('program_studi_id') ?? 0);
                return $sessionProdiId > 0 ? (string) $sessionProdiId : '';
            }

            return '';
        }

        $allowedIds = array_map(static fn ($row) => (int) ($row['id'] ?? 0), $programStudiList);
        return in_array($programStudiId, $allowedIds, true) ? (string) $programStudiId : '';
    }

    private function buildPaginationMeta(int $currentPage, int $totalPages, array $filter): array
    {
        if ($totalPages <= 1) {
            return [];
        }

        $baseQuery = array_filter($filter, static fn ($v) => $v !== null && $v !== '');
        unset($baseQuery['page_laporan']);

        $buildUrl = static function (int $page) use ($baseQuery): string {
            $query = $baseQuery;
            $query['page_laporan'] = $page;
            return base_url('/laporan?' . http_build_query($query));
        };

        $windowStart = max(1, $currentPage - 2);
        $windowEnd = min($totalPages, $currentPage + 2);

        if (($windowEnd - $windowStart + 1) < 5) {
            if ($windowStart === 1) {
                $windowEnd = min($totalPages, $windowStart + 4);
            } elseif ($windowEnd === $totalPages) {
                $windowStart = max(1, $windowEnd - 4);
            }
        }

        $pages = [];
        for ($page = $windowStart; $page <= $windowEnd; $page++) {
            $pages[] = [
                'label' => $page,
                'url' => $buildUrl($page),
                'active' => $page === $currentPage,
            ];
        }

        return [
            'prev_url' => $currentPage > 1 ? $buildUrl($currentPage - 1) : null,
            'next_url' => $currentPage < $totalPages ? $buildUrl($currentPage + 1) : null,
            'pages' => $pages,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
        ];
    }
}
