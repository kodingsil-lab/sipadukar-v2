<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$request = service('request');
$currentQuery = $request->getGet();
$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$allProgramStudiLabel = (string) ($selectedProgramStudiLabelAll ?? (has_role('dekan') ? 'Semua Prodi di Bawah Naungan' : 'Semua Prodi Aktif'));
$programStudiQuerySuffix = $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
$canShowKriteriaActions = has_role(['admin', 'lpm', 'kaprodi', 'dosen']);
$canBulkDeleteDokumen = has_role(['admin', 'lpm', 'kaprodi', 'dosen']);

$buildSortHeader = static function (
    string $label,
    string $field,
    array $sortUrls,
    string $activeSortBy,
    string $activeSortDir,
    bool $center = false
): string {
    $url = $sortUrls[$field] ?? '#';
    $isActive = $activeSortBy === $field;
    $iconClass = 'bi-arrow-down-up';
    if ($isActive) {
        $iconClass = $activeSortDir === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up';
    }

    return '<a class="th-sort-link' . ($center ? ' justify-content-center' : '') . ($isActive ? ' active' : '') . '" href="' . esc($url) . '">' .
        '<span>' . esc($label) . '</span>' .
        '<i class="bi ' . esc($iconClass) . '"></i>' .
    '</a>';
};
?>

<style>
    .doc-table-toolbar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.5rem;
    }

    .doc-bulk-inline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .doc-bulk-icon-btn {
        min-width: 2rem;
    }

    .subbagian-controls-inline {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.4rem;
        width: 100%;
    }

    .subbagian-controls-inline .action-group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: nowrap;
        margin: 0;
    }

    /* Local tidy-up for icon action buttons on this page */
    .kriteria-table-block .action-group {
        gap: 0.35rem;
    }

    .kriteria-table-block .action-group > form {
        margin: 0;
    }

    .kriteria-table-block .icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 9px !important;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.05);
    }

    .kriteria-table-block .icon-btn i {
        font-size: 12px;
    }

    .kriteria-table-block .doc-bulk-inline {
        gap: 0.45rem;
    }

    @media (max-width: 991.98px) {
        .subbagian-controls-inline {
            justify-content: flex-start;
            align-items: stretch;
        }

        .subbagian-controls-inline .action-group {
            flex-wrap: wrap;
            justify-content: flex-start;
        }

    }
</style>

<?php if (! empty($showProgramStudiFilter ?? false)): ?>
    <div class="card card-clean mb-3">
        <div class="card-body py-3">
            <form method="get" action="<?= current_url(); ?>" class="kriteria-prodi-filter-form">
                <?php foreach ($currentQuery as $queryKey => $queryValue): ?>
                    <?php if ($queryKey === 'program_studi_id'): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php if (is_array($queryValue)): ?>
                        <?php foreach ($queryValue as $nestedValue): ?>
                            <input type="hidden" name="<?= esc($queryKey); ?>[]" value="<?= esc((string) $nestedValue); ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="hidden" name="<?= esc($queryKey); ?>" value="<?= esc((string) $queryValue); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="kriteria-prodi-filter-head">
                    <label for="program-studi-filter" class="form-label fw-semibold mb-0">Filter Program Studi</label>
                </div>
                <div class="kriteria-prodi-filter-controls">
                    <select id="program-studi-filter" name="program_studi_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value=""><?= esc($allProgramStudiLabel); ?></option>
                        <?php foreach (($programStudiAktifList ?? []) as $prodi): ?>
                            <option value="<?= esc($prodi['id']); ?>" <?= $selectedProgramStudiId === (int) ($prodi['id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= esc($prodi['nama_program_studi'] ?? '-'); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?= esc(current_url()); ?>" class="btn btn-light border btn-sm">Reset</a>
                    <div class="kriteria-prodi-filter-status <?= $selectedProgramStudiId > 0 ? 'is-specific' : 'is-all'; ?>" role="status" aria-live="polite">
                        <i class="bi bi-funnel-fill"></i>
                        <span>Mode pantau: <strong><?= esc($selectedProgramStudiLabel ?? $allProgramStudiLabel); ?></strong></span>
                    </div>
                </div>
                <div class="form-text mt-1">Pilih prodi untuk memantau, menambah, dan mengelola dokumen berdasarkan Program Studi.</div>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
        <h3 class="page-title"><?= esc($kriteria['kode']); ?> - <?= esc($kriteria['nama_kriteria']); ?></h3>
        <p class="page-subtitle"><?= esc($kriteria['deskripsi'] ?? '-'); ?></p>
    </div>
    <?php if ($canShowKriteriaActions): ?>
        <div class="action-group">
            <a href="<?= base_url('/kriteria/' . (int) $kriteria['id'] . '/dokumen/create') . $programStudiQuerySuffix; ?>" class="btn btn-primary">
                Tambah Dokumen
            </a>

            <?php if (has_role(['admin', 'lpm', 'kaprodi'])): ?>
                <a href="<?= base_url('/kriteria/' . $kriteria['id'] . '/sub-bagian/create') . $programStudiQuerySuffix; ?>" class="btn btn-primary">
                    Tambah Sub Bagian
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (! empty($dokumenDirectList ?? []) || empty($subBagianList)): ?>
    <div id="dokumen-utama" class="card card-clean mb-4">
        <div class="card-body">
            <div class="kriteria-table-block">
            <?php $bulkDirectFormId = 'bulk-delete-form-direct'; ?>
            <div class="doc-table-toolbar">
                <?php if ($canBulkDeleteDokumen): ?>
                    <form id="<?= esc($bulkDirectFormId); ?>" method="post" action="<?= base_url('/dokumen/bulk-delete'); ?>" class="doc-bulk-inline js-doc-bulk-form js-confirm-submit" data-confirm="Yakin hapus dokumen terpilih?">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="kriteria_id" value="<?= (int) ($kriteria['id'] ?? 0); ?>">
                        <input type="hidden" name="sub_bagian_id" value="0">
                        <input type="hidden" name="program_studi_id" value="<?= (int) ($selectedProgramStudiId ?? 0); ?>">
                        <button type="submit" class="btn btn-xs btn-danger icon-btn doc-bulk-icon-btn js-doc-bulk-delete-btn" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Dokumen Terpilih" aria-label="Hapus Dokumen Terpilih">
                            <i class="bi bi-list-check"></i>
                            <span class="visually-hidden">Hapus Dokumen Terpilih</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div id="dokumen-utama-table-head" class="table-scroll-anchor"></div>
            <div class="table-responsive">
                <table class="table table-clean table-strong align-middle kriteria-doc-table js-kriteria-table">
                    <thead>
                        <tr>
                            <?php if ($canBulkDeleteDokumen): ?>
                                <th width="44" class="cell-center">
                                    <input
                                        type="checkbox"
                                        class="form-check-input js-doc-check-all"
                                        data-target-form="<?= esc($bulkDirectFormId); ?>"
                                        aria-label="Pilih semua dokumen pada dokumen utama"
                                    >
                                </th>
                            <?php endif; ?>
                            <th width="60" class="cell-center sortable-col"><?= $buildSortHeader('No', 'no', $dokumenDirectSortUrls ?? [], $dokumenDirectSortBy ?? 'no', $dokumenDirectSortDir ?? 'asc', true); ?></th>
                            <th width="24%" class="sortable-col"><?= $buildSortHeader('Nama Dokumen', 'nama', $dokumenDirectSortUrls ?? [], $dokumenDirectSortBy ?? 'no', $dokumenDirectSortDir ?? 'asc'); ?></th>
                            <th width="24%" class="sortable-col"><?= $buildSortHeader('Deskripsi', 'deskripsi', $dokumenDirectSortUrls ?? [], $dokumenDirectSortBy ?? 'no', $dokumenDirectSortDir ?? 'asc'); ?></th>
                            <th width="12%" class="sortable-col"><?= $buildSortHeader('Penanggung Jawab', 'penanggung', $dokumenDirectSortUrls ?? [], $dokumenDirectSortBy ?? 'no', $dokumenDirectSortDir ?? 'asc'); ?></th>
                            <th width="190" class="cell-center sortable-col"><?= $buildSortHeader('Tanggal & Waktu', 'waktu', $dokumenDirectSortUrls ?? [], $dokumenDirectSortBy ?? 'no', $dokumenDirectSortDir ?? 'asc', true); ?></th>
                            <th width="90" class="cell-center sortable-col"><?= $buildSortHeader('Status', 'status', $dokumenDirectSortUrls ?? [], $dokumenDirectSortBy ?? 'no', $dokumenDirectSortDir ?? 'asc', true); ?></th>
                            <th width="220" class="cell-center col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($dokumenDirectList ?? [])): ?>
                            <?php foreach (($dokumenDirectList ?? []) as $j => $dokumen): ?>
                                <tr>
                                    <?php if ($canBulkDeleteDokumen): ?>
                                        <td class="cell-center">
                                            <input
                                                type="checkbox"
                                                class="form-check-input js-doc-row-check"
                                                data-target-form="<?= esc($bulkDirectFormId); ?>"
                                                name="selected_ids[]"
                                                value="<?= (int) ($dokumen['id'] ?? 0); ?>"
                                                form="<?= esc($bulkDirectFormId); ?>"
                                                aria-label="Pilih dokumen #<?= (int) ($dokumen['id'] ?? 0); ?>"
                                            >
                                        </td>
                                    <?php endif; ?>
                                    <td class="cell-center"><?= (int) ($dokumenDirectOffset ?? 0) + $j + 1; ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($dokumen['judul_dokumen']); ?></div>
                                        <div class="table-subtext">
                                            <?= esc($dokumen['kode_dokumen'] ?: '-'); ?> / v<?= esc($dokumen['versi'] ?? 1); ?>
                                        </div>
                                    </td>
                                    <td><?= esc($dokumen['deskripsi'] ?: '-'); ?></td>
                                    <td><?= esc($dokumen['nama_pengunggah'] ?: '-'); ?></td>
                                    <td class="cell-center text-nowrap"><?= esc($dokumen['waktu_tampil'] ?? '-'); ?></td>
                                    <td class="cell-center">
                                        <span class="badge bg-<?= badge_status_dokumen($dokumen['status_dokumen']); ?>">
                                            <?= esc(label_status_dokumen($dokumen['status_dokumen'])); ?>
                                        </span>
                                    </td>
                                    <td class="cell-center">
                                        <div class="action-group">
                                            <?php
                                            $safeExternalLink = sanitize_external_dokumen_link((string) ($dokumen['link_dokumen'] ?? ''));
                                            $isLinkDokumen = ($dokumen['sumber_dokumen'] ?? 'file') === 'link' && $safeExternalLink !== '';
                                            $detailHref = base_url('/dokumen/' . $dokumen['id']) . $programStudiQuerySuffix;
                                            ?>
                                            <a href="<?= esc($detailHref); ?>" class="btn btn-xs btn-primary icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Dokumen" aria-label="Detail Dokumen">
                                                <i class="bi bi-eye-fill"></i>
                                                <span class="visually-hidden">Detail</span>
                                            </a>
                                            <?php if (! empty($dokumen['path_file']) || $isLinkDokumen): ?>
                                                <?php
                                                $referensiHijauHref = $isLinkDokumen
                                                    ? $safeExternalLink
                                                    : base_url('/file/dokumen/' . $dokumen['id'] . '/download');
                                                $referensiHijauTitle = $isLinkDokumen ? 'Buka Link Dokumen' : 'Download Dokumen';
                                                $referensiHijauIcon = $isLinkDokumen ? 'bi-box-arrow-up-right' : 'bi-download';
                                                $referensiHijauSrText = $isLinkDokumen ? 'Buka Link Dokumen' : 'Download';
                                                ?>
                                                <a href="<?= esc($referensiHijauHref); ?>" class="btn btn-xs btn-success icon-btn" <?= $isLinkDokumen ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($referensiHijauTitle); ?>" aria-label="<?= esc($referensiHijauTitle); ?>">
                                                    <i class="bi <?= esc($referensiHijauIcon); ?>"></i>
                                                    <span class="visually-hidden"><?= esc($referensiHijauSrText); ?></span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can_manage_dokumen($dokumen)): ?>
                                                <a href="<?= base_url('/dokumen/' . $dokumen['id'] . '/edit') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Dokumen" aria-label="Edit Dokumen">
                                                    <i class="bi bi-pencil-fill"></i>
                                                    <span class="visually-hidden">Edit</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can_review_dokumen($dokumen)): ?>
                                                <a href="<?= base_url('/dokumen/' . $dokumen['id'] . '/review') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-review icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Review Dokumen" aria-label="Review Dokumen">
                                                    <i class="bi bi-clipboard2-check-fill"></i>
                                                    <span class="visually-hidden">Review</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can_manage_dokumen($dokumen)): ?>
                                                <form action="<?= base_url('/dokumen/' . $dokumen['id'] . '/delete') . $programStudiQuerySuffix; ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus dokumen ini?">
                                                    <?= csrf_field(); ?>
                                                    <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Dokumen" aria-label="Hapus Dokumen">
                                                        <i class="bi bi-trash-fill"></i>
                                                        <span class="visually-hidden">Hapus</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $canBulkDeleteDokumen ? '8' : '7'; ?>" class="text-center text-muted">
                                    Belum ada dokumen untuk kriteria ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (! empty($dokumenDirectPagination ?? [])): ?>
                <div class="d-flex justify-content-end mt-3">
                    <nav aria-label="Pagination dokumen utama">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= empty($dokumenDirectPagination['prev_url']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= esc($dokumenDirectPagination['prev_url'] ?? '#'); ?>">Sebelumnya</a>
                            </li>
                            <?php foreach ($dokumenDirectPagination['pages'] as $page): ?>
                                <li class="page-item <?= $page['active'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= esc($page['url']); ?>"><?= esc($page['label']); ?></a>
                                </li>
                            <?php endforeach; ?>
                            <li class="page-item <?= empty($dokumenDirectPagination['next_url']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= esc($dokumenDirectPagination['next_url'] ?? '#'); ?>">Berikutnya</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($subBagianList as $i => $subBagian): ?>
    <div id="subbagian-<?= (int) $subBagian['id']; ?>" class="card card-clean mb-4">
        <div class="card-body">
            <div class="subbagian-head mb-2">
                <div class="subbagian-title-wrap">
                    <h5 class="mb-1"><?= ($i + 1); ?>. <?= esc($subBagian['nama_sub_bagian']); ?></h5>
                    <div class="small text-muted">
                        <?= esc($subBagian['deskripsi'] ?: 'Belum ada deskripsi sub bagian.'); ?>
                    </div>
                </div>

                <div class="subbagian-controls-inline">
                    <div class="action-group">
                        <?php $bulkFormId = 'bulk-delete-form-sb-' . (int) $subBagian['id']; ?>
                        <?php if (has_role(['admin', 'lpm', 'kaprodi', 'dosen'])): ?>
                            <a href="<?= base_url('/sub-bagian/' . $subBagian['id'] . '/dokumen/create') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-primary icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah Dokumen" aria-label="Tambah Dokumen">
                                <i class="bi bi-plus-lg"></i>
                                <span class="visually-hidden">Tambah Dokumen</span>
                            </a>
                        <?php endif; ?>
                        <?php if (has_role(['admin', 'lpm', 'kaprodi'])): ?>
                            <a href="<?= base_url('/sub-bagian/' . $subBagian['id'] . '/edit') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Sub Bagian" aria-label="Edit Sub Bagian">
                                <i class="bi bi-pencil-square"></i>
                                <span class="visually-hidden">Edit Sub Bagian</span>
                            </a>
                            <?php if (($subBagian['jumlah_dokumen'] ?? 0) > 0): ?>
                                <button type="button" class="btn btn-xs btn-secondary icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Tidak bisa dihapus karena masih ada <?= (int) ($subBagian['jumlah_dokumen'] ?? 0); ?> dokumen." aria-label="Tidak bisa hapus sub bagian">
                                    <i class="bi bi-trash-fill"></i>
                                    <span class="visually-hidden">Tidak bisa hapus sub bagian</span>
                                </button>
                            <?php else: ?>
                                <form action="<?= base_url('/sub-bagian/' . $subBagian['id'] . '/delete') . $programStudiQuerySuffix; ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus sub bagian ini?">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Sub Bagian" aria-label="Hapus Sub Bagian">
                                        <i class="bi bi-trash-fill"></i>
                                        <span class="visually-hidden">Hapus Sub Bagian</span>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <div class="doc-bulk-inline">
                                <button
                                    type="submit"
                                    form="<?= esc($bulkFormId); ?>"
                                    class="btn btn-xs btn-danger icon-btn doc-bulk-icon-btn js-doc-bulk-delete-btn"
                                    disabled
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Hapus Dokumen Terpilih"
                                    aria-label="Hapus Dokumen Terpilih"
                                >
                                    <i class="bi bi-list-check"></i>
                                    <span class="visually-hidden">Hapus Dokumen Terpilih</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <div class="table-responsive">
                <div class="kriteria-table-block">
                <div id="subbagian-table-head-<?= (int) $subBagian['id']; ?>" class="table-scroll-anchor"></div>
                <?php if ($canBulkDeleteDokumen): ?>
                    <form id="<?= esc($bulkFormId); ?>" method="post" action="<?= base_url('/dokumen/bulk-delete'); ?>" class="d-none js-doc-bulk-form js-confirm-submit" data-confirm="Yakin hapus dokumen terpilih?">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="kriteria_id" value="<?= (int) ($kriteria['id'] ?? 0); ?>">
                        <input type="hidden" name="sub_bagian_id" value="<?= (int) ($subBagian['id'] ?? 0); ?>">
                        <input type="hidden" name="program_studi_id" value="<?= (int) ($selectedProgramStudiId ?? 0); ?>">
                    </form>
                <?php endif; ?>
                <table class="table table-clean table-strong align-middle kriteria-doc-table js-kriteria-table">
                    <thead>
                        <tr>
                            <?php if ($canBulkDeleteDokumen): ?>
                                <th width="44" class="cell-center">
                                    <input
                                        type="checkbox"
                                        class="form-check-input js-doc-check-all"
                                        data-target-form="<?= esc($bulkFormId); ?>"
                                        aria-label="Pilih semua dokumen pada sub bagian ini"
                                    >
                                </th>
                            <?php endif; ?>
                            <th width="60" class="cell-center sortable-col"><?= $buildSortHeader('No', 'no', $subBagian['dokumen_sort_urls'] ?? [], $subBagian['dokumen_sort_by'] ?? 'no', $subBagian['dokumen_sort_dir'] ?? 'asc', true); ?></th>
                            <th width="24%" class="sortable-col"><?= $buildSortHeader('Nama Dokumen', 'nama', $subBagian['dokumen_sort_urls'] ?? [], $subBagian['dokumen_sort_by'] ?? 'no', $subBagian['dokumen_sort_dir'] ?? 'asc'); ?></th>
                            <th width="24%" class="sortable-col"><?= $buildSortHeader('Deskripsi', 'deskripsi', $subBagian['dokumen_sort_urls'] ?? [], $subBagian['dokumen_sort_by'] ?? 'no', $subBagian['dokumen_sort_dir'] ?? 'asc'); ?></th>
                            <th width="12%" class="sortable-col"><?= $buildSortHeader('Penanggung Jawab', 'penanggung', $subBagian['dokumen_sort_urls'] ?? [], $subBagian['dokumen_sort_by'] ?? 'no', $subBagian['dokumen_sort_dir'] ?? 'asc'); ?></th>
                            <th width="190" class="cell-center sortable-col"><?= $buildSortHeader('Tanggal & Waktu', 'waktu', $subBagian['dokumen_sort_urls'] ?? [], $subBagian['dokumen_sort_by'] ?? 'no', $subBagian['dokumen_sort_dir'] ?? 'asc', true); ?></th>
                            <th width="90" class="cell-center sortable-col"><?= $buildSortHeader('Status', 'status', $subBagian['dokumen_sort_urls'] ?? [], $subBagian['dokumen_sort_by'] ?? 'no', $subBagian['dokumen_sort_dir'] ?? 'asc', true); ?></th>
                            <th width="220" class="cell-center col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($subBagian['dokumen_list'])): ?>
                            <?php foreach ($subBagian['dokumen_list'] as $j => $dokumen): ?>
                                <tr>
                                    <?php if ($canBulkDeleteDokumen): ?>
                                        <td class="cell-center">
                                            <input
                                                type="checkbox"
                                                class="form-check-input js-doc-row-check"
                                                data-target-form="<?= esc($bulkFormId); ?>"
                                                name="selected_ids[]"
                                                value="<?= (int) ($dokumen['id'] ?? 0); ?>"
                                                form="<?= esc($bulkFormId); ?>"
                                                aria-label="Pilih dokumen #<?= (int) ($dokumen['id'] ?? 0); ?>"
                                            >
                                        </td>
                                    <?php endif; ?>
                                    <td class="cell-center"><?= (int) ($subBagian['dokumen_offset'] ?? 0) + $j + 1; ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($dokumen['judul_dokumen']); ?></div>
                                        <div class="table-subtext">
                                            <?= esc($dokumen['kode_dokumen'] ?: '-'); ?> / v<?= esc($dokumen['versi'] ?? 1); ?>
                                        </div>
                                    </td>
                                    <td><?= esc($dokumen['deskripsi'] ?: '-'); ?></td>
                                    <td><?= esc($dokumen['nama_pengunggah'] ?: '-'); ?></td>
                                    <td class="cell-center text-nowrap"><?= esc($dokumen['waktu_tampil'] ?? '-'); ?></td>
                                    <td class="cell-center">
                                        <span class="badge bg-<?= badge_status_dokumen($dokumen['status_dokumen']); ?>">
                                            <?= esc(label_status_dokumen($dokumen['status_dokumen'])); ?>
                                        </span>
                                    </td>
                                    <td class="cell-center">
                                        <div class="action-group">
                                            <?php
                                            $safeExternalLink = sanitize_external_dokumen_link((string) ($dokumen['link_dokumen'] ?? ''));
                                            $isLinkDokumen = ($dokumen['sumber_dokumen'] ?? 'file') === 'link' && $safeExternalLink !== '';
                                            $detailHref = base_url('/dokumen/' . $dokumen['id']) . $programStudiQuerySuffix;
                                            ?>
                                            <a href="<?= esc($detailHref); ?>" class="btn btn-xs btn-primary icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Dokumen" aria-label="Detail Dokumen">
                                                <i class="bi bi-eye-fill"></i>
                                                <span class="visually-hidden">Detail</span>
                                            </a>
                                            <?php if (! empty($dokumen['path_file']) || $isLinkDokumen): ?>
                                                <?php
                                                $referensiHijauHref = $isLinkDokumen
                                                    ? $safeExternalLink
                                                    : base_url('/file/dokumen/' . $dokumen['id'] . '/download');
                                                $referensiHijauTitle = $isLinkDokumen ? 'Buka Link Dokumen' : 'Download Dokumen';
                                                $referensiHijauIcon = $isLinkDokumen ? 'bi-box-arrow-up-right' : 'bi-download';
                                                $referensiHijauSrText = $isLinkDokumen ? 'Buka Link Dokumen' : 'Download';
                                                ?>
                                                <a href="<?= esc($referensiHijauHref); ?>" class="btn btn-xs btn-success icon-btn" <?= $isLinkDokumen ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($referensiHijauTitle); ?>" aria-label="<?= esc($referensiHijauTitle); ?>">
                                                    <i class="bi <?= esc($referensiHijauIcon); ?>"></i>
                                                    <span class="visually-hidden"><?= esc($referensiHijauSrText); ?></span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can_manage_dokumen($dokumen)): ?>
                                                <a href="<?= base_url('/dokumen/' . $dokumen['id'] . '/edit') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Dokumen" aria-label="Edit Dokumen">
                                                    <i class="bi bi-pencil-fill"></i>
                                                    <span class="visually-hidden">Edit</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can_review_dokumen($dokumen)): ?>
                                                <a href="<?= base_url('/dokumen/' . $dokumen['id'] . '/review') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-review icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Review Dokumen" aria-label="Review Dokumen">
                                                    <i class="bi bi-clipboard2-check-fill"></i>
                                                    <span class="visually-hidden">Review</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can_manage_dokumen($dokumen)): ?>
                                                <form action="<?= base_url('/dokumen/' . $dokumen['id'] . '/delete') . $programStudiQuerySuffix; ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus dokumen ini?">
                                                    <?= csrf_field(); ?>
                                                    <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Dokumen" aria-label="Hapus Dokumen">
                                                        <i class="bi bi-trash-fill"></i>
                                                        <span class="visually-hidden">Hapus</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $canBulkDeleteDokumen ? '8' : '7'; ?>" class="text-center text-muted">
                                    Belum ada dokumen untuk sub bagian ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (! empty($subBagian['dokumen_pagination'] ?? [])): ?>
                <div class="d-flex justify-content-end mt-3">
                    <nav aria-label="Pagination dokumen sub bagian <?= (int) ($subBagian['id'] ?? 0); ?>">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= empty($subBagian['dokumen_pagination']['prev_url']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= esc($subBagian['dokumen_pagination']['prev_url'] ?? '#'); ?>">Sebelumnya</a>
                            </li>
                            <?php foreach ($subBagian['dokumen_pagination']['pages'] as $page): ?>
                                <li class="page-item <?= $page['active'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= esc($page['url']); ?>"><?= esc($page['label']); ?></a>
                                </li>
                            <?php endforeach; ?>
                            <li class="page-item <?= empty($subBagian['dokumen_pagination']['next_url']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= esc($subBagian['dokumen_pagination']['next_url'] ?? '#'); ?>">Berikutnya</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.querySelectorAll('.js-doc-bulk-form').forEach(function (form) {
    var formId = form.getAttribute('id');
    if (!formId) return;

    var checkAll = document.querySelector('.js-doc-check-all[data-target-form="' + formId + '"]');
    var rowChecks = Array.prototype.slice.call(
        document.querySelectorAll('.js-doc-row-check[data-target-form="' + formId + '"]')
    );
    var submitBtn = document.querySelector('.js-doc-bulk-delete-btn[form="' + formId + '"]') || form.querySelector('.js-doc-bulk-delete-btn');

    var updateBulkTooltip = function (button, selectedCount) {
        if (!button) return;

        var tooltipText = selectedCount > 0
            ? ('Hapus ' + selectedCount + ' dokumen terpilih')
            : 'Hapus Dokumen Terpilih';

        button.setAttribute('title', tooltipText);
        button.setAttribute('aria-label', tooltipText);
        button.setAttribute('data-bs-original-title', tooltipText);

        if (window.bootstrap && window.bootstrap.Tooltip) {
            var tooltip = window.bootstrap.Tooltip.getInstance(button);
            if (tooltip) {
                tooltip.setContent({ '.tooltip-inner': tooltipText });
            }
        }
    };

    var refreshBulkState = function () {
        var selectedCount = rowChecks.filter(function (item) { return item.checked; }).length;

        if (submitBtn) {
            submitBtn.disabled = selectedCount === 0;
        }
        updateBulkTooltip(submitBtn, selectedCount);

        if (checkAll) {
            checkAll.checked = rowChecks.length > 0 && selectedCount === rowChecks.length;
            checkAll.indeterminate = selectedCount > 0 && selectedCount < rowChecks.length;
        }
    };

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks.forEach(function (input) {
                input.checked = checkAll.checked;
            });
            refreshBulkState();
        });
    }

    rowChecks.forEach(function (input) {
        input.addEventListener('change', refreshBulkState);
    });

    refreshBulkState();
});
</script>

<?= $this->endSection(); ?>
