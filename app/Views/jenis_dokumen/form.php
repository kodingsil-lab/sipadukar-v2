<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/jenis-dokumen/' . (int) $data['id'] . '/update')
    : base_url('/jenis-dokumen/store');
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="page-title mb-1"><?= esc($title); ?></h3>
                    <p class="page-subtitle mb-0">Isi data jenis dokumen yang akan muncul di form upload dokumen.</p>
                </div>

                <form action="<?= $action; ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="row form-grid">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Nama Jenis Dokumen <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="nama_jenis_dokumen"
                                class="form-control"
                                value="<?= esc(old('nama_jenis_dokumen', $data['nama_jenis_dokumen'] ?? '', false)); ?>"
                                placeholder="Contoh: SK / Laporan / Pedoman / SOP"
                                required
                            >
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <?php $statusOld = old('is_aktif', $data['is_aktif'] ?? 1); ?>
                            <select name="is_aktif" class="form-select" required>
                                <option value="1" <?= (string) $statusOld === '1' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?= (string) $statusOld === '0' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= base_url('/jenis-dokumen'); ?>" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Jenis Dokumen' : 'Simpan Jenis Dokumen'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

