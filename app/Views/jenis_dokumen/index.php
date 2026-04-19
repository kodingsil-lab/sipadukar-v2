<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Jenis Dokumen</h3>
        <p class="page-subtitle">Kelola daftar jenis dokumen untuk form Dokumen Kriteria.</p>
    </div>

    <?php if (has_role(['admin', 'lpm'])): ?>
        <a href="<?= base_url('/jenis-dokumen/create'); ?>" class="btn btn-primary">Tambah Jenis Dokumen</a>
    <?php endif; ?>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle kriteria-doc-table">
                <thead>
                    <tr>
                        <th width="60" class="cell-center">No</th>
                        <th>Nama Jenis Dokumen</th>
                        <th width="120" class="cell-center">Status</th>
                        <th width="220" class="cell-center col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($list)): ?>
                        <?php foreach ($list as $idx => $row): ?>
                            <tr>
                                <td class="cell-center"><?= (int) $idx + 1; ?></td>
                                <td><div class="fw-semibold"><?= esc($row['nama_jenis_dokumen']); ?></div></td>
                                <td class="cell-center">
                                    <?php if ((int) ($row['is_aktif'] ?? 1) === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <div class="action-group justify-content-center">
                                        <a href="<?= base_url('/jenis-dokumen/' . (int) $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Jenis Dokumen" aria-label="Edit Jenis Dokumen">
                                            <i class="bi bi-pencil-fill"></i>
                                            <span class="visually-hidden">Edit</span>
                                        </a>
                                        <form action="<?= base_url('/jenis-dokumen/' . (int) $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus jenis dokumen ini?">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Jenis Dokumen" aria-label="Hapus Jenis Dokumen">
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
                            <td colspan="4" class="text-center text-muted">Belum ada data jenis dokumen.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
