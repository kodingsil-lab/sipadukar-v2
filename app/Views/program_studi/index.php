<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Program Studi</h3>
        <p class="page-subtitle">Lengkapi data identitas dan akreditasi Program Studi.</p>
    </div>

    <?php if (has_role(['admin', 'lpm'])): ?>
        <a href="<?= base_url('/program-studi/create'); ?>" class="btn btn-primary">Tambah Program Studi</a>
    <?php endif; ?>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle">
                <thead>
                    <tr>
                        <th>Nama Program Studi</th>
                        <th class="cell-center">Jenjang</th>
                        <th>UPPS</th>
                        <th>Ketua / NUPTK</th>
                        <th class="cell-center">Lembaga Akreditasi</th>
                        <th class="cell-center">Akreditasi</th>
                        <th width="140" class="cell-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($programStudiList)): ?>
                        <?php foreach ($programStudiList as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['nama_program_studi']); ?></div>
                                    <div class="table-subtext"><?= esc($row['kode_program_studi_pddikti'] ?: '-'); ?></div>
                                </td>
                                <td class="cell-center"><?= esc($row['jenjang'] ?: '-'); ?></td>
                                <td>
                                    <div><?= esc($row['nama_upps'] ?: '-'); ?></div>
                                    <div class="table-subtext"><?= esc($row['nama_singkatan_upps'] ?: '-'); ?></div>
                                </td>
                                <td>
                                    <div><?= esc($row['nama_ketua_program_studi'] ?: '-'); ?></div>
                                    <div class="table-subtext">NUPTK: <?= esc($row['nuptk'] ?: '-'); ?></div>
                                </td>
                                <td class="cell-center"><?= esc($row['lembaga_akreditasi'] ?: '-'); ?></td>
                                <td class="cell-center">
                                    <?php if (! empty($row['status_akreditasi'])): ?>
                                        <?php
                                        $badgeClass = match ($row['status_akreditasi']) {
                                            'Unggul' => 'bg-success',
                                            'Baik Sekali' => 'bg-warning',
                                            'Baik' => 'bg-secondary',
                                            default => 'bg-secondary',
                                        };
                                        ?>
                                        <span class="badge <?= esc($badgeClass); ?>"><?= esc($row['status_akreditasi']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-center">
                                    <?php if (has_role(['admin', 'lpm'])): ?>
                                        <div class="action-group justify-content-center">
                                            <a href="<?= base_url('/program-studi/' . $row['id'] . '/edit'); ?>" class="btn btn-xs btn-warning icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Program Studi" aria-label="Edit Program Studi">
                                                <i class="bi bi-pencil-fill"></i>
                                                <span class="visually-hidden">Edit</span>
                                            </a>
                                            <form action="<?= base_url('/program-studi/' . $row['id'] . '/delete'); ?>" method="post" class="js-confirm-submit" data-confirm="Yakin hapus data Program Studi ini?">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-danger icon-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Program Studi" aria-label="Hapus Program Studi">
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
                            <td colspan="7" class="text-center text-muted">Belum ada data Program Studi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
