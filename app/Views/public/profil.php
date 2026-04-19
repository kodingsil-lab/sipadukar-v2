<?php $this->extend('layouts/public'); ?>
<?php $this->section('content'); ?>
<?php
$formatTanggalRingkas = static function ($value): string {
    $teks = trim((string) $value);
    if ($teks === '' || $teks === '-') {
        return '-';
    }

    $timestamp = strtotime($teks);
    if ($timestamp === false) {
        return $teks;
    }

    return date('d M Y', $timestamp);
};
?>

<div class="container-public container-fluid py-4">
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card card-clean h-100">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1">Profil Perguruan Tinggi</h5>
                            <p class="text-muted small mb-0">Identitas institusi yang menjadi dasar telaah administratif dan akademik pada asesmen lapangan.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-clean align-middle">
                            <tbody>
                                <tr>
                                    <th width="240">Nama Perguruan Tinggi</th>
                                    <td><?= esc($profilPt['nama_pt'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Singkatan</th>
                                    <td><?= esc($profilPt['nama_singkatan'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat Lengkap</th>
                                    <td><?= esc($profilPt['alamat_lengkap'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Website Resmi</th>
                                    <td>
                                        <?php if (!empty($profilPt['website_resmi']) && $profilPt['website_resmi'] !== '-'): ?>
                                            <a href="<?= esc($profilPt['website_resmi']) ?>" target="_blank" rel="noopener noreferrer"><?= esc($profilPt['website_resmi']) ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Akreditasi PT</th>
                                    <td><span class="badge badge-soft-primary"><?= esc($profilPt['status_akreditasi_pt'] ?? '-') ?></span></td>
                                </tr>
                                <tr>
                                    <th>Masa Berlaku Akreditasi</th>
                                    <td><?= esc($formatTanggalRingkas($profilPt['tanggal_berlaku_akreditasi'] ?? '-')) ?> s.d. <?= esc($formatTanggalRingkas($profilPt['tanggal_berakhir_akreditasi'] ?? '-')) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card card-clean h-100">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1">Kontak dan Dokumen Legal</h5>
                            <p class="text-muted small mb-0">Informasi yang biasanya dibutuhkan asesor untuk verifikasi cepat terhadap identitas institusi.</p>
                        </div>
                    </div>

                    <div class="public-doc-list compact">
                        <div class="public-doc-item compact">
                            <div class="public-doc-main">
                                <div class="public-doc-title small-title">Nomor SK Akreditasi</div>
                                <div class="public-doc-meta"><span class="public-meta-chip"><?= esc($profilPt['nomor_sk_akreditasi'] ?? '-') ?></span></div>
                            </div>
                        </div>
                        <div class="public-doc-item compact">
                            <div class="public-doc-main">
                                <div class="public-doc-title small-title">Email Resmi</div>
                                <div class="public-doc-meta"><span class="public-meta-chip"><?= esc($profilPt['email_resmi_pt'] ?? '-') ?></span></div>
                            </div>
                        </div>
                        <div class="public-doc-item compact">
                            <div class="public-doc-main">
                                <div class="public-doc-title small-title">Nomor Telepon</div>
                                <div class="public-doc-meta"><span class="public-meta-chip"><?= esc($profilPt['nomor_telepon'] ?? '-') ?></span></div>
                            </div>
                        </div>
                        <div class="public-doc-item compact">
                            <div class="public-doc-main">
                                <div class="public-doc-title small-title">Lembaga Akreditasi</div>
                                <div class="public-doc-meta"><span class="public-meta-chip"><?= esc($profilPt['lembaga_akreditasi'] ?? '-') ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card card-clean">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1">Program Studi Aktif Akreditasi</h5>
                            <p class="text-muted small mb-0">Daftar program studi yang saat ini ditampilkan pada ruang asesmen public.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-clean table-strong align-middle">
                            <thead>
                                <tr>
                                    <th>Program Studi</th>
                                    <th width="110">Jenjang</th>
                                    <th>UPPS</th>
                                    <th width="150">Status</th>
                                    <th width="150">Berlaku Hingga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($programStudi)): ?>
                                    <?php foreach ($programStudi as $ps): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= esc($ps['nama_program_studi']) ?></div>
                                                <div class="table-subtext">Kode PDDIKTI: <?= esc($ps['kode_program_studi_pddikti'] ?: '-') ?></div>
                                            </td>
                                            <td><?= esc($ps['jenjang'] ?: '-') ?></td>
                                            <td><?= esc($ps['nama_upps'] ?: '-') ?></td>
                                            <td><span class="badge badge-soft-primary"><?= esc($ps['status_akreditasi'] ?: '-') ?></span></td>
                                            <td><?= esc($formatTanggalRingkas($ps['tanggal_berakhir'] ?? '-')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada program studi aktif akreditasi yang ditampilkan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card card-clean">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1">Lembaga Akreditasi Referensi</h5>
                            <p class="text-muted small mb-0">Referensi lembaga akreditasi aktif yang tercatat pada aplikasi untuk kebutuhan penelusuran data.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <?php if (!empty($lembagas)): ?>
                            <?php foreach ($lembagas as $lembaga): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card-institusi h-100">
                                        <div class="card-unit-header">
                                            <div>
                                                <div class="unit-eyebrow">Lembaga Akreditasi</div>
                                                <div class="unit-title"><?= esc($lembaga['nama_lembaga_akreditasi']) ?></div>
                                            </div>
                                            <span class="prodi-head-badge"><?= esc($lembaga['nama_singkatan'] ?: '-') ?></span>
                                        </div>
                                        <div class="institusi-body">
                                            <div class="institusi-divider"></div>
                                            <div class="institusi-meta">
                                                <div class="institusi-grid">
                                                    <div class="institusi-row row-nomor-sk">
                                                        <span class="institusi-label">Nama Singkatan</span>
                                                        <span class="institusi-sep">:</span>
                                                        <span class="institusi-value"><?= esc($lembaga['nama_singkatan'] ?: '-') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-muted small">Belum ada lembaga akreditasi aktif yang tercatat.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
