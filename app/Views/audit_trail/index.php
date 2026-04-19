<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$request = service('request');
$currentQuery = $request->getGet();
$activeSortBy = strtolower(trim((string) ($filter['sort_by'] ?? 'created_at')));
$activeSortDir = strtolower(trim((string) ($filter['sort_dir'] ?? 'desc')));
if (! in_array($activeSortDir, ['asc', 'desc'], true)) {
    $activeSortDir = 'desc';
}

$buildSortHeader = static function (
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
    $query['page_audit'] = 1;

    $url = base_url('/audit-trail?' . http_build_query($query));
    $isActive = $activeSortBy === $field;
    $iconClass = 'bi-arrow-down-up';
    if ($isActive) {
        $iconClass = $activeSortDir === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up';
    }

    return '<a class="th-sort-link js-audit-sort-link' . ($center ? ' justify-content-center' : '') . ($isActive ? ' active' : '') . '" href="' . esc($url) . '">' .
        '<span>' . esc($label) . '</span>' .
        '<i class="bi ' . esc($iconClass) . '"></i>' .
    '</a>';
};
?>

<style>
.audit-filter-actions {
    justify-content: flex-end;
    flex-wrap: nowrap;
}

.audit-filter-actions .btn {
    white-space: nowrap;
}

@media (min-width: 992px) {
    .audit-filter-row {
        flex-wrap: nowrap;
    }
}
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Audit Trail</h3>
        <p class="page-subtitle">Riwayat aktivitas pengguna pada sistem.</p>
    </div>
</div>

<div id="audit-table-section" class="card card-clean">
    <div id="audit-table-head" class="table-scroll-anchor"></div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Log Aktivitas Pengguna</h5>
                <p class="text-muted small mb-0">Filter, urutkan, dan kelola log aktivitas sistem.</p>
            </div>
            <div class="small text-muted">
                Total data: <strong><?= (int) ($auditTotalData ?? count($auditList)); ?></strong>
            </div>
        </div>

        <form method="get" action="<?= base_url('/audit-trail'); ?>" class="mb-4 js-audit-filter-form">
            <?php if (! empty($filter['sort_by'])): ?>
                <input type="hidden" name="sort_by" value="<?= esc((string) $filter['sort_by']); ?>">
            <?php endif; ?>
            <?php if (! empty($filter['sort_dir'])): ?>
                <input type="hidden" name="sort_dir" value="<?= esc((string) $filter['sort_dir']); ?>">
            <?php endif; ?>

            <div class="row g-2 align-items-end audit-filter-row">
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <label class="form-label">Aktivitas</label>
                    <select name="aktivitas" class="form-select form-select-sm">
                        <option value="">Semua Aktivitas</option>
                        <?php foreach (($aktivitasList ?? []) as $aktivitas): ?>
                            <option value="<?= esc($aktivitas); ?>" <?= (($filter['aktivitas'] ?? '') === $aktivitas) ? 'selected' : ''; ?>>
                                <?= esc($aktivitas); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label class="form-label">Modul</label>
                    <select name="modul" class="form-select form-select-sm">
                        <option value="">Semua Modul</option>
                        <?php foreach (($modulList ?? []) as $modul): ?>
                            <option value="<?= esc($modul); ?>" <?= (($filter['modul'] ?? '') === $modul) ? 'selected' : ''; ?>>
                                <?= esc($modul); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-12">
                    <label class="form-label">Kata Kunci</label>
                    <input
                        type="text"
                        name="keyword"
                        class="form-control form-control-sm"
                        value="<?= esc($filter['keyword'] ?? ''); ?>"
                        placeholder="Nama user, username, aktivitas, deskripsi, IP"
                    >
                </div>
                <div class="col-xl-4 col-lg-12 col-md-12">
                    <div class="d-flex gap-2 audit-filter-actions flex-wrap">
                        <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                        <a href="<?= base_url('/audit-trail'); ?>" class="btn btn-sm btn-light border js-audit-reset">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <form method="post" action="<?= base_url('/audit-trail/bulk-delete'); ?>" class="js-audit-bulk-form js-confirm-submit" data-confirm="Yakin hapus log terpilih?">
            <?= csrf_field(); ?>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <button type="submit" class="btn btn-sm btn-danger js-bulk-delete-btn" disabled>
                    <i class="bi bi-trash-fill me-1"></i>
                    Hapus Log Terpilih
                </button>
                <div class="small text-muted js-selected-count">0 log dipilih</div>
            </div>

            <div class="table-responsive">
                <table class="table table-clean table-strong align-middle kriteria-doc-table js-kriteria-table">
                    <thead>
                        <tr>
                            <th width="44" class="cell-center">
                                <input type="checkbox" class="form-check-input js-audit-check-all" aria-label="Pilih semua log pada halaman ini">
                            </th>
                            <th width="60" class="cell-center sortable-col"><?= $buildSortHeader('No', 'created_at', $currentQuery, $activeSortBy, $activeSortDir, true); ?></th>
                            <th class="sortable-col"><?= $buildSortHeader('Waktu', 'created_at', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                            <th class="sortable-col"><?= $buildSortHeader('User', 'nama_user', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                            <th>Role / Unit</th>
                            <th class="sortable-col"><?= $buildSortHeader('Aktivitas', 'aktivitas', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                            <th class="sortable-col"><?= $buildSortHeader('Modul', 'modul', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                            <th>Deskripsi</th>
                            <th class="sortable-col"><?= $buildSortHeader('IP', 'ip_address', $currentQuery, $activeSortBy, $activeSortDir); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($auditList)): ?>
                            <?php foreach ($auditList as $i => $row): ?>
                                <tr>
                                    <td class="cell-center">
                                        <input type="checkbox" class="form-check-input js-audit-row-check" name="selected_ids[]" value="<?= (int) ($row['id'] ?? 0); ?>" aria-label="Pilih log #<?= (int) ($row['id'] ?? 0); ?>">
                                    </td>
                                    <td class="cell-center"><?= (int) ($auditOffset ?? 0) + $i + 1; ?></td>
                                    <td><?= esc($row['created_at'] ?? '-'); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($row['nama_user'] ?: '-'); ?></div>
                                        <div class="table-subtext"><?= esc($row['username'] ?: '-'); ?></div>
                                    </td>
                                    <td>
                                        <div><?= esc($row['role_user'] ?: '-'); ?></div>
                                        <div class="table-subtext"><?= esc($row['unit_kerja'] ?: '-'); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= esc($row['aktivitas'] ?: '-'); ?></span>
                                    </td>
                                    <td><?= esc($row['modul'] ?: '-'); ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 360px;" title="<?= esc($row['deskripsi'] ?: '-'); ?>">
                                            <?= esc($row['deskripsi'] ?: '-'); ?>
                                        </div>
                                    </td>
                                    <td><?= esc($row['ip_address'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada data audit trail.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <?php if (! empty($auditPagination ?? [])): ?>
            <div class="d-flex justify-content-end mt-3">
                <nav aria-label="Pagination audit trail">
                    <ul class="pagination pagination-sm mb-0 js-audit-pagination">
                        <li class="page-item <?= empty($auditPagination['prev_url']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= esc($auditPagination['prev_url'] ?? '#'); ?>">Sebelumnya</a>
                        </li>
                        <?php foreach (($auditPagination['pages'] ?? []) as $page): ?>
                            <li class="page-item <?= ! empty($page['active']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= esc($page['url'] ?? '#'); ?>"><?= esc((string) ($page['label'] ?? '')); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <li class="page-item <?= empty($auditPagination['next_url']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= esc($auditPagination['next_url'] ?? '#'); ?>">Berikutnya</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(() => {
    const sectionSelector = '#audit-table-section';
    const headSelector = '#audit-table-head';
    const paginationLinkSelector = '.js-audit-pagination a.page-link';
    const sortLinkSelector = '.js-audit-sort-link';
    const filterFormSelector = '.js-audit-filter-form';
    const resetLinkSelector = '.js-audit-reset';
    const checkAllSelector = '.js-audit-check-all';
    const rowCheckSelector = '.js-audit-row-check';
    const deleteBtnSelector = '.js-bulk-delete-btn';
    const selectedCountSelector = '.js-selected-count';
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

    const updateBulkState = (root = document) => {
        const checkboxes = Array.from(root.querySelectorAll(rowCheckSelector));
        const checked = checkboxes.filter((el) => el.checked);
        const btn = root.querySelector(deleteBtnSelector);
        const countEl = root.querySelector(selectedCountSelector);
        const checkAll = root.querySelector(checkAllSelector);

        if (btn) {
            btn.disabled = checked.length === 0;
        }
        if (countEl) {
            countEl.textContent = `${checked.length} log dipilih`;
        }
        if (checkAll) {
            checkAll.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
            checkAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
        }
    };

    const scrollToTableHead = () => {
        const head = document.querySelector(headSelector);
        if (!head) {
            return;
        }
        head.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const replaceSection = (htmlText, nextUrl, pushHistory = true) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');
        const incoming = doc.querySelector(sectionSelector);
        const current = document.querySelector(sectionSelector);
        if (!incoming || !current) {
            return false;
        }

        current.outerHTML = incoming.outerHTML;
        initTooltips(document);
        updateBulkState(document);
        scrollToTableHead();

        if (pushHistory) {
            window.history.pushState({ auditAjax: true }, '', nextUrl);
        }
        return true;
    };

    const loadPage = async (url, pushHistory = true) => {
        if (isLoading) {
            return;
        }
        isLoading = true;
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                window.location.href = url;
                return;
            }
            const htmlText = await response.text();
            const swapped = replaceSection(htmlText, url, pushHistory);
            if (!swapped) {
                window.location.href = url;
            }
        } catch (e) {
            window.location.href = url;
        } finally {
            isLoading = false;
        }
    };

    const buildUrlFromFilterForm = (form) => {
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
        const checkAll = event.target.closest(checkAllSelector);
        if (!checkAll) {
            return;
        }
        document.querySelectorAll(rowCheckSelector).forEach((el) => {
            el.checked = checkAll.checked;
        });
        updateBulkState(document);
    });

    document.addEventListener('change', (event) => {
        if (!event.target.closest(rowCheckSelector)) {
            return;
        }
        updateBulkState(document);
    });

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
        loadPage(href, true);
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
        loadPage(href, true);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest(filterFormSelector);
        if (!form) {
            return;
        }

        event.preventDefault();
        loadPage(buildUrlFromFilterForm(form), true);
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
        loadPage(href, true);
    });

    window.addEventListener('popstate', () => {
        if (!document.querySelector(sectionSelector)) {
            return;
        }
        loadPage(window.location.href, false);
    });

    initTooltips(document);
    updateBulkState(document);
})();
</script>

<?= $this->endSection(); ?>
