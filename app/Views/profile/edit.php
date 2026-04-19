<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="mb-1">Edit Profil Saya</h3>
                    <p class="text-muted mb-0">Ubah data personal akun Anda. Hak akses dan unit kerja tetap diatur Admin.</p>
                </div>

                <form action="<?= base_url('/profil/update'); ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= esc(old('nama_lengkap', $user['nama_lengkap'] ?? '', false)); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NUPTK/NIDN</label>
                            <input type="text" name="nip" class="form-control" value="<?= esc(old('nip', $user['nip'] ?? '', false)); ?>" placeholder="Isi NUPTK atau NIDN (opsional)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= esc(old('username', $user['username'] ?? '', false)); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'] ?? '', false)); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="<?= esc(old('jabatan', $user['jabatan'] ?? '', false)); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru (opsional)</label>
                            <input type="password" name="password" class="form-control">
                            <div class="form-text">Kosongkan jika password tidak ingin diubah.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <input type="text" class="form-control" value="<?= esc($user['unit_kerja'] ?? '-'); ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= esc($user['role_names'] ?? '-'); ?>" readonly>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/profil'); ?>" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
