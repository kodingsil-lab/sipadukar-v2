<?php $this->extend('layouts/public'); ?>
<?php $this->section('content'); ?>
<?php
$formatTanggalRingkas = static function ($value): string {
    $teks = trim((string) $value);
    if ($teks === '' || $teks === '-') {
        return '-';
    }

    $timestamp = strtotime($teks);
    if ($timestamp === false) {
        return $teks;
    }

    return date('d M Y', $timestamp);
};
?>

<div class="container-public container-fluid py-4">
    <div class="card card-clean">
        <div class="card-body">
            <div class="section-head mb-4">
                <div>
                    <h5 class="mb-1">Daftar Dokumen Penting</h5>
                    <p class="text-muted small mb-0">Dokumen dikelompokkan berdasarkan jenis agar lebih mudah dipindai ketika asesmen dimulai.</p>
                </div>
                <a href="<?= site_url('portal/pencarian') ?>" class="btn btn-sm btn-outline-public">Cari Dokumen Lain</a>
            </div>

            <?php if (!empty($dokumenGrouped)): ?>
                <div class="row g-4">
                    <?php foreach ($dokumenGrouped as $jenisDok => $listDoc): ?>
                        <div class="col-12">
                            <div class="card card-clean h-100">
                                <div class="card-body">
                                    <div class="section-head mb-3">
                                        <div>
                                            <h5 class="mb-1"><?= esc($jenisDok) ?></h5>
                                            <p class="text-muted small mb-0"><?= esc((string) count($listDoc)) ?> dokumen final tersedia pada kelompok ini.</p>
                                        </div>
                                        <span class="badge badge-soft-primary"><?= esc((string) count($listDoc)) ?></span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-clean table-strong align-middle kriteria-doc-table">
                                            <thead>
                                                <tr>
                                                    <th width="60" class="cell-center">No</th>
                                                    <th>Judul Dokumen</th>
                                                    <th width="110">Kriteria</th>
                                                    <th>Program Studi</th>
                                                    <th width="90" class="cell-center">Tahun</th>
                                                    <th width="160" class="cell-center">Validasi</th>
                                                    <th width="120" class="cell-center col-aksi">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($listDoc as $index => $doc): ?>
                                                    <?php $safeExternalLink = sanitize_external_dokumen_link((string) ($doc['link_dokumen'] ?? '')); ?>
                                                    <?php $isLinkDokumen = (($doc['sumber_dokumen'] ?? 'file') === 'link') && $safeExternalLink !== ''; ?>
                                                    <tr>
                                                        <td class="cell-center"><?= esc((string) ($index + 1)) ?></td>
                                                        <td>
                                                            <div class="fw-semibold"><?= esc($doc['judul_dokumen'] ?? '-') ?></div>
                                                            <div class="table-subtext">
                                                                <?= esc($doc['jenis_dokumen'] ?? '-') ?><?= !empty($doc['kode_dokumen']) ? ' / ' . esc($doc['kode_dokumen']) : '' ?>
                                                            </div>
                                                        </td>
                                                        <td><?= esc($doc['kode_kriteria'] ?? '-') ?></td>
                                                        <td><?= esc($doc['nama_program_studi'] ?? '-') ?></td>
                                                        <td class="cell-center"><?= esc((string) ($doc['tahun_dokumen'] ?? '-')) ?></td>
                                                        <td class="cell-center"><?= esc($formatTanggalRingkas($doc['tanggal_validasi'] ?? '-')) ?></td>
                                                        <td class="cell-center">
                                                            <div class="action-group">
                                                                <a href="<?= site_url('file/dokumen/' . ($doc['id'] ?? 0) . '/preview') ?>" class="btn btn-xs btn-primary icon-btn" target="_blank" title="Lihat Dokumen" aria-label="Lihat Dokumen">
                                                                    <i class="bi bi-eye-fill"></i>
                                                                </a>
                                                                <?php if (!empty($doc['path_file']) || $isLinkDokumen): ?>
                                                                    <?php
                                                                    $downloadHref = $isLinkDokumen
                                                                        ? $safeExternalLink
                                                                        : site_url('file/dokumen/' . ($doc['id'] ?? 0) . '/download');
                                                                    $downloadIcon = $isLinkDokumen ? 'bi-box-arrow-up-right' : 'bi-download';
                                                                    ?>
                                                                    <a href="<?= esc($downloadHref) ?>" class="btn btn-xs btn-success icon-btn" <?= $isLinkDokumen ? 'target="_blank" rel="noopener noreferrer"' : '' ?> title="Unduh Dokumen" aria-label="Unduh Dokumen">
                                                                        <i class="bi <?= esc($downloadIcon) ?>"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">Belum ada dokumen penting yang dapat ditampilkan saat ini.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
