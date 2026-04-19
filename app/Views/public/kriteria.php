<?php $this->extend('layouts/public'); ?>
<?php $this->section('content'); ?>
<?php
$selectedKriteria = $selectedKriteria ?? null;
$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$selectedProgramStudiLabel = (string) ($selectedProgramStudiLabel ?? 'Prodi Persiapan Akreditasi');
$selectedProgramStudiLabelAll = (string) ($selectedProgramStudiLabelAll ?? 'Prodi Persiapan Akreditasi');
$programStudiFilterLocked = ! empty($programStudiFilterLocked);
$showProgramStudiAllOption = ! empty($showProgramStudiAllOption);
$hasActiveProgramStudi = ! empty($programStudiAktifList);
$activeKriteriaId = (int) ($kriteriaActive ?? ($selectedKriteria['id'] ?? ($kriteriaPanels[0]['id'] ?? 0)));
$currentQuery = service('request')->getGet();
$activePanel = null;
foreach (($kriteriaPanels ?? []) as $panelItem) {
    if ((int) ($panelItem['id'] ?? 0) === $activeKriteriaId) {
        $activePanel = $panelItem;
        break;
    }
}

if ($activePanel === null) {
    $activePanel = $kriteriaPanels[0] ?? null;
}

$buildProgramStudiQuery = static function (int $kriteriaId, int $programStudiId): string {
    $query = ['kriteria_id' => $kriteriaId];
    if ($programStudiId > 0) {
        $query['program_studi_id'] = $programStudiId;
    }

    return '?' . http_build_query($query);
};
?>

<style>
    .kriteria-doc-table .btn.btn-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    .kriteria-doc-table .btn.btn-primary:hover,
    .kriteria-doc-table .btn.btn-primary:focus {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #ffffff;
    }
</style>

<div class="container-public container-fluid py-4">
    <div class="card card-clean mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= current_url(); ?>" class="kriteria-prodi-filter-form">
                <?php if ($activeKriteriaId > 0): ?>
                    <input type="hidden" id="portalKriteriaActiveInput" name="kriteria_id" value="<?= esc((string) $activeKriteriaId) ?>">
                <?php endif; ?>
                <?php if ($programStudiFilterLocked && $selectedProgramStudiId > 0): ?>
                    <input type="hidden" name="program_studi_id" value="<?= esc((string) $selectedProgramStudiId) ?>">
                <?php endif; ?>

                <div class="kriteria-prodi-filter-head">
                    <label for="portalProgramStudiFilter" class="form-label fw-semibold mb-0">Filter Prodi Persiapan Akreditasi</label>
                </div>
                <?php if ($hasActiveProgramStudi): ?>
                    <div class="kriteria-prodi-filter-controls">
                        <?php if ($programStudiFilterLocked): ?>
                            <div class="kriteria-prodi-filter-locked" aria-live="polite">
                                <span class="kriteria-prodi-filter-locked-label">Prodi aktif</span>
                                <strong><?= esc($selectedProgramStudiLabel); ?></strong>
                                <span class="kriteria-prodi-filter-locked-note">Terkunci otomatis karena hanya ada satu prodi aktif akreditasi.</span>
                            </div>
                        <?php else: ?>
                            <select id="portalProgramStudiFilter" name="program_studi_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php if ($showProgramStudiAllOption): ?>
                                    <option value="" <?= $selectedProgramStudiId === 0 ? 'selected' : '' ?>>Pilih Prodi Persiapan Akreditasi</option>
                                <?php endif; ?>
                                <?php foreach (($programStudiAktifList ?? []) as $prodi): ?>
                                    <option value="<?= esc((string) ($prodi['id'] ?? 0)); ?>" <?= $selectedProgramStudiId === (int) ($prodi['id'] ?? 0) ? 'selected' : ''; ?>>
                                        <?= esc($prodi['nama_program_studi'] ?? '-'); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <?php if ($showProgramStudiAllOption): ?>
                            <a href="<?= esc(site_url('portal/kriteria') . ($activeKriteriaId > 0 ? ('?kriteria_id=' . $activeKriteriaId) : '')) ?>" class="btn btn-light border btn-sm" id="portalProgramStudiReset">Reset</a>
                        <?php endif; ?>
                        <div class="kriteria-prodi-filter-status <?= $selectedProgramStudiId > 0 ? 'is-specific' : 'is-all'; ?>" role="status" aria-live="polite">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Mode pantau: <strong><?= esc($selectedProgramStudiLabel); ?></strong></span>
                        </div>
                    </div>
                    <div class="form-text mt-1">Pilih program studi terlebih dahulu agar dokumen kriteria yang ditampilkan sesuai dengan prodi yang diasesmen.</div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        Belum ada Program Studi aktif akreditasi yang dapat dipilih. Dokumen kriteria public akan menyesuaikan otomatis setelah Admin mengaktifkan minimal satu program studi.
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($activePanel !== null): ?>
        <?php
        $directDocs = $activePanel['direct_docs'] ?? [];
        $subBagianList = $activePanel['sub_bagian_list'] ?? [];
        ?>
        <div class="card card-clean mb-4">
            <div class="card-body">
                <div class="section-head mb-3">
                    <div>
                        <h5 class="mb-1"><?= esc($activePanel['kode'] ?? '-') ?> - <?= esc($activePanel['nama_kriteria'] ?? 'Kriteria') ?></h5>
                        <p class="text-muted small mb-0"><?= esc($activePanel['deskripsi'] ?? 'Dokumen final untuk kriteria terpilih ditampilkan pada tabel di bawah ini.') ?></p>
                    </div>
                </div>
                <div class="public-doc-meta">
                    <span class="public-meta-chip"><?= esc((string) ($activePanel['total_dokumen'] ?? 0)) ?> dokumen final</span>
                    <span class="public-meta-chip">Status tervalidasi</span>
                    <span class="public-meta-chip">Aksi baca saja</span>
                </div>
            </div>
        </div>

        <?php if (! empty($directDocs) || empty($subBagianList)): ?>
            <div class="card card-clean mb-4">
                <div class="card-body">
                    <div class="subbagian-head mb-2">
                        <div class="subbagian-title-wrap">
                            <h5 class="mb-1">Dokumen Utama</h5>
                            <div class="small text-muted">Dokumen final yang langsung terhubung ke <?= esc($activePanel['kode'] ?? 'kriteria') ?> tanpa sub bagian spesifik.</div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-clean table-strong align-middle kriteria-doc-table">
                            <thead>
                                <tr>
                                    <th width="60" class="cell-center">No</th>
                                    <th width="24%">Nama Dokumen</th>
                                    <th width="24%">Deskripsi</th>
                                    <th width="12%">Penanggung Jawab</th>
                                    <th width="190" class="cell-center">Tanggal &amp; Waktu</th>
                                    <th width="90" class="cell-center">Status</th>
                                    <th width="140" class="cell-center col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (! empty($directDocs)): ?>
                                    <?php foreach ($directDocs as $docIndex => $dokumenRow): ?>
                                        <tr>
                                            <td class="cell-center"><?= esc((string) ($docIndex + 1)) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= esc($dokumenRow['judul_dokumen'] ?? '-') ?></div>
                                                <div class="table-subtext"><?= esc($dokumenRow['kode_dokumen'] ?: '-') ?> / v<?= esc((string) ($dokumenRow['versi'] ?? 1)) ?></div>
                                            </td>
                                            <td><?= esc($dokumenRow['deskripsi'] ?: '-') ?></td>
                                            <td><?= esc($dokumenRow['nama_pengunggah'] ?: '-') ?></td>
                                            <td class="cell-center text-nowrap"><?= esc($dokumenRow['waktu_tampil'] ?? '-') ?></td>
                                            <td class="cell-center"><span class="badge bg-success">Tervalidasi</span></td>
                                            <td class="cell-center">
                                                <div class="action-group">
                                                    <a href="<?= site_url('file/dokumen/' . $dokumenRow['id'] . '/preview') ?>" class="btn btn-xs btn-primary icon-btn" target="_blank" title="Lihat Dokumen" aria-label="Lihat Dokumen">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <?php $safeExternalLink = sanitize_external_dokumen_link((string) ($dokumenRow['link_dokumen'] ?? '')); ?>
                                                    <?php $isLinkDokumen = (($dokumenRow['sumber_dokumen'] ?? 'file') === 'link') && $safeExternalLink !== ''; ?>
                                                    <?php if (! empty($dokumenRow['path_file']) || $isLinkDokumen): ?>
                                                        <?php $downloadHref = $isLinkDokumen ? $safeExternalLink : site_url('file/dokumen/' . $dokumenRow['id'] . '/download'); ?>
                                                        <?php $downloadIcon = $isLinkDokumen ? 'bi-box-arrow-up-right' : 'bi-download'; ?>
                                                        <a href="<?= esc($downloadHref) ?>" class="btn btn-xs btn-success icon-btn" <?= $isLinkDokumen ? 'target="_blank" rel="noopener noreferrer"' : '' ?> title="Unduh Dokumen" aria-label="Unduh Dokumen">
                                                            <i class="bi <?= esc($downloadIcon) ?>"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada dokumen pada bagian utama kriteria ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($subBagianList as $subIndex => $subBagian): ?>
            <div class="card card-clean mb-4">
                <div class="card-body">
                    <div class="subbagian-head mb-2">
                        <div class="subbagian-title-wrap">
                            <h5 class="mb-1"><?= esc((string) ($subIndex + 1)) ?>. <?= esc($subBagian['nama_sub_bagian'] ?? '-') ?></h5>
                            <div class="small text-muted"><?= esc($subBagian['deskripsi'] ?: 'Belum ada deskripsi sub bagian.') ?></div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-clean table-strong align-middle kriteria-doc-table">
                            <thead>
                                <tr>
                                    <th width="60" class="cell-center">No</th>
                                    <th width="24%">Nama Dokumen</th>
                                    <th width="24%">Deskripsi</th>
                                    <th width="12%">Penanggung Jawab</th>
                                    <th width="190" class="cell-center">Tanggal &amp; Waktu</th>
                                    <th width="90" class="cell-center">Status</th>
                                    <th width="140" class="cell-center col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (! empty($subBagian['dokumen_list'])): ?>
                                    <?php foreach ($subBagian['dokumen_list'] as $docIndex => $dokumenRow): ?>
                                        <tr>
                                            <td class="cell-center"><?= esc((string) ($docIndex + 1)) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= esc($dokumenRow['judul_dokumen'] ?? '-') ?></div>
                                                <div class="table-subtext"><?= esc($dokumenRow['kode_dokumen'] ?: '-') ?> / v<?= esc((string) ($dokumenRow['versi'] ?? 1)) ?></div>
                                            </td>
                                            <td><?= esc($dokumenRow['deskripsi'] ?: '-') ?></td>
                                            <td><?= esc($dokumenRow['nama_pengunggah'] ?: '-') ?></td>
                                            <td class="cell-center text-nowrap"><?= esc($dokumenRow['waktu_tampil'] ?? '-') ?></td>
                                            <td class="cell-center"><span class="badge bg-success">Tervalidasi</span></td>
                                            <td class="cell-center">
                                                <div class="action-group">
                                                    <a href="<?= site_url('file/dokumen/' . $dokumenRow['id'] . '/preview') ?>" class="btn btn-xs btn-primary icon-btn" target="_blank" title="Lihat Dokumen" aria-label="Lihat Dokumen">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <?php $safeExternalLink = sanitize_external_dokumen_link((string) ($dokumenRow['link_dokumen'] ?? '')); ?>
                                                    <?php $isLinkDokumen = (($dokumenRow['sumber_dokumen'] ?? 'file') === 'link') && $safeExternalLink !== ''; ?>
                                                    <?php if (! empty($dokumenRow['path_file']) || $isLinkDokumen): ?>
                                                        <?php $downloadHref = $isLinkDokumen ? $safeExternalLink : site_url('file/dokumen/' . $dokumenRow['id'] . '/download'); ?>
                                                        <?php $downloadIcon = $isLinkDokumen ? 'bi-box-arrow-up-right' : 'bi-download'; ?>
                                                        <a href="<?= esc($downloadHref) ?>" class="btn btn-xs btn-success icon-btn" <?= $isLinkDokumen ? 'target="_blank" rel="noopener noreferrer"' : '' ?> title="Unduh Dokumen" aria-label="Unduh Dokumen">
                                                            <i class="bi <?= esc($downloadIcon) ?>"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada dokumen untuk sub bagian ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card card-clean">
            <div class="card-body text-center text-muted py-5">Belum ada kriteria aktif yang dapat ditampilkan.</div>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>