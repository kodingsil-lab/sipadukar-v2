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

$selectedProgramStudiId = (int) ($selectedProgramStudiId ?? 0);
$selectedProgramStudiLabel = (string) ($selectedProgramStudiLabel ?? 'Prodi Persiapan Akreditasi');
$selectedProgramStudiLabelAll = (string) ($selectedProgramStudiLabelAll ?? 'Prodi Persiapan Akreditasi');
$programStudiFilterLocked = ! empty($programStudiFilterLocked);
$showProgramStudiAllOption = ! empty($showProgramStudiAllOption);
$hasActiveProgramStudi = ! empty($prodiAktifAkreditasi);
$resetFilterUrl = base_url('/');
if (! empty($kriteriaActive)) {
    $resetFilterUrl .= '?' . http_build_query(['kriteria_id' => (int) $kriteriaActive]);
}

$prodiHeaderNamaAwal = trim((string) ($prodiAktifAkreditasi[0]['nama_program_studi'] ?? 'Program Studi'));
$prodiHeaderJenjangAwal = trim((string) ($prodiAktifAkreditasi[0]['jenjang'] ?? ''));
$prodiHeaderLembagaAwal = trim((string) ($prodiAktifAkreditasi[0]['lembaga_akreditasi'] ?? '-'));
$prodiHeaderLogoAwal = app_asset_url($prodiAktifAkreditasi[0]['logo_lembaga_path'] ?? '');
$ringkasanMap = [];
foreach (($ringkasanKriteria ?? []) as $ringkasanItem) {
    $ringkasanMap[(int) ($ringkasanItem['id'] ?? 0)] = $ringkasanItem;
}
?>

<div class="container-public container-fluid py-4">
    <div class="hero-dashboard mb-4">
        <div class="hero-dashboard-grid hero-dashboard-grid-admin public-hero-grid">
            <div class="public-hero-main">
                <div class="public-hero-main-top">
                    <span class="badge-soft">Portal Dokumen Akreditasi</span>
                    <h1 class="hero-title mt-3 mb-2">Selamat Datang</h1>
                    <p class="hero-sub mb-0">Akses dokumen akreditasi untuk kebutuhan asesmen lapangan secara cepat, terstruktur, dan terverifikasi.</p>

                    <div class="hero-stats">
                        <div class="hero-stat-item">
                            <strong><?= esc((string) count($prodiAktifAkreditasi ?? [])) ?></strong>
                            <span>Prodi Aktif</span>
                        </div>
                        <div class="hero-stat-item">
                            <strong><?= esc((string) $kriteriaTerisi) ?>/<?= esc((string) $totalKriteria) ?></strong>
                            <span>Kriteria Terisi</span>
                        </div>
                        <div class="hero-stat-item">
                            <strong><?= esc((string) $totalDokumenPublic) ?></strong>
                            <span>Dokumen Final</span>
                        </div>
                    </div>
                </div>

                <div class="public-hero-main-bottom">
                    <div class="progress hero-progress" role="progressbar" aria-valuenow="<?= esc((string) $progressPersen) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar hero-progress-bar" style="width: <?= esc((string) $progressPersen) ?>%"></div>
                    </div>
                    <div class="hero-progress-caption mt-2">Cakupan dokumen final pada kriteria aktif: <?= esc((string) $progressPersen) ?>%</div>
                </div>
            </div>

            <div class="hero-dashboard-side">
                <div class="hero-side-stack hero-side-stack-admin">
                    <div class="card-institusi card-profil-pt">
                        <div class="card-unit-header">
                            <div>
                                <div class="unit-eyebrow">Profil Perguruan Tinggi</div>
                                <div class="unit-title"><?= esc($profilInstitusi['nama_pt'] ?? '-') ?></div>
                            </div>
                            <div class="institusi-logo-cluster">
                                <?php $logoLembagaUrl = app_asset_url($profilInstitusi['logo_lembaga_path'] ?? ''); ?>
                                <?php if ($logoLembagaUrl !== ''): ?>
                                    <span class="cluster-logo cluster-logo-lembaga">
                                        <img src="<?= esc($logoLembagaUrl) ?>" alt="Logo Lembaga Akreditasi">
                                    </span>
                                <?php endif; ?>
                                <span class="cluster-label"><?= esc($profilInstitusi['lembaga_akreditasi'] ?? '-') ?></span>
                                <span class="cluster-separator"></span>
                                <span class="cluster-logo cluster-logo-kampus">
                                    <?php $logoPtUrl = app_logo_header_url(); ?>
                                    <?php if ($logoPtUrl !== ''): ?>
                                        <img src="<?= esc($logoPtUrl) ?>" alt="Logo Perguruan Tinggi">
                                    <?php else: ?>
                                        <i class="bi bi-building"></i>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="institusi-body">
                            <div class="institusi-divider"></div>
                            <div class="institusi-meta">
                                <div class="institusi-grid">
                                    <div class="institusi-row">
                                        <span class="institusi-label">Badan Penyelenggara</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($profilInstitusi['badan_penyelenggara'] ?? '-') ?></span>
                                    </div>
                                    <div class="institusi-row">
                                        <span class="institusi-label">Kode PT</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($profilInstitusi['kode_pt_pddikti'] ?? '-') ?></span>
                                    </div>
                                    <div class="institusi-row">
                                        <span class="institusi-label">Lembaga Akreditasi</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($profilInstitusi['lembaga_akreditasi'] ?? '-') ?></span>
                                    </div>
                                    <div class="institusi-row">
                                        <span class="institusi-label">Peringkat Akreditasi</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($profilInstitusi['peringkat'] ?? '-') ?></span>
                                    </div>
                                    <div class="institusi-row row-nomor-sk">
                                        <span class="institusi-label">No. SK</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($profilInstitusi['nomor_sk'] ?? '-') ?></span>
                                    </div>
                                    <div class="institusi-row">
                                        <span class="institusi-label">Tgl. Berlaku</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($formatTanggalRingkas($profilInstitusi['mulai_berlaku'] ?? '-')) ?></span>
                                    </div>
                                    <div class="institusi-row">
                                        <span class="institusi-label">Tgl. Berakhir</span>
                                        <span class="institusi-sep">:</span>
                                        <span class="institusi-value"><?= esc($formatTanggalRingkas($profilInstitusi['berlaku_sampai'] ?? '-')) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-institusi card-prodi-slider">
                        <div class="card-unit-header">
                            <div>
                                <div class="unit-eyebrow">Prodi Persiapan Akreditasi</div>
                                <div class="unit-title prodi-header-title" id="prodiHeaderTitle">
                                    <?= esc($prodiHeaderNamaAwal) ?>
                                    <?php if ($prodiHeaderJenjangAwal !== ''): ?>
                                        <span class="prodi-header-jenjang" id="prodiHeaderJenjang"><?= esc($prodiHeaderJenjangAwal) ?></span>
                                    <?php else: ?>
                                        <span class="prodi-header-jenjang d-none" id="prodiHeaderJenjang"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="institusi-logo-cluster prodi-logo-cluster" id="prodiHeaderLogoCluster">
                                <span class="cluster-logo cluster-logo-lembaga" id="prodiHeaderLogoWrap">
                                    <?php if ($prodiHeaderLogoAwal !== ''): ?>
                                        <img id="prodiHeaderLogoImg" src="<?= esc($prodiHeaderLogoAwal) ?>" alt="Logo Lembaga Akreditasi">
                                    <?php else: ?>
                                        <i class="bi bi-bank" id="prodiHeaderLogoIcon"></i>
                                    <?php endif; ?>
                                </span>
                                <span class="cluster-label" id="prodiHeaderLembaga"><?= esc($prodiHeaderLembagaAwal !== '' ? $prodiHeaderLembagaAwal : '-') ?></span>
                            </div>
                        </div>
                        <div class="institusi-divider"></div>

                        <?php if (! empty($prodiAktifAkreditasi)): ?>
                            <div id="carouselProdiAktif" class="carousel carousel-fade" data-bs-ride="carousel" data-bs-interval="4500" data-bs-pause="hover" data-bs-touch="true">
                                <div class="carousel-inner">
                                    <?php foreach ($prodiAktifAkreditasi as $idx => $prodi): ?>
                                        <?php
                                        $uppsLabel = trim((string) ($prodi['nama_upps'] ?? '-'));
                                        $uppsSingkatan = trim((string) ($prodi['nama_singkatan_upps'] ?? ''));
                                        if ($uppsLabel !== '-' && $uppsSingkatan !== '' && stripos($uppsLabel, $uppsSingkatan) === false) {
                                            $uppsLabel .= ' (' . $uppsSingkatan . ')';
                                        } elseif ($uppsLabel === '-' && $uppsSingkatan !== '') {
                                            $uppsLabel = $uppsSingkatan;
                                        }

                                        $statusAkreditasiProdi = trim((string) ($prodi['status_akreditasi'] ?? '-')) ?: '-';
                                        ?>
                                        <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                            <div
                                                class="prodi-slide-item"
                                                data-prodi-nama="<?= esc($prodi['nama_program_studi'] ?? '-') ?>"
                                                data-prodi-jenjang="<?= esc($prodi['jenjang'] ?? '') ?>"
                                                data-prodi-lembaga="<?= esc($prodi['lembaga_akreditasi'] ?? '-') ?>"
                                                data-prodi-logo="<?= esc(app_asset_url($prodi['logo_lembaga_path'] ?? '')) ?>"
                                            >
                                                <div class="institusi-meta">
                                                    <div class="institusi-grid">
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">UPPS</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($uppsLabel) ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Jenjang</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['jenjang'] ?: '-') ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Status Akreditasi</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($statusAkreditasiProdi) ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Kode Prodi</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['kode_program_studi_pddikti'] ?: '-') ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Ketua Prodi</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['nama_ketua_program_studi'] ?: '-') ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="card-unit-footer">
                                    <?php if (count($prodiAktifAkreditasi) > 1): ?>
                                        <button class="prodi-carousel-nav-btn" id="prodiPrevBtn" type="button" data-bs-target="#carouselProdiAktif" data-bs-slide="prev" onclick="return publicProdiCarouselNav('prev');">
                                            <i class="bi bi-chevron-left"></i> Prev
                                        </button>
                                    <?php endif; ?>

                                    <div class="prodi-carousel-meta">
                                        <div class="prodi-carousel-counter">
                                            <span id="prodiSlideNow">1</span> / <?= esc((string) count($prodiAktifAkreditasi)) ?> Prodi
                                        </div>
                                        <div class="carousel-indicators prodi-carousel-indicators">
                                            <?php foreach ($prodiAktifAkreditasi as $idx => $unused): ?>
                                                <button
                                                    type="button"
                                                    data-bs-target="#carouselProdiAktif"
                                                    data-bs-slide-to="<?= esc((string) $idx) ?>"
                                                    class="<?= $idx === 0 ? 'active' : '' ?>"
                                                    aria-current="<?= $idx === 0 ? 'true' : 'false' ?>"
                                                    aria-label="Slide <?= esc((string) ($idx + 1)) ?>"
                                                ></button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <?php if (count($prodiAktifAkreditasi) > 1): ?>
                                        <button class="prodi-carousel-nav-btn" id="prodiNextBtn" type="button" data-bs-target="#carouselProdiAktif" data-bs-slide="next" onclick="return publicProdiCarouselNav('next');">
                                            Next <i class="bi bi-chevron-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">
                                Belum ada Program Studi aktif akreditasi untuk ditampilkan pada portal public.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-clean mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= current_url(); ?>" class="kriteria-prodi-filter-form" id="berandaProgramStudiForm">
                <?php if (! empty($kriteriaActive)): ?>
                    <input type="hidden" name="kriteria_id" value="<?= esc((string) $kriteriaActive) ?>">
                <?php endif; ?>
                <?php if ($programStudiFilterLocked && $selectedProgramStudiId > 0): ?>
                    <input type="hidden" name="program_studi_id" value="<?= esc((string) $selectedProgramStudiId) ?>">
                <?php endif; ?>

                <div class="kriteria-prodi-filter-head">
                    <label for="berandaProgramStudiFilter" class="form-label fw-semibold mb-0">Filter Prodi Persiapan Akreditasi</label>
                </div>
                <?php if ($hasActiveProgramStudi): ?>
                    <div class="kriteria-prodi-filter-controls">
                        <?php if ($programStudiFilterLocked): ?>
                            <div class="kriteria-prodi-filter-locked" aria-live="polite">
                                <span class="kriteria-prodi-filter-locked-label">Prodi aktif</span>
                                <strong><?= esc($selectedProgramStudiLabel); ?></strong>
                                <span class="kriteria-prodi-filter-locked-note">Terkunci otomatis karena hanya ada satu prodi aktif akreditasi.</span>
                            </div>
                        <?php else: ?>
                            <select
                                id="berandaProgramStudiFilter"
                                name="program_studi_id"
                                class="form-select form-select-sm"
                                data-all-label="<?= esc($selectedProgramStudiLabelAll) ?>"
                            >
                                <?php if ($showProgramStudiAllOption): ?>
                                    <option value="" <?= $selectedProgramStudiId === 0 ? 'selected' : '' ?>>Pilih Prodi Persiapan Akreditasi</option>
                                <?php endif; ?>
                                <?php foreach (($prodiAktifAkreditasi ?? []) as $prodi): ?>
                                    <option value="<?= esc((string) ($prodi['id'] ?? 0)); ?>" <?= $selectedProgramStudiId === (int) ($prodi['id'] ?? 0) ? 'selected' : ''; ?>>
                                        <?= esc($prodi['nama_program_studi'] ?? '-'); ?><?= ! empty($prodi['jenjang']) ? ' (' . esc($prodi['jenjang']) . ')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <?php if (! $programStudiFilterLocked): ?>
                            <button type="submit" class="btn btn-light border btn-sm">Terapkan</button>
                        <?php endif; ?>
                        <?php if ($showProgramStudiAllOption): ?>
                            <a href="<?= esc($resetFilterUrl) ?>" class="btn btn-light border btn-sm" id="berandaProgramStudiReset">Reset</a>
                        <?php endif; ?>
                        <div class="kriteria-prodi-filter-status <?= $selectedProgramStudiId > 0 ? 'is-specific' : 'is-all'; ?>" id="berandaProgramStudiStatus" role="status" aria-live="polite">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Mode pantau: <strong><?= esc($selectedProgramStudiLabel); ?></strong></span>
                        </div>
                    </div>
                    <div class="form-text mt-1">Pilih program studi terlebih dahulu agar dokumen kriteria yang ditampilkan sesuai dengan prodi yang diasesmen.</div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        Belum ada Program Studi aktif akreditasi yang dapat dipilih. Filter prodi public akan aktif otomatis setelah Admin mengaktifkan minimal satu program studi.
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card card-clean h-100">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1">Daftar Kriteria</h5>
                            <p class="text-muted small mb-0">Pilih kriteria untuk memusatkan telaah ke eviden final yang relevan.</p>
                        </div>
                    </div>

                    <div class="kriteria-list">
                        <?php if (! empty($kriterias)): ?>
                            <?php foreach ($kriterias as $k): ?>
                                <?php $isActive = (int) ($kriteriaActive ?? 0) === (int) ($k['id'] ?? 0); ?>
                                <?php $ringkasan = $ringkasanMap[(int) ($k['id'] ?? 0)] ?? []; ?>
                                <?php
                                $kriteriaQuery = ['kriteria_id' => (int) ($k['id'] ?? 0)];
                                if ($selectedProgramStudiId > 0) {
                                    $kriteriaQuery['program_studi_id'] = $selectedProgramStudiId;
                                }
                                ?>
                                <a href="<?= esc(base_url('/?' . http_build_query($kriteriaQuery))) ?>" class="kriteria-item<?= $isActive ? ' active' : '' ?>" data-kriteria-id="<?= esc((string) ($k['id'] ?? 0)) ?>" data-kriteria-kode="<?= esc($k['kode'] ?? 'K') ?>" data-kriteria-nama="<?= esc($k['nama_kriteria'] ?? '-') ?>" data-kriteria-nomor="<?= esc((string) ($k['nomor_kriteria'] ?? '-')) ?>" data-kriteria-deskripsi="<?= esc($k['deskripsi'] ?? 'Dokumen final untuk kriteria terpilih ditampilkan pada tabel di bawah ini.') ?>"<?= $isActive ? ' style="border-color: rgba(107, 114, 128, 0.35); background: #f3f4f6;"' : '' ?>>
                                    <div class="kriteria-kode"><?= esc($k['kode'] ?? 'K') ?></div>
                                    <div class="kriteria-body">
                                        <div class="d-flex justify-content-between gap-2 align-items-start">
                                            <div class="kriteria-title"><?= esc($k['nama_kriteria'] ?? '-') ?></div>
                                            <span class="badge badge-soft-primary js-kriteria-total"><?= esc((string) ($ringkasan['total_dokumen'] ?? 0)) ?></span>
                                        </div>
                                        <div class="kriteria-desc js-kriteria-desc">Kriteria <?= esc((string) ($k['nomor_kriteria'] ?? '-')) ?> · <?= esc((string) ($ringkasan['persentase'] ?? 0)) ?>% dari total dokumen final</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted small">Belum ada kriteria aktif yang dapat ditampilkan.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card card-clean mb-4">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1" id="berandaKriteriaTitle"><?= esc($selectedKriteria['kode'] ?? '-') ?> - <?= esc($selectedKriteria['nama_kriteria'] ?? 'Kriteria') ?></h5>
                            <p class="text-muted small mb-0" id="berandaKriteriaDescription"><?= esc($selectedKriteria['deskripsi'] ?? 'Dokumen final untuk kriteria terpilih ditampilkan pada tabel di bawah ini.') ?></p>
                        </div>
                    </div>

                    <div class="public-doc-meta">
                        <span class="public-meta-chip" id="berandaKriteriaCount"><?= esc((string) count($dokumen ?? [])) ?> dokumen final</span>
                        <span class="public-meta-chip">Status aktif dan tervalidasi</span>
                    </div>
                </div>
            </div>

            <div class="card card-clean">
                <div class="card-body">
                    <div class="section-head mb-3">
                        <div>
                            <h5 class="mb-1">Daftar Dokumen</h5>
                            <p class="text-muted small mb-0">Gunakan preview untuk meninjau isi dokumen, lalu unduh jika file tersedia.</p>
                        </div>
                    </div>

                    <div class="table-responsive public-doc-table-wrap">
                        <table class="table table-clean table-strong align-middle public-doc-table-mobile">
                            <thead>
                                <tr>
                                    <th width="70">No.</th>
                                    <th>Dokumen</th>
                                    <th width="140">Jenis</th>
                                    <th>Deskripsi</th>
                                    <th width="90">Tahun</th>
                                    <th width="160">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="berandaDokumenTableBody">
                                <?php if (! empty($dokumen)): ?>
                                    <?php foreach ($dokumen as $index => $doc): ?>
                                        <tr>
                                            <td data-label="No."><?= esc((string) ($index + 1)) ?></td>
                                            <td data-label="Dokumen">
                                                <div class="fw-semibold"><?= esc($doc['judul_dokumen']) ?></div>
                                                <div class="table-subtext"><?= esc($doc['kode_dokumen'] ?: 'Tanpa kode dokumen') ?></div>
                                            </td>
                                            <td data-label="Jenis"><span class="badge badge-soft-primary"><?= esc($doc['jenis_dokumen'] ?: '-') ?></span></td>
                                            <td data-label="Deskripsi"><?= esc($doc['deskripsi'] ?: '-') ?></td>
                                            <td data-label="Tahun"><?= esc((string) ($doc['tahun_dokumen'] ?? '-')) ?></td>
                                            <td data-label="Aksi">
                                                <div class="d-flex gap-2 flex-wrap public-doc-row-actions">
                                                    <a href="<?= site_url('file/dokumen/' . $doc['id'] . '/preview') ?>" class="btn btn-sm btn-primary-public" target="_blank">Lihat</a>
                                                    <?php if (! empty($doc['path_file'])): ?>
                                                        <a href="<?= site_url('file/dokumen/' . $doc['id'] . '/download') ?>" class="btn btn-sm btn-outline-public">Unduh</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada dokumen final untuk kriteria ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (! empty($prodiAktifAkreditasi) && count($prodiAktifAkreditasi) > 1): ?>
<script>
function publicProdiCarouselNav(direction) {
    var carouselEl = document.getElementById('carouselProdiAktif');
    if (!carouselEl || !window.bootstrap || !bootstrap.Carousel) {
        return false;
    }

    var instance = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
        interval: 4500,
        ride: 'carousel',
        wrap: true,
        touch: true,
        pause: false
    });

    if (direction === 'prev') {
        instance.prev();
    } else {
        instance.next();
    }

    if (typeof instance.cycle === 'function') {
        instance.cycle();
    }

    return false;
}

function initPublicProdiCarousel() {
    var carouselEl = document.getElementById('carouselProdiAktif');
    var counterEl = document.getElementById('prodiSlideNow');
    var headerTitleEl = document.getElementById('prodiHeaderTitle');
    var headerJenjangEl = document.getElementById('prodiHeaderJenjang');
    var headerLembagaEl = document.getElementById('prodiHeaderLembaga');
    var headerLogoWrapEl = document.getElementById('prodiHeaderLogoWrap');
    var prevBtn = document.getElementById('prodiPrevBtn');
    var nextBtn = document.getElementById('prodiNextBtn');
    var autoSlideTimer = null;
    var AUTO_INTERVAL_MS = 4500;

    if (carouselEl && carouselEl.dataset.carouselReady === '1') {
        return;
    }

    if (!carouselEl || !counterEl) {
        return;
    }
    if (!window.bootstrap || !bootstrap.Carousel) {
        return;
    }

    carouselEl.dataset.carouselReady = '1';

    var syncProdiHeader = function (slideIndex) {
        if (!headerTitleEl || !headerJenjangEl) {
            return;
        }
        var slides = carouselEl.querySelectorAll('.carousel-item');
        var activeSlide = slides[slideIndex] || carouselEl.querySelector('.carousel-item.active');
        if (!activeSlide) {
            return;
        }

        var slideItem = activeSlide.querySelector('.prodi-slide-item');
        if (!slideItem) {
            return;
        }

        var nama = (slideItem.getAttribute('data-prodi-nama') || '').trim();
        var jenjang = (slideItem.getAttribute('data-prodi-jenjang') || '').trim();
        var lembaga = (slideItem.getAttribute('data-prodi-lembaga') || '-').trim();
        var logoUrl = (slideItem.getAttribute('data-prodi-logo') || '').trim();

        var textNode = headerTitleEl.childNodes[0];
        if (textNode && textNode.nodeType === Node.TEXT_NODE) {
            textNode.nodeValue = nama ? (nama + ' ') : 'Program Studi ';
        }

        if (jenjang !== '') {
            headerJenjangEl.textContent = jenjang;
            headerJenjangEl.classList.remove('d-none');
        } else {
            headerJenjangEl.textContent = '';
            headerJenjangEl.classList.add('d-none');
        }

        if (headerLembagaEl) {
            headerLembagaEl.textContent = lembaga !== '' ? lembaga : '-';
        }

        if (headerLogoWrapEl) {
            var existingImg = headerLogoWrapEl.querySelector('img');
            var existingIcon = headerLogoWrapEl.querySelector('i');
            if (logoUrl !== '') {
                if (!existingImg) {
                    if (existingIcon) {
                        existingIcon.remove();
                    }
                    existingImg = document.createElement('img');
                    existingImg.alt = 'Logo Lembaga Akreditasi';
                    headerLogoWrapEl.appendChild(existingImg);
                }
                existingImg.src = logoUrl;
            } else {
                if (existingImg) {
                    existingImg.remove();
                }
                if (!existingIcon) {
                    existingIcon = document.createElement('i');
                    existingIcon.className = 'bi bi-bank';
                    headerLogoWrapEl.appendChild(existingIcon);
                }
            }
        }
    };

    syncProdiHeader(0);

    carouselEl.addEventListener('slid.bs.carousel', function (event) {
        counterEl.textContent = String((event.to || 0) + 1);
        syncProdiHeader(event.to || 0);
    });

    var carouselInstance = new bootstrap.Carousel(carouselEl, {
        interval: 4500,
        ride: 'carousel',
        wrap: true,
        touch: true,
        pause: false
    });
    carouselInstance.cycle();

    var startAutoSlide = function () {
        if (autoSlideTimer !== null) {
            window.clearInterval(autoSlideTimer);
        }
        autoSlideTimer = window.setInterval(function () {
            publicProdiCarouselNav('next');
        }, AUTO_INTERVAL_MS);
    };

    var stopAutoSlide = function () {
        if (autoSlideTimer !== null) {
            window.clearInterval(autoSlideTimer);
            autoSlideTimer = null;
        }
    };

    startAutoSlide();

    if (prevBtn) {
        prevBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            publicProdiCarouselNav('prev');
            startAutoSlide();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            publicProdiCarouselNav('next');
            startAutoSlide();
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopAutoSlide();
        } else {
            startAutoSlide();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPublicProdiCarousel);
} else {
    initPublicProdiCarousel();
}
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var kriteriaLinks = Array.from(document.querySelectorAll('.kriteria-list .kriteria-item[data-kriteria-id]'));
    var filterForm = document.getElementById('berandaProgramStudiForm');
    var filterSelect = document.getElementById('berandaProgramStudiFilter');
    var filterStatus = document.getElementById('berandaProgramStudiStatus');
    var filterResetLink = document.getElementById('berandaProgramStudiReset');
    var titleEl = document.getElementById('berandaKriteriaTitle');
    var descriptionEl = document.getElementById('berandaKriteriaDescription');
    var countEl = document.getElementById('berandaKriteriaCount');
    var tableBodyEl = document.getElementById('berandaDokumenTableBody');
    var activeStyle = 'border-color: rgba(107, 114, 128, 0.35); background: #f3f4f6;';
    var currentRequest = null;
    var homepageBaseUrl = <?= json_encode(base_url('/')) ?>;

    if (kriteriaLinks.length === 0 || !titleEl || !descriptionEl || !countEl || !tableBodyEl) {
        return;
    }

    var escapeHtml = function (value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    var formatTanggal = function (value) {
        if (!value) {
            return '-';
        }

        var date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }).format(date);
    };

    var renderRows = function (rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            tableBodyEl.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada dokumen final untuk kriteria ini.</td></tr>';
            return;
        }

        tableBodyEl.innerHTML = rows.map(function (doc, index) {
            return '<tr>' +
                '<td data-label="No.">' + escapeHtml(index + 1) + '</td>' +
                '<td data-label="Dokumen">' +
                    '<div class="fw-semibold">' + escapeHtml(doc.judul_dokumen || '-') + '</div>' +
                    '<div class="table-subtext">' + escapeHtml(doc.kode_dokumen || 'Tanpa kode dokumen') + '</div>' +
                '</td>' +
                '<td data-label="Jenis"><span class="badge badge-soft-primary">' + escapeHtml(doc.jenis_dokumen || '-') + '</span></td>' +
                '<td data-label="Deskripsi">' + escapeHtml(doc.deskripsi || '-') + '</td>' +
                '<td data-label="Tahun">' + escapeHtml(doc.tahun_dokumen || '-') + '</td>' +
                '<td data-label="Aksi">' +
                    '<div class="d-flex gap-2 flex-wrap public-doc-row-actions">' +
                        '<a href="<?= site_url('file/dokumen') ?>/' + encodeURIComponent(doc.id) + '/preview" class="btn btn-sm btn-primary-public" target="_blank">Lihat</a>' +
                        ((doc.path_file && String(doc.path_file).trim() !== '')
                            ? '<a href="<?= site_url('file/dokumen') ?>/' + encodeURIComponent(doc.id) + '/download" class="btn btn-sm btn-outline-public">Unduh</a>'
                            : '') +
                    '</div>' +
                '</td>' +
            '</tr>';
        }).join('');
    };

    var setActiveLink = function (activeLink) {
        kriteriaLinks.forEach(function (link) {
            var isActive = link === activeLink;
            link.classList.toggle('active', isActive);
            if (isActive) {
                link.setAttribute('style', activeStyle);
            } else {
                link.removeAttribute('style');
            }
        });
    };

    var setLoadingState = function () {
        tableBodyEl.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Memuat dokumen...</td></tr>';
    };

    var getCurrentProgramStudiId = function () {
        if (!filterSelect) {
            return '';
        }

        return (filterSelect.value || '').trim();
    };

    var buildHomepageUrl = function (kriteriaId, programStudiId) {
        var url = new URL(homepageBaseUrl);
        url.searchParams.set('kriteria_id', String(kriteriaId || ''));

        if (String(programStudiId || '').trim() !== '' && Number(programStudiId) > 0) {
            url.searchParams.set('program_studi_id', String(programStudiId));
        } else {
            url.searchParams.delete('program_studi_id');
        }

        return url;
    };

    var updateProgramStudiStatus = function (programStudiId, programStudiLabel) {
        if (!filterStatus) {
            return;
        }

        var strongEl = filterStatus.querySelector('strong');
        if (strongEl) {
            strongEl.textContent = programStudiLabel || (filterSelect ? (filterSelect.getAttribute('data-all-label') || 'Prodi Persiapan Akreditasi') : 'Prodi Persiapan Akreditasi');
        }

        filterStatus.classList.toggle('is-specific', Number(programStudiId || 0) > 0);
        filterStatus.classList.toggle('is-all', Number(programStudiId || 0) <= 0);
    };

    var updateKriteriaRingkasan = function (ringkasanRows, programStudiId) {
        var ringkasanMap = {};
        if (Array.isArray(ringkasanRows)) {
            ringkasanRows.forEach(function (row) {
                ringkasanMap[String(row.id || '')] = row;
            });
        }

        kriteriaLinks.forEach(function (link) {
            var kriteriaId = link.getAttribute('data-kriteria-id') || '';
            var nomor = link.getAttribute('data-kriteria-nomor') || '-';
            var row = ringkasanMap[kriteriaId] || {};
            var totalEl = link.querySelector('.js-kriteria-total');
            var descEl = link.querySelector('.js-kriteria-desc');

            if (totalEl) {
                totalEl.textContent = String(row.total_dokumen || 0);
            }

            if (descEl) {
                descEl.textContent = 'Kriteria ' + nomor + ' · ' + String(row.persentase || 0) + '% dari total dokumen final';
            }

            link.href = buildHomepageUrl(kriteriaId, programStudiId).toString();
        });
    };

    var updateSummary = function (link, docsCount) {
        var kode = link.getAttribute('data-kriteria-kode') || '-';
        var nama = link.getAttribute('data-kriteria-nama') || 'Kriteria';
        var deskripsi = link.getAttribute('data-kriteria-deskripsi') || 'Dokumen final untuk kriteria terpilih ditampilkan pada tabel di bawah ini.';

        titleEl.textContent = kode + ' - ' + nama;
        descriptionEl.textContent = deskripsi;
        countEl.textContent = String(docsCount) + ' dokumen final';
    };

    var loadKriteria = function (link, pushHistory) {
        var kriteriaId = link.getAttribute('data-kriteria-id');
        var programStudiId = getCurrentProgramStudiId();
        if (!kriteriaId) {
            return;
        }

        if (currentRequest && typeof currentRequest.abort === 'function') {
            currentRequest.abort();
        }

        setActiveLink(link);
        updateSummary(link, 0);
        setLoadingState();

        var controller = new AbortController();
        currentRequest = controller;

        var requestUrl = new URL('<?= site_url('portal/kriteria') ?>/' + encodeURIComponent(kriteriaId));
        if (String(programStudiId).trim() !== '' && Number(programStudiId) > 0) {
            requestUrl.searchParams.set('program_studi_id', String(programStudiId));
        }

        fetch(requestUrl.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: controller.signal
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Gagal memuat dokumen kriteria.');
                }
                return response.json();
            })
            .then(function (result) {
                if (!result || result.success !== true) {
                    throw new Error('Respon dokumen tidak valid.');
                }

                var docs = Array.isArray(result.data) ? result.data : [];
                var meta = result.meta || {};
                var activeProgramStudiId = String(meta.selectedProgramStudiId || programStudiId || '').trim();
                updateSummary(link, docs.length);
                renderRows(docs);
                updateKriteriaRingkasan(meta.ringkasanKriteria || [], activeProgramStudiId);
                updateProgramStudiStatus(activeProgramStudiId, meta.selectedProgramStudiLabel || '');

                if (filterSelect) {
                    var optionExists = Array.from(filterSelect.options).some(function (option) {
                        return String(option.value || '') === activeProgramStudiId;
                    });

                    if (optionExists) {
                        filterSelect.value = activeProgramStudiId;
                    } else if (activeProgramStudiId === '' && Array.from(filterSelect.options).some(function (option) {
                        return String(option.value || '') === '';
                    })) {
                        filterSelect.value = '';
                    }
                }

                if (pushHistory) {
                    var nextUrl = buildHomepageUrl(kriteriaId, activeProgramStudiId);
                    window.history.pushState({ kriteriaId: kriteriaId, programStudiId: activeProgramStudiId }, '', nextUrl.toString());
                }
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                tableBodyEl.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat dokumen kriteria.</td></tr>';
            });
    };

        if (filterForm) {
            filterForm.addEventListener('submit', function (event) {
                var activeLink = document.querySelector('.kriteria-list .kriteria-item.active[data-kriteria-id]') || kriteriaLinks[0];
                if (!activeLink) {
                    return;
                }

                event.preventDefault();
                loadKriteria(activeLink, true);
            });
        }

        if (filterSelect && !filterSelect.disabled) {
            filterSelect.addEventListener('change', function () {
                var activeLink = document.querySelector('.kriteria-list .kriteria-item.active[data-kriteria-id]') || kriteriaLinks[0];
                if (activeLink) {
                    loadKriteria(activeLink, true);
                }
            });
        }

        if (filterResetLink) {
            filterResetLink.addEventListener('click', function (event) {
                var hasAllOption = filterSelect && Array.from(filterSelect.options).some(function (option) {
                    return String(option.value || '') === '';
                });
                var activeLink = document.querySelector('.kriteria-list .kriteria-item.active[data-kriteria-id]') || kriteriaLinks[0];

                if (!filterSelect || !hasAllOption || !activeLink) {
                    return;
                }

                event.preventDefault();
                filterSelect.value = '';
                loadKriteria(activeLink, true);
            });
        }

    kriteriaLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            loadKriteria(link, true);
        });
    });

    window.addEventListener('popstate', function () {
        var currentUrl = new URL(window.location.href);
        var currentId = currentUrl.searchParams.get('kriteria_id') || '';
        var currentProgramStudiId = currentUrl.searchParams.get('program_studi_id') || '';

        if (filterSelect) {
            var targetProgramStudiExists = Array.from(filterSelect.options).some(function (option) {
                return String(option.value || '') === currentProgramStudiId;
            });

            if (targetProgramStudiExists) {
                filterSelect.value = currentProgramStudiId;
            } else if (currentProgramStudiId === '' && Array.from(filterSelect.options).some(function (option) {
                return String(option.value || '') === '';
            })) {
                filterSelect.value = '';
            }
        }

        var targetLink = kriteriaLinks.find(function (link) {
            return (link.getAttribute('data-kriteria-id') || '') === currentId;
        }) || kriteriaLinks[0];

        if (targetLink) {
            loadKriteria(targetLink, false);
        }
    });
});
</script>

<?php $this->endSection(); ?>
