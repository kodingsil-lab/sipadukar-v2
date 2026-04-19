<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header">
    <h3 class="page-title">Manajemen Prodi Akreditasi</h3>
    <p class="page-subtitle">Pilih Program Studi yang aktif untuk persiapan dan pengelolaan dokumen akreditasi.</p>
</div>

<div class="card card-clean">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-clean table-strong align-middle">
                <thead>
                    <tr>
                        <th>Program Studi</th>
                        <th>UPPS</th>
                        <th class="cell-center">Lembaga Akreditasi</th>
                        <th class="cell-center">Status</th>
                        <th class="cell-center" width="190">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($programStudiList)): ?>
                        <?php foreach ($programStudiList as $row): ?>
                            <?php $isAktif = (int) ($row['is_aktif_akreditasi'] ?? 0) === 1; ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['nama_program_studi']); ?></div>
                                    <div class="table-subtext"><?= esc($row['kode_program_studi_pddikti'] ?: '-'); ?><?= $row['jenjang'] ? ' / ' . esc($row['jenjang']) : ''; ?></div>
                                </td>
                                <td>
                                    <div><?= esc($row['nama_upps'] ?: '-'); ?></div>
                                    <div class="table-subtext"><?= esc($row['nama_singkatan_upps'] ?: '-'); ?></div>
                                </td>
                                <td class="cell-center"><?= esc($row['lembaga_akreditasi'] ?: '-'); ?></td>
                                <td class="cell-center">
                                    <span class="badge <?= $isAktif ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?= $isAktif ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td class="cell-center">
                                    <?php if (has_role('admin')): ?>
                                        <form action="<?= base_url('/pengaturan/manajemen-prodi-akreditasi/' . $row['id'] . '/toggle'); ?>" method="post">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-xs <?= $isAktif ? 'btn-outline-secondary' : 'btn-primary'; ?>">
                                                <?= $isAktif ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Hanya admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data Program Studi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

