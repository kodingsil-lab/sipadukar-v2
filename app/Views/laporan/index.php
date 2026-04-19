<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$request = service('request');
$currentQuery = $request->getGet();
$filterQueryString = http_build_query(array_filter($filter, static fn($v) => $v !== null && $v !== ''));
$activeSortBy = strtolower(trim((string) ($filter['sort_by'] ?? '')));
$activeSortDir = strtolower(trim((string) ($filter['sort_dir'] ?? '')));
if (! in_array($activeSortDir, ['asc', 'desc'], true)) {
    $activeSortDir = 'desc';
}

$buildLaporanSortHeader = static function (
    string $label,
    string $field,
    array $currentQuery,
    string $activeSortBy,
    string $activeSortDir,
    bool $center = false
): string {
    $nextDir = 'asc';
    if ($activeSortBy === $field && $activeSortDir === 'asc') {
        $nextDir = 'desc';
    }

    $query = $currentQuery;
    $query['sort_by'] = $field;
    $query['sort_dir'] = $nextDir;
    $query['page_laporan'] = 1;

    $url = base_url('/laporan?' . http_build_query($query));
    $isActive = $activeSortBy === $field;
    $iconClass = 'bi-arrow-down-up';
    if ($isActive) {
        $iconClass = $activeSortDir === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up';
    }

    return '<a class="th-sort-link js-laporan-sort-link' . ($center ? ' justify-content-center' : '') . ($isActive ? ' active' : '') . '" href="' . esc($url) . '">' .
        '<span>' . esc($label) . '</span>' .
        '<i class="bi ' . esc($iconClass) . '"></i>' .
    '</a>';
};
?>
<style>
.laporan-filter-actions {
    justify-content: flex-end;
    flex-wrap: nowrap;
}

.laporan-filter-actions .btn {
    white-space: nowrap;
}

@media (min-width: 992px) {
    .laporan-filter-row {
        flex-wrap: nowrap;
    }
}
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Laporan Dokumen</h3>
        <p class="page-subtitle">Laporan dokumen berbasis filter Program Studi, Kriteria, Sub Bagian, Status, dan Tahun.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <h5 class="mb-3">Rekap Per Status</h5>

                <div class="table-responsive">
                    <table class="table table-clean table-strong align-middle kriteria-doc-table js-kriteria-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th width="100">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($rekapStatus)): ?>
                                <?php foreach ($rekapStatus as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= badge_status_dokumen($row['status_dokumen']); ?>">
                                                <?= esc(label_status_dokumen($row['status_dokumen'])); ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold"><?= esc($row['total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Belum ada data.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card card-clean h-100">
            <div class="card-body">
                <h5 class="mb-3">Rekap Per Kriteria</h5>

                <div class="table-responsive">
                    <table class="table table-clean table-strong align-middle kriteria-doc-table js-kriteria-table">
                        <thead>
                            <tr>
                                <th width="80">Kode</th>
                                <th>Kriteria</th>
                                <th width="90">Total</th>
                                <th width="90">Valid</th>
                                <th width="90">Revisi</th>
                                <th width="90">Draft</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($rekapKriteria)): ?>
                                <?php foreach ($rekapKriteria as $row): ?>
                                    <tr>
                                        <td><span class="badge badge-soft-primary"><?= esc($row['kode']); ?></span></td>
                                        <td class="fw-semibold"><?= esc($row['nama_kriteria']); ?></td>
                                        <td><?= esc($row['total_dokumen'] ?? 0); ?></td>
                                        <td><span class="text-success fw-semibold"><?= esc($row['tervalidasi'] ?? 0); ?></span></td>
                                        <td><span class="text-danger fw-semibold"><?= esc($row['perlu_revisi'] ?? 0); ?></span></td>
                                        <td><span class="text-secondary fw-semibold"><?= esc($row['draft'] ?? 0); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data rekap.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div id="laporan-table-section" class="card card-clean">
    <div id="laporan-table-head" class="table-scroll-anchor"></div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Hasil Laporan Dokumen</h5>
                <p class="text-muted small mb-0">Menampilkan hasil filter dokumen secara detail.</p>
            </div>
            <div class="small text-muted">
                Total data: <strong><?= (int) ($laporanTotalData ?? count($laporanList)); ?></strong>
            </div>
        </div>

        <form method="get" action="<?= base_url('/laporan'); ?>" class="mb-4 js-laporan-filter-form">
            <?php if (! empty($filter['mode'])): ?>
                <input type="hidden" name="mode" value="<?= esc($filter['mode']); ?>">
            <?php endif; ?>
            <?php if (! empty($filter['sort_by'])): ?>
                <input type="hidden" name="sort_by" value="<?= esc((string) $filter['sort_by']); ?>">
            <?php endif; ?>
            <?php if (! empty($filter['sort_dir'])): ?>
                <input type="hidden" name="sort_dir" value="<?= esc((string) $filter['sort_dir']); ?>">
            <?php endif; ?>
            <div class="row g-2 align-items-end laporan-filter-row">
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label class="form-label">Program Studi</label>
                    <?php if (! empty($canSelectProgramStudi)): ?>
                        <select name="program_studi_id" class="form-select form-select-sm">
                            <option value="">Semua Program Studi</option>
                            <?php foreach (($programStudiList ?? []) as $prodi): ?>
                                <option value="<?= esc($prodi['id']); ?>" <?= ($filter['program_studi_id'] !== null && $filter['program_studi_id'] !== '' && (int) $filter['program_studi_id'] === (int) $prodi['id']) ? 'selected' : ''; ?>>
                                    <?= esc($prodi['nama_program_studi']); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <?php
                        $lockedProdi = $programStudiList[0] ?? null;
                        $lockedProdiId = (int) ($lockedProdi['id'] ?? ($filter['program_studi_id'] ?? 0));
                        $lockedProdiLabel = trim((string) ($lockedProdi['nama_program_studi'] ?? 'Program Studi'));
                        if (! empty($lockedProdi['jenjang'])) {
                            $lockedProdiLabel .= ' (' . $lockedProdi['jenjang'] . ')';
                        }
                        ?>
                        <input type="hidden" name="program_studi_id" value="<?= $lockedProdiId > 0 ? esc((string) $lockedProdiId) : ''; ?>">
                        <input type="text" class="form-control form-control-sm" value="<?= esc($lockedProdiLabel); ?>" readonly>
                    <?php endif; ?>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6">
                    <label class="form-label">Kriteria</label>
                    <select name="kriteria_id" class="form-select form-select-sm">
                        <option value="">Semua Kriteria</option>
                        <?php foreach ($kriteriaList as $k): ?>
                            <option value="<?= esc($k['id']); ?>" <?= ($filter['kriteria_id'] !== null && $filter['kriteria_id'] !== '' && (int) $filter['kriteria_id'] === (int) $k['id']) ? 'selected' : ''; ?>>
                                <?= esc($k['kode']); ?> - <?= esc($k['nama_kriteria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status_dokumen" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <?php
                        $opsiStatus = [
                            'draft'          => 'Draft',
                            'diajukan'       => 'Diajukan',
                            'ditinjau'       => 'Ditinjau',
                            'perlu_revisi'   => 'Perlu Revisi',
                            'disubmit_ulang' => 'Disubmit Ulang',
                            'tervalidasi'    => 'Tervalidasi',
                        ];
                        ?>
                        <?php foreach ($opsiStatus as $value => $label): ?>
                            <option value="<?= esc($value); ?>" <?= ($filter['status_dokumen'] === $value) ? 'selected' : ''; ?>>
                                <?= esc($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-1 col-md-6">
                    <label class="form-label">Tahun</label>
                    <input
                        type="number"
                        name="tahun_dokumen"
                        class="form-control form-control-sm"
                        value="<?= esc($filter['tahun_dokumen'] ?? ''); ?>"
                        min="2000"
                        max="2100"
                        placeholder="2026"
                    >
                </div>
                <div class="col-xl-5 col-lg-4 col-md-12">
                    <div class="d-flex gap-2 justify-content-xl-end flex-wrap laporan-filter-actions">
                        <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                        <a href="<?= base_url('/laporan'); ?>" class="btn btn-sm btn-light border js-laporan-reset">Reset</a>
                        <a href="<?= base_url('/laporan/export/excel?' . $filterQueryString); ?>" class="btn btn-sm btn-success">Export Excel</a>
                        <a href="<?= base_url('/laporan/export/pdf?' . $filterQueryString); ?>" target="_blank" class="btn btn-sm btn-light border">Export PDF</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle kriteria-doc-table js-kriteria-table">
                <thead>
                    <tr>
                        <th width="50" class="cell-center sortable-col"><?= $buildLaporanSortHeader('No', 'updated_at', $currentQuery, $activeSortBy, $activeSortDir, true); ?></th>
                        <th class="sortable-col"><?= $buildLaporanSortHeader('Judul Dokumen', 'judul', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        <th class="sortable-col"><?= $buildLaporanSortHeader('Kriteria', 'kriteria', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        <th class="sortable-col"><?= $buildLaporanSortHeader('Sub Bagian', 'sub_bagian', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        <th class="sortable-col"><?= $buildLaporanSortHeader('Program Studi / Unit', 'program_studi', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        <th class="sortable-col"><?= $buildLaporanSortHeader('Pengunggah', 'pengunggah', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        <th class="cell-center sortable-col"><?= $buildLaporanSortHeader('Tahun', 'tahun', $currentQuery, $activeSortBy, $activeSortDir, true); ?></th>
                        <th class="cell-center sortable-col"><?= $buildLaporanSortHeader('Status', 'status', $currentQuery, $activeSortBy, $activeSortDir, true); ?></th>
                        <th class="sortable-col"><?= $buildLaporanSortHeader('Versi', 'versi', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        <th width="170" class="cell-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($laporanList)): ?>
                        <?php foreach ($laporanList as $i => $row): ?>
                            <tr>
                                <td class="cell-center"><?= (int) ($laporanOffset ?? 0) + $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['judul_dokumen']); ?></div>
                                    <div class="table-subtext">
                                        <?= esc($row['kode_dokumen'] ?: '-'); ?> / <?= esc($row['nomor_dokumen'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td><?= esc($row['kode_kriteria'] ?? '-'); ?></td>
                                <td><?= esc($row['nama_sub_bagian'] ?? '-'); ?></td>
                                <td><?= esc($row['nama_program_studi'] ?: ($row['unit_kerja'] ?: '-')); ?></td>
                                <td><?= esc($row['nama_pengunggah'] ?? '-'); ?></td>
                                <td class="cell-center"><?= esc($row['tahun_dokumen'] ?: '-'); ?></td>
                                <td class="cell-center">
                                    <span class="badge bg-<?= badge_status_dokumen($row['status_dokumen']); ?>">
                                        <?= esc(label_status_dokumen($row['status_dokumen'])); ?>
                                    </span>
                                </td>
                                <td class="cell-center">v<?= esc($row['versi']); ?></td>
                                <td class="cell-center">
                                    <div class="action-group">
                                        <?php
                                        $safeExternalLink = sanitize_external_dokumen_link((string) ($row['link_dokumen'] ?? ''));
                                        $isLinkDokumen = ($row['sumber_dokumen'] ?? 'file') === 'link' && $safeExternalLink !== '';
                                        $detailHref = base_url('/dokumen/' . $row['id']);
                                        ?>
                                        <a href="<?= esc($detailHref); ?>" class="btn btn-xs btn-primary icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Dokumen" aria-label="Detail Dokumen">
                                            <i class="bi bi-eye-fill"></i>
                                            <span class="visually-hidden">Detail</span>
                                        </a>
                                        <?php if (! empty($row['path_file']) || $isLinkDokumen): ?>
                                            <?php
                                            $referensiHijauHref = $isLinkDokumen
                                                ? $safeExternalLink
                                                : base_url('/file/dokumen/' . $row['id'] . '/download');
                                            $referensiHijauTitle = $isLinkDokumen ? 'Buka Link Dokumen' : 'Download Dokumen';
                                            $referensiHijauIcon = $isLinkDokumen ? 'bi-box-arrow-up-right' : 'bi-download';
                                            $referensiHijauText = $isLinkDokumen ? 'Buka Link Dokumen' : 'Download Dokumen';
                                            ?>
                                            <a href="<?= esc($referensiHijauHref); ?>" class="btn btn-xs btn-success icon-btn" <?= $isLinkDokumen ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($referensiHijauTitle); ?>" aria-label="<?= esc($referensiHijauTitle); ?>">
                                                <i class="bi <?= esc($referensiHijauIcon); ?>"></i>
                                                <span class="visually-hidden"><?= esc($referensiHijauText); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">Tidak ada data yang sesuai filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($laporanPagination ?? [])): ?>
            <div class="d-flex justify-content-end mt-3">
                <nav aria-label="Pagination laporan dokumen">
                    <ul class="pagination pagination-sm mb-0 js-laporan-pagination">
                        <li class="page-item <?= empty($laporanPagination['prev_url']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= esc($laporanPagination['prev_url'] ?? '#'); ?>">Sebelumnya</a>
                        </li>
                        <?php foreach (($laporanPagination['pages'] ?? []) as $page): ?>
                            <li class="page-item <?= ! empty($page['active']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= esc($page['url'] ?? '#'); ?>"><?= esc((string) ($page['label'] ?? '')); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <li class="page-item <?= empty($laporanPagination['next_url']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= esc($laporanPagination['next_url'] ?? '#'); ?>">Berikutnya</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
(() => {
    const sectionSelector = '#laporan-table-section';
    const headSelector = '#laporan-table-head';
    const paginationLinkSelector = '.js-laporan-pagination a.page-link';
    const sortLinkSelector = '.js-laporan-sort-link';
    const filterFormSelector = '.js-laporan-filter-form';
    const resetLinkSelector = '.js-laporan-reset';
    let isLoading = false;

    const initTooltips = (root = document) => {
        if (!window.bootstrap || !window.bootstrap.Tooltip) {
            return;
        }

        root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            if (!window.bootstrap.Tooltip.getInstance(el)) {
                new window.bootstrap.Tooltip(el);
            }
        });
    };

    const scrollToTableHead = () => {
        const head = document.querySelector(headSelector);
        if (!head) {
            return;
        }

        head.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const replaceTableSection = (htmlText, nextUrl, pushHistory = true) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');
        const incomingSection = doc.querySelector(sectionSelector);
        const currentSection = document.querySelector(sectionSelector);

        if (!incomingSection || !currentSection) {
            return false;
        }

        currentSection.outerHTML = incomingSection.outerHTML;
        initTooltips(document);
        scrollToTableHead();

        if (pushHistory) {
            window.history.pushState({ laporanAjax: true }, '', nextUrl);
        }

        return true;
    };

    const loadPaginationPage = async (url, pushHistory = true) => {
        if (isLoading) {
            return;
        }

        isLoading = true;
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                window.location.href = url;
                return;
            }

            const htmlText = await response.text();
            const swapped = replaceTableSection(htmlText, url, pushHistory);
            if (!swapped) {
                window.location.href = url;
            }
        } catch (error) {
            window.location.href = url;
        } finally {
            isLoading = false;
        }
    };

    const buildFilterUrlFromForm = (form) => {
        const action = form.getAttribute('action') || window.location.pathname;
        const params = new URLSearchParams();
        const formData = new FormData(form);

        formData.forEach((value, key) => {
            const normalized = String(value ?? '').trim();
            if (normalized !== '') {
                params.append(key, normalized);
            }
        });

        const query = params.toString();
        return query === '' ? action : `${action}?${query}`;
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest(paginationLinkSelector);
        if (!link) {
            return;
        }

        const pageItem = link.closest('.page-item');
        if (!pageItem || pageItem.classList.contains('disabled') || pageItem.classList.contains('active')) {
            event.preventDefault();
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href === '#') {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        loadPaginationPage(href, true);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest(sortLinkSelector);
        if (!link) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href === '#') {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        loadPaginationPage(href, true);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest(filterFormSelector);
        if (!form) {
            return;
        }

        event.preventDefault();
        const targetUrl = buildFilterUrlFromForm(form);
        loadPaginationPage(targetUrl, true);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest(resetLinkSelector);
        if (!link) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href === '#') {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        loadPaginationPage(href, true);
    });

    window.addEventListener('popstate', () => {
        if (!document.querySelector(sectionSelector)) {
            return;
        }

        loadPaginationPage(window.location.href, false);
    });
})();
</script>

<?= $this->endSection(); ?>
