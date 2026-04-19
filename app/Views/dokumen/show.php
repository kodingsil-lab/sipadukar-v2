<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$programStudiQuerySuffix = $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
$subBagianLabel = trim((string) ($dokumen['nama_sub_bagian'] ?? '')) ?: '-';
$isLpmReviewer = has_role('lpm') && can_review_dokumen($dokumen);
$isFinalValidator = $isLpmReviewer && can_final_validate_dokumen($dokumen);
$reviewModeLabel = 'Validasi Final LPM';
$reviewAction = base_url('/dokumen/' . $dokumen['id'] . '/finalisasi') . $programStudiQuerySuffix;
$statusReviewValue = old('status_review', $dokumen['status_dokumen'] ?? '');
$safeExternalLink = sanitize_external_dokumen_link((string) ($dokumen['link_dokumen'] ?? ''));
$isLinkSource = ($dokumen['sumber_dokumen'] ?? 'file') === 'link' && $safeExternalLink !== '';
$isFileSource = ! empty($dokumen['path_file']);
$isPreviewableFile = $isFileSource && bisa_preview_file($dokumen['ekstensi_file'] ?? null);
$embeddedLink = $isLinkSource ? dokumen_preview_embed_link($safeExternalLink) : '';
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
        <h3 class="page-title"><?= esc($dokumen['judul_dokumen']); ?></h3>
        <p class="page-subtitle">
            <?= esc($dokumen['kode_kriteria']); ?> - <?= esc($dokumen['nama_kriteria']); ?> /
            <?= esc($subBagianLabel); ?>
        </p>
    </div>

    <div class="action-group page-actions">
        <a href="<?= base_url('/kriteria/' . $dokumen['kriteria_id']) . $programStudiQuerySuffix . '#subbagian-' . (int) $dokumen['sub_bagian_id']; ?>" class="btn btn-xs btn-light border">
            Kembali
        </a>

        <?php if ($isLinkSource): ?>
            <a href="<?= esc($safeExternalLink); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-success">
                Buka Link
            </a>
        <?php endif; ?>

        <?php if ($isFileSource): ?>
            <a href="<?= base_url('/file/dokumen/' . $dokumen['id'] . '/download'); ?>" class="btn btn-xs btn-success">
                Download
            </a>
        <?php endif; ?>

        <?php if (can_manage_dokumen($dokumen)): ?>
            <a href="<?= base_url('/dokumen/' . $dokumen['id'] . '/edit') . $programStudiQuerySuffix; ?>" class="btn btn-xs btn-warning">
                Edit
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card card-clean mb-4">
            <div class="card-body">
                <h5 class="mb-3">Informasi Dokumen</h5>
                <div class="mb-2">
                    <div class="small text-muted mb-1">Judul Dokumen</div>
                    <div class="fw-semibold"><?= esc($dokumen['judul_dokumen']); ?></div>
                </div>
                <div>
                    <div class="small text-muted mb-1">Sub Bagian</div>
                    <div><?= esc($subBagianLabel); ?></div>
                </div>
            </div>
        </div>

        <?php if ($isLpmReviewer): ?>
            <div class="card card-clean mb-4">
                <div class="card-body">
                    <h5 class="mb-1">Form Review</h5>
                    <p class="text-muted small mb-3">Mode saat ini: <strong><?= esc($reviewModeLabel); ?></strong></p>

                    <form action="<?= esc($reviewAction); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">Status Review</label>
                            <select name="status_review" class="form-select" required>
                                <?php if ($isFinalValidator): ?>
                                    <option value="tervalidasi" <?= $statusReviewValue === 'tervalidasi' ? 'selected' : ''; ?>>Tervalidasi</option>
                                    <option value="perlu_revisi" <?= $statusReviewValue === 'perlu_revisi' ? 'selected' : ''; ?>>Perlu Revisi</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Review</label>
                            <textarea
                                name="catatan_review"
                                class="form-control"
                                rows="4"
                                placeholder="Tulis catatan final validasi/revisi"
                            ><?= old('catatan_review'); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan Finalisasi
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card card-clean">
            <div class="card-body">
                <h5 class="mb-3">Riwayat Review</h5>

                <div class="table-responsive">
                    <table class="table table-clean align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="60" class="cell-center">No</th>
                                <th>Reviewer</th>
                                <th class="cell-center">Status</th>
                                <th>Catatan</th>
                                <th width="170">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($reviewList)): ?>
                                <?php foreach ($reviewList as $i => $row): ?>
                                    <tr>
                                        <td class="cell-center"><?= $i + 1; ?></td>
                                        <td><?= esc($row['nama_reviewer'] ?? '-'); ?></td>
                                        <td class="cell-center">
                                            <span class="badge bg-<?= badge_status_review($row['status_review']); ?>">
                                                <?= esc(label_status_review($row['status_review'])); ?>
                                            </span>
                                        </td>
                                        <td><?= esc($row['catatan_review'] ?: '-'); ?></td>
                                        <td><?= esc($row['tanggal_review'] ?: '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada riwayat review.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-clean h-100">
            <div class="card-body">
                <h5 class="mb-3">Preview Dokumen</h5>

                <?php if ($isLinkSource && $embeddedLink !== ''): ?>
                    <iframe
                        src="<?= esc($embeddedLink); ?>"
                        title="Preview Dokumen Link"
                        style="width:100%; min-height:720px; border:1px solid #e9ecef; border-radius:10px;"
                        allowfullscreen
                    ></iframe>
                    <div class="small text-muted mt-2">
                        Jika preview tidak tampil, kemungkinan link dibatasi oleh kebijakan keamanan sumber. Silakan buka tautan dokumen dari tombol di bagian atas halaman.
                    </div>
                <?php elseif ($isPreviewableFile): ?>
                    <iframe
                        src="<?= base_url('/file/dokumen/' . $dokumen['id'] . '/preview'); ?>"
                        title="Preview Dokumen File"
                        style="width:100%; min-height:720px; border:1px solid #e9ecef; border-radius:10px;"
                        allowfullscreen
                    ></iframe>
                <?php elseif ($isFileSource): ?>
                    <div class="alert alert-warning mb-0">
                        File dengan ekstensi <strong><?= esc(strtolower((string) ($dokumen['ekstensi_file'] ?? '-'))); ?></strong> belum bisa dipreview langsung. Silakan unduh file untuk melihat isi dokumen.
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        Dokumen belum memiliki sumber file atau link yang dapat dipreview.
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
