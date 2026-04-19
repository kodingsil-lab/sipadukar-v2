<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h3 class="mb-1">Profil Saya</h3>
                        <p class="text-muted mb-0">Informasi akun yang sedang digunakan untuk login.</p>
                    </div>
                    <a href="<?= base_url('/profil/edit'); ?>" class="btn btn-primary">
                        Edit Profil
                    </a>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Nama Lengkap</div>
                            <div class="fw-semibold"><?= esc($user['nama_lengkap'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">NUPTK/NIDN</div>
                            <div class="fw-semibold"><?= esc($user['nip'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Username</div>
                            <div class="fw-semibold"><?= esc($user['username'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Email</div>
                            <div class="fw-semibold"><?= esc($user['email'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Unit Kerja</div>
                            <div class="fw-semibold"><?= esc($user['unit_kerja'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Jabatan</div>
                            <div class="fw-semibold"><?= esc($user['jabatan'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3 bg-light-subtle">
                            <div class="small text-muted mb-2">Role</div>
                            <?php if (! empty($user['roles'])): ?>
                                <div class="action-group">
                                    <?php foreach ($user['roles'] as $role): ?>
                                        <span class="badge badge-soft-primary"><?= esc($role['nama_role']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
