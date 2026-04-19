<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Lembaga Akreditasi</h3>
        <p class="page-subtitle">Kelola data lembaga akreditasi untuk sistem.</p>
    </div>

    <?php if (has_role(['admin', 'lpm'])): ?>
        <a href="<?= base_url('/lembaga-akreditasi/create'); ?>" class="btn btn-primary">Tambah Lembaga Akreditasi</a>
    <?php endif; ?>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle kriteria-doc-table">
                <thead>
                    <tr>
                        <th width="60" class="cell-center">No</th>
                        <th width="34%">Nama Lembaga Akreditasi</th>
                        <th width="16%">Singkatan Lembaga Akreditasi</th>
                        <th width="170" class="cell-center">Logo Lembaga Akreditasi</th>
                        <th width="28%">Alamat Website</th>
                        <th width="90" class="cell-center">Status</th>
                        <th width="220" class="cell-center col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($list)): ?>
                        <?php foreach ($list as $idx => $row): ?>
                            <tr>
                                <td class="cell-center"><?= (int) $idx + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['nama_lembaga_akreditasi']); ?></div>
                                </td>
                                <td>
                                    <span><?= esc($row['nama_singkatan'] ?: '-'); ?></span>
                                </td>
                                <td class="cell-center">
                                    <?php $logoUrl = app_asset_url($row['logo_path'] ?? ''); ?>
                                    <?php if ($logoUrl !== ''): ?>
                                        <div class="d-inline-flex align-items-center justify-content-center bg-white border rounded-3 p-1" style="width:56px;height:56px;">
                                            <img src="<?= esc($logoUrl); ?>" alt="Logo <?= esc($row['nama_singkatan'] ?: $row['nama_lembaga_akreditasi']); ?>" class="img-fluid" style="max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;">
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (! empty($row['alamat_website'])): ?>
                                        <a href="<?= esc($row['alamat_website']); ?>" target="_blank" rel="noopener noreferrer" class="text-dark text-decoration-none">
                                            <?= esc($row['alamat_website']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <?php if ((int) $row['is_aktif'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <?php if (has_role(['admin', 'lpm'])): ?>
                                        <div class="action-group justify-content-center">
                                            <a href="<?= base_url('/lembaga-akreditasi/' . $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Lembaga Akreditasi" aria-label="Edit Lembaga Akreditasi">
                                                <i class="bi bi-pencil-fill"></i>
                                                <span class="visually-hidden">Edit</span>
                                            </a>
                                            <form action="<?= base_url('/lembaga-akreditasi/' . $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus lembaga akreditasi ini?">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Lembaga Akreditasi" aria-label="Hapus Lembaga Akreditasi">
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
                            <td colspan="7" class="text-center text-muted">Belum ada data lembaga akreditasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
