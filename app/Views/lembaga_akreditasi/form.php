<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/lembaga-akreditasi/' . $data['id'] . '/update')
    : base_url('/lembaga-akreditasi/store');
$logoLembagaPath = trim((string) ($data['logo_path'] ?? ''));
$logoLembagaUrl = $logoLembagaPath !== '' ? base_url('/' . ltrim($logoLembagaPath, '/')) : '';
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="page-title mb-1"><?= esc($title); ?></h3>
                    <p class="page-subtitle mb-0">Lengkapi identitas lembaga akreditasi.</p>
                </div>

                <form action="<?= $action; ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field(); ?>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Identitas Lembaga Akreditasi</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Lembaga Akreditasi <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_lembaga_akreditasi" class="form-control" value="<?= esc(old('nama_lembaga_akreditasi', $data['nama_lembaga_akreditasi'] ?? '', false)); ?>" placeholder="Contoh: Badan Akreditasi Nasional Perguruan Tinggi" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Singkatan</label>
                                    <input type="text" name="nama_singkatan" class="form-control" value="<?= esc(old('nama_singkatan', $data['nama_singkatan'] ?? '', false)); ?>" placeholder="Contoh: BAN-PT atau LAMDIK">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Alamat Website</label>
                                    <input type="url" name="alamat_website" class="form-control" value="<?= esc(old('alamat_website', $data['alamat_website'] ?? '', false)); ?>" placeholder="Contoh: https://banpt.or.id">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Status</label>
                                    <?php $statusOld = old('is_aktif', $data['is_aktif'] ?? 1); ?>
                                    <select name="is_aktif" class="form-select" required>
                                        <option value="1" <?= (string) $statusOld === '1' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="0" <?= (string) $statusOld === '0' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Logo Lembaga Akreditasi</label>
                                    <input type="file" name="logo_lembaga" class="form-control" accept=".png,.jpg,.jpeg,.webp,.svg">
                                    <div class="small text-muted mt-1">Opsional. Format: PNG/JPG/WEBP/SVG. Maksimal 2MB.</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Preview Logo</label>
                                    <div>
                                        <?php if ($logoLembagaUrl !== ''): ?>
                                            <img src="<?= esc($logoLembagaUrl); ?>" alt="Logo Lembaga Akreditasi" style="width:64px;height:64px;object-fit:contain;border-radius:10px;border:1px solid #dbe3ef;background:#fff;padding:6px;">
                                            <div class="small text-muted mt-1">Upload logo baru untuk mengganti logo saat ini.</div>
                                        <?php else: ?>
                                            <span class="small text-muted">Belum ada logo lembaga.</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/lembaga-akreditasi'); ?>" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Lembaga Akreditasi' : 'Simpan Lembaga Akreditasi'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
