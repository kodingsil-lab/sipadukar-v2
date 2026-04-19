<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Instrumen</h3>
        <p class="page-subtitle">Daftar instrumen akreditasi, panduan, dan dokumen pendukung asesmen</p>
    </div>

    <?php if (has_role(['admin', 'lpm'])): ?>
        <a href="<?= base_url('/instrumen/create'); ?>" class="btn btn-primary">
            Tambah Instrumen
        </a>
    <?php endif; ?>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle">
                <thead>
                    <tr>
                        <th width="60" class="cell-center">No</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Versi</th>
                        <th>Tanggal Berlaku</th>
                        <th>File</th>
                        <th class="cell-center">Status</th>
                        <th width="340" class="cell-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($instrumenList)): ?>
                        <?php foreach ($instrumenList as $i => $row): ?>
                            <tr>
                                <td class="cell-center"><?= $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['judul']); ?></div>
                                    <div class="table-subtext"><?= esc($row['deskripsi'] ?: '-'); ?></div>
                                </td>
                                <td><?= esc($row['kategori'] ?: '-'); ?></td>
                                <td><?= esc($row['versi_instrumen'] ?: '-'); ?></td>
                                <td><?= esc($row['tanggal_berlaku'] ?: '-'); ?></td>
                                <td>
                                    <div class="table-title"><?= esc($row['nama_file'] ?: '-'); ?></div>
                                    <div class="table-subtext"><?= esc(format_ukuran_file($row['ukuran_file'] ?? null)); ?></div>
                                </td>
                                <td class="cell-center">
                                    <?php if ((int) $row['is_aktif'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <div class="action-group">
                                        <?php if (! empty($row['path_file'])): ?>
                                            <?php if (bisa_preview_file($row['ekstensi_file'] ?? null)): ?>
                                                <a href="<?= base_url('/file/instrumen/' . $row['id'] . '/preview'); ?>" target="_blank" class="btn btn-xs btn-info text-white icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Preview File" aria-label="Preview File">
                                                    <i class="bi bi-eye-fill"></i>
                                                    <span class="visually-hidden">Preview</span>
                                                </a>
                                            <?php endif; ?>

                                            <a href="<?= base_url('/file/instrumen/' . $row['id'] . '/download'); ?>" class="btn btn-xs btn-success icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Download File" aria-label="Download File">
                                                <i class="bi bi-download"></i>
                                                <span class="visually-hidden">Download</span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (has_role(['admin', 'lpm'])): ?>
                                            <a href="<?= base_url('/instrumen/' . $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Instrumen" aria-label="Edit Instrumen">
                                                <i class="bi bi-pencil-fill"></i>
                                                <span class="visually-hidden">Edit</span>
                                            </a>

                                            <form action="<?= base_url('/instrumen/' . $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus instrumen ini?">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Instrumen" aria-label="Hapus Instrumen">
                                                    <i class="bi bi-trash-fill"></i>
                                                    <span class="visually-hidden">Hapus</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data instrumen.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
