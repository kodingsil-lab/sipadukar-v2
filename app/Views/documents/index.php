<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="container">
    <h1>Dokumen Saya</h1>

    <a href="<?= base_url('/documents/create'); ?>" class="btn btn-primary mb-3">Upload Dokumen Baru</a>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
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
                        <td>
                            <span class="badge bg-<?= getStatusBadgeClass($doc['current_status']); ?>">
                                <?= esc(getStatusLabel($doc['current_status'])); ?>
                            </span>
                        </td>
                        <td><?= $doc['version']; ?></td>
                        <td>
                            <?php if ($doc['current_status'] === 'draft'): ?>
                                <a href="<?= base_url('/documents/edit/' . $doc['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form action="<?= base_url('/documents/submit/' . $doc['id']); ?>" method="post" class="d-inline js-confirm-submit" data-confirm="Ajukan dokumen ini?">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-success">Ajukan</button>
                                </form>
                            <?php elseif ($doc['current_status'] === 'perlu_revisi'): ?>
                                <a href="<?= base_url('/documents/edit/' . $doc['id']); ?>" class="btn btn-sm btn-warning">Revisi</a>
                                <form action="<?= base_url('/documents/resubmit/' . $doc['id']); ?>" method="post" class="d-inline js-confirm-submit" data-confirm="Submit ulang dokumen ini?">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-success">Submit Ulang</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Tidak ada aksi</span>
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
