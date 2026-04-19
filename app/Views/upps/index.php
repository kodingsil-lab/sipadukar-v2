<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">UPPS</h3>
        <p class="page-subtitle">Lengkapi Data Unit Pengelola Program Studi</p>
    </div>

    <?php if (has_role(['admin', 'lpm'])): ?>
        <a href="<?= base_url('/upps/create'); ?>" class="btn btn-primary">Tambah UPPS</a>
    <?php endif; ?>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle">
                <thead>
                    <tr>
                        <th>Nama UPPS</th>
                        <th>Jenis Unit</th>
                        <th>Nama Pimpinan UPPS</th>
                        <th width="140" class="cell-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($uppsList)): ?>
                        <?php foreach ($uppsList as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['nama_upps']); ?></div>
                                    <div class="table-subtext"><?= esc($row['nama_singkatan'] ?: '-'); ?></div>
                                </td>
                                <td><?= esc($row['jenis_unit'] ?: '-'); ?></td>
                                <td>
                                    <div><?= esc($row['nama_pimpinan_upps'] ?: '-'); ?></div>
                                    <div class="table-subtext">NUPTK: <?= esc($row['nutpk'] ?: '-'); ?></div>
                                </td>
                                <td class="cell-center">
                                    <?php if (has_role(['admin', 'lpm'])): ?>
                                        <div class="action-group justify-content-center">
                                            <a href="<?= base_url('/upps/' . $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit UPPS" aria-label="Edit UPPS">
                                                <i class="bi bi-pencil-fill"></i>
                                                <span class="visually-hidden">Edit</span>
                                            </a>
                                            <form action="<?= base_url('/upps/' . $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus data UPPS ini?">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus UPPS" aria-label="Hapus UPPS">
                                                    <i class="bi bi-trash-fill"></i>
                                                    <span class="visually-hidden">Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data UPPS.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
