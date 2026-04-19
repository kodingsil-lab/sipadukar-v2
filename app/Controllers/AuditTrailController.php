<?php

namespace App\Controllers;

use App\Models\AuditTrailModel;

class AuditTrailController extends BaseController
{
    protected AuditTrailModel $auditTrailModel;

    public function __construct()
    {
        $this->auditTrailModel = new AuditTrailModel();
    }

    public function index()
    {
        $filter = [
            'aktivitas' => $this->request->getGet('aktivitas'),
            'modul'     => $this->request->getGet('modul'),
            'keyword'   => $this->request->getGet('keyword'),
            'sort_by'   => $this->request->getGet('sort_by'),
            'sort_dir'  => $this->request->getGet('sort_dir'),
        ];
        $filter['sort_by'] = trim((string) ($filter['sort_by'] ?? '')) ?: 'created_at';
        $sortDir = strtolower(trim((string) ($filter['sort_dir'] ?? '')));
        $filter['sort_dir'] = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $perPage = 25;
        $currentPage = max(1, (int) ($this->request->getGet('page_audit') ?? 1));
        $totalData = $this->auditTrailModel->countFiltered($filter);
        $totalPages = max(1, (int) ceil($totalData / $perPage));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $perPage;
        $auditList = $this->auditTrailModel->getFiltered($filter, $perPage, $offset);
        $pagination = $this->buildPaginationMeta($currentPage, $totalPages, $filter);
        $aktivitasList = $this->auditTrailModel->getDistinctAktivitas();
        $modulList = $this->auditTrailModel->getDistinctModul();

        return view('audit_trail/index', [
            'title'     => 'Audit Trail',
            'filter'    => $filter,
            'auditList' => $auditList,
            'auditOffset' => $offset,
            'auditTotalData' => $totalData,
            'auditPagination' => $pagination,
            'aktivitasList' => $aktivitasList,
            'modulList' => $modulList,
        ]);
    }

    public function bulkDelete()
    {
        $selectedRaw = $this->request->getPost('selected_ids');
        $requestedCount = is_array($selectedRaw) ? count($selectedRaw) : 0;
        $selectedIds = $this->request->getPost('selected_ids');
        $ids = is_array($selectedIds) ? $selectedIds : [];
        $ids = array_values(array_unique(array_map(static fn ($id) => (int) $id, $ids)));
        $ids = array_values(array_filter($ids, static fn ($id) => $id > 0));

        if (empty($ids)) {
            catat_audit(
                'hapus_audit_trail_gagal',
                'audit_trail',
                null,
                'Bulk delete audit trail ditolak karena daftar ID kosong. requested_count=' . $requestedCount
            );
            return redirect()->back()->with('error', 'Pilih minimal satu log untuk dihapus.');
        }

        $deleted = $this->auditTrailModel->deleteByIds($ids);
        if ($deleted <= 0) {
            catat_audit(
                'hapus_audit_trail_gagal',
                'audit_trail',
                null,
                'Bulk delete audit trail tidak menghapus data. requested_count=' . count($ids)
            );
            return redirect()->back()->with('error', 'Tidak ada log yang berhasil dihapus.');
        }

        catat_audit(
            'hapus_audit_trail',
            'audit_trail',
            null,
            'Bulk delete audit trail berhasil. deleted_count=' . $deleted
        );

        return redirect()->back()->with('success', 'Berhasil menghapus ' . $deleted . ' log audit trail.');
    }

    private function buildPaginationMeta(int $currentPage, int $totalPages, array $filter): array
    {
        if ($totalPages <= 1) {
            return [];
        }

        $baseQuery = array_filter($filter, static fn ($v) => $v !== null && $v !== '');
        unset($baseQuery['page_audit']);

        $buildUrl = static function (int $page) use ($baseQuery): string {
            $query = $baseQuery;
            $query['page_audit'] = $page;
            return base_url('/audit-trail?' . http_build_query($query));
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
        ];
    }
}
