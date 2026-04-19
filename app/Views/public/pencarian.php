<?php $this->extend('layouts/public'); ?>
<?php $this->section('content'); ?>
<?php $jumlahHasil = count($dokumen ?? []); ?>
<?php $hasActiveProgramStudi = ! empty($hasActiveProgramStudi); ?>

<div class="container-public container-fluid py-4">
    <div class="card card-clean mb-4">
        <div class="card-body">
            <div class="section-head mb-3">
                <div>
                    <h5 class="mb-1">Form Pencarian</h5>
                    <p class="text-muted small mb-0">Masukkan kombinasi filter yang diperlukan, lalu sistem akan menampilkan dokumen final yang sesuai.</p>
                </div>
            </div>

            <?php if (! $hasActiveProgramStudi): ?>
                <div class="alert alert-warning" role="alert">
                    Belum ada Program Studi yang diaktifkan untuk akreditasi oleh Admin. Pencarian dokumen public sementara dinonaktifkan.
                </div>
            <?php endif; ?>

            <form method="GET" action="<?= site_url('portal/pencarian') ?>" class="row g-3">
                <div class="col-12">
                    <label for="q" class="form-label fw-semibold">Kata Kunci</label>
                    <input type="text" class="form-control" id="q" name="q" value="<?= esc($keyword) ?>" placeholder="Cari berdasarkan judul, deskripsi, atau kode dokumen" <?= ! $hasActiveProgramStudi ? 'disabled' : ''; ?>>
                </div>

                <div class="col-md-4">
                    <label for="kriteria_id" class="form-label fw-semibold">Kriteria</label>
                    <select class="form-select" id="kriteria_id" name="kriteria_id" <?= ! $hasActiveProgramStudi ? 'disabled' : ''; ?>>
                        <option value="">Semua Kriteria</option>
                        <?php foreach (($kriterias ?? []) as $k): ?>
                            <option value="<?= esc((string) ($k['id'] ?? 0)) ?>" <?= ((string) $kriteriaId === (string) $k['id']) ? 'selected' : '' ?>>
                                <?= esc(($k['kode'] ?? 'K') . ' - ' . ($k['nama_kriteria'] ?? '-')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="kategori" class="form-label fw-semibold">Jenis Dokumen</label>
                    <select class="form-select" id="kategori" name="kategori" <?= ! $hasActiveProgramStudi ? 'disabled' : ''; ?>>
                        <option value="">Semua Kategori</option>
                        <?php foreach (($kategoriList ?? []) as $kat): ?>
                            <option value="<?= esc($kat['jenis_dokumen']) ?>" <?= ($kategori === $kat['jenis_dokumen']) ? 'selected' : '' ?>><?= esc($kat['jenis_dokumen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tahun" class="form-label fw-semibold">Tahun Dokumen</label>
                    <select class="form-select" id="tahun" name="tahun" <?= ! $hasActiveProgramStudi ? 'disabled' : ''; ?>>
                        <option value="">Semua Tahun</option>
                        <?php foreach (($tahunList ?? []) as $t): ?>
                            <option value="<?= esc((string) $t['tahun_dokumen']) ?>" <?= ((string) $tahun === (string) $t['tahun_dokumen']) ? 'selected' : '' ?>><?= esc((string) $t['tahun_dokumen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary-public" <?= ! $hasActiveProgramStudi ? 'disabled' : ''; ?>>Cari Dokumen</button>
                    <a href="<?= site_url('portal/pencarian') ?>" class="btn btn-outline-public">Reset Filter</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-clean">
        <div class="card-body">
            <div class="section-head mb-3">
                <div>
                    <h5 class="mb-1">Hasil Pencarian</h5>
                    <p class="text-muted small mb-0">Hanya dokumen final tervalidasi yang ditampilkan pada tabel hasil.</p>
                </div>
                <?php if (!empty($hasFilter)): ?>
                    <span class="badge badge-soft-primary"><?= esc((string) $jumlahHasil) ?> hasil</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($hasFilter)): ?>
                <div class="table-responsive">
                    <table class="table table-clean table-strong align-middle">
                        <thead>
                            <tr>
                                <th>Dokumen</th>
                                <th width="120">Kriteria</th>
                                <th width="160">Kategori</th>
                                <th width="90">Tahun</th>
                                <th width="160">Validasi</th>
                                <th width="160">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dokumen)): ?>
                                <?php foreach ($dokumen as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= esc($doc['judul_dokumen']) ?></div>
                                            <div class="table-subtext"><?= esc($doc['kode_dokumen'] ?: 'Tanpa kode dokumen') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-primary"><?= esc($doc['kode_kriteria'] ?: ('K' . str_pad((string) ($doc['nomor_kriteria'] ?? '-'), 2, '0', STR_PAD_LEFT))) ?></span>
                                        </td>
                                        <td><?= esc($doc['jenis_dokumen'] ?: '-') ?></td>
                                        <td><?= esc((string) ($doc['tahun_dokumen'] ?? '-')) ?></td>
                                        <td><?= esc(!empty($doc['tanggal_validasi']) ? date('d M Y', strtotime($doc['tanggal_validasi'])) : '-') ?></td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a href="<?= site_url('file/dokumen/' . $doc['id'] . '/preview') ?>" class="btn btn-sm btn-primary-public" target="_blank">Lihat</a>
                                                <?php if (!empty($doc['path_file'])): ?>
                                                    <a href="<?= site_url('file/dokumen/' . $doc['id'] . '/download') ?>" class="btn btn-sm btn-outline-public">Unduh</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada dokumen yang cocok dengan filter yang dipilih.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">Gunakan form di atas untuk memulai pencarian dokumen akreditasi.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
