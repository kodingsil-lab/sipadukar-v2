<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$isFinalValidator = can_final_validate_dokumen($dokumen);
$isFinalReviewLocked = $isFinalValidator && in_array($dokumen['status_dokumen'] ?? '', ['tervalidasi', 'perlu_revisi'], true);
$reviewModeLabel = 'Validasi Final LPM';
$reviewModeClass = 'bg-primary-subtle text-primary';
$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$programStudiQuerySuffix = $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
$reviewAction = base_url('/dokumen/' . $dokumen['id'] . '/finalisasi') . $programStudiQuerySuffix;
$statusReviewValue = old('status_review', $dokumen['status_dokumen'] ?? '');
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h3 class="mb-1 d-flex align-items-center gap-2">
                            <span>Review Dokumen</span>
                            <span class="badge rounded-pill <?= esc($reviewModeClass); ?>"><?= esc($reviewModeLabel); ?></span>
                        </h3>
                        <p class="text-muted mb-0">
                            <?= esc($dokumen['judul_dokumen']); ?> —
                            <?= esc($dokumen['kode_kriteria']); ?> /
                            <?= esc($dokumen['nama_sub_bagian']); ?>
                        </p>
                    </div>
                    <a href="<?= base_url('/dokumen/' . $dokumen['id']) . $programStudiQuerySuffix; ?>" class="btn btn-light border">
                        Kembali ke Detail
                    </a>
                </div>
            </div>
        </div>

        <div class="card card-clean mb-4">
            <div class="card-body p-4">
                <h5 class="mb-1">Form Review</h5>
                <p class="text-muted small mb-3">Mode saat ini: <strong><?= esc($reviewModeLabel); ?></strong></p>

                <form action="<?= esc($reviewAction); ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Status Review</label>
                        <select name="status_review" class="form-select" <?= $isFinalReviewLocked ? 'disabled' : ''; ?> required>
                            <option value="tervalidasi" <?= $statusReviewValue === 'tervalidasi' ? 'selected' : ''; ?>>Tervalidasi</option>
                            <option value="perlu_revisi" <?= $statusReviewValue === 'perlu_revisi' ? 'selected' : ''; ?>>Perlu Revisi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Review</label>
                        <textarea
                            name="catatan_review"
                            class="form-control"
                            rows="5"
                            placeholder="Tulis catatan final validasi/revisi"
                            <?= $isFinalReviewLocked ? 'disabled' : ''; ?>
                        ><?= old('catatan_review'); ?></textarea>
                    </div>

                    <?php if ($isFinalReviewLocked): ?>
                        <div class="alert alert-info mb-3">
                            Dokumen sudah dalam status final (<strong><?= esc(label_status_dokumen($dokumen['status_dokumen'])); ?></strong>) dan tidak dapat diubah kembali melalui formulir ini.
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" <?= $isFinalReviewLocked ? 'disabled' : ''; ?>>
                        <?= $isFinalReviewLocked ? 'Finalisasi Terkunci' : 'Simpan Finalisasi'; ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="card card-clean">
            <div class="card-body">
                <h5 class="mb-3">Riwayat Review Sebelumnya</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                                    <td colspan="5" class="text-center text-muted">Belum ada review sebelumnya.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
