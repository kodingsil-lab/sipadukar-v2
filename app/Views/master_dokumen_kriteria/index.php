<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$oldProgramStudiId = (int) old('program_studi_id');
$oldKriteriaId = (int) old('kriteria_id');
$oldNamaSubBagianFilter = trim((string) old('nama_sub_bagian_filter'));
$generateMode = (string) (session()->getFlashdata('generate_mode') ?? 'execute');
$generateDetail = session()->getFlashdata('generate_detail');
$generatedTitles = is_array($generateDetail['generated_titles'] ?? null) ? $generateDetail['generated_titles'] : [];
$skippedTitles = is_array($generateDetail['skipped_titles'] ?? null) ? $generateDetail['skipped_titles'] : [];
?>

<style>
    .master-dokumen-header-actions {
        gap: .45rem;
        flex-wrap: nowrap;
    }

    .master-dokumen-header-actions .btn {
        min-height: 34px;
        padding: 5px 11px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .master-dokumen-header-actions .btn i {
        font-size: 13px;
        line-height: 1;
    }

    .master-dokumen-generate-actions {
        gap: .45rem;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: nowrap;
    }

    .master-dokumen-generate-actions .btn {
        min-height: 40px;
        padding: 5px 11px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        flex: 1 1 0;
        width: auto;
        min-width: 0;
    }

    .master-dokumen-generate-actions .btn i {
        font-size: 13px;
        line-height: 1;
    }

    @media (max-width: 767.98px) {
        .master-dokumen-header-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .master-dokumen-header-actions .btn {
            flex: 1 1 auto;
        }

        .master-dokumen-generate-actions {
            flex-wrap: wrap;
        }

        .master-dokumen-generate-actions .btn {
            flex: 1 1 auto;
        }
    }
</style>

<?php if (! empty($generatedTitles) || ! empty($skippedTitles)): ?>
    <div class="card card-clean mb-3">
        <div class="card-body">
            <h6 class="mb-3">Detail Hasil Generate</h6>

            <?php if (! empty($generatedTitles)): ?>
                <div class="mb-3">
                    <span class="badge bg-success mb-2"><?= $generateMode === 'preview' ? 'Akan Dibuat' : 'Dibuat'; ?> (<?= count($generatedTitles); ?>)</span>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($generatedTitles as $judul): ?>
                            <li><?= esc((string) $judul); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (! empty($skippedTitles)): ?>
                <div>
                    <span class="badge bg-warning text-dark mb-2"><?= $generateMode === 'preview' ? 'Akan Dilewati' : 'Dilewati'; ?> (<?= count($skippedTitles); ?>)</span>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($skippedTitles as $judul): ?>
                            <li><?= esc((string) $judul); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Master Dokumen Kriteria</h3>
        <p class="page-subtitle">Kelola template dokumen per kriteria/sub bagian, lalu generate ke Program Studi aktif akreditasi.</p>
    </div>

    <div class="d-flex flex-wrap align-items-center master-dokumen-header-actions">
        <a href="<?= base_url('/master-dokumen-kriteria/template-excel'); ?>" class="btn btn-outline-secondary" download="template_master_dokumen_kriteria.xlsx">
            <i class="bi bi-download me-1"></i>Unduh Template
        </a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#imporDokumenModal">
            <i class="bi bi-upload me-1"></i>Impor Excel
        </button>
        <a href="<?= base_url('/master-dokumen-kriteria/create'); ?>" class="btn btn-primary">Tambah Master</a>
    </div>
</div>

<div class="card card-clean mb-3">
    <div class="card-body">
        <h6 class="mb-3">Generate ke Program Studi</h6>
        <form action="<?= base_url('/master-dokumen-kriteria/generate'); ?>" method="post" class="row g-3">
            <?= csrf_field(); ?>

            <div class="col-md-3">
                <label for="program_studi_id" class="form-label">Program Studi Aktif Akreditasi</label>
                <select id="program_studi_id" name="program_studi_id" class="form-select" required>
                    <option value="">Pilih Program Studi</option>
                    <?php foreach (($programStudiAktifList ?? []) as $prodi): ?>
                        <?php $prodiId = (int) ($prodi['id'] ?? 0); ?>
                        <option value="<?= $prodiId; ?>" <?= $oldProgramStudiId === $prodiId ? 'selected' : ''; ?>>
                            <?= esc($prodi['nama_program_studi'] ?? '-'); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="kriteria_id" class="form-label">Filter Kriteria (Opsional)</label>
                <select id="kriteria_id" name="kriteria_id" class="form-select">
                    <option value="">Semua Kriteria</option>
                    <?php foreach (($kriteriaList ?? []) as $kriteria): ?>
                        <?php $kriteriaId = (int) ($kriteria['id'] ?? 0); ?>
                        <option value="<?= $kriteriaId; ?>" <?= $oldKriteriaId === $kriteriaId ? 'selected' : ''; ?>>
                            <?= esc($kriteria['kode'] ?? '-'); ?> - <?= esc($kriteria['nama_kriteria'] ?? '-'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="nama_sub_bagian_filter" class="form-label">Filter Sub Bagian/Judul (Opsional)</label>
                <input
                    type="text"
                    id="nama_sub_bagian_filter"
                    name="nama_sub_bagian_filter"
                    class="form-control"
                    value="<?= esc($oldNamaSubBagianFilter); ?>"
                    placeholder="Contoh: Ketepatan Rumusan"
                >
            </div>

            <div class="col-md-3 d-flex align-items-end master-dokumen-generate-actions">
                <button type="submit" name="preview_only" value="1" class="btn btn-outline-secondary">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-lightning-fill me-1"></i>Generate Draft
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card card-clean">
    <div class="card-body">

<!-- Modal Impor Dokumen -->
<div class="modal fade" id="imporDokumenModal" tabindex="-1" aria-labelledby="imporDokumenModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('/master-dokumen-kriteria/impor'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="imporDokumenModalLabel">Impor Master Dokumen Kriteria (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file_excel" class="form-label">Pilih File Excel (.xlsx)</label>
                        <input type="file" class="form-control" id="file_excel" name="file_excel" accept=".xlsx" required>
                        <div class="form-text">Wajib kolom: <b>Kriteria (isi K1 s.d. K9)</b>, Judul Dokumen, Tahun Dokumen. Jenis dokumen otomatis mengikuti master. Semua dokumen otomatis aktif.</div>
                        <div class="form-text">Unduh template: <a href="<?= base_url('/master-dokumen-kriteria/template-excel'); ?>" download="template_master_dokumen_kriteria.xlsx">Template Excel</a></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Impor</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <?php $bulkMasterFormId = 'bulk-delete-form-master'; ?>
        <div class="doc-table-toolbar mb-2 d-flex justify-content-end">
            <form id="<?= esc($bulkMasterFormId); ?>" method="post" action="<?= base_url('/master-dokumen-kriteria/bulk-delete'); ?>" class="doc-bulk-inline js-master-bulk-form js-confirm-submit" data-confirm="Yakin hapus master dokumen terpilih?">
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-2 px-3 js-master-bulk-delete-btn" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Master Dokumen Terpilih" aria-label="Hapus Master Dokumen Terpilih">
                    <i class="bi bi-trash-fill"></i>
                    <span>Hapus Terpilih</span>
                    <span class="badge bg-light text-dark ms-1 js-master-selected-badge" style="font-size:0.95em;">0</span>
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle kriteria-doc-table">
                <thead>
                    <tr>
                        <th width="44" class="cell-center">
                            <input type="checkbox" class="form-check-input js-master-check-all" aria-label="Pilih semua">
                        </th>
                        <th width="60" class="cell-center">No</th>
                        <th>Kriteria</th>
                        <th width="180" class="cell-center">Mode</th>
                        <th>Sub Bagian</th>
                        <th>Judul Dokumen</th>
                        <th>Jenis Dokumen</th>
                        <th width="100" class="cell-center">Tahun</th>
                        <th width="100" class="cell-center">Status</th>
                        <th width="120" class="cell-center col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($list)): ?>
                        <?php foreach ($list as $idx => $row): ?>
                            <?php
                            $currentPage = (int) ($pager->getCurrentPage() ?? 1);
                            $rowNum = ($currentPage - 1) * ($perPage ?? 25) + $idx + 1;
                            ?>
                            <tr>
                                <td class="cell-center">
                                    <input
                                        type="checkbox"
                                        class="form-check-input js-master-row-check"
                                        name="selected_ids[]"
                                        form="<?= esc($bulkMasterFormId); ?>"
                                        value="<?= (int) $row['id']; ?>"
                                        aria-label="Pilih <?= esc($row['judul_dokumen'] ?? '-'); ?>"
                                    >
                                </td>
                                <td class="cell-center"><?= $rowNum; ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['kode'] ?? '-'); ?></div>
                                    <div class="table-subtext"><?= esc($row['nama_kriteria'] ?? '-'); ?></div>
                                </td>
                                <td class="cell-center">
                                    <?php if (trim((string) ($row['nama_sub_bagian_tampil'] ?? '')) !== '' && strtolower(trim((string) ($row['nama_sub_bagian_tampil'] ?? ''))) !== 'dokumen utama'): ?>
                                        <span class="badge bg-primary">Dengan Sub Bagian</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tanpa Sub Bagian</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($row['nama_sub_bagian_tampil'] ?? 'Dokumen Utama'); ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['judul_dokumen'] ?? '-'); ?></div>
                                    <div class="table-subtext"><?= esc($row['deskripsi'] ?? '-'); ?></div>
                                </td>
                                <td><?= esc($row['jenis_dokumen'] ?: '-'); ?></td>
                                <td class="cell-center"><?= esc((string) ($row['tahun_dokumen'] ?? '-')); ?></td>
                                <td class="cell-center">
                                    <?php if ((int) ($row['is_aktif'] ?? 1) === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <div class="action-group justify-content-center">
                                        <a href="<?= base_url('/master-dokumen-kriteria/' . (int) $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Master Dokumen" aria-label="Edit Master Dokumen">
                                            <i class="bi bi-pencil-fill"></i>
                                            <span class="visually-hidden">Edit</span>
                                        </a>
                                        <form action="<?= base_url('/master-dokumen-kriteria/' . (int) $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus master dokumen ini?">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Master Dokumen" aria-label="Hapus Master Dokumen">
                                                <i class="bi bi-trash-fill"></i>
                                                <span class="visually-hidden">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">Belum ada data master dokumen kriteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
        <div class="d-flex justify-content-center mt-3">
            <?= $pager->links(); ?>
        </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('bulk-delete-form-master');
    if (!form) return;

    var checkAll  = document.querySelector('.js-master-check-all');
    var rowChecks = Array.prototype.slice.call(document.querySelectorAll('.js-master-row-check'));
    var submitBtn = form.querySelector('.js-master-bulk-delete-btn');
    var badge = form.querySelector('.js-master-selected-badge');

    function refresh() {
        var n = rowChecks.filter(function (c) { return c.checked; }).length;
        if (submitBtn) submitBtn.disabled = n === 0;
        if (badge) badge.textContent = n;
        if (checkAll) {
            checkAll.checked       = rowChecks.length > 0 && n === rowChecks.length;
            checkAll.indeterminate = n > 0 && n < rowChecks.length;
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks.forEach(function (c) { c.checked = checkAll.checked; });
            refresh();
        });
    }

    rowChecks.forEach(function (c) { c.addEventListener('change', refresh); });
    refresh();
}());
</script>

<?= $this->endSection(); ?>
