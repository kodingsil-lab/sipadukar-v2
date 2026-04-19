<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title">Dokumen Kriteria</h3>
        <p class="page-subtitle">Daftar 9 kriteria akreditasi dan jumlah sub bagian aktif.</p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($kriteriaList as $item): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card card-clean h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge badge-soft-primary"><?= esc($item['kode']); ?></span>
                        <span class="text-muted small">Urutan <?= esc($item['urutan']); ?></span>
                    </div>

                    <h5 class="mb-2"><?= esc($item['nama_kriteria']); ?></h5>
                    <p class="text-muted small mb-4" style="min-height: 58px;">
                        <?= esc($item['deskripsi'] ?? '-'); ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Sub Bagian</div>
                            <div class="fw-bold fs-5"><?= esc($item['jumlah_sub_bagian']); ?></div>
                        </div>
                        <a href="<?= base_url('/kriteria/' . $item['id']); ?>" class="btn btn-primary">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection(); ?>
