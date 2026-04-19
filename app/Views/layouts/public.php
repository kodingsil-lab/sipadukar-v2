<?php
$menuActive = $menuActive ?? null;
$homeUrl = site_url('/');
$kriteriaUrl = site_url('portal/kriteria');
$pencarianUrl = site_url('portal/pencarian');
$loginUrl = site_url('login');
$logoHeaderUrl = app_logo_header_url();
$uri = service('uri');
$request = service('request');
$kriteriaNavList = [];
try {
    $kriteriaNavList = (new \App\Models\KriteriaModel())->getAktif();
} catch (\Throwable $exception) {
    $kriteriaNavList = [];
}
$kriteriaActiveId = (int) ($request->getGet('kriteria_id') ?? 0);
$adminButtonUrl = is_login() ? site_url('dashboard') : $loginUrl;
$adminButtonLabel = is_login() ? 'Kembali ke Dashboard' : 'Login Admin';
$adminButtonIcon = is_login() ? 'bi-speedometer2' : 'bi-box-arrow-in-right';
$segmentCount = $uri->getTotalSegments();
$segment2 = $segmentCount >= 2 ? $uri->getSegment(2) : null;
$segment3 = $segmentCount >= 3 ? $uri->getSegment(3) : null;
if ($kriteriaActiveId <= 0 && $segment2 === 'kriteria' && $segment3 !== null) {
    $kriteriaActiveId = (int) $segment3;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'SIPADUKAR') ?> - Sistem Pengelolaan Dokumen Akreditasi Terpadu</title>
    <link rel="icon" type="image/x-icon" href="<?= esc(app_favicon_url()) ?>">
    <link rel="shortcut icon" href="<?= esc(app_favicon_url()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #b91c1c;
            --primary-dark: #991b1b;
            --primary-soft: #fee2e2;
            --action-primary: #22c55e;
            --action-primary-dark: #16a34a;
            --action-neutral: #6b7280;
            --action-neutral-dark: #4b5563;
            --action-neutral-soft: #f3f4f6;
            --public-topbar-start: #374151;
            --public-topbar-end: #4b5563;
            --public-hero-start: #f9fafb;
            --public-hero-end: #e5e7eb;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
            --card-shadow-hover: 0 18px 40px rgba(15, 23, 42, 0.10);
            --table-font-size: 14px;
            --table-subtext-size: 12px;
            --card-logo-frame-size: 30px;
            --card-logo-frame-size-campus: 34px;
            --card-logo-inner-padding: 2px;
        }

        html {
            font-size: 17px;
        }

        body {
            background: var(--bg-page);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
        }

        .topbar {
            background: linear-gradient(90deg, var(--public-topbar-start) 0%, var(--public-topbar-end) 100%);
            box-shadow: 0 8px 22px rgba(71, 85, 105, 0.24);
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .topbar .navbar-toggler {
            border: 0;
            box-shadow: none;
        }

        .topbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: #fff !important;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .navbar-brand-stack {
            display: inline-flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .navbar-brand-text {
            color: #fff !important;
            font-weight: 700;
            letter-spacing: .2px;
            font-size: 1.1rem;
        }

        .navbar-brand-subtext {
            color: rgba(255, 255, 255, 0.92);
            font-weight: 500;
            font-size: 0.72rem;
            letter-spacing: 0.1px;
        }

        .public-nav-link {
            color: rgba(255, 255, 255, 0.92) !important;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 10px 12px !important;
            border-radius: 12px;
            transition: all .2s ease;
        }

        .public-nav-link:hover,
        .public-nav-link.active {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff !important;
        }

        .public-nav-dropdown .dropdown-menu {
            border-radius: 16px;
            border: 1px solid rgba(232, 238, 248, 0.9);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.10);
            padding: 10px;
            min-width: 420px;
        }

        .public-nav-dropdown .dropdown-item {
            border-radius: 10px;
            padding: 10px 12px;
            color: #1f2937;
            font-weight: 500;
            transition: all .2s ease;
            white-space: normal;
        }

        .public-nav-dropdown .dropdown-item:hover,
        .public-nav-dropdown .dropdown-item:focus {
            background: #f1f5f9;
            color: #1f2937;
        }

        .public-nav-dropdown .dropdown-item.active,
        .public-nav-dropdown .dropdown-item:active {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-login-admin {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff;
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .btn-login-admin:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .container-public {
            width: 100%;
            max-width: none;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--public-hero-start), var(--public-hero-end));
            color: var(--text-main);
            padding: 3rem 0;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 16px 34px rgba(75, 85, 99, 0.14);
        }

        .hero-section .hero-title {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.01em;
            color: #111827;
        }

        .hero-subtitle {
            font-size: 14px;
            line-height: 1.55;
            opacity: 1;
            color: #374151;
        }

        .card-clean {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
            background: #fff;
            transition: all .2s ease;
        }

        .card-clean:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .hero-dashboard {
            background: linear-gradient(135deg, var(--public-hero-start), var(--public-hero-end));
            border-radius: 18px;
            padding: 32px;
            color: var(--text-main);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(203, 213, 225, 0.85);
            box-shadow: 0 16px 34px rgba(71, 85, 105, 0.16);
        }

        .hero-dashboard::after {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(148, 163, 184, 0.18);
            border-radius: 50%;
            filter: blur(40px);
            pointer-events: none;
        }

        .hero-dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 520px);
            gap: 18px;
            align-items: start;
            position: relative;
            z-index: 1;
        }

        .hero-dashboard-grid-admin {
            grid-template-columns: minmax(0, 1fr) minmax(760px, 1040px);
        }

        .public-hero-grid {
            grid-template-columns: minmax(0, 1fr) minmax(760px, 1040px);
            align-items: start;
        }

        .public-hero-main {
            min-height: 278px;
            height: auto;
            max-height: none;
            overflow: visible;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 10px;
        }

        .public-hero-main-top {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .public-hero-main-bottom {
            margin-top: 8px;
        }

        .public-hero-grid .hero-dashboard-side {
            width: 100%;
            min-width: 0;
        }

        .hero-title {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.01em;
            color: #111827;
        }

        .hero-sub {
            font-size: 14px;
            line-height: 1.55;
            max-width: 560px;
            color: #475569;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            background: #f3f4f6;
            color: #374151;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #d1d5db;
        }

        .hero-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 12px;
        }

        .hero-stat-item strong {
            display: block;
            font-size: 18px;
            line-height: 1.1;
            color: #111827;
        }

        .hero-stat-item span {
            font-size: 12px;
            color: #6b7280;
        }

        .hero-progress {
            height: 6px;
            border-radius: 10px;
            background: #d1d5db;
            overflow: hidden;
            max-width: 420px;
        }

        .hero-progress-bar {
            background: #22c55e;
            border-radius: 10px;
        }

        .hero-progress-caption {
            font-size: 12px;
            color: #4b5563;
            font-weight: 500;
        }

        .hero-dashboard-side {
            width: 100%;
        }

        .hero-side-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .hero-side-stack-admin {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-items: stretch;
        }

        .hero-side-stack-admin .card-institusi {
            min-height: 278px;
            height: auto;
            max-height: none;
            overflow: visible;
        }

        .public-hero-grid .hero-side-stack-admin {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .card-institusi {
            background: #ffffff;
            border-radius: 18px;
            padding: 18px 20px;
            color: #1e293b;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(226, 232, 240, 0.9);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .card-unit-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .unit-eyebrow {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .unit-title {
            font-size: 1.04rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
        }

        .prodi-header-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .prodi-header-jenjang {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            line-height: 1;
        }

        .institusi-body {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        .institusi-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #e2e8f0;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
        }

        .institusi-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .institusi-logo-float-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .institusi-logo-cluster {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 6px 10px;
            min-height: 44px;
        }

        .cluster-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: var(--card-logo-frame-size);
            height: var(--card-logo-frame-size);
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            flex-shrink: 0;
        }

        .cluster-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
            padding: var(--card-logo-inner-padding);
        }

        .cluster-logo i {
            color: #64748b;
        }

        .cluster-logo-kampus {
            width: var(--card-logo-frame-size-campus);
            height: var(--card-logo-frame-size-campus);
        }

        .prodi-logo-cluster {
            min-width: 122px;
            justify-content: center;
            padding: 5px 10px;
        }

        .cluster-label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            line-height: 1;
            white-space: nowrap;
        }

        .cluster-separator {
            width: 1px;
            height: 20px;
            background: #cbd5e1;
            display: inline-block;
        }

        .prodi-head-badge {
            border-radius: 999px;
            border: 1px solid #dbe5f1;
            background: #f8fafc;
            color: #475569;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .institusi-divider {
            height: 0;
            border-top: 1px solid #f1f5f9;
            margin: 8px 0 12px;
        }

        .card-unit-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #64748b;
            font-size: 12px;
            margin-top: 8px;
        }

        .institusi-meta {
            flex: 1 1 auto;
            min-width: 0;
        }

        .institusi-grid {
            display: grid;
            gap: 6px;
        }

        .institusi-row {
            display: grid;
            grid-template-columns: 168px 12px minmax(0, 1fr);
            gap: 6px;
            align-items: baseline;
        }

        .institusi-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.01em;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            line-height: 1.25;
        }

        .institusi-sep {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-align: center;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            line-height: 1.2;
        }

        .institusi-value {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            line-height: 1.25;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        .institusi-badges .badge {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        .card-prodi-slider {
            position: relative;
            padding-bottom: 0;
        }

        .card-profil-pt {
            width: 100%;
            max-width: 500px;
            justify-self: start;
        }

        .hero-side-stack-admin .card-profil-pt {
            max-width: none;
            justify-self: stretch;
        }

        .institusi-row.row-nomor-sk .institusi-value {
            white-space: nowrap;
            word-break: keep-all;
            overflow-wrap: normal;
        }

        .prodi-slide-item {
            border-left: 3px solid #434d5b;
            padding-left: 12px;
            min-height: auto;
            position: relative;
            opacity: 0;
            transform: translateX(14px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        #carouselProdiAktif .carousel-item.active .prodi-slide-item {
            opacity: 1;
            transform: translateX(0);
        }

        .prodi-slide-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            min-height: auto;
        }

        .prodi-slide-title-wrap {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .prodi-slide-logo-spot {
            position: static;
            flex-shrink: 0;
        }

        .prodi-slide-logo-spot .institusi-logo-float-item {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            padding: 3px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .prodi-slide-logo-spot .institusi-logo-lembaga img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transform: none;
        }

        .prodi-slide-divider {
            margin: 10px 0 14px;
        }

        .prodi-slide-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
        }

        .prodi-slide-jenjang {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            vertical-align: middle;
        }

        #carouselProdiAktif,
        #carouselProdiAktif .carousel-inner {
            overflow: visible;
        }

        #carouselProdiAktif .carousel-inner {
            min-height: 0;
        }

        #carouselProdiAktif .carousel-item {
            transition: opacity .4s ease-in-out;
        }

        .prodi-carousel-nav-btn {
            height: 30px;
            border: 1px solid #dbe5f1;
            background: #ffffff;
            color: #475569;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 82px;
            gap: 4px;
            flex-shrink: 0;
        }

        .card-prodi-slider .institusi-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 0;
        }

        .card-prodi-slider .institusi-divider {
            margin: 4px 0 6px;
        }

        .card-prodi-slider .prodi-slide-item {
            padding-top: 0;
        }

        .card-prodi-slider .card-unit-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
            margin-top: 8px;
            position: relative;
            z-index: 2;
        }

        .card-prodi-slider .institusi-meta {
            overflow: hidden;
            padding-bottom: 0;
            margin-top: -1px;
        }

        .card-prodi-slider .institusi-grid {
            gap: 6px;
        }

        .card-prodi-slider .institusi-value {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-profil-pt .card-unit-footer {
            margin-top: auto;
        }

        .prodi-carousel-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 0;
            flex: 1;
        }

        .prodi-carousel-counter {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            line-height: 1.2;
        }

        .prodi-carousel-indicators {
            position: static;
            margin: 0;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
        }

        .prodi-carousel-indicators [data-bs-target] {
            width: 10px;
            height: 10px;
            border: 0;
            border-radius: 50%;
            margin: 0;
            background-color: #cbd5e1;
            opacity: .4;
            transform: scale(1);
            transition: opacity .2s ease, transform .2s ease;
        }

        .prodi-carousel-indicators .active {
            background-color: #434d5b;
            opacity: 1;
            transform: scale(1.2);
        }

        .dashboard-stat-card {
            position: relative;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.78);
            background: #ffffff;
            box-shadow: 0 3px 16px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(12px);
            overflow: hidden;
            transition: all 0.25s ease;
            min-height: 160px;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.10);
        }

        .dashboard-stat-card .card-body {
            position: relative;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: 100%;
        }

        .dashboard-stat-main {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            flex: 1;
        }

        .dashboard-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .dashboard-stat-content {
            min-width: 0;
            flex: 1;
        }

        .dashboard-stat-card .card-label {
            font-size: 0.82rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            line-height: 1.2;
        }

        .dashboard-stat-card .card-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 4px;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .dashboard-stat-card .card-note {
            font-size: 0.82rem;
            color: #8e9297;
            font-weight: 400;
            margin-bottom: 0;
            line-height: 1.35;
        }

        .dashboard-stat-badge {
            position: absolute;
            right: 20px;
            top: 16px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 999px;
            padding: 5px 14px;
            color: #374151;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dashboard-stat-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
        }

        .dashboard-stat-progress {
            flex: 1;
            height: 5px;
            border-radius: 999px;
            background: #f0f2f5;
            overflow: hidden;
        }

        .dashboard-stat-progress span,
        .table-progress-mini span {
            display: block;
            height: 100%;
            border-radius: inherit;
            transition: width 0.3s ease;
        }

        .dashboard-stat-trend {
            font-size: 0.72rem;
            color: #6c757d;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .dashboard-stat-card.stat-primary .dashboard-stat-icon,
        .dashboard-stat-card.stat-primary .dashboard-stat-badge,
        .dashboard-stat-card.stat-primary .dashboard-stat-trend { color: #2563eb; }
        .dashboard-stat-card.stat-primary .dashboard-stat-icon { background: rgba(37, 99, 235, 0.14); }
        .dashboard-stat-card.stat-primary .dashboard-stat-progress span { background: #2563eb; }

        .dashboard-stat-card.stat-prodi .dashboard-stat-icon,
        .dashboard-stat-card.stat-prodi .dashboard-stat-badge,
        .dashboard-stat-card.stat-prodi .dashboard-stat-trend { color: #7c3aed; }
        .dashboard-stat-card.stat-prodi .dashboard-stat-icon { background: rgba(124, 58, 237, 0.14); }
        .dashboard-stat-card.stat-prodi .dashboard-stat-progress span { background: #7c3aed; }

        .dashboard-stat-card.stat-persiapan .dashboard-stat-icon,
        .dashboard-stat-card.stat-persiapan .dashboard-stat-badge,
        .dashboard-stat-card.stat-persiapan .dashboard-stat-trend { color: #f59e0b; }
        .dashboard-stat-card.stat-persiapan .dashboard-stat-icon { background: rgba(245, 158, 11, 0.14); }
        .dashboard-stat-card.stat-persiapan .dashboard-stat-progress span { background: #f59e0b; }

        .dashboard-stat-card.stat-dokumen .dashboard-stat-icon,
        .dashboard-stat-card.stat-dokumen .dashboard-stat-badge,
        .dashboard-stat-card.stat-dokumen .dashboard-stat-trend { color: #10b981; }
        .dashboard-stat-card.stat-dokumen .dashboard-stat-icon { background: rgba(16, 185, 129, 0.14); }
        .dashboard-stat-card.stat-dokumen .dashboard-stat-progress span { background: #10b981; }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .section-head h5 {
            font-weight: 700;
            color: var(--text-main);
        }

        .table-clean {
            margin-bottom: 0;
            font-size: var(--table-font-size);
            border-color: rgba(0, 0, 0, 0.05);
        }

        .table-clean thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-top: 0;
            padding: 10px 12px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table-clean tbody td,
        .table-clean tbody th {
            padding: 10px 12px;
            border-color: rgba(0, 0, 0, 0.05);
            vertical-align: middle;
            font-size: var(--table-font-size);
        }

        .table-clean tbody tr:hover {
            background: #f9fbff;
        }

        .table-subtext {
            font-size: var(--table-subtext-size);
            color: #94a3b8;
            margin-top: 2px;
        }

        .badge-soft-primary {
            background: #e5e7eb;
            color: #4b5563;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .table-progress-mini {
            width: 100%;
            height: 6px;
            border-radius: 999px;
            background: #e8eef8;
            overflow: hidden;
        }

        .table-progress-mini span {
            background: linear-gradient(90deg, var(--public-hero-start), var(--public-hero-end));
        }

        .kriteria-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .kriteria-prodi-filter-form {
            width: 100%;
            max-width: none;
        }

        .kriteria-prodi-filter-head {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .kriteria-prodi-filter-head .form-label {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.01em;
        }

        .kriteria-prodi-filter-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .kriteria-prodi-filter-status.is-all {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #7f1d1d;
        }

        .kriteria-prodi-filter-status.is-specific {
            border: 1px solid #bee6d0;
            background: #eaf9f0;
            color: #2f6f4f;
        }

        .kriteria-prodi-filter-status i {
            font-size: 12px;
        }

        .kriteria-prodi-filter-status.is-all i {
            color: #991b1b;
        }

        .kriteria-prodi-filter-status.is-specific i {
            color: #2e7d4f;
        }

        .kriteria-prodi-filter-status strong {
            font-weight: 700;
        }

        .kriteria-prodi-filter-status.is-all strong {
            color: #7f1d1d;
        }

        .kriteria-prodi-filter-status.is-specific strong {
            color: #1f6b44;
        }

        .kriteria-prodi-filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .kriteria-prodi-filter-controls .form-select {
            flex: 1 1 460px;
            min-width: 340px;
            min-height: 38px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .kriteria-prodi-filter-controls .btn {
            font-size: 15px;
            font-weight: 700;
        }

        .kriteria-prodi-filter-locked {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid #dbe5f1;
            border-radius: 10px;
            background: #f8fafc;
            color: #0f172a;
        }

        .kriteria-prodi-filter-locked-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .kriteria-prodi-filter-locked strong {
            font-size: 15px;
            line-height: 1.35;
        }

        .kriteria-prodi-filter-locked-note {
            font-size: 12px;
            line-height: 1.45;
            color: #64748b;
        }

        .kriteria-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px;
            border: 1px solid #edf2fa;
            border-radius: 16px;
            background: #fbfdff;
            transition: all .2s ease;
        }

        .kriteria-item:hover {
            border-color: rgba(107, 114, 128, 0.30);
            background: #f3f4f6;
        }

        .kriteria-kode {
            min-width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e5e7eb;
            color: #4b5563;
            font-weight: 700;
        }

        .kriteria-title {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .kriteria-desc {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .public-doc-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .public-doc-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 14px;
            border: 1px solid #edf2fa;
            border-radius: 16px;
            background: #fbfdff;
        }

        .public-doc-item.compact {
            padding: 12px 14px;
            align-items: center;
        }

        .public-doc-main {
            min-width: 0;
            flex: 1;
        }

        .public-doc-title {
            font-size: 0.94rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
        }

        .public-doc-title.small-title {
            font-size: 0.86rem;
        }

        .public-doc-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .public-kriteria-accordion {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .public-kriteria-accordion .accordion-item {
            border: 1px solid #e6edf7;
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        }

        .public-kriteria-accordion .accordion-button {
            padding: 18px 20px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            color: #0f172a;
            box-shadow: none;
            gap: 16px;
            align-items: center;
        }

        .public-kriteria-accordion .accordion-button:not(.collapsed) {
            background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
            color: #0f172a;
            box-shadow: none;
        }

        .public-kriteria-accordion .accordion-button::after {
            margin-left: 14px;
        }

        .public-kriteria-button-main {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1 1 auto;
        }

        .public-kriteria-button-code {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e5e7eb;
            color: #4b5563;
            font-size: 0.95rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .public-kriteria-button-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .public-kriteria-button-text strong {
            font-size: 1rem;
            color: #0f172a;
            line-height: 1.3;
        }

        .public-kriteria-button-text small {
            margin-top: 3px;
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .public-kriteria-count {
            flex-shrink: 0;
        }

        .public-kriteria-accordion .accordion-body {
            padding: 20px;
            background: #f8fbff;
        }

        .btn-xs {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 8px;
            font-weight: 500;
            line-height: 1.2;
        }

        .table-clean .btn {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
        }

        .table-clean .btn.btn-primary {
            background: var(--action-neutral);
            border-color: var(--action-neutral);
            color: #fff;
        }

        .table-clean .btn.btn-primary:hover {
            background: var(--action-neutral-dark);
            border-color: var(--action-neutral-dark);
            color: #fff;
        }

        .table-clean .btn.btn-success {
            background: #22c55e;
            border-color: #22c55e;
            color: #fff;
        }

        .table-clean .btn.btn-success:hover {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .table-clean .action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .kriteria-doc-table .action-group {
            flex-wrap: nowrap;
            gap: 4px;
            justify-content: center;
            width: 100%;
        }

        .kriteria-doc-table .btn {
            white-space: nowrap;
        }

        .kriteria-doc-table .col-aksi {
            text-align: center !important;
        }

        .subbagian-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .subbagian-title-wrap {
            min-width: 0;
        }

        .icon-btn {
            width: 30px;
            height: 30px;
            padding: 0 !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .icon-btn i {
            font-size: 13px;
        }

        .table-strong thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.18);
        }

        .table-strong tbody td {
            font-size: 15px;
            color: #0f172a;
            border-color: rgba(15, 23, 42, 0.12);
        }

        .table-strong tbody tr {
            border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        }

        .table-strong .table-subtext {
            font-size: 13px;
            color: #64748b;
        }

        .cell-center {
            text-align: center;
        }

        .public-meta-chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
        }

        .public-doc-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .btn-primary-public {
            background-color: var(--action-neutral);
            border-color: var(--action-neutral);
            color: white;
            transition: all 0.2s ease;
        }

        .btn-primary-public:hover {
            background-color: var(--action-neutral-dark);
            border-color: var(--action-neutral-dark);
            color: white;
        }

        .btn-outline-public {
            border-color: var(--action-neutral);
            color: var(--action-neutral-dark);
            background-color: var(--action-neutral-soft);
        }

        .btn-outline-public:hover {
            background-color: #e5e7eb;
            border-color: var(--action-neutral-dark);
            color: white;
        }

        .doc-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .doc-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }

        .doc-title {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.05rem;
        }

        .doc-meta {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .doc-action {
            margin-top: 1rem;
        }

        .badge-valid {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-revisi {
            background-color: #f8d7da;
            color: #721c24;
        }

        .footer-public {
            margin-top: auto;
            text-align: center;
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.92);
            padding: 12px 0;
            background: linear-gradient(90deg, var(--public-topbar-start) 0%, var(--public-topbar-end) 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        .footer-public a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
        }

        .heart-red {
            color: #ef4444;
        }

        @media (max-width: 991.98px) {
            .hero-dashboard {
                padding: 18px;
            }

            .hero-dashboard-grid,
            .hero-dashboard-grid-admin,
            .public-hero-grid,
            .hero-side-stack-admin {
                grid-template-columns: 1fr;
            }

            .public-hero-grid .hero-side-stack-admin {
                grid-template-columns: 1fr;
            }

            .hero-side-stack-admin .card-institusi {
                min-height: 0;
            }

            .card-unit-header {
                flex-direction: column;
                align-items: stretch;
            }

            .institusi-logo-cluster {
                width: auto;
                max-width: 100%;
                min-width: 0;
                align-self: flex-start;
                justify-content: flex-start;
                flex-wrap: nowrap;
            }

            .prodi-logo-cluster {
                width: 100%;
                min-width: 0;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .cluster-label {
                white-space: normal;
                line-height: 1.3;
            }

            .institusi-row {
                grid-template-columns: 120px 10px minmax(0, 1fr);
                gap: 4px;
                align-items: start;
            }

            .institusi-sep {
                display: inline-block;
                text-align: center;
                line-height: 1.1;
            }

            .public-doc-item {
                flex-direction: column;
            }

            .public-doc-actions {
                width: 100%;
            }

            .public-kriteria-accordion .accordion-button {
                align-items: flex-start;
            }

            .public-kriteria-button-main {
                align-items: flex-start;
            }

            .public-kriteria-count {
                margin-right: 8px;
            }

            .subbagian-head {
                grid-template-columns: 1fr;
            }

            .kriteria-prodi-filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .kriteria-prodi-filter-controls .form-select {
                flex: 0 0 auto;
                min-width: 0;
                width: 100%;
                min-height: 44px;
                font-size: 14px;
                padding-top: 8px;
                padding-bottom: 8px;
            }

            .kriteria-prodi-filter-controls .btn {
                width: 100%;
                min-height: 42px;
                font-size: 14px;
            }

            .kriteria-prodi-filter-locked {
                min-height: 42px;
                padding: 10px 12px;
            }

            .kriteria-prodi-filter-status {
                white-space: normal;
                width: 100%;
                padding: 10px 12px;
                font-size: 14px;
            }

            .public-kriteria-accordion .accordion-body {
                padding: 16px;
            }

            .icon-btn {
                width: 28px;
                height: 28px;
            }

            .icon-btn i {
                font-size: 12px;
            }
        }

        @media (max-width: 768px) {
            .topbar .container-fluid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: start;
                column-gap: 8px;
                row-gap: 8px;
            }

            .topbar .navbar-toggler {
                position: static;
                margin-left: 0;
                align-self: start;
            }

            .topbar-brand {
                width: 100%;
                min-width: 0;
                margin-right: 0;
                white-space: normal;
            }

            .navbar-brand-stack {
                min-width: 0;
            }

            .topbar .navbar-collapse {
                grid-column: 1 / -1;
                width: 100%;
            }

            .navbar-brand-subtext {
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
                white-space: normal;
                overflow-wrap: anywhere;
                font-size: 0.6rem;
                line-height: 1.2;
            }

            .hero-dashboard {
                padding: 16px;
                border-radius: 16px;
            }

            .card-profil-pt .card-unit-header {
                gap: 10px;
            }

            .card-profil-pt .institusi-logo-cluster {
                width: auto;
                max-width: 100%;
                min-width: 0;
                align-self: flex-start;
                justify-content: flex-start;
                flex-wrap: nowrap;
                padding: 5px 8px;
                gap: 6px;
            }

            .card-profil-pt .cluster-label {
                white-space: nowrap;
                font-size: 11px;
            }

            .card-profil-pt .cluster-separator {
                height: 18px;
            }

            .card-profil-pt .cluster-logo {
                width: 28px;
                height: 28px;
            }

            .card-profil-pt .cluster-logo-kampus {
                width: 30px;
                height: 30px;
            }

            .public-hero-main {
                min-height: 0;
                gap: 8px;
            }

            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 0.95rem;
            }

            .hero-sub {
                font-size: 0.92rem;
                line-height: 1.5;
                max-width: none;
            }

            .hero-stats {
                gap: 10px;
                margin-top: 10px;
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                align-items: start;
            }

            .hero-stat-item {
                min-width: 0;
                display: flex;
                align-items: baseline;
                gap: 8px;
                flex-wrap: nowrap;
            }

            .hero-stat-item strong {
                font-size: 1.2rem;
                display: inline-block;
                flex-shrink: 0;
            }

            .hero-stat-item span {
                font-size: 11px;
                display: inline-block;
                line-height: 1.3;
                word-break: break-word;
            }

            .hero-progress {
                max-width: none;
            }

            .hero-progress-caption {
                font-size: 11px;
            }

            .card-institusi {
                padding: 14px;
                border-radius: 16px;
            }

            .unit-eyebrow {
                font-size: 12px;
                margin-bottom: 4px;
            }

            .unit-title {
                font-size: 0.96rem;
            }

            .institusi-row {
                grid-template-columns: 112px 8px minmax(0, 1fr);
                gap: 4px;
            }

            .institusi-label,
            .institusi-value {
                font-size: 12.5px;
                line-height: 1.25;
            }

            .kriteria-prodi-filter-head .form-label {
                font-size: 14px;
            }

            .kriteria-prodi-filter-controls {
                gap: 8px;
            }

            .kriteria-prodi-filter-controls .form-select {
                min-height: 40px;
                font-size: 13px;
                padding-top: 7px;
                padding-bottom: 7px;
            }

            .kriteria-prodi-filter-controls .btn {
                min-height: 38px;
                font-size: 13px;
                padding-top: 7px;
                padding-bottom: 7px;
            }

            .kriteria-prodi-filter-locked {
                min-height: 38px;
                padding: 9px 11px;
                border-radius: 9px;
            }

            .kriteria-prodi-filter-locked strong {
                font-size: 13px;
            }

            .kriteria-prodi-filter-locked-note {
                font-size: 11px;
            }

            .kriteria-prodi-filter-status {
                font-size: 13px;
                gap: 6px;
            }

            .public-meta-chip {
                font-size: 10px;
                padding: 5px 8px;
            }

            .public-doc-table-wrap {
                overflow: visible;
            }

            .public-doc-table-mobile thead {
                display: none;
            }

            .public-doc-table-mobile,
            .public-doc-table-mobile tbody,
            .public-doc-table-mobile tr,
            .public-doc-table-mobile td {
                display: block;
                width: 100%;
            }

            .public-doc-table-mobile tbody {
                display: grid;
                gap: 12px;
            }

            .public-doc-table-mobile tr {
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                background: #fbfdff;
                padding: 12px;
                box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
            }

            .public-doc-table-mobile td {
                border: 0 !important;
                padding: 0;
                text-align: left;
            }

            .public-doc-table-mobile td + td {
                margin-top: 10px;
            }

            .public-doc-table-mobile td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.02em;
                text-transform: uppercase;
                color: #64748b;
            }

            .public-doc-table-mobile td[data-label="No."] {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                width: auto;
                padding: 4px 8px;
                border-radius: 999px;
                background: #f1f5f9;
                color: #334155;
                font-size: 12px;
                font-weight: 700;
            }

            .public-doc-table-mobile td[data-label="No."]::before {
                margin-bottom: 0;
                font-size: 10px;
            }

            .public-doc-row-actions {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px !important;
            }

            .public-doc-row-actions .btn {
                width: 100%;
                min-height: 36px;
                font-size: 12px;
            }

            .topbar .navbar-nav {
                gap: 4px;
                padding-top: 10px;
            }

            .btn-login-admin {
                width: 100%;
                justify-content: center;
            }

            .public-nav-dropdown .dropdown-menu {
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="topbar navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand topbar-brand" href="<?= $homeUrl ?>">
                <span class="brand-mark">
                    <?php if ($logoHeaderUrl !== ''): ?>
                        <img src="<?= esc($logoHeaderUrl) ?>" alt="Logo SIPADUKAR">
                    <?php else: ?>
                        <i class="bi bi-building text-white"></i>
                    <?php endif; ?>
                </span>
                <span class="navbar-brand-stack">
                    <span class="navbar-brand-text">SIPADUKAR</span>
                    <span class="navbar-brand-subtext">Sistem Pengelolaan Dokumen Akreditasi Terpadu</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarPublic">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link public-nav-link <?= ($menuActive === 'beranda') ? 'active' : '' ?>" href="<?= $homeUrl ?>">
                            <i class="bi bi-house-door"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item dropdown public-nav-dropdown">
                        <a
                            class="nav-link public-nav-link dropdown-toggle <?= ($menuActive === 'kriteria') ? 'active' : '' ?>"
                            href="#"
                            id="navbarPublicKriteria"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-list-check"></i> Dokumen Kriteria
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarPublicKriteria">
                            <?php if (! empty($kriteriaNavList)): ?>
                                <?php foreach ($kriteriaNavList as $item): ?>
                                    <?php
                                    $itemId = (int) ($item['id'] ?? 0);
                                    $itemQuery = ['kriteria_id' => $itemId];
                                    $programStudiId = (int) ($request->getGet('program_studi_id') ?? 0);
                                    if ($programStudiId > 0) {
                                        $itemQuery['program_studi_id'] = $programStudiId;
                                    }
                                    ?>
                                    <li>
                                        <a class="dropdown-item <?= (($menuActive === 'kriteria') && $kriteriaActiveId === $itemId) ? 'active' : '' ?>" href="<?= esc($kriteriaUrl . '?' . http_build_query($itemQuery)) ?>">
                                            <?= esc($item['kode'] ?? 'K') ?> - <?= esc($item['nama_kriteria'] ?? 'Kriteria') ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="dropdown-item text-muted">Belum ada kriteria</span></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link public-nav-link <?= ($menuActive === 'pencarian') ? 'active' : '' ?>" href="<?= $pencarianUrl ?>">
                            <i class="bi bi-search"></i> Pencarian
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-login-admin d-inline-flex align-items-center gap-2" href="<?= esc($adminButtonUrl) ?>">
                            <i class="bi <?= esc($adminButtonIcon) ?>"></i> <?= esc($adminButtonLabel) ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="footer-public">
        <div class="container-fluid px-4">
            <small>
                <span>SIPADUKAR &copy; 2026 Sistem Pengelolaan Dokumen Akreditasi Terpadu - Developed By</span>
                <a href="https://wa.me/628113821126" target="_blank" rel="noopener noreferrer">KSJ</a>
                <span class="heart-red"><i class="bi bi-heart-fill"></i></span>
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
