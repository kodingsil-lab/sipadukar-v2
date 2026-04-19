<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="container">
    <h1>Upload Dokumen Baru</h1>

    <form action="<?= base_url('/documents/store'); ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <div class="mb-3">
            <label for="title" class="form-label">Judul Dokumen</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">File Dokumen</label>
            <input type="file" name="file" id="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
            <div class="form-text">Maksimal 10MB. Format: PDF, DOC, DOCX, JPG, JPEG, PNG.</div>
        </div>

        <div class="mb-3">
            <label for="prodi_id" class="form-label">Program Studi (Opsional)</label>
            <input type="number" name="prodi_id" id="prodi_id" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="<?= base_url('/documents'); ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection(); ?>