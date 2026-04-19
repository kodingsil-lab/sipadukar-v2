<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="container">
    <h1>Review Dokumen (LPM)</h1>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Uploader</th>
                    <th>Status</th>
                    <th>Versi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><?= $doc['id']; ?></td>
                        <td><?= esc($doc['title']); ?></td>
                        <td><?= esc($doc['uploaded_by']); ?></td>
                        <td>
                            <span class="badge bg-<?= getStatusBadgeClass($doc['current_status']); ?>">
                                <?= esc(getStatusLabel($doc['current_status'])); ?>
                            </span>
                        </td>
                        <td><?= $doc['version']; ?></td>
                        <td>
                            <?php if (in_array($doc['current_status'], ['diajukan', 'disubmit_ulang'])): ?>
                                <a href="<?= base_url('/reviews/' . $doc['id']); ?>" class="btn btn-sm btn-primary">Review</a>
                            <?php else: ?>
                                <span class="text-muted">Tidak perlu review</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
?>

<?= $this->endSection(); ?>