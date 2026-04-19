<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/peraturan/' . $data['id'] . '/update')
    : base_url('/peraturan/store');
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="mb-1"><?= esc($title); ?></h3>
                    <p class="text-muted mb-0">Kelola dokumen peraturan dan regulasi akreditasi</p>
                </div>

                <form action="<?= $action; ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field(); ?>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="judul" class="form-control" value="<?= esc(old('judul', $data['judul'] ?? '', false)); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control" value="<?= esc(old('kategori', $data['kategori'] ?? '', false)); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Peraturan</label>
                            <input type="text" name="nomor_peraturan" class="form-control" value="<?= esc(old('nomor_peraturan', $data['nomor_peraturan'] ?? '', false)); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="tahun" class="form-control" value="<?= esc(old('tahun', $data['tahun'] ?? '', false)); ?>" min="2000" max="2100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= esc(old('deskripsi', $data['deskripsi'] ?? '', false)); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Terbit</label>
                            <input type="date" name="tanggal_terbit" class="form-control" value="<?= esc(old('tanggal_terbit', $data['tanggal_terbit'] ?? '', false)); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_aktif" class="form-select" required>
                                <option value="1" <?= old('is_aktif', $data['is_aktif'] ?? 1) == 1 ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?= old('is_aktif', $data['is_aktif'] ?? 1) == 0 ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $isEdit ? 'Upload File Baru (opsional)' : 'Upload File Peraturan'; ?></label>
                        <input type="file" name="file_peraturan" class="form-control" <?= $isEdit ? '' : 'required'; ?>>
                        <div class="form-text">Format: pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png, zip, rar. Maksimal 10 MB.</div>

                        <?php if ($isEdit && ! empty($data['nama_file'])): ?>
                            <div class="mt-2 small text-muted">
                                File saat ini: <strong><?= esc($data['nama_file']); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/peraturan'); ?>" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Update Peraturan' : 'Simpan Peraturan'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
