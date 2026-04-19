<?= $this->extend('layouts/app'); ?>

<?= $this->section('content'); ?>
<?php
$profilInstitusi = array_merge([
    'nama_pt'          => 'Universitas San Pedro',
    'nama_upps'        => 'UPPS FKIP',
    'nama_prodi'       => 'Pendidikan Guru Sekolah Dasar',
    'lembaga_akreditasi' => '-',
    'nomor_sk'         => '-',
    'mulai_berlaku'    => '-',
    'berlaku_sampai'   => '-',
    'peringkat'        => 'Baik',
    'tahun_akreditasi' => date('Y'),
], $profilInstitusi ?? []);

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

$namaPenggunaDashboard = trim(nama_user_login());
if ($namaPenggunaDashboard === '') {
    $namaPenggunaDashboard = 'Pengguna';
}

$prodiHeaderNamaAwal = trim((string) ($prodiAktifAkreditasi[0]['nama_program_studi'] ?? 'Program Studi'));
$prodiHeaderJenjangAwal = trim((string) ($prodiAktifAkreditasi[0]['jenjang'] ?? ''));
$prodiHeaderLembagaAwal = trim((string) ($prodiAktifAkreditasi[0]['lembaga_akreditasi'] ?? '-'));
$prodiHeaderLogoAwal = app_asset_url($prodiAktifAkreditasi[0]['logo_lembaga_path'] ?? '');
$isAdminLikeDashboard = has_role(['admin', 'lpm', 'kaprodi', 'dosen']);
$isDekanDashboard = has_role('dekan');
$showHeroSideStackAsGrid = $isAdminLikeDashboard || $isDekanDashboard;

$heroSubtext = 'Pantau progres dokumen akreditasi secara cepat, rapi, dan terstruktur.';
if (has_role('admin')) {
    $heroSubtext = 'Monitoring sistem, aktivitas pengguna, dan pengelolaan akses secara keseluruhan.';
} elseif (has_role('lpm')) {
    $heroSubtext = 'Monitoring dan validasi dokumen akreditasi pada seluruh program studi.';
} elseif (has_role('dekan')) {
    $heroSubtext = 'Monitoring progres penyusunan dokumen akreditasi di tingkat fakultas.';
} elseif (has_role('kaprodi')) {
    $heroSubtext = 'Monitoring penyusunan dan kelengkapan dokumen akreditasi program studi.';
} elseif (has_role('dosen')) {
    $heroSubtext = 'Monitoring progres penyusunan dan pembaruan dokumen akreditasi sesuai penugasan.';
}
?>

<div class="hero-dashboard mb-4">
    <div class="hero-dashboard-grid <?= $showHeroSideStackAsGrid ? 'hero-dashboard-grid-admin' : ''; ?>">
        <div>
            <span class="badge-soft">SIPADUKAR</span>
            <h1 class="hero-title mt-3 mb-2">Selamat Datang, <?= esc($namaPenggunaDashboard); ?></h1>
            <p class="hero-sub mb-0">
                <?= esc($heroSubtext); ?>
            </p>

            <div class="hero-stats">
                <div class="hero-stat-item">
                    <strong><?= esc((string) count($prodiAktifAkreditasi ?? [])); ?></strong>
                    <span>Prodi Aktif</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?= esc($totalDokumen); ?></strong>
                    <span>Total Dokumen</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?= esc($statStatus['perlu_revisi'] ?? 0); ?></strong>
                    <span>Perlu Revisi</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?= esc($statStatus['tervalidasi'] ?? 0); ?></strong>
                    <span>Tervalidasi</span>
                </div>
            </div>

            <div class="progress hero-progress mt-3" role="progressbar" aria-valuenow="<?= esc($progressPersen ?? 0); ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar hero-progress-bar" style="width: <?= esc($progressPersen ?? 0); ?>%"></div>
            </div>
        </div>

        <div class="hero-dashboard-side">
            <div class="hero-side-stack <?= $showHeroSideStackAsGrid ? 'hero-side-stack-admin' : ''; ?>">
                <div class="card-institusi card-profil-pt">
                    <div class="card-unit-header">
                        <div>
                            <div class="unit-eyebrow">Profil Perguruan Tinggi</div>
                            <div class="unit-title"><?= esc($profilInstitusi['nama_pt']); ?></div>
                        </div>
                        <div class="institusi-logo-cluster">
                            <?php $logoLembagaUrl = app_asset_url($profilInstitusi['logo_lembaga_path'] ?? ''); ?>
                            <?php if ($logoLembagaUrl !== ''): ?>
                                <span class="cluster-logo cluster-logo-lembaga">
                                    <img src="<?= esc($logoLembagaUrl); ?>" alt="Logo Lembaga Akreditasi">
                                </span>
                            <?php endif; ?>
                            <span class="cluster-label"><?= esc($profilInstitusi['lembaga_akreditasi'] ?? '-'); ?></span>
                            <span class="cluster-separator"></span>
                            <span class="cluster-logo cluster-logo-kampus">
                                <?php $logoPtUrl = app_logo_header_url(); ?>
                                <?php if ($logoPtUrl !== ''): ?>
                                    <img src="<?= esc($logoPtUrl); ?>" alt="Logo Perguruan Tinggi">
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
                                    <span class="institusi-value"><?= esc($profilInstitusi['badan_penyelenggara'] ?? '-'); ?></span>
                                </div>
                                <div class="institusi-row">
                                    <span class="institusi-label">Kode PT</span>
                                    <span class="institusi-sep">:</span>
                                    <span class="institusi-value"><?= esc($profilInstitusi['kode_pt_pddikti'] ?? '-'); ?></span>
                                </div>
                                <div class="institusi-row">
                                    <span class="institusi-label">Lembaga Akreditasi</span>
                                    <span class="institusi-sep">:</span>
                                    <span class="institusi-value"><?= esc($profilInstitusi['lembaga_akreditasi'] ?? '-'); ?></span>
                                </div>
                                <div class="institusi-row">
                                    <span class="institusi-label">Peringkat Akreditasi</span>
                                    <span class="institusi-sep">:</span>
                                    <span class="institusi-value"><?= esc($profilInstitusi['peringkat']); ?></span>
                                </div>
                                <div class="institusi-row row-nomor-sk">
                                    <span class="institusi-label">No. SK</span>
                                    <span class="institusi-sep">:</span>
                                    <span class="institusi-value"><?= esc($profilInstitusi['nomor_sk']); ?></span>
                                </div>
                                <div class="institusi-row">
                                    <span class="institusi-label">Tgl. Berlaku</span>
                                    <span class="institusi-sep">:</span>
                                    <span class="institusi-value"><?= esc($formatTanggalRingkas($profilInstitusi['mulai_berlaku'] ?? '-')); ?></span>
                                </div>
                                <div class="institusi-row">
                                    <span class="institusi-label">Tgl. Berakhir</span>
                                    <span class="institusi-sep">:</span>
                                    <span class="institusi-value"><?= esc($formatTanggalRingkas($profilInstitusi['berlaku_sampai'] ?? '-')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen'])): ?>
                    <div class="card-institusi card-prodi-slider">
                        <div class="card-unit-header">
                            <div>
                                <div class="unit-eyebrow">Prodi Persiapan Akreditasi</div>
                                <div class="unit-title prodi-header-title" id="prodiHeaderTitle">
                                    <?= esc($prodiHeaderNamaAwal); ?>
                                    <?php if ($prodiHeaderJenjangAwal !== ''): ?>
                                        <span class="prodi-header-jenjang" id="prodiHeaderJenjang"><?= esc($prodiHeaderJenjangAwal); ?></span>
                                    <?php else: ?>
                                        <span class="prodi-header-jenjang d-none" id="prodiHeaderJenjang"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="institusi-logo-cluster prodi-logo-cluster" id="prodiHeaderLogoCluster">
                                <span class="cluster-logo cluster-logo-lembaga" id="prodiHeaderLogoWrap">
                                    <?php if ($prodiHeaderLogoAwal !== ''): ?>
                                        <img id="prodiHeaderLogoImg" src="<?= esc($prodiHeaderLogoAwal); ?>" alt="Logo Lembaga Akreditasi">
                                    <?php else: ?>
                                        <i class="bi bi-bank" id="prodiHeaderLogoIcon"></i>
                                    <?php endif; ?>
                                </span>
                                <span class="cluster-label" id="prodiHeaderLembaga"><?= esc($prodiHeaderLembagaAwal !== '' ? $prodiHeaderLembagaAwal : '-'); ?></span>
                            </div>
                        </div>
                        <div class="institusi-divider"></div>

                        <?php if (! empty($prodiAktifAkreditasi)): ?>
                            <div id="carouselProdiAktif" class="carousel carousel-fade" data-bs-ride="carousel" data-bs-interval="4500" data-bs-pause="hover" data-bs-touch="true">
                                <div class="carousel-inner">
                                    <?php foreach ($prodiAktifAkreditasi as $idx => $prodi): ?>
                                        <div class="carousel-item <?= $idx === 0 ? 'active' : ''; ?>">
                                            <div
                                                class="prodi-slide-item"
                                                data-prodi-nama="<?= esc($prodi['nama_program_studi'] ?? '-'); ?>"
                                                data-prodi-jenjang="<?= esc($prodi['jenjang'] ?? ''); ?>"
                                                data-prodi-lembaga="<?= esc($prodi['lembaga_akreditasi'] ?? '-'); ?>"
                                                data-prodi-logo="<?= esc(app_asset_url($prodi['logo_lembaga_path'] ?? '')); ?>"
                                            >
                                                <div class="institusi-meta">
                                                    <div class="institusi-grid">
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Lembaga Akreditasi</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['lembaga_akreditasi'] ?: '-'); ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">UPPS</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['nama_singkatan_upps'] ?: ($prodi['nama_upps'] ?? '-')); ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Jenjang</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['jenjang'] ?: '-'); ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Kode Prodi</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['kode_program_studi_pddikti'] ?: '-'); ?></span>
                                                        </div>
                                                        <div class="institusi-row">
                                                            <span class="institusi-label">Ketua Prodi</span>
                                                            <span class="institusi-sep">:</span>
                                                            <span class="institusi-value"><?= esc($prodi['nama_ketua_program_studi'] ?: '-'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="card-unit-footer">
                                    <?php if (count($prodiAktifAkreditasi) > 1): ?>
                                        <button class="prodi-carousel-nav-btn" id="prodiPrevBtn" type="button" data-bs-target="#carouselProdiAktif" data-bs-slide="prev" onclick="return dashboardProdiCarouselNav('prev');">
                                            <i class="bi bi-chevron-left"></i> Prev
                                        </button>
                                    <?php endif; ?>

                                    <div class="prodi-carousel-meta">
                                        <div class="prodi-carousel-counter">
                                            <span id="prodiSlideNow">1</span> / <?= esc((string) count($prodiAktifAkreditasi)); ?> Prodi
                                        </div>
                                        <div class="carousel-indicators prodi-carousel-indicators">
                                            <?php foreach ($prodiAktifAkreditasi as $idx => $unused): ?>
                                                <button
                                                    type="button"
                                                    data-bs-target="#carouselProdiAktif"
                                                    data-bs-slide-to="<?= esc((string) $idx); ?>"
                                                    class="<?= $idx === 0 ? 'active' : ''; ?>"
                                                    aria-current="<?= $idx === 0 ? 'true' : 'false'; ?>"
                                                    aria-label="Slide <?= esc((string) ($idx + 1)); ?>"
                                                ></button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <?php if (count($prodiAktifAkreditasi) > 1): ?>
                                        <button class="prodi-carousel-nav-btn" id="prodiNextBtn" type="button" data-bs-target="#carouselProdiAktif" data-bs-slide="next" onclick="return dashboardProdiCarouselNav('next');">
                                            Next <i class="bi bi-chevron-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">
                                Belum ada Program Studi aktif akreditasi. Aktifkan dari menu Pengaturan -> Manajemen Prodi Akreditasi.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (! empty($prodiAktifAkreditasi) && count($prodiAktifAkreditasi) > 1): ?>
<script>
function dashboardProdiCarouselNav(direction) {
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

function initDashboardProdiCarousel() {
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

    var triggerPrev = function () {
        if (carouselInstance && typeof carouselInstance.prev === 'function') {
            carouselInstance.prev();
        }
    };

    var triggerNext = function () {
        if (carouselInstance && typeof carouselInstance.next === 'function') {
            carouselInstance.next();
        }
    };

    var startAutoSlide = function () {
        if (autoSlideTimer !== null) {
            window.clearInterval(autoSlideTimer);
        }
        autoSlideTimer = window.setInterval(function () {
            triggerNext();
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
            dashboardProdiCarouselNav('prev');
            startAutoSlide();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            dashboardProdiCarouselNav('next');
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
    document.addEventListener('DOMContentLoaded', initDashboardProdiCarousel);
} else {
    initDashboardProdiCarousel();
}
</script>
<?php endif; ?>

<?php if (! empty($antrianKerja) && ! has_role(['lpm', 'dekan', 'dosen', 'kaprodi'])): ?>
    <div class="card card-clean mb-4">
        <div class="card-body">
            <div class="section-head mb-3">
                <div>
                    <h5 class="mb-1"><?= esc($judulAntrian ?? 'Antrian Kerja'); ?></h5>
                    <p class="text-muted small mb-0"><?= esc($subtitleAntrian ?? 'Akses cepat dokumen berdasarkan status kerja.'); ?></p>
                </div>
            </div>
            <div class="row g-3">
                <?php foreach ($antrianKerja as $item): ?>
                    <?php
                    $tone = $item['tone'] ?? 'primary';
                    $accentClass = '';
                    if ($tone === 'warning') {
                        $accentClass = 'accent-warning';
                    } elseif ($tone === 'success') {
                        $accentClass = 'accent-success';
                    } elseif ($tone === 'danger') {
                        $accentClass = 'accent-danger';
                    }
                    ?>
                    <div class="col-md-6 col-xl-3">
                        <a href="<?= esc($item['url'] ?? base_url('/laporan')); ?>" class="text-decoration-none d-block">
                            <div class="card dashboard-card card-clean h-100 <?= esc($accentClass); ?>">
                                <div class="card-body">
                                    <div class="card-label"><?= esc($item['label'] ?? '-'); ?></div>
                                    <div class="card-value"><?= esc((string) ($item['count'] ?? 0)); ?></div>
                                    <div class="card-note"><?= esc($item['note'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <?php foreach (($summaryCards ?? []) as $card): ?>
        <?php
        $tone = $card['tone'] ?? 'primary';
        $cardUrl = trim((string) ($card['url'] ?? ''));
        $labelKey = strtolower(trim((string) ($card['label'] ?? '')));
        $statVariant = 'stat-primary';
        $statIcon = 'bi-graph-up-arrow';

        if (str_contains($labelKey, 'user')) {
            $statVariant = 'stat-user';
            $statIcon = 'bi-people-fill';
        } elseif (str_contains($labelKey, 'program studi')) {
            $statVariant = 'stat-prodi';
            $statIcon = 'bi-mortarboard-fill';
        } elseif (str_contains($labelKey, 'prodi persiapan')) {
            $statVariant = 'stat-persiapan';
            $statIcon = 'bi-hourglass-split';
        } elseif (str_contains($labelKey, 'total dokumen')) {
            $statVariant = 'stat-dokumen';
            $statIcon = 'bi-file-earmark-text-fill';
        } elseif (str_contains($labelKey, 'ditolak') || $tone === 'danger') {
            $statVariant = 'stat-danger';
            $statIcon = 'bi-x-circle-fill';
        } elseif ($tone === 'warning') {
            $statVariant = 'stat-persiapan';
            $statIcon = 'bi-exclamation-triangle-fill';
        } elseif ($tone === 'info') {
            $statVariant = 'stat-prodi';
            $statIcon = 'bi-clock-history';
        } elseif ($tone === 'success') {
            $statVariant = 'stat-dokumen';
            $statIcon = 'bi-check-circle-fill';
        }

        $countValue = (int) ($card['count'] ?? 0);
        $progressValue = (int) ($card['percent'] ?? 0);
        if ($progressValue < 0) {
            $progressValue = 0;
        } elseif ($progressValue > 100) {
            $progressValue = 100;
        }
        ?>
        <div class="col-md-6 col-xl-3">
            <?php if ($cardUrl !== ''): ?>
                <a href="<?= esc($cardUrl); ?>" class="text-decoration-none d-block h-100"> 
            <?php endif; ?>
                    <div class="card dashboard-stat-card h-100 position-relative <?= esc($statVariant); ?>">
                        <div class="card-body">
                            <span class="dashboard-stat-badge">Real-time</span>
                            <div class="dashboard-stat-main">
                                <div class="dashboard-stat-icon">
                                    <i class="bi <?= esc($statIcon); ?>"></i>
                                </div>
                                <div class="dashboard-stat-content">
                                    <div class="card-label"><?= esc($card['label'] ?? '-'); ?></div>
                                    <div class="card-value"><?= esc((string) $countValue); ?></div>
                                    <div class="card-note"><?= esc($card['note'] ?? '-'); ?></div>
                                </div>
                            </div>
                            <div class="dashboard-stat-footer">
                                <div class="dashboard-stat-progress">
                                    <span style="width: <?= esc((string) $progressValue); ?>%"></span>
                                </div>
                                <div class="dashboard-stat-trend">
                                    <i class="bi bi-graph-up-arrow"></i> <?= esc((string) $progressValue); ?>%
                                </div>
                                <?php if ($cardUrl !== ''): ?>
                                    <button type="button" class="btn btn-sm dashboard-card-action-btn" 
                                            onclick="event.stopPropagation(); window.location.href='<?= esc($cardUrl); ?>';"
                                            data-label="<?= esc($card['label'] ?? ''); ?>">
                                        <i class="bi bi-arrow-right-circle"></i>
                                        <?php
                                        $btnLabel = match(strtolower(trim($card['label'] ?? ''))) {
                                            'antrian finalisasi' => 'Lihat Antrian',
                                            'perlu revisi' => 'Tinjau Revisi',
                                            'ditolak' => 'Lihat Detail',
                                            'tervalidasi' => 'Lihat Final',
                                            default => 'Lihat'
                                        };
                                        echo esc($btnLabel);
                                        ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php if ($cardUrl !== ''): ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="section-head mb-3" id="ringkasanKriteriaSection">
                    <div>
                        <h5 class="mb-1" id="ringkasanKriteriaHeading" tabindex="-1">Ringkasan Progres per Kriteria</h5>
                        <p class="text-muted small mb-0">Pantau jumlah dokumen tiap kriteria dan status utamanya.</p>
                    </div>
                </div>

                <?php if (has_role(['admin', 'lpm'])): ?>
                    <div class="card card-clean mb-3" id="ringkasanFilterWrap">
                        <div class="card-body p-3">
                            <form method="get" action="<?= base_url('/dashboard'); ?>" id="prodiProgressFilterForm" class="ringkasan-filter-form">
                                <div class="row g-2 align-items-end justify-content-lg-end">
                                    <div class="col-12 col-lg-7">
                                        <label for="prodiProgressFilter" class="form-label">Program Studi Aktif Akreditasi</label>
                                        <select id="prodiProgressFilter" name="prodi_progress_id" class="form-select">
                                            <option value="0">Semua Prodi Aktif</option>
                                            <?php foreach (($prodiAktifAkreditasi ?? []) as $prodiFilter): ?>
                                                <?php $prodiFilterId = (int) ($prodiFilter['id'] ?? 0); ?>
                                                <option value="<?= esc((string) $prodiFilterId); ?>" <?= ((int) ($selectedProdiProgressId ?? 0) === $prodiFilterId) ? 'selected' : ''; ?>>
                                                    <?= esc($prodiFilter['nama_program_studi'] ?? '-'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-auto">
                                        <div class="ringkasan-filter-actions">
                                            <button type="submit" class="btn btn-primary ringkasan-filter-btn">Terapkan Filter</button>
                                            <a href="<?= base_url('/dashboard'); ?>" class="btn btn-light border ringkasan-filter-btn" id="prodiProgressFilterReset">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="table-responsive" id="ringkasanKriteriaTableWrap">
                    <table class="table table-clean table-strong align-middle">
                        <thead>
                            <tr>
                                <th width="70">Kode</th>
                                <th>Kriteria</th>
                                <th width="120">Total</th>
                                <th width="120">Valid</th>
                                <th width="120">Revisi</th>
                                <th width="120">Draft</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($ringkasanKriteria)): ?>
                                <?php foreach ($ringkasanKriteria as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-soft-primary"><?= esc($row['kode']); ?></span>
                                        </td>
                                        <td class="fw-semibold"><?= esc($row['nama_kriteria']); ?></td>
                                        <td><?= esc($row['total_dokumen'] ?? 0); ?></td>
                                        <td><span class="text-success fw-semibold"><?= esc($row['total_tervalidasi'] ?? 0); ?></span></td>
                                        <td><span class="text-danger fw-semibold"><?= esc($row['total_revisi'] ?? 0); ?></span></td>
                                        <td><span class="text-secondary fw-semibold"><?= esc($row['total_draft'] ?? 0); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data ringkasan untuk filter yang dipilih.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="section-head mb-3">
                    <div>
                        <h5 class="mb-1">Daftar Kriteria</h5>
                        <p class="text-muted small mb-0">Akses cepat ke dokumen 9 kriteria.</p>
                    </div>
                </div>

                <div class="kriteria-list">
                    <?php foreach ($kriteriaList as $item): ?>
                        <a href="<?= base_url('/kriteria/' . $item['id']); ?>" class="kriteria-item">
                            <div class="kriteria-kode"><?= esc($item['kode']); ?></div>
                            <div class="kriteria-body">
                                <div class="kriteria-title"><?= esc($item['nama_kriteria']); ?></div>
                                <div class="kriteria-desc"><?= esc($item['deskripsi'] ?? '-'); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php if (has_role(['admin', 'lpm'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('prodiProgressFilterForm');
    var select = document.getElementById('prodiProgressFilter');
    var resetLink = document.getElementById('prodiProgressFilterReset');
    var tableWrap = document.getElementById('ringkasanKriteriaTableWrap');
    var heading = document.getElementById('ringkasanKriteriaHeading');
    if (!form || !select || !tableWrap || !heading) {
        return;
    }

    var setLoading = function (isLoading) {
        tableWrap.style.opacity = isLoading ? '0.55' : '1';
        tableWrap.style.transition = 'opacity .2s ease';
    };

    var focusRingkasan = function () {
        heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        heading.focus({ preventScroll: true });
    };

    var loadRingkasan = function (url) {
        setLoading(true);
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Gagal memuat filter');
                }
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newTableWrap = doc.getElementById('ringkasanKriteriaTableWrap');
                if (!newTableWrap) {
                    throw new Error('Area tabel tidak ditemukan');
                }

                tableWrap.innerHTML = newTableWrap.innerHTML;
                window.history.replaceState({}, '', url);
                focusRingkasan();
            })
            .catch(function () {
                window.location.href = url;
            })
            .finally(function () {
                setLoading(false);
            });
    };

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var params = new URLSearchParams(new FormData(form));
        var url = form.action + '?' + params.toString();
        loadRingkasan(url);
    });

    select.addEventListener('change', function () {
        form.requestSubmit();
    });

    if (resetLink) {
        resetLink.addEventListener('click', function (event) {
            event.preventDefault();
            select.value = '0';
            loadRingkasan(form.action);
        });
    }
});
</script>

<style>
/* Action button in footer */
.dashboard-card-action-btn {
    background: rgba(15, 23, 42, 0.05);
    color: #111827;
    border: 1px solid rgba(148, 163, 184, 0.4);
    padding: 7px 12px;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}

.dashboard-card-action-btn:hover {
    background: rgba(15, 23, 42, 0.12);
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.08);
}

.dashboard-card-action-btn:active {
    transform: translateY(0);
}

/* Ensure card remains clickable */
.dashboard-stat-card {
    overflow: hidden;
}

.dashboard-stat-card a {
    display: block;
    height: 100%;
}
</style>

<?php endif; ?>

<?= $this->endSection(); ?>
