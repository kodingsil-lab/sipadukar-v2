<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/program-studi/' . $data['id'] . '/update')
    : base_url('/program-studi/store');
$uppsSelected = old('upps_id', $data['upps_id'] ?? '');
$lembagaAkreditasiList = $lembagaAkreditasiList ?? [];
?>

<div class="row justify-content-center">
    <div class="col-xl-11">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="page-title mb-1"><?= esc($title); ?></h3>
                    <p class="page-subtitle mb-0">Lengkapi identitas Program Studi dan data akreditasinya.</p>
                </div>

                <form action="<?= $action; ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Unit Pengelola Program Studi (UPPS)</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Pilih UPPS <span class="text-danger">*</span></label>
                                    <select name="upps_id" class="form-select" required>
                                        <option value="">Pilih UPPS</option>
                                        <?php foreach (($uppsList ?? []) as $upps): ?>
                                            <option value="<?= (int) $upps['id']; ?>" <?= (string) $uppsSelected === (string) $upps['id'] ? 'selected' : ''; ?>>
                                                <?= esc($upps['nama_upps']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Identitas Unit Program Studi</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Program Studi <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_program_studi" class="form-control" value="<?= esc(old('nama_program_studi', $data['nama_program_studi'] ?? '', false)); ?>" placeholder="Isi nama lengkap Program Studi" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Singkatan</label>
                                    <input type="text" name="nama_singkatan" class="form-control" value="<?= esc(old('nama_singkatan', $data['nama_singkatan'] ?? '', false)); ?>" placeholder="Contoh: PGSD">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Kode Program Studi (PDDikti)</label>
                                    <input type="text" name="kode_program_studi_pddikti" class="form-control" value="<?= esc(old('kode_program_studi_pddikti', $data['kode_program_studi_pddikti'] ?? '', false)); ?>" placeholder="Isi kode Prodi dari PDDikti">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Jenjang</label>
                                    <?php $jenjangOld = old('jenjang', $data['jenjang'] ?? ''); ?>
                                    <select name="jenjang" class="form-select">
                                        <option value="">Pilih jenjang</option>
                                        <option value="D3" <?= $jenjangOld === 'D3' ? 'selected' : ''; ?>>D3</option>
                                        <option value="S1" <?= $jenjangOld === 'S1' ? 'selected' : ''; ?>>S1</option>
                                        <option value="S2" <?= $jenjangOld === 'S2' ? 'selected' : ''; ?>>S2</option>
                                        <option value="S3" <?= $jenjangOld === 'S3' ? 'selected' : ''; ?>>S3</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <hr class="form-separator">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Website Resmi</label>
                                    <input type="url" name="website_resmi" class="form-control" value="<?= esc(old('website_resmi', $data['website_resmi'] ?? '', false)); ?>" placeholder="Contoh: https://prodi.kampus.ac.id">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email Resmi Program Studi</label>
                                    <input type="email" name="email_resmi_program_studi" class="form-control" value="<?= esc(old('email_resmi_program_studi', $data['email_resmi_program_studi'] ?? '', false)); ?>" placeholder="Contoh: prodi@kampus.ac.id">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" name="nomor_telepon" class="form-control" value="<?= esc(old('nomor_telepon', $data['nomor_telepon'] ?? '', false)); ?>" placeholder="Contoh: (021) 555-1234">
                                </div>

                                <div class="col-12">
                                    <hr class="form-separator">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Ketua Program Studi</label>
                                    <input type="text" name="nama_ketua_program_studi" class="form-control" value="<?= esc(old('nama_ketua_program_studi', $data['nama_ketua_program_studi'] ?? '', false)); ?>" placeholder="Isi nama Ketua Program Studi">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">NUPTK</label>
                                    <input type="text" name="nuptk" class="form-control" value="<?= esc(old('nuptk', $data['nuptk'] ?? '', false)); ?>" placeholder="Isi NUPTK Ketua Program Studi">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Data Akreditasi Program Studi</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Status Akreditasi</label>
                                    <?php $statusAkreditasiOld = old('status_akreditasi', $data['status_akreditasi'] ?? ''); ?>
                                    <select name="status_akreditasi" class="form-select">
                                        <option value="">Pilih status akreditasi</option>
                                        <option value="Baik" <?= $statusAkreditasiOld === 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                        <option value="Baik Sekali" <?= $statusAkreditasiOld === 'Baik Sekali' ? 'selected' : ''; ?>>Baik Sekali</option>
                                        <option value="Unggul" <?= $statusAkreditasiOld === 'Unggul' ? 'selected' : ''; ?>>Unggul</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nomor SK Akreditasi</label>
                                    <input type="text" name="nomor_sk_akreditasi" class="form-control" value="<?= esc(old('nomor_sk_akreditasi', $data['nomor_sk_akreditasi'] ?? '', false)); ?>" placeholder="Isi nomor SK akreditasi Program Studi">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal SK</label>
                                    <input type="date" name="tanggal_sk" class="form-control" value="<?= esc(old('tanggal_sk', $data['tanggal_sk'] ?? '', false)); ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal Mulai Berlaku</label>
                                    <input type="date" name="tanggal_mulai_berlaku" class="form-control" value="<?= esc(old('tanggal_mulai_berlaku', $data['tanggal_mulai_berlaku'] ?? '', false)); ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal Berakhir</label>
                                    <input type="date" name="tanggal_berakhir" class="form-control" value="<?= esc(old('tanggal_berakhir', $data['tanggal_berakhir'] ?? '', false)); ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Lembaga Akreditasi</label>
                                    <?php $lembagaOld = old('lembaga_akreditasi', $data['lembaga_akreditasi'] ?? ''); ?>
                                    <select name="lembaga_akreditasi" class="form-select">
                                        <option value="">Pilih lembaga akreditasi</option>
                                        <?php foreach ($lembagaAkreditasiList as $lembaga): ?>
                                            <?php $singkatan = trim((string) ($lembaga['nama_singkatan'] ?? '')); ?>
                                            <?php if ($singkatan === '') { continue; } ?>
                                            <option value="<?= esc($singkatan); ?>" <?= $lembagaOld === $singkatan ? 'selected' : ''; ?>>
                                                <?= esc($lembaga['nama_lembaga_akreditasi']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/program-studi'); ?>" class="btn btn-light border">Kembali</a>
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Program Studi' : 'Simpan Program Studi'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
