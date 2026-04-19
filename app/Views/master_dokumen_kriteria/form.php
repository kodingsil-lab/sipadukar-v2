<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$actionUrl = $isEdit
    ? base_url('/master-dokumen-kriteria/' . (int) ($data['id'] ?? 0) . '/update')
    : base_url('/master-dokumen-kriteria/store');

$oldKriteriaId = (int) old('kriteria_id', (int) ($data['kriteria_id'] ?? 0));
$oldNamaSubBagian = old('nama_sub_bagian', (string) ($data['nama_sub_bagian'] ?? ''));
$oldUseSubBagian = (string) old('use_sub_bagian', trim((string) $oldNamaSubBagian) !== '' ? '1' : '0');
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="page-title"><?= esc($title ?? 'Form Master Dokumen Kriteria'); ?></h3>
        <p class="page-subtitle">Isi data master dokumen sebagai template generate ke Program Studi.</p>
    </div>
</div>

<div class="card card-clean">
    <div class="card-body">
        <form action="<?= $actionUrl; ?>" method="post" class="row g-3">
            <?= csrf_field(); ?>

            <div class="col-md-6">
                <label for="kriteria_id" class="form-label">Kriteria</label>
                <select id="kriteria_id" name="kriteria_id" class="form-select" required>
                    <option value="">Pilih Kriteria</option>
                    <?php foreach (($kriteriaList ?? []) as $kriteria): ?>
                        <?php $kriteriaId = (int) ($kriteria['id'] ?? 0); ?>
                        <option value="<?= $kriteriaId; ?>" <?= $oldKriteriaId === $kriteriaId ? 'selected' : ''; ?>>
                            <?= esc($kriteria['kode'] ?? '-'); ?> - <?= esc($kriteria['nama_kriteria'] ?? '-'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="use_sub_bagian" class="form-label">Gunakan Sub Bagian</label>
                <select id="use_sub_bagian" name="use_sub_bagian" class="form-select">
                    <option value="1" <?= $oldUseSubBagian === '1' ? 'selected' : ''; ?>>Ya</option>
                    <option value="0" <?= $oldUseSubBagian === '0' ? 'selected' : ''; ?>>Tidak</option>
                </select>
            </div>

            <div class="col-md-6" id="sub-bagian-wrap">
                <label for="nama_sub_bagian" class="form-label">Sub Bagian <span class="text-muted">(Opsional)</span></label>
                <input
                    type="text"
                    id="nama_sub_bagian"
                    name="nama_sub_bagian"
                    class="form-control"
                    value="<?= esc((string) $oldNamaSubBagian); ?>"
                    placeholder="Contoh: Ketepatan Rumusan Visi Keilmuan PS"
                >
                <div class="form-text">Jika dikosongkan, dokumen akan masuk ke Dokumen Utama (tanpa sub bagian khusus).</div>
            </div>

            <div class="col-md-12">
                <label for="judul_dokumen" class="form-label">Judul Dokumen</label>
                <input
                    type="text"
                    id="judul_dokumen"
                    name="judul_dokumen"
                    class="form-control"
                    value="<?= esc(old('judul_dokumen', (string) ($data['judul_dokumen'] ?? ''))); ?>"
                    required
                >
            </div>

            <div class="col-md-12">
                <label for="deskripsi" class="form-label">Deskripsi <span class="text-muted">(Opsional)</span></label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"><?= esc(old('deskripsi', (string) ($data['deskripsi'] ?? ''))); ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="jenis_dokumen" class="form-label">Jenis Dokumen <span class="text-muted">(Opsional)</span></label>
                <input
                    type="text"
                    id="jenis_dokumen"
                    name="jenis_dokumen"
                    class="form-control"
                    value="<?= esc(old('jenis_dokumen', (string) ($data['jenis_dokumen'] ?? ''))); ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="tahun_dokumen" class="form-label">Tahun Dokumen</label>
                <input
                    type="number"
                    id="tahun_dokumen"
                    name="tahun_dokumen"
                    class="form-control"
                    min="2000"
                    max="2100"
                    value="<?= esc(old('tahun_dokumen', (string) ($data['tahun_dokumen'] ?? date('Y')))); ?>"
                    required
                >
            </div>

            <div class="col-md-3">
                <label for="is_aktif" class="form-label">Status</label>
                <select id="is_aktif" name="is_aktif" class="form-select" required>
                    <?php $activeValue = (string) old('is_aktif', (string) ($data['is_aktif'] ?? '1')); ?>
                    <option value="1" <?= $activeValue === '1' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="0" <?= $activeValue === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-between">
                <a href="<?= base_url('/master-dokumen-kriteria'); ?>" class="btn btn-light border">Kembali</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan'; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var useSubBagian = document.getElementById('use_sub_bagian');
    var subBagianWrap = document.getElementById('sub-bagian-wrap');
    var subBagianInput = document.getElementById('nama_sub_bagian');

    if (!useSubBagian || !subBagianWrap || !subBagianInput) {
        return;
    }

    var syncSubBagianMode = function () {
        var active = useSubBagian.value === '1';
        subBagianWrap.style.display = active ? '' : 'none';
        subBagianInput.disabled = !active;
        subBagianInput.required = active;
    };

    useSubBagian.addEventListener('change', syncSubBagianMode);
    syncSubBagianMode();
});
</script>

<?= $this->endSection(); ?>
