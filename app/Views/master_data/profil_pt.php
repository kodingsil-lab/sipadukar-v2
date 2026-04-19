<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$profil = $profil ?? [];
$lembagaAkreditasiList = $lembagaAkreditasiList ?? [];
?>

<div class="row justify-content-center profil-pt-page">
    <div class="col-xl-11">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="page-title mb-1">Profil PT</h3>
                    <p class="page-subtitle mb-0">Lengkapi identitas dan data akreditasi perguruan tinggi</p>
                </div>

                <form action="<?= base_url('/profil-pt/update'); ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field(); ?>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Identitas Perguruan Tinggi</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Perguruan Tinggi <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_pt" class="form-control" value="<?= esc(old('nama_pt', $profil['nama_pt'] ?? '', false)); ?>" placeholder="Isi nama resmi perguruan tinggi" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Singkatan</label>
                                    <input type="text" name="nama_singkatan" class="form-control" value="<?= esc(old('nama_singkatan', $profil['nama_singkatan'] ?? '', false)); ?>" placeholder="Contoh: UNSP atau UNISAP">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Status PT</label>
                                    <select name="status_pt" class="form-select">
                                        <option value="">Pilih status perguruan tinggi</option>
                                        <option value="PTN" <?= old('status_pt', $profil['status_pt'] ?? '') === 'PTN' ? 'selected' : ''; ?>>PTN</option>
                                        <option value="PTS" <?= old('status_pt', $profil['status_pt'] ?? '') === 'PTS' ? 'selected' : ''; ?>>PTS</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Kode PT (PDDikti)</label>
                                    <input type="text" name="kode_pt_pddikti" class="form-control" value="<?= esc(old('kode_pt_pddikti', $profil['kode_pt_pddikti'] ?? '', false)); ?>" placeholder="Isi kode PT dari PDDikti">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tahun Berdiri</label>
                                    <input type="text" name="tahun_berdiri" class="form-control" value="<?= esc(old('tahun_berdiri', ($profil['tahun_berdiri'] ?? null) ?: '', false)); ?>" placeholder="Contoh: 2001">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Badan Penyelenggara</label>
                                    <input type="text" name="badan_penyelenggara" class="form-control" value="<?= esc(old('badan_penyelenggara', $profil['badan_penyelenggara'] ?? '', false)); ?>" placeholder="Isi nama yayasan/kementerian penyelenggara">
                                </div>

                                <div class="col-12">
                                    <hr class="form-separator">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea name="alamat_lengkap" class="form-control" rows="3" placeholder="Isi alamat lengkap perguruan tinggi"><?= esc(old('alamat_lengkap', $profil['alamat_lengkap'] ?? '', false)); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <hr class="form-separator">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Website Resmi</label>
                                    <input type="url" name="website_resmi" class="form-control" value="<?= esc(old('website_resmi', $profil['website_resmi'] ?? '', false)); ?>" placeholder="Contoh: https://www.namapt.ac.id">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email Resmi PT</label>
                                    <input type="email" name="email_resmi_pt" class="form-control" value="<?= esc(old('email_resmi_pt', $profil['email_resmi_pt'] ?? '', false)); ?>" placeholder="Contoh: info@namapt.ac.id">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" name="nomor_telepon" class="form-control" value="<?= esc(old('nomor_telepon', $profil['nomor_telepon'] ?? '', false)); ?>" placeholder="Contoh: (021) 555-1234">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-clean mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Data Akreditasi Institusi</h5>
                            <div class="row form-grid">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Lembaga Akreditasi</label>
                                    <?php $lembagaOld = old('lembaga_akreditasi', $profil['lembaga_akreditasi'] ?? ''); ?>
                                    <select name="lembaga_akreditasi" class="form-select">
                                        <option value="">Pilih lembaga akreditasi</option>
                                        <?php foreach ($lembagaAkreditasiList as $lembaga): ?>
                                            <?php $singkatan = trim((string) ($lembaga['nama_singkatan'] ?? '')); ?>
                                            <?php if ($singkatan === '') { continue; } ?>
                                            <option value="<?= esc($singkatan); ?>" <?= $lembagaOld === $singkatan ? 'selected' : ''; ?>>
                                                <?= esc($singkatan); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Status Akreditasi PT</label>
                                    <select name="status_akreditasi_pt" class="form-select">
                                        <option value="">Pilih status akreditasi institusi</option>
                                        <option value="Baik" <?= old('status_akreditasi_pt', $profil['status_akreditasi_pt'] ?? '') === 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                        <option value="Baik Sekali" <?= old('status_akreditasi_pt', $profil['status_akreditasi_pt'] ?? '') === 'Baik Sekali' ? 'selected' : ''; ?>>Baik Sekali</option>
                                        <option value="Unggul" <?= old('status_akreditasi_pt', $profil['status_akreditasi_pt'] ?? '') === 'Unggul' ? 'selected' : ''; ?>>Unggul</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nomor SK Akreditasi</label>
                                    <input type="text" name="nomor_sk_akreditasi" class="form-control" value="<?= esc(old('nomor_sk_akreditasi', $profil['nomor_sk_akreditasi'] ?? '', false)); ?>" placeholder="Isi nomor SK akreditasi institusi">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal SK</label>
                                    <input type="date" name="tanggal_sk" class="form-control" value="<?= esc(old('tanggal_sk', $profil['tanggal_sk'] ?? '', false)); ?>" placeholder="Pilih tanggal SK diterbitkan">
                                </div>

                                <div class="col-12">
                                    <hr class="form-separator">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal Berlaku Akreditasi</label>
                                    <input type="date" name="tanggal_berlaku_akreditasi" class="form-control" value="<?= esc(old('tanggal_berlaku_akreditasi', $profil['tanggal_berlaku_akreditasi'] ?? '', false)); ?>" placeholder="Pilih tanggal mulai berlaku">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal Berakhir Akreditasi</label>
                                    <input type="date" name="tanggal_berakhir_akreditasi" class="form-control" value="<?= esc(old('tanggal_berakhir_akreditasi', $profil['tanggal_berakhir_akreditasi'] ?? '', false)); ?>" placeholder="Pilih tanggal berakhir">
                                </div>

                                <div class="col-12">
                                    <hr class="form-separator">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Unggah SK Akreditasi PT</label>
                                    <input type="file" name="file_sk_akreditasi" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                    <?php if (! empty($profil['file_sk_akreditasi_path'])): ?>
                                        <div class="small mt-1">File saat ini:
                                            <a href="<?= base_url('/file/profil-pt/sk/download'); ?>" target="_blank" rel="noopener noreferrer">Lihat File</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Unggah Sertifikat Akreditasi PT</label>
                                    <input type="file" name="file_sertifikat_akreditasi" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                    <?php if (! empty($profil['file_sertifikat_akreditasi_path'])): ?>
                                        <div class="small mt-1">File saat ini:
                                            <a href="<?= base_url('/file/profil-pt/sertifikat/download'); ?>" target="_blank" rel="noopener noreferrer">Lihat File</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Simpan Profil PT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
