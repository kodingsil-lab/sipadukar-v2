<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="page-title mb-1">Pengaturan Aplikasi</h3>
                    <p class="page-subtitle mb-0">Atur logo perguruan tinggi, favicon, dan zona waktu aplikasi.</p>
                </div>

                <form action="<?= base_url('/pengaturan/aplikasi/update'); ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field(); ?>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label">Logo Perguruan Tinggi</label>
                            <input type="file" name="logo_pt" class="form-control" accept=".png,.jpg,.jpeg,.webp,.svg">
                            <div class="small text-muted mt-1">Dipakai untuk logo login, logo header, dan logo informasi institusi. Maks 2MB.</div>
                            <div class="mt-2">
                                <?php $logoHeader = app_logo_header_url(); ?>
                                <?php if ($logoHeader !== ''): ?>
                                    <img src="<?= esc($logoHeader); ?>" alt="Logo Perguruan Tinggi" style="width:64px;height:64px;object-fit:contain;object-position:center;padding:4px;background:#fff;border-radius:12px;border:1px solid #dbe3ef;">
                                <?php else: ?>
                                    <span class="small text-muted">Belum ada logo perguruan tinggi.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept=".png,.ico,.webp,.svg">
                            <div class="small text-muted mt-1">Format PNG/WEBP akan otomatis dioptimasi ke canvas transparan 64x64 (tanpa terpotong) agar tampil rapi di tab browser. Maks 1MB.</div>
                            <div class="mt-2">
                                <?php $favicon = app_favicon_url(); ?>
                                <?php if ($favicon !== ''): ?>
                                    <img src="<?= esc($favicon); ?>" alt="Favicon" style="width:40px;height:40px;object-fit:contain;object-position:center;padding:3px;background:#fff;border-radius:8px;border:1px solid #dbe3ef;">
                                <?php else: ?>
                                    <span class="small text-muted">Belum ada favicon.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">Zona Waktu</label>
                            <select name="app_timezone" class="form-select" required>
                                <?php foreach (($timezones ?? []) as $tz): ?>
                                    <option value="<?= esc($tz); ?>" <?= ($currentTz ?? 'Asia/Jakarta') === $tz ? 'selected' : ''; ?>>
                                        <?= esc($tz); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="small text-muted mt-1">Dipakai untuk tanggal/jam di seluruh sistem.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
