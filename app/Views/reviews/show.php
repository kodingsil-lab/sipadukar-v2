<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="container">
    <h1>Review Dokumen</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Detail Dokumen</h5>
            <p><strong>ID:</strong> <?= $document['id']; ?></p>
            <p><strong>Judul:</strong> <?= esc($document['title']); ?></p>
            <p><strong>Status:</strong>
                <span class="badge bg-<?= getStatusBadgeClass($document['current_status']); ?>">
                    <?= esc(getStatusLabel($document['current_status'])); ?>
                </span>
            </p>
            <p><strong>Versi:</strong> <?= $document['version']; ?></p>
            <?php if ($document['file_path']): ?>
                <p><strong>File:</strong> <a href="<?= base_url('/file/documents/' . $document['id'] . '/download'); ?>" target="_blank">Download</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Form Review</h5>
            <form action="<?= base_url('/reviews/' . $document['id'] . '/review'); ?>" method="post">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label for="decision" class="form-label">Keputusan</label>
                    <select name="decision" id="decision" class="form-select" required>
                        <option value="validated">Tervalidasi</option>
                        <option value="revision_required">Perlu Revisi</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">Komentar</label>
                    <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Berikan komentar jika diperlukan"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Review</button>
                <a href="<?= base_url('/reviews'); ?>" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Riwayat Review</h5>
            <?php if (!empty($reviews)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Reviewer</th>
                                <th>Keputusan</th>
                                <th>Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= esc($review['created_at']); ?></td>
                                    <td><?= esc($review['reviewer_id']); ?></td>
                                    <td>
                                        <span class="badge bg-<?= getDecisionBadgeClass($review['decision']); ?>">
                                            <?= esc(getDecisionLabel($review['decision'])); ?>
                                        </span>
                                    </td>
                                    <td><?= esc($review['comment'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada riwayat review.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
function getStatusBadgeClass(string $status): string
{
    return match ($status) {
        'draft'          => 'secondary',
        'diajukan'       => 'primary',
        'perlu_revisi'   => 'warning',
        'disubmit_ulang' => 'info',
        'tervalidasi'    => 'success',
        'ditolak'        => 'danger',
        default          => 'secondary',
    };
}

function getStatusLabel(string $status): string
{
    return match ($status) {
        'draft'          => 'Draft',
        'diajukan'       => 'Diajukan',
        'perlu_revisi'   => 'Perlu Revisi',
        'disubmit_ulang' => 'Disubmit Ulang',
        'tervalidasi'    => 'Tervalidasi',
        'ditolak'        => 'Ditolak',
        default          => 'Unknown',
    };
}

function getDecisionBadgeClass(string $decision): string
{
    return match ($decision) {
        'validated'         => 'success',
        'revision_required' => 'warning',
        'rejected'          => 'danger',
        default             => 'secondary',
    };
}

function getDecisionLabel(string $decision): string
{
    return match ($decision) {
        'validated'         => 'Tervalidasi',
        'revision_required' => 'Perlu Revisi',
        'rejected'          => 'Ditolak',
        default             => 'Unknown',
    };
}
?>

<?= $this->endSection(); ?>