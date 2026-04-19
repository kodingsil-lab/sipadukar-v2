<?php

namespace App\Controllers;

use App\Models\KriteriaModel;
use App\Models\SubBagianModel;
use App\Models\DokumenModel;
use App\Models\ProgramStudiModel;

class KriteriaController extends BaseController
{
    private const DEFAULT_SUB_BAGIAN_SLUG = '__default_kriteria_tanpa_subbagian__';
    private const PER_PAGE_DEFAULT = 15;
    private const ALLOWED_SORT_FIELDS = ['no', 'nama', 'deskripsi', 'penanggung', 'waktu', 'status'];

    public function index()
    {
        $kriteriaModel = new KriteriaModel();

        $kriteriaPertama = $kriteriaModel
            ->where('is_aktif', 1)
            ->orderBy('urutan', 'ASC')
            ->first();

        if (! $kriteriaPertama) {
            return redirect()->to('/dashboard')->with('error', 'Data kriteria belum tersedia.');
        }

        return redirect()->to('/kriteria/' . (int) $kriteriaPertama['id']);
    }

    public function show($id)
    {
        $request = service('request');
        $kriteriaModel  = new KriteriaModel();
        $subBagianModel = new SubBagianModel();
        $dokumenModel   = new DokumenModel();
        $programStudiModel = new ProgramStudiModel();
        $pakaiFilterProdiAktif = $dokumenModel->isFilterProdiAktifEnabled();

        $kriteria = $kriteriaModel->find((int) $id);

        if (! $kriteria) {
            return redirect()->to('/kriteria')->with('error', 'Data kriteria tidak ditemukan.');
        }

        $showProgramStudiFilter = has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen']);
        $programStudiAktifList = [];
        $selectedProgramStudiId = 0;
        $selectedProgramStudiLabelAll = has_role('dekan')
            ? 'Semua Prodi di Bawah Naungan'
            : ((has_role('kaprodi') || has_role('dosen')) ? 'Semua Prodi Penugasan' : 'Semua Prodi Aktif');
        $selectedProgramStudiLabel = $selectedProgramStudiLabelAll;

        if ($showProgramStudiFilter) {
            $programStudiBuilder = $programStudiModel
                ->where('is_aktif_akreditasi', 1);

            if (has_role('dekan')) {
                $accessibleProgramStudiIds = user_accessible_program_studi_ids();
                if (! empty($accessibleProgramStudiIds)) {
                    $programStudiBuilder->whereIn('id', $accessibleProgramStudiIds);
                } else {
                    $userUppsId = (int) (session()->get('upps_id') ?? 0);
                    if ($userUppsId > 0) {
                        $programStudiBuilder->where('upps_id', $userUppsId);
                    } else {
                        $programStudiBuilder->where('1 = 0');
                    }
                }
            } elseif (has_role('kaprodi')) {
                $accessibleProgramStudiIds = user_accessible_program_studi_ids();
                if (! empty($accessibleProgramStudiIds)) {
                    $programStudiBuilder->whereIn('id', $accessibleProgramStudiIds);
                } else {
                    $programStudiBuilder->where('1 = 0');
                }
            } elseif (has_role('dosen')) {
                $accessibleProgramStudiIds = user_accessible_program_studi_ids();
                if (! empty($accessibleProgramStudiIds)) {
                    $programStudiBuilder->whereIn('id', $accessibleProgramStudiIds);
                } else {
                    $programStudiBuilder->where('1 = 0');
                }
            }

            $programStudiAktifList = $programStudiBuilder
                ->orderBy('nama_program_studi', 'ASC')
                ->findAll();

            $activeIds = [];
            foreach ($programStudiAktifList as $prodi) {
                $prodiId = (int) ($prodi['id'] ?? 0);
                if ($prodiId > 0) {
                    $activeIds[] = $prodiId;
                }
            }

            $selectedProgramStudiId = (int) ($request->getGet('program_studi_id') ?? 0);
            if ($selectedProgramStudiId > 0 && ! in_array($selectedProgramStudiId, $activeIds, true)) {
                $selectedProgramStudiId = 0;
            }

            if ($selectedProgramStudiId > 0) {
                foreach ($programStudiAktifList as $prodi) {
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
        }

        $subBagianListRaw = $subBagianModel->getByKriteria(
            (int) $id,
            $selectedProgramStudiId > 0 ? $selectedProgramStudiId : null
        );
        $dokumenList      = $dokumenModel->getByKriteria(
            (int) $id,
            $pakaiFilterProdiAktif,
            $selectedProgramStudiId > 0 ? $selectedProgramStudiId : null,
            $selectedProgramStudiId <= 0
        );

        $defaultSubBagianId = 0;
        $subBagianList = [];
        foreach ($subBagianListRaw as $subBagian) {
            if (($subBagian['slug_sub_bagian'] ?? '') === self::DEFAULT_SUB_BAGIAN_SLUG) {
                $defaultSubBagianId = (int) ($subBagian['id'] ?? 0);
                continue;
            }

            $subBagianList[] = $subBagian;
        }

        $dokumenBySubBagian = [];
        foreach ($dokumenList as $dokumen) {
            $subBagianId = (int) ($dokumen['sub_bagian_id'] ?? 0);
            if ($subBagianId <= 0) {
                continue;
            }

            $dokumen = $this->decorateWaktuDokumen($dokumen);
            $dokumenBySubBagian[$subBagianId][] = $dokumen;
        }

        foreach ($subBagianList as &$subBagian) {
            $subBagianId = (int) ($subBagian['id'] ?? 0);
            $fullList = $dokumenBySubBagian[$subBagianId] ?? [];
            $subBagian['jumlah_dokumen'] = count($fullList);

            $searchKey = 'q_sb_' . $subBagianId;
            $sortKey = 'sort_sb_' . $subBagianId;
            $dirKey = 'dir_sb_' . $subBagianId;
            $pageKey = 'p_sb_' . $subBagianId;

            $searchTerm = trim((string) ($request->getGet($searchKey) ?? ''));
            $sortBy = (string) ($request->getGet($sortKey) ?? 'no');
            $sortDir = (string) ($request->getGet($dirKey) ?? 'asc');

            $processedList = $this->applySearchAndSort($fullList, $searchTerm, $sortBy, $sortDir);
            $subBagian['jumlah_dokumen_filter'] = count($processedList);

            $page = max(1, (int) ($request->getGet($pageKey) ?? 1));
            $paging = $this->paginateList($processedList, $page, self::PER_PAGE_DEFAULT);

            $subBagian['dokumen_list'] = $paging['items'];
            $subBagian['dokumen_page'] = $paging['page'];
            $subBagian['dokumen_offset'] = $paging['offset'];
            $subBagian['dokumen_total_pages'] = $paging['total_pages'];
            $subBagian['dokumen_search_key'] = $searchKey;
            $subBagian['dokumen_search_term'] = $searchTerm;
            $subBagian['dokumen_sort_by'] = $sortBy;
            $subBagian['dokumen_sort_dir'] = $sortDir;
            $subBagian['dokumen_pagination'] = $this->buildPagination(
                (int) $id,
                $request->getGet(),
                $pageKey,
                $paging['page'],
                $paging['total_pages'],
                '#subbagian-table-head-' . $subBagianId
            );
            $subBagian['dokumen_sort_urls'] = $this->buildSortUrls(
                (int) $id,
                $request->getGet(),
                $sortKey,
                $dirKey,
                $pageKey,
                $sortBy,
                $sortDir,
                '#subbagian-table-head-' . $subBagianId
            );
        }
        unset($subBagian);

        $dokumenDirectList = [];
        $dokumenDirectOffset = 0;
        $dokumenDirectPagination = [];
        $dokumenDirectSearchTerm = '';
        $dokumenDirectSortBy = 'no';
        $dokumenDirectSortDir = 'asc';
        $dokumenDirectSortUrls = [];
        $dokumenDirectJumlahFilter = 0;
        $dokumenDirectSearchKey = '';
        $dokumenDirectPageKey = '';
        if ($defaultSubBagianId > 0) {
            $fullDirectList = $dokumenBySubBagian[$defaultSubBagianId] ?? [];
            $directSearchKey = 'q_sb_' . $defaultSubBagianId;
            $directSortKey = 'sort_sb_' . $defaultSubBagianId;
            $directDirKey = 'dir_sb_' . $defaultSubBagianId;
            $directPageKey = 'p_sb_' . $defaultSubBagianId;
            $dokumenDirectSearchKey = $directSearchKey;
            $dokumenDirectPageKey = $directPageKey;

            $dokumenDirectSearchTerm = trim((string) ($request->getGet($directSearchKey) ?? ''));
            $dokumenDirectSortBy = (string) ($request->getGet($directSortKey) ?? 'no');
            $dokumenDirectSortDir = (string) ($request->getGet($directDirKey) ?? 'asc');
            $processedDirectList = $this->applySearchAndSort($fullDirectList, $dokumenDirectSearchTerm, $dokumenDirectSortBy, $dokumenDirectSortDir);
            $dokumenDirectJumlahFilter = count($processedDirectList);

            $directPage = max(1, (int) ($request->getGet($directPageKey) ?? 1));
            $directPaging = $this->paginateList($processedDirectList, $directPage, self::PER_PAGE_DEFAULT);

            $dokumenDirectList = $directPaging['items'];
            $dokumenDirectOffset = $directPaging['offset'];
            $dokumenDirectPagination = $this->buildPagination(
                (int) $id,
                $request->getGet(),
                $directPageKey,
                $directPaging['page'],
                $directPaging['total_pages'],
                '#dokumen-utama-table-head'
            );
            $dokumenDirectSortUrls = $this->buildSortUrls(
                (int) $id,
                $request->getGet(),
                $directSortKey,
                $directDirKey,
                $directPageKey,
                $dokumenDirectSortBy,
                $dokumenDirectSortDir,
                '#dokumen-utama-table-head'
            );
        } elseif (empty($subBagianList)) {
            $directSearchKey = 'q_direct';
            $directSortKey = 'sort_direct';
            $directDirKey = 'dir_direct';
            $directPageKey = 'p_direct';
            $dokumenDirectSearchKey = $directSearchKey;
            $dokumenDirectPageKey = $directPageKey;

            $dokumenDirectSearchTerm = trim((string) ($request->getGet($directSearchKey) ?? ''));
            $dokumenDirectSortBy = (string) ($request->getGet($directSortKey) ?? 'no');
            $dokumenDirectSortDir = (string) ($request->getGet($directDirKey) ?? 'asc');
            $decoratedDirectList = array_map(fn(array $row) => $this->decorateWaktuDokumen($row), $dokumenList);
            $processedDirectList = $this->applySearchAndSort($decoratedDirectList, $dokumenDirectSearchTerm, $dokumenDirectSortBy, $dokumenDirectSortDir);
            $dokumenDirectJumlahFilter = count($processedDirectList);

            $directPage = max(1, (int) ($request->getGet($directPageKey) ?? 1));
            $directPaging = $this->paginateList($processedDirectList, $directPage, self::PER_PAGE_DEFAULT);

            $dokumenDirectList = $directPaging['items'];
            $dokumenDirectOffset = $directPaging['offset'];
            $dokumenDirectPagination = $this->buildPagination(
                (int) $id,
                $request->getGet(),
                $directPageKey,
                $directPaging['page'],
                $directPaging['total_pages'],
                '#dokumen-utama-table-head'
            );
            $dokumenDirectSortUrls = $this->buildSortUrls(
                (int) $id,
                $request->getGet(),
                $directSortKey,
                $directDirKey,
                $directPageKey,
                $dokumenDirectSortBy,
                $dokumenDirectSortDir,
                '#dokumen-utama-table-head'
            );
        }

        return view('kriteria/show', [
            'title'         => 'Detail Kriteria',
            'kriteria'      => $kriteria,
            'subBagianList' => $subBagianList,
            'dokumenDirectList' => $dokumenDirectList,
            'dokumenDirectOffset' => $dokumenDirectOffset,
            'dokumenDirectPagination' => $dokumenDirectPagination,
            'dokumenDirectSearchTerm' => $dokumenDirectSearchTerm,
            'dokumenDirectSortBy' => $dokumenDirectSortBy,
            'dokumenDirectSortDir' => $dokumenDirectSortDir,
            'dokumenDirectSortUrls' => $dokumenDirectSortUrls,
            'dokumenDirectJumlahFilter' => $dokumenDirectJumlahFilter,
            'dokumenDirectSearchKey' => $dokumenDirectSearchKey,
            'dokumenDirectPageKey' => $dokumenDirectPageKey,
            'pakaiFilterProdiAktif' => $pakaiFilterProdiAktif,
            'showProgramStudiFilter' => $showProgramStudiFilter,
            'programStudiAktifList' => $programStudiAktifList,
            'selectedProgramStudiId' => $selectedProgramStudiId,
            'selectedProgramStudiLabelAll' => $selectedProgramStudiLabelAll,
            'selectedProgramStudiLabel' => $selectedProgramStudiLabel,
        ]);
    }

    private function paginateList(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($items, $offset, $perPage),
            'page' => $page,
            'offset' => $offset,
            'total' => $total,
            'total_pages' => $totalPages,
        ];
    }

    private function buildPagination(int $kriteriaId, array $queryParams, string $pageKey, int $currentPage, int $totalPages, string $anchor = ''): array
    {
        if ($totalPages <= 1) {
            return [];
        }

        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'label' => $i,
                'url' => $this->buildPageUrl($kriteriaId, $queryParams, $pageKey, $i, $anchor),
                'active' => $i === $currentPage,
            ];
        }

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'prev_url' => $currentPage > 1 ? $this->buildPageUrl($kriteriaId, $queryParams, $pageKey, $currentPage - 1, $anchor) : null,
            'next_url' => $currentPage < $totalPages ? $this->buildPageUrl($kriteriaId, $queryParams, $pageKey, $currentPage + 1, $anchor) : null,
            'pages' => $pages,
        ];
    }

    private function buildPageUrl(int $kriteriaId, array $queryParams, string $pageKey, int $page, string $anchor = ''): string
    {
        $queryParams[$pageKey] = $page;
        $queryString = http_build_query(array_filter($queryParams, static fn($value) => $value !== null && $value !== ''));
        $base = base_url('/kriteria/' . $kriteriaId);

        return $base . ($queryString !== '' ? '?' . $queryString : '') . $anchor;
    }

    private function buildSortUrls(
        int $kriteriaId,
        array $queryParams,
        string $sortKey,
        string $dirKey,
        string $pageKey,
        string $currentSortBy,
        string $currentSortDir,
        string $anchor = ''
    ): array {
        $urls = [];
        foreach (self::ALLOWED_SORT_FIELDS as $field) {
            $dir = 'asc';
            if ($field === $currentSortBy && $currentSortDir === 'asc') {
                $dir = 'desc';
            }

            $params = $queryParams;
            $params[$sortKey] = $field;
            $params[$dirKey] = $dir;
            $params[$pageKey] = 1;

            $queryString = http_build_query(array_filter($params, static fn($value) => $value !== null && $value !== ''));
            $base = base_url('/kriteria/' . $kriteriaId);

            $urls[$field] = $base . ($queryString !== '' ? '?' . $queryString : '') . $anchor;
        }

        return $urls;
    }

    private function applySearchAndSort(array $items, string $searchTerm, string $sortBy, string $sortDir): array
    {
        $sortBy = in_array($sortBy, self::ALLOWED_SORT_FIELDS, true) ? $sortBy : 'no';
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        if ($searchTerm !== '') {
            $needle = mb_strtolower($searchTerm);
            $items = array_values(array_filter($items, static function (array $row) use ($needle): bool {
                $haystack = mb_strtolower(
                    trim((string) ($row['judul_dokumen'] ?? '')) . ' ' .
                    trim((string) ($row['kode_dokumen'] ?? '')) . ' ' .
                    trim((string) ($row['deskripsi'] ?? '')) . ' ' .
                    trim((string) ($row['nama_pengunggah'] ?? '')) . ' ' .
                    label_status_dokumen($row['status_dokumen'] ?? '')
                );

                return str_contains($haystack, $needle);
            }));
        }

        $indexed = [];
        foreach ($items as $i => $row) {
            $row['_idx'] = $i;
            $indexed[] = $row;
        }

        usort($indexed, static function (array $a, array $b) use ($sortBy, $sortDir): int {
            $multiplier = $sortDir === 'desc' ? -1 : 1;

            $left = '';
            $right = '';
            switch ($sortBy) {
                case 'nama':
                    $left = mb_strtolower((string) ($a['judul_dokumen'] ?? ''));
                    $right = mb_strtolower((string) ($b['judul_dokumen'] ?? ''));
                    break;
                case 'deskripsi':
                    $left = mb_strtolower((string) ($a['deskripsi'] ?? ''));
                    $right = mb_strtolower((string) ($b['deskripsi'] ?? ''));
                    break;
                case 'penanggung':
                    $left = mb_strtolower((string) ($a['nama_pengunggah'] ?? ''));
                    $right = mb_strtolower((string) ($b['nama_pengunggah'] ?? ''));
                    break;
                case 'status':
                    $left = mb_strtolower(label_status_dokumen((string) ($a['status_dokumen'] ?? '')));
                    $right = mb_strtolower(label_status_dokumen((string) ($b['status_dokumen'] ?? '')));
                    break;
                case 'waktu':
                    $left = (string) ((int) ($a['_waktu_sort'] ?? 0));
                    $right = (string) ((int) ($b['_waktu_sort'] ?? 0));
                    break;
                case 'no':
                default:
                    $left = (string) ((int) ($a['_idx'] ?? 0));
                    $right = (string) ((int) ($b['_idx'] ?? 0));
                    break;
            }

            if ($left === $right) {
                return ((int) ($a['_idx'] ?? 0) <=> (int) ($b['_idx'] ?? 0)) * $multiplier;
            }

            if (in_array($sortBy, ['no', 'waktu'], true)) {
                return (((int) $left <=> (int) $right) * $multiplier);
            }

            return ($left <=> $right) * $multiplier;
        });

        foreach ($indexed as &$row) {
            unset($row['_idx']);
        }
        unset($row);

        return $indexed;
    }

    private function decorateWaktuDokumen(array $dokumen): array
    {
        $waktuRaw = $this->resolveWaktuAktivitas($dokumen);
        $timestamp = $waktuRaw !== null ? strtotime($waktuRaw) : false;
        $suffix = $this->resolveTimezoneSuffix();
        $dokumen['_waktu_sort'] = $timestamp !== false ? $timestamp : 0;
        $dokumen['waktu_tampil'] = $timestamp !== false
            ? date('d-m-Y | H.i', $timestamp) . ($suffix !== '' ? ' ' . $suffix : '')
            : '-';

        return $dokumen;
    }

    private function resolveWaktuAktivitas(array $dokumen): ?string
    {
        $candidates = [
            $dokumen['tanggal_validasi'] ?? null,
            $dokumen['tanggal_submit'] ?? null,
            $dokumen['tanggal_upload'] ?? null,
            $dokumen['updated_at'] ?? null,
            $dokumen['created_at'] ?? null,
        ];

        foreach ($candidates as $value) {
            $value = trim((string) ($value ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function resolveTimezoneSuffix(): string
    {
        $timezone = (string) app_setting('app_timezone', config('App')->appTimezone ?? 'Asia/Jakarta');

        return match ($timezone) {
            'Asia/Jakarta' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => '',
        };
    }
}
