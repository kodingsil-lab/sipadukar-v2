<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$programStudiQuerySuffix = $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
$action = $isEdit
    ? base_url('/sub-bagian/' . $data['id'] . '/update') . $programStudiQuerySuffix
    : base_url('/kriteria/' . $kriteria['id'] . '/sub-bagian/store') . $programStudiQuerySuffix;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="mb-1"><?= esc($title); ?></h3>
                    <p class="text-muted mb-0">
                        <?= esc($kriteria['kode']); ?> - <?= esc($kriteria['nama_kriteria']); ?>
                    </p>
                </div>

                <form action="<?= $action; ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="program_studi_id" value="<?= (int) $selectedProgramStudiId; ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Sub Bagian</label>
                        <input
                            type="text"
                            name="nama_sub_bagian"
                            class="form-control"
                            value="<?= esc(old('nama_sub_bagian', $data['nama_sub_bagian'] ?? '', false)); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea
                            id="deskripsi-sub-bagian"
                            name="deskripsi"
                            class="form-control"
                            rows="4"
                        ><?= esc(old('deskripsi', $data['deskripsi'] ?? '', false)); ?></textarea>
                        <div class="small text-muted mt-1 d-flex justify-content-between align-items-center">
                            <span>Maksimal 30 kata.</span>
                            <span id="deskripsi-word-counter">0/30 kata</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Urutan</label>
                            <input
                                type="number"
                                name="urutan"
                                class="form-control"
                                value="<?= esc(old('urutan', $data['urutan'] ?? 1, false)); ?>"
                                min="1"
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_aktif" class="form-select" required>
                                <option value="1" <?= old('is_aktif', $data['is_aktif'] ?? 1) == 1 ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?= old('is_aktif', $data['is_aktif'] ?? 1) == 0 ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/kriteria/' . $kriteria['id']) . $programStudiQuerySuffix; ?>" class="btn btn-light border">
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Update Sub Bagian' : 'Simpan Sub Bagian'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var textarea = document.getElementById('deskripsi-sub-bagian');
    var counter = document.getElementById('deskripsi-word-counter');
    if (!textarea || !counter) return;

    function countWords(text) {
        var normalized = (text || '').trim();
        if (normalized === '') return 0;
        return normalized.split(/\s+/).filter(Boolean).length;
    }

    function updateCounter() {
        var total = countWords(textarea.value);
        counter.textContent = total + '/30 kata';
        if (total > 30) {
            counter.classList.add('text-danger');
            textarea.setCustomValidity('Deskripsi maksimal 30 kata.');
        } else {
            counter.classList.remove('text-danger');
            textarea.setCustomValidity('');
        }
    }

    textarea.addEventListener('input', updateCounter);
    updateCounter();
})();
</script>

<?= $this->endSection(); ?>
