<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/upps/' . $data['id'] . '/update')
    : base_url('/upps/store');
?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="page-title mb-1"><?= esc($title); ?></h3>
                    <p class="page-subtitle mb-0">Lengkapi identitas unit pengelola program studi (UPPS).</p>
                </div>

                <form action="<?= $action; ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Identitas UPPS</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama UPPS <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_upps" class="form-control" value="<?= esc(old('nama_upps', $data['nama_upps'] ?? '', false)); ?>" placeholder="Isi nama lengkap UPPS" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Singkatan</label>
                                    <input type="text" name="nama_singkatan" class="form-control" value="<?= esc(old('nama_singkatan', $data['nama_singkatan'] ?? '', false)); ?>" placeholder="Contoh: FKIP atau FTI">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Jenis Unit</label>
                                    <?php $jenisOld = old('jenis_unit', $data['jenis_unit'] ?? ''); ?>
                                    <select name="jenis_unit" class="form-select">
                                        <option value="">Pilih jenis unit</option>
                                        <option value="Fakultas" <?= $jenisOld === 'Fakultas' ? 'selected' : ''; ?>>Fakultas</option>
                                        <option value="Jurusan" <?= $jenisOld === 'Jurusan' ? 'selected' : ''; ?>>Jurusan</option>
                                        <option value="Pascasarjana" <?= $jenisOld === 'Pascasarjana' ? 'selected' : ''; ?>>Pascasarjana</option>
                                        <option value="Sekolah Tinggi" <?= $jenisOld === 'Sekolah Tinggi' ? 'selected' : ''; ?>>Sekolah Tinggi</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Pimpinan UPPS</label>
                                    <input type="text" name="nama_pimpinan_upps" class="form-control" value="<?= esc(old('nama_pimpinan_upps', $data['nama_pimpinan_upps'] ?? '', false)); ?>" placeholder="Isi nama pimpinan UPPS">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">NUTPK</label>
                                    <input type="text" name="nutpk" class="form-control" value="<?= esc(old('nutpk', $data['nutpk'] ?? '', false)); ?>" placeholder="Isi nomor unik tenaga pendidik (NUTPK)">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email Resmi UPPS</label>
                                    <input type="email" name="email_resmi_upps" class="form-control" value="<?= esc(old('email_resmi_upps', $data['email_resmi_upps'] ?? '', false)); ?>" placeholder="Contoh: upps@kampus.ac.id">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" name="nomor_telepon" class="form-control" value="<?= esc(old('nomor_telepon', $data['nomor_telepon'] ?? '', false)); ?>" placeholder="Contoh: (021) 555-1234">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/upps'); ?>" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Update UPPS' : 'Simpan UPPS'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
