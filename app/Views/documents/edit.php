<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="container">
    <h1>Edit Dokumen</h1>

    <form action="<?= base_url('/documents/update/' . $document['id']); ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <div class="mb-3">
            <label for="title" class="form-label">Judul Dokumen</label>
            <input type="text" name="title" id="title" class="form-control" value="<?= esc($document['title']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">File Dokumen (Opsional - biarkan kosong jika tidak ingin mengubah)</label>
            <input type="file" name="file" id="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <div class="form-text">Maksimal 10MB. Format: PDF, DOC, DOCX, JPG, JPEG, PNG.</div>
            <?php if ($document['file_path']): ?>
                <div class="mt-2">File saat ini: <a href="<?= base_url('/file/documents/' . $document['id'] . '/download'); ?>" target="_blank">Download</a></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="prodi_id" class="form-label">Program Studi (Opsional)</label>
            <input type="number" name="prodi_id" id="prodi_id" class="form-control" value="<?= esc($document['prodi_id'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="<?= base_url('/documents'); ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection(); ?>