<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>

<?php
$isEdit = $mode === 'edit';
$action = $isEdit
    ? base_url('/dokumen/' . $data['id'] . '/update')
    : base_url('/sub-bagian/' . $subBagian['id'] . '/dokumen/store');
$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$programStudiQuerySuffix = $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
$action .= $programStudiQuerySuffix;

$unitKerjaValue = $data['unit_kerja'] ?? unit_kerja_user_login();
$selectedProdiId = old('program_studi_id', $data['program_studi_id'] ?? ($selectedProgramStudiId > 0 ? $selectedProgramStudiId : (session()->get('program_studi_id') ?? '')));
$jenisDokumenAktif = $jenisDokumenList ?? [];
$selectedJenisDokumen = old('jenis_dokumen', $data['jenis_dokumen'] ?? '', false);
$selectedSumberDokumen = old('sumber_dokumen', $data['sumber_dokumen'] ?? (! empty($data['link_dokumen']) ? 'link' : 'file'));
$linkDokumenValue = old('link_dokumen', $data['link_dokumen'] ?? '', false);
$sumberAwalDokumen = (string) ($data['sumber_dokumen'] ?? 'file');
$isSumberAwalLink = $sumberAwalDokumen === 'link';
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card card-clean">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h3 class="mb-1"><?= esc($title); ?></h3>
                    <p class="text-muted mb-0">
                        <?= esc($kriteria['kode']); ?> - <?= esc($kriteria['nama_kriteria']); ?> /
                        <?= esc($subBagian['nama_sub_bagian']); ?>
                    </p>
                </div>

                <form action="<?= $action; ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Judul Dokumen</label>
                        <input
                            type="text"
                            name="judul_dokumen"
                            class="form-control"
                            value="<?= esc(old('judul_dokumen', $data['judul_dokumen'] ?? '', false)); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea
                            name="deskripsi"
                            class="form-control"
                            rows="4"
                        ><?= esc(old('deskripsi', $data['deskripsi'] ?? '', false)); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Dokumen</label>
                            <select name="jenis_dokumen" class="form-select" required>
                                <option value="">Pilih Jenis Dokumen</option>
                                <?php
                                $jenisTersedia = array_map(static fn ($row) => (string) ($row['nama_jenis_dokumen'] ?? ''), $jenisDokumenAktif);
                                if ($selectedJenisDokumen !== '' && ! in_array((string) $selectedJenisDokumen, $jenisTersedia, true)):
                                ?>
                                    <option value="<?= esc($selectedJenisDokumen); ?>" selected><?= esc($selectedJenisDokumen); ?> (Nonaktif)</option>
                                <?php endif; ?>
                                <?php foreach ($jenisDokumenAktif as $jenis): ?>
                                    <option value="<?= esc($jenis['nama_jenis_dokumen']); ?>" <?= (string) $selectedJenisDokumen === (string) $jenis['nama_jenis_dokumen'] ? 'selected' : ''; ?>>
                                        <?= esc($jenis['nama_jenis_dokumen']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($jenisDokumenAktif)): ?>
                                <div class="form-text text-danger">
                                    Belum ada master Jenis Dokumen aktif.
                                    <?php if (has_role(['admin', 'lpm'])): ?>
                                        <a href="<?= base_url('/jenis-dokumen'); ?>">Kelola di menu Jenis Dokumen</a>.
                                    <?php else: ?>
                                        Hubungi Admin/LPM untuk menambahkan.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun Dokumen</label>
                            <input
                                type="number"
                                name="tahun_dokumen"
                                class="form-control"
                                value="<?= esc(old('tahun_dokumen', $data['tahun_dokumen'] ?? '', false)); ?>"
                                min="2000"
                                max="2100"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Program Studi</label>
                            <?php if (has_role(['admin', 'lpm', 'dekan', 'dosen'])): ?>
                                <select name="program_studi_id" class="form-select">
                                    <option value="">Pilih Program Studi</option>
                                    <?php foreach (($programStudiList ?? []) as $prodi): ?>
                                        <option value="<?= esc($prodi['id']); ?>" <?= (string) $selectedProdiId === (string) $prodi['id'] ? 'selected' : ''; ?>>
                                            <?= esc($prodi['nama_program_studi']); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <?php if (has_role('dekan') && ! has_role(['admin', 'lpm'])): ?>
                                        Pilih Program Studi dalam scope UPPS Anda.
                                    <?php elseif (has_role('dosen') && ! has_role(['admin', 'lpm', 'dekan'])): ?>
                                        Pilih Program Studi utama atau prodi penugasan tambahan Anda.
                                    <?php else: ?>
                                        Unit kerja akan mengikuti nama Program Studi yang dipilih.
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= esc(($data['nama_program_studi'] ?? '') !== '' ? $data['nama_program_studi'] : ($unitKerjaValue ?: '-')); ?>"
                                    readonly
                                >
                                <input type="hidden" name="program_studi_id" value="<?= esc((string) $selectedProdiId); ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Terakhir</label>
                        <textarea
                            name="catatan_terakhir"
                            class="form-control"
                            rows="3"
                            placeholder="Kosongkan jika belum ada catatan"
                        ><?= esc(old('catatan_terakhir', $data['catatan_terakhir'] ?? '', false)); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block mb-2">Sumber Referensi Dokumen</label>
                        <div class="form-text">Gunakan salah satu. Saat file dipilih, input link akan nonaktif. Saat link diisi, input file akan nonaktif.</div>
                        <input type="hidden" name="sumber_dokumen" class="js-sumber-hidden" value="<?= esc($selectedSumberDokumen); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 js-upload-file-group">
                            <label class="form-label js-file-label">
                                <?= $isEdit ? 'Upload File Dokumen' : 'Upload File Dokumen'; ?>
                            </label>
                            <input
                                type="file"
                                name="file_dokumen"
                                class="form-control js-file-input"
                            >
                            <div class="form-text js-file-hint">
                                Format: pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png. Maksimal 10 MB.
                            </div>

                            <?php if ($isEdit && ! empty($data['nama_file'])): ?>
                                <div class="mt-2 small text-muted">
                                    File saat ini: <strong><?= esc($data['nama_file']); ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3 js-upload-link-group">
                            <label class="form-label js-link-label">Upload Link Dokumen</label>
                            <input
                                type="url"
                                name="link_dokumen"
                                class="form-control js-link-input"
                                value="<?= esc($linkDokumenValue); ?>"
                                placeholder="https://drive.google.com/... atau tautan cloud lainnya"
                            >
                            <div class="form-text js-link-hint">Saat ditampilkan, sistem hanya menampilkan teks preview, bukan URL mentah.</div>
                            <?php $safeCurrentLink = $isEdit ? sanitize_external_dokumen_link((string) ($data['link_dokumen'] ?? '')) : ''; ?>
                            <?php if ($safeCurrentLink !== ''): ?>
                                <div class="mt-2 small text-muted">
                                    Link aktif saat ini:
                                    <a href="<?= esc($safeCurrentLink); ?>" target="_blank" rel="noopener noreferrer">Preview Dokumen Eksternal</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('/kriteria/' . $kriteria['id']) . $programStudiQuerySuffix . '#subbagian-' . (int) $subBagian['id']; ?>" class="btn btn-light border">
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Update Dokumen' : 'Simpan Dokumen'; ?>
                        </button>
                    </div>
                </form>

                <script>
                (function () {
                    var fileInput = document.querySelector('.js-file-input');
                    var linkInput = document.querySelector('.js-link-input');
                    var sumberHidden = document.querySelector('.js-sumber-hidden');
                    var fileLabel = document.querySelector('.js-file-label');
                    var fileHint = document.querySelector('.js-file-hint');
                    var linkLabel = document.querySelector('.js-link-label');
                    var linkHint = document.querySelector('.js-link-hint');
                    var isEdit = <?= $isEdit ? 'true' : 'false'; ?>;
                    var isSumberAwalLink = <?= $isSumberAwalLink ? 'true' : 'false'; ?>;
                    var defaultSource = <?= json_encode($selectedSumberDokumen === 'link' ? 'link' : 'file'); ?>;
                    if (!fileInput || !linkInput || !sumberHidden) {
                        return;
                    }

                    var getActiveSource = function () {
                        var hasFile = fileInput.files && fileInput.files.length > 0;
                        var hasLink = linkInput.value.trim() !== '';

                        if (hasFile) {
                            return 'file';
                        }

                        if (hasLink) {
                            return 'link';
                        }

                        return isEdit ? (isSumberAwalLink ? 'link' : 'file') : defaultSource;
                    };

                    var syncState = function () {
                        var active = getActiveSource();
                        var hasFile = fileInput.files && fileInput.files.length > 0;
                        var hasLink = linkInput.value.trim() !== '';

                        fileInput.disabled = hasLink;
                        linkInput.disabled = hasFile;
                        sumberHidden.value = active;

                        if (fileLabel) {
                            if (!isEdit) {
                                fileLabel.textContent = 'Upload File Dokumen';
                            } else if (isSumberAwalLink) {
                                fileLabel.textContent = 'Upload File Dokumen (wajib jika pindah dari link)';
                            } else {
                                fileLabel.textContent = active === 'file'
                                    ? 'Upload File Baru (opsional)'
                                    : 'Upload File Dokumen';
                            }
                        }

                        if (fileHint) {
                            var formatHint = 'Format: pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png. Maksimal 10 MB.';
                            if (!isEdit) {
                                fileHint.textContent = formatHint;
                            } else if (isSumberAwalLink) {
                                fileHint.textContent = 'Wajib upload file jika ingin mengubah sumber aktif ke file. ' + formatHint;
                            } else {
                                fileHint.textContent = active === 'file'
                                    ? 'Kosongkan jika tidak ingin mengganti file aktif. ' + formatHint
                                    : formatHint;
                            }
                        }

                        if (linkLabel) {
                            if (!isEdit) {
                                linkLabel.textContent = 'Upload Link Dokumen';
                            } else if (!isSumberAwalLink) {
                                linkLabel.textContent = 'Upload Link Dokumen (wajib jika pindah dari file)';
                            } else {
                                linkLabel.textContent = 'Upload Link Dokumen';
                            }
                        }

                        if (linkHint) {
                            var linkBaseHint = 'Saat ditampilkan, sistem hanya menampilkan teks preview, bukan URL mentah.';
                            if (isEdit && !isSumberAwalLink) {
                                linkHint.textContent = 'Isi URL jika ingin mengubah sumber aktif ke link. ' + linkBaseHint;
                            } else {
                                linkHint.textContent = linkBaseHint;
                            }
                        }
                    };

                    fileInput.addEventListener('change', function () {
                        var hasFile = fileInput.files && fileInput.files.length > 0;
                        if (hasFile) {
                            linkInput.value = '';
                        }
                        syncState();
                    });

                    linkInput.addEventListener('input', function () {
                        if (linkInput.value.trim() !== '') {
                            fileInput.value = '';
                        }
                        syncState();
                    });

                    syncState();
                })();
                </script>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
