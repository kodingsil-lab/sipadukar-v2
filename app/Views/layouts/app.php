<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'SIPADUKAR'); ?></title>
    <link rel="icon" type="image/x-icon" href="<?= esc(app_favicon_url()); ?>">
    <link rel="shortcut icon" href="<?= esc(app_favicon_url()); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3468cb;
            --primary-dark: #2857b6;
            --primary-soft: #eef4ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: #e8eef8;
            --bg-page: #f4f7fc;
            --table-font-size: 14px;
            --table-title-size: 14px;
            --table-subtext-size: 12px;
            --card-shadow: 0 14px 35px rgba(15, 23, 42, 0.06);
            --card-shadow-hover: 0 18px 40px rgba(15, 23, 42, 0.10);
            --dash-logo-kampus-size: 66px;
            --dash-logo-lembaga-size: 66px;
            --dash-logo-kampus-size-mobile: 58px;
            --dash-logo-lembaga-size-mobile: 58px;
            --dash-logo-gap: 8px;
            --dash-logo-frame-padding: 4px;
            --dash-logo-frame-radius: 12px;
            --dash-logo-wrap-top: 12px;
            --prodi-logo-frame-size: 66px;
            --prodi-logo-frame-size-mobile: 58px;
            --prodi-logo-image-scale: 1.22;
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
            font-size: 1rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
        }

        .top-shell {
            z-index: 1030;
        }

        .topbar-main {
            background: linear-gradient(90deg, #3468cb 0%, #3d74db 100%);
            box-shadow: 0 8px 22px rgba(52, 104, 203, 0.16);
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 0;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            padding: 0;
            border-radius: 0;
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

        .topbar-profile-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .density-toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #ffffff;
            transition: all .2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .density-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.24);
            color: #ffffff;
        }

        .density-toggle-btn i {
            font-size: 16px;
        }

        .profile-dropdown-shell .profile-toggle {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            padding: 6px 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            transition: all .2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .profile-dropdown-shell .profile-toggle:hover,
        .profile-dropdown-shell .profile-toggle.show {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff, #dbeafe);
            color: #3468cb;
            font-weight: 700;
            font-size: 0.88rem;
        }

        .profile-meta {
            display: inline-flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.1;
        }

        .profile-name {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }

        .profile-role {
            color: rgba(255, 255, 255, 0.80);
            font-size: 12px;
            font-weight: 500;
        }

        .profile-caret {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.92);
        }

        .profile-dropdown {
            min-width: 230px;
            border-radius: 14px;
            padding: 8px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        }

        .profile-dropdown .dropdown-item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 9px 11px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
        }

        .profile-dropdown .dropdown-item i {
            color: #64748b;
        }

        .profile-dropdown .dropdown-item.logout-item {
            color: #dc2626;
        }

        .profile-dropdown .dropdown-item.logout-item i {
            color: #dc2626;
        }

        .topbar-menu {
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid #dbe6f3;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.05);
            backdrop-filter: blur(6px);
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .topbar-menu .navbar-toggler {
            border: 0;
            box-shadow: none;
        }

        .menu-link,
        .menu-text {
            color: #1f2937 !important;
            font-weight: 600;
        }

        .menu-link {
            border-radius: 10px;
            padding: 10px 12px !important;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: 1px solid transparent;
        }

        .menu-link:focus-visible {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #1f2937 !important;
            outline: none;
        }

        .menu-link:hover,
        .menu-link.active,
        .menu-link.dropdown-toggle.show,
        .navbar .nav-item.show > .menu-link {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #1f2937 !important;
        }

        .menu-icon {
            color: #64748b;
            font-size: 0.95rem;
        }

        .menu-link:hover .menu-icon,
        .menu-link.active .menu-icon,
        .menu-link.dropdown-toggle.show .menu-icon {
            color: #475569;
        }

        .content-wrapper {
            padding-top: 170px;
            padding-bottom: 28px;
            flex: 1 0 auto;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            margin-bottom: 4px;
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.25;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .page-subtitle {
            margin-bottom: 0;
            font-size: 0.92rem;
            line-height: 1.45;
            color: #64748b;
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
            background: linear-gradient(135deg, #3468cb, #5b8def);
            border-radius: 18px;
            padding: 32px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.25);
        }

        .hero-dashboard::after {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.12);
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

        .hero-title {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.01em;
            color: #ffffff;
        }

        .hero-sub {
            opacity: 0.88;
            font-size: 14px;
            line-height: 1.55;
            max-width: 560px;
            color: rgba(255, 255, 255, 0.95);
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

        .badge-soft {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        .hero-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 16px;
        }

        .hero-stat-item strong {
            display: block;
            font-size: 18px;
            line-height: 1.1;
            color: #ffffff;
        }

        .hero-stat-item span {
            font-size: 12px;
            opacity: 0.85;
            color: rgba(255, 255, 255, 0.95);
        }

        .hero-progress {
            height: 6px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.25);
            overflow: hidden;
            max-width: 420px;
        }

        .hero-progress-bar {
            background: #22c55e;
            border-radius: 10px;
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
            background: #eef4ff;
            color: #3468cb;
            border: 1px solid #d7e4ff;
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

        .cluster-logo-kampus {
            width: var(--card-logo-frame-size-campus);
            height: var(--card-logo-frame-size-campus);
        }

        .prodi-logo-cluster {
            min-width: 122px;
            justify-content: center;
            padding: 5px 10px;
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

        .institusi-divider {
            height: 0;
            border-top: 1px solid #f1f5f9;
            margin: 8px 0 12px;
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
            border-left: 3px solid #2b59b5;
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
            background: #eef4ff;
            color: #3468cb;
            border: 1px solid #d7e4ff;
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
            cursor: pointer;
            pointer-events: auto;
            position: relative;
            z-index: 5;
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
            z-index: 6;
            pointer-events: auto;
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
            background-color: #3468cb;
            opacity: 1;
            transform: scale(1.2);
        }

        .eyebrow {
            display: inline-block;
            padding: 6px 12px;
            background: var(--primary-soft);
            color: var(--primary);
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .dashboard-card {
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary);
        }

        .dashboard-card.accent-success::before {
            background: #16a34a;
        }

        .dashboard-card.accent-warning::before {
            background: #dc2626;
        }

        .dashboard-card.accent-danger::before {
            background: #b91c1c;
        }

        .card-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 30px;
            line-height: 1;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 10px;
        }

        .card-note {
            font-size: 13px;
            color: var(--text-muted);
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
            pointer-events: none;
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

        .dashboard-stat-progress span {
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

        .dashboard-stat-trend i {
            font-size: 0.75rem;
        }

        .dashboard-stat-card.stat-user .dashboard-stat-icon {
            color: #2b59b5;
            background: rgba(43, 89, 181, 0.14);
        }

        .dashboard-stat-card.stat-user .dashboard-stat-badge,
        .dashboard-stat-card.stat-user .dashboard-stat-trend {
            color: #2b59b5;
        }

        .dashboard-stat-card.stat-user .dashboard-stat-progress span {
            background: #2b59b5;
        }

        .dashboard-stat-card.stat-prodi .dashboard-stat-icon {
            color: #7c3aed;
            background: rgba(124, 58, 237, 0.14);
        }

        .dashboard-stat-card.stat-prodi .dashboard-stat-badge,
        .dashboard-stat-card.stat-prodi .dashboard-stat-trend {
            color: #7c3aed;
        }

        .dashboard-stat-card.stat-prodi .dashboard-stat-progress span {
            background: #7c3aed;
        }

        .dashboard-stat-card.stat-persiapan .dashboard-stat-icon {
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.14);
        }

        .dashboard-stat-card.stat-persiapan .dashboard-stat-badge,
        .dashboard-stat-card.stat-persiapan .dashboard-stat-trend {
            color: #f59e0b;
        }

        .dashboard-stat-card.stat-persiapan .dashboard-stat-progress span {
            background: #f59e0b;
        }

        .dashboard-stat-card.stat-primary .dashboard-stat-icon {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.14);
        }

        .dashboard-stat-card.stat-primary .dashboard-stat-badge,
        .dashboard-stat-card.stat-primary .dashboard-stat-trend {
            color: #2563eb;
        }

        .dashboard-stat-card.stat-primary .dashboard-stat-progress span {
            background: #2563eb;
        }

        .dashboard-stat-card.stat-danger .dashboard-stat-icon {
            color: #dc2626;
            background: rgba(220, 38, 38, 0.14);
        }

        .dashboard-stat-card.stat-danger .dashboard-stat-badge,
        .dashboard-stat-card.stat-danger .dashboard-stat-trend {
            color: #dc2626;
        }

        .dashboard-stat-card.stat-danger .dashboard-stat-progress span {
            background: #dc2626;
        }

        .dashboard-stat-card.stat-dokumen .dashboard-stat-icon {
            color: #10b981;
            background: rgba(16, 185, 129, 0.14);
        }

        .dashboard-stat-card.stat-dokumen .dashboard-stat-badge,
        .dashboard-stat-card.stat-dokumen .dashboard-stat-trend {
            color: #10b981;
        }

        .dashboard-stat-card.stat-dokumen .dashboard-stat-progress span {
            background: #10b981;
        }

        .ringkasan-filter-form .form-label {
            margin-bottom: 6px;
        }

        .ringkasan-filter-summary {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ringkasan-filter-active {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            line-height: 1.2;
        }

        .ringkasan-filter-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .ringkasan-filter-btn {
            min-width: 136px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {
            .ringkasan-filter-form .row {
                justify-content: stretch !important;
            }

            .ringkasan-filter-summary {
                width: 100%;
                min-height: auto;
                margin-bottom: 2px;
            }

            .ringkasan-filter-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .ringkasan-filter-btn {
                width: 100%;
                min-width: 0;
            }
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

        .table-clean tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all .2s ease;
        }

        .table-clean tbody tr:hover {
            background: #f9fbff;
        }

        .table-clean .table-title,
        .table-clean td .fw-semibold {
            font-size: var(--table-title-size);
            font-weight: 600;
            color: #1e293b;
        }

        .table-subtext {
            font-size: var(--table-subtext-size);
            color: #94a3b8;
            margin-top: 2px;
        }

        .table-clean thead th,
        .table thead th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-borderless th {
            color: #4b5563;
            font-weight: 600;
        }

        .table-borderless td {
            color: #111827;
        }

        .table-clean .badge,
        .badge-status {
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 999px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
        }

        .table-clean .badge.bg-success {
            background: #dcfce7 !important;
            color: #166534 !important;
        }

        .table-clean .badge.bg-secondary {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .table-clean .badge.bg-warning {
            background: #fef9c3 !important;
            color: #854d0e !important;
        }

        .table-clean .badge.bg-danger {
            background: #fee2e2 !important;
            color: #991b1b !important;
        }

        .table-clean .badge.bg-info,
        .table-clean .badge.bg-primary {
            background: #dbeafe !important;
            color: #1d4ed8 !important;
        }

        .badge-soft-primary {
            background: var(--primary-soft);
            color: var(--primary);
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            min-height: 42px;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            font-size: 14px;
            box-shadow: none;
            transition: all .2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3468cb;
            box-shadow: 0 0 0 2px rgba(52, 104, 203, 0.1);
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }

        /* Form standardization across pages (dokumen/peraturan/instrumen/users/sub-bagian/profil PT/etc) */
        .card-clean form .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }

        .card-clean form .form-control,
        .card-clean form .form-select,
        .card-clean form textarea.form-control {
            min-height: 44px;
            font-size: 14px;
            line-height: 1.35;
            padding: 10px 12px;
        }

        .card-clean form textarea.form-control {
            min-height: 110px;
        }

        .card-clean form .form-control::placeholder,
        .card-clean form textarea.form-control::placeholder {
            color: #94a3b8;
            font-size: 14px;
        }

        form input[type="file"].form-control {
            padding: 0;
            overflow: hidden;
            min-height: 42px;
            height: 42px;
            line-height: 1.2;
        }

        form input[type="file"].form-control:focus {
            box-shadow: none;
            border-color: #cbd5e1;
        }

        form input[type="file"].form-control::file-selector-button {
            height: 100%;
            margin: 0;
            margin-right: 10px;
            padding: 9px 14px;
            border: 0;
            border-right: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #1f2937;
            font-weight: 500;
        }

        .card-clean form .form-text,
        .card-clean form small,
        .card-clean form .small {
            font-size: 12px;
            color: #64748b;
        }

        .card-clean form .row,
        .card-clean form .row.form-grid {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }

        .card-clean form .card + .card,
        .card-clean form .row + .row {
            margin-top: 0.25rem;
        }

        /* keep kriteria inline search compact */
        .kriteria-search-inline .form-control,
        .kriteria-search-inline .form-control.form-control-sm {
            min-height: 38px;
            font-size: 13px;
            padding: 8px 12px;
        }

        .profil-pt-page .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.005em;
        }

        .profil-pt-page .page-subtitle {
            font-size: 13px;
            color: #64748b;
        }

        .profil-pt-page h5 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px !important;
        }

        .profil-pt-page .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .profil-pt-page .form-control,
        .profil-pt-page .form-select,
        .profil-pt-page textarea.form-control {
            font-size: 14px;
        }

        .profil-pt-page .form-control::placeholder,
        .profil-pt-page textarea.form-control::placeholder {
            font-size: 14px;
            color: #94a3b8;
        }

        .profil-pt-page .small,
        .profil-pt-page .text-muted {
            font-size: 12px;
        }

        .profil-pt-page .form-separator {
            margin: 2px 0;
            border: 0;
            border-top: 1px dashed #e2e8f0;
            opacity: 1;
        }

        .btn {
            border-radius: 14px;
            font-weight: 600;
            padding: 10px 16px;
            transition: all .2s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-info {
            background: #0ea5e9;
            border-color: #0ea5e9;
        }

        .btn-info:hover {
            background: #0284c7;
            border-color: #0284c7;
        }

        .btn-success {
            background: #16a34a;
            border-color: #16a34a;
        }

        .btn-success:hover {
            background: #15803d;
            border-color: #15803d;
        }

        .btn-review {
            background: #7c3aed;
            border-color: #7c3aed;
            color: #fff;
        }

        .btn-review:hover {
            background: #6d28d9;
            border-color: #6d28d9;
            color: #fff;
        }

        .btn-sm {
            padding: 7px 12px;
            border-radius: 12px;
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

        .table-clean .btn.btn-info {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
        }

        .table-clean .btn.btn-info:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .table-clean .btn.btn-success {
            background: #22c55e;
            border-color: #22c55e;
        }

        .table-clean .btn.btn-success:hover {
            background: #16a34a;
            border-color: #16a34a;
        }

        .table-clean .btn.btn-warning {
            background: #f59e0b;
            border-color: #f59e0b;
            color: #fff;
        }

        .table-clean .btn.btn-warning:hover {
            background: #d97706;
            border-color: #d97706;
            color: #fff;
        }

        .table-clean .btn.btn-danger {
            background: #ef4444;
            border-color: #ef4444;
        }

        .table-clean .btn.btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .table-clean .btn.btn-review {
            background: #7c3aed;
            border-color: #7c3aed;
            color: #fff;
        }

        .table-clean .btn.btn-review:hover {
            background: #6d28d9;
            border-color: #6d28d9;
            color: #fff;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .page-actions {
            justify-content: flex-end;
            gap: 8px;
        }

        .page-actions .btn {
            min-height: 34px;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
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

        .table-scroll-anchor {
            scroll-margin-top: 175px;
        }

        .kriteria-doc-table th.sortable-col {
            padding: 0 !important;
        }

        .th-sort-link {
            width: 100%;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: inherit;
            text-decoration: none;
            padding: 10px 12px;
            font-weight: inherit;
            transition: color .2s ease;
        }

        .th-sort-link i {
            font-size: 11px;
            color: #94a3b8;
            line-height: 1;
        }

        .th-sort-link:hover {
            color: #334155;
        }

        .th-sort-link:hover i {
            color: #64748b;
        }

        .th-sort-link.active {
            color: #0f172a;
        }

        .th-sort-link.active i {
            color: #475569;
        }

        .kriteria-search-inline {
            width: 100%;
            max-width: 440px;
            margin-left: auto;
        }

        .kriteria-search-tight {
            margin-top: 4px;
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
            border: 1px solid #cfe0ff;
            background: #eef4ff;
            color: #375e98;
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
            color: #2f5fad;
        }

        .kriteria-prodi-filter-status.is-specific i {
            color: #2e7d4f;
        }

        .kriteria-prodi-filter-status strong {
            font-weight: 700;
        }

        .kriteria-prodi-filter-status.is-all strong {
            color: #1f4f9a;
        }

        .kriteria-prodi-filter-status.is-specific strong {
            color: #1f6b44;
        }

        .kriteria-prodi-filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
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

        .search-input-wrap {
            position: relative;
        }

        .search-input-wrap i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .search-input-wrap .form-control,
        .search-input-wrap .form-control.form-control-sm {
            height: 36px;
            min-height: 36px;
            border-radius: 10px;
            padding-left: 38px;
            padding-right: 12px;
            font-size: 13px;
            line-height: 1.3;
            border-color: #d6dee9;
            background: #f1f5f9;
            color: #334155;
        }

        .search-input-wrap .form-control::placeholder {
            color: #94a3b8;
        }

        .search-input-wrap .form-control[type="search"] {
            -webkit-appearance: none;
            appearance: none;
        }

        .search-input-wrap .form-control[type="search"]::-webkit-search-decoration,
        .search-input-wrap .form-control[type="search"]::-webkit-search-cancel-button,
        .search-input-wrap .form-control[type="search"]::-webkit-search-results-button,
        .search-input-wrap .form-control[type="search"]::-webkit-search-results-decoration {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }

        .subbagian-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(460px, 620px);
            gap: 12px;
            align-items: start;
        }

        .subbagian-title-wrap .small {
            margin-bottom: 0;
        }

        .subbagian-controls {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            width: 100%;
        }

        .subbagian-controls .kriteria-search-inline {
            margin-left: 0;
            width: min(100%, 420px);
            max-width: 420px;
            margin-top: 8px;
        }

        .subbagian-controls .action-group {
            justify-content: flex-end;
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

        body.density-dense .content-wrapper {
            padding-top: 158px;
            padding-bottom: 22px;
        }

        body.density-dense .page-header {
            margin-bottom: 1rem;
        }

        body.density-dense .page-title {
            font-size: 1.26rem;
            margin-bottom: 2px;
        }

        body.density-dense .page-subtitle {
            font-size: 0.84rem;
        }

        body.density-dense .card-clean {
            border-radius: 12px;
        }

        body.density-dense .card-body {
            padding: 0.95rem 1rem;
        }

        body.density-dense .table-clean thead th {
            font-size: 12px;
            padding: 8px 10px;
        }

        body.density-dense .table-clean tbody td,
        body.density-dense .table-clean tbody th {
            font-size: 13px;
            padding: 8px 10px;
        }

        body.density-dense .table-clean .table-title,
        body.density-dense .table-clean td .fw-semibold {
            font-size: 13px;
        }

        body.density-dense .table-subtext {
            font-size: 11px;
        }

        body.density-dense .table-clean .badge,
        body.density-dense .badge-status {
            font-size: 10px;
            padding: 3px 8px;
        }

        body.density-dense .table-clean .btn,
        body.density-dense .btn-xs {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 7px;
        }

        body.density-dense .form-control,
        body.density-dense .form-select {
            min-height: 38px;
            padding: 8px 10px;
            font-size: 13px;
        }

        body.density-dense .form-label {
            font-size: 12px;
        }

        body.density-dense .page-actions .btn {
            min-height: 30px;
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 8px;
        }

        body.density-dense .icon-btn {
            width: 28px;
            height: 28px;
        }

        body.density-dense .icon-btn i {
            font-size: 12px;
        }

        .dropdown-menu {
            border-radius: 16px;
            border: 1px solid rgba(232, 238, 248, 0.9);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.10);
            padding: 10px;
        }

        .dropdown-item {
            border-radius: 10px;
            padding: 10px 12px;
            color: #1f2937;
            font-weight: 500;
            transition: all .2s ease;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: #f1f5f9;
            color: #1f2937;
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background: #e2e8f0;
            color: #0f172a;
        }

        .pagination .page-link {
            color: #475569;
            border-color: #d1d5db;
            background-color: #ffffff;
        }

        .pagination .page-link:hover {
            color: #334155;
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }

        .pagination .page-item.active .page-link {
            color: #1f2937;
            background-color: #e5e7eb;
            border-color: #d1d5db;
        }

        .pagination .page-item.disabled .page-link {
            color: #9ca3af;
            background-color: #f3f4f6;
            border-color: #e5e7eb;
        }

        .table-hover tbody tr:hover {
            background-color: #f7faff;
        }

        .bg-light-subtle {
            background: #f8fbff !important;
            border-color: #e6eef8 !important;
        }

        code {
            background: #eef4ff;
            padding: 3px 8px;
            border-radius: 8px;
            color: #234b97;
        }

        .kriteria-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
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
            border-color: rgba(52, 104, 203, 0.20);
            background: #f7faff;
        }

        .kriteria-kode {
            min-width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
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

        .app-footer {
            text-align: center;
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.92);
            padding: 6px 12px 7px;
            background: linear-gradient(90deg, #3468cb 0%, #3d74db 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            flex-shrink: 0;
        }

        .app-footer a {
            color: #ffffff;
            font-weight: 700;
        }

        .app-footer-content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
        }

        .heart-red {
            color: #ef4444;
        }

        @media (max-width: 991.98px) {
            .content-wrapper {
                padding-top: 156px;
            }

            .table-scroll-anchor {
                scroll-margin-top: 160px;
            }

            .hero-dashboard {
                padding: 20px;
            }

            .hero-dashboard-grid {
                grid-template-columns: 1fr;
            }

            .hero-dashboard-grid-admin {
                grid-template-columns: 1fr;
            }

            .hero-side-stack-admin {
                grid-template-columns: 1fr;
            }

            .hero-side-stack-admin .card-institusi {
                height: auto;
                min-height: 0;
                max-height: none;
                overflow: visible;
            }

            .institusi-body {
                align-items: flex-start;
            }

            .card-unit-header {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .card-profil-pt {
                max-width: 100%;
            }

            .institusi-row.row-nomor-sk .institusi-value {
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }

            .institusi-logo-cluster {
                width: 100%;
                justify-content: flex-start;
            }

            .prodi-slide-top {
                flex-wrap: wrap;
            }

            .prodi-slide-logo-spot .institusi-logo-float-item {
                width: 42px;
                height: 42px;
            }

            #carouselProdiAktif .carousel-inner {
                min-height: 0;
                height: auto;
            }

            #carouselProdiAktif .carousel-item {
                height: auto;
            }

            .card-unit-footer {
                flex-wrap: wrap;
            }

            .card-prodi-slider {
                padding-bottom: 0;
            }

            .card-prodi-slider .institusi-body {
                justify-content: flex-start;
            }

            .card-prodi-slider .card-unit-footer {
                position: static;
                left: auto;
                right: auto;
                bottom: auto;
                margin-top: 8px;
            }

            .prodi-carousel-meta {
                width: 100%;
                justify-content: space-between;
            }

            .card-value {
                font-size: 24px;
            }

            .app-footer-content {
                white-space: normal;
            }

            .subbagian-head {
                grid-template-columns: 1fr;
            }

            .subbagian-controls {
                align-items: flex-start;
                width: 100%;
            }

            .subbagian-controls .action-group {
                justify-content: flex-start;
            }

            .subbagian-controls .kriteria-search-inline {
                width: 100%;
                max-width: 100%;
            }

            .kriteria-prodi-filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .kriteria-prodi-filter-controls .form-select {
                min-width: 0;
            }

            .kriteria-prodi-filter-status {
                white-space: normal;
                width: 100%;
            }

        }

        @media (max-width: 767.98px) {
            .profile-meta {
                display: none;
            }

            .profile-toggle {
                padding: 6px 8px !important;
                gap: 6px !important;
            }

            .profile-avatar {
                width: 36px;
                height: 36px;
            }

            .profile-caret {
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>
<?php
$request = service('request');
$session = session();

$densityRequest = strtolower(trim((string) $request->getGet('density')));
if (in_array($densityRequest, ['dense', 'comfortable'], true)) {
    $session->set('ui_density', $densityRequest);
}

$savedDensity = (string) ($session->get('ui_density') ?? '');
$defaultDensity = has_role(['asesor', 'dekan', 'kaprodi']) ? 'dense' : 'comfortable';
$uiDensity = in_array($savedDensity, ['dense', 'comfortable'], true) ? $savedDensity : $defaultDensity;
$isDenseMode = $uiDensity === 'dense';

$queryParams = $request->getGet();
unset($queryParams['density']);
$queryParams['density'] = $isDenseMode ? 'comfortable' : 'dense';
$densityToggleUrl = current_url() . '?' . http_build_query($queryParams);

$uri = service('uri');
$kriteriaNavList = [];
try {
    $kriteriaNavList = (new \App\Models\KriteriaModel())->getAktif();
} catch (\Throwable $e) {
    $kriteriaNavList = [];
}

$segmentCount = $uri->getTotalSegments();
$segment1 = $segmentCount >= 1 ? $uri->getSegment(1) : null;
$segment2 = $segmentCount >= 2 ? $uri->getSegment(2) : null;

$isKriteriaPage = $segment1 === 'kriteria';
$kriteriaAktifId = (int) ($segment2 ?? 0);
$isMasterDataPage = in_array($segment1, ['profil-pt', 'upps', 'program-studi', 'lembaga-akreditasi', 'jenis-dokumen', 'master-dokumen-kriteria', 'master-data'], true);
$isPengaturanPage = in_array($segment1, ['users', 'audit-trail', 'pengaturan'], true);
$isAplikasiPengaturanPage = $segment1 === 'pengaturan' && $segment2 === 'aplikasi';
$isManajemenProdiAkreditasiPage = $segment1 === 'pengaturan' && $segment2 === 'manajemen-prodi-akreditasi';
$userIdTopbar = (int) ($session->get('user_id') ?? 0);

$namaUserTopbar = trim(nama_user_login());
$unitUserTopbar = trim(unit_kerja_user_login());
$rolesSlugTopbar = user_roles();
$roleNamesTopbar = $session->get('role_names') ?? [];
$roleUtamaTopbar = trim((string) ($roleNamesTopbar[0] ?? ''));
if ($roleUtamaTopbar === '' && ! empty($rolesSlugTopbar[0])) {
    $roleUtamaTopbar = label_role((string) $rolesSlugTopbar[0]);
}
$subLabelTopbar = $unitUserTopbar !== '' ? $unitUserTopbar : ($roleUtamaTopbar !== '' ? $roleUtamaTopbar : 'Pengguna');

$avatarPathTopbar = trim((string) ($session->get('foto_profil') ?? ''));
$avatarUrlTopbar = '';
if ($avatarPathTopbar !== '') {
    $avatarUrlTopbar = preg_match('#^https?://#i', $avatarPathTopbar)
        ? $avatarPathTopbar
        : base_url('/' . ltrim($avatarPathTopbar, '/'));
}

$namaPartsTopbar = preg_split('/\s+/', $namaUserTopbar) ?: [];
$inisialTopbar = '';
foreach ($namaPartsTopbar as $partTopbar) {
    if ($partTopbar === '') {
        continue;
    }
    $inisialTopbar .= strtoupper(substr($partTopbar, 0, 1));
    if (strlen($inisialTopbar) >= 2) {
        break;
    }
}
if ($inisialTopbar === '') {
    $inisialTopbar = 'U';
}

$profilSayaUrlTopbar = base_url('/profil');
$isImpersonatingTopbar = (bool) ($session->get('is_impersonating') ?? false);

$logoHeaderUrl = app_logo_header_url();

$canAccessPeraturan = has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen']);
$canAccessInstrumen = has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen']);
$canAccessKriteria = has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen']);
?>
<body class="<?= $isDenseMode ? 'density-dense' : 'density-comfortable'; ?>">

<div class="top-shell fixed-top">
    <div class="topbar-main">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <a class="d-inline-flex align-items-center gap-3" href="<?= site_url('/'); ?>">
                <span class="brand-mark">
                    <?php if ($logoHeaderUrl !== ''): ?>
                        <img src="<?= esc($logoHeaderUrl); ?>" alt="Logo Header">
                    <?php else: ?>
                        <i class="bi bi-journal-richtext"></i>
                    <?php endif; ?>
                </span>
                <span class="navbar-brand-stack">
                    <span class="navbar-brand-text">SIPADUKAR</span>
                    <span class="navbar-brand-subtext">Sistem Pengelolaan Dokumen Akreditasi Terpadu</span>
                </span>
            </a>

            <div class="topbar-profile-wrap mt-3 mt-lg-0">
                <a href="<?= esc($densityToggleUrl); ?>" class="density-toggle-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ubah mode tampilan" aria-label="Ubah mode tampilan">
                    <i class="bi bi-layout-text-sidebar-reverse"></i>
                </a>

                <div class="dropdown profile-dropdown-shell">
                    <button class="profile-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="profile-avatar">
                            <?php if ($avatarUrlTopbar !== ''): ?>
                                <img src="<?= esc($avatarUrlTopbar); ?>" alt="Avatar <?= esc($namaUserTopbar); ?>">
                            <?php else: ?>
                                <span class="profile-avatar-placeholder"><?= esc($inisialTopbar); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="profile-meta">
                            <span class="profile-name"><?= esc($namaUserTopbar); ?></span>
                            <span class="profile-role"><?= esc($subLabelTopbar); ?></span>
                        </span>
                        <span class="profile-caret"><i class="bi bi-chevron-down"></i></span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end profile-dropdown">
                        <?php if ($isImpersonatingTopbar): ?>
                            <li>
                                <form action="<?= base_url('/users/impersonation/stop'); ?>" method="post" class="m-0">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="dropdown-item text-warning-emphasis">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Kembali ke Admin
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item" href="<?= esc($profilSayaUrlTopbar); ?>">
                                <i class="bi bi-sliders2"></i>
                                Profil dan Akun
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="<?= base_url('/logout'); ?>" method="post" class="m-0">
                                <?= csrf_field(); ?>
                                <button type="submit" class="dropdown-item logout-item">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg topbar-menu">
        <div class="container-fluid px-4">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="topMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?= base_url('/dashboard'); ?>">
                            <i class="bi bi-grid-1x2-fill menu-icon"></i>
                            Dashboard
                        </a>
                    </li>
                    <?php if ($canAccessPeraturan): ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?= base_url('/peraturan'); ?>">
                                <i class="bi bi-book-fill menu-icon"></i>
                                Peraturan
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canAccessInstrumen): ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?= base_url('/instrumen'); ?>">
                                <i class="bi bi-ui-checks-grid menu-icon"></i>
                                Instrumen
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canAccessKriteria): ?>
                        <li class="nav-item dropdown">
                            <a
                                class="nav-link menu-link dropdown-toggle <?= $isKriteriaPage ? 'active' : ''; ?>"
                                href="#"
                                id="kriteriaMenu"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="bi bi-folder2-open menu-icon"></i>
                                Dokumen Kriteria
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="kriteriaMenu">
                                <?php if (! empty($kriteriaNavList)): ?>
                                    <?php foreach ($kriteriaNavList as $item): ?>
                                        <?php $itemId = (int) ($item['id'] ?? 0); ?>
                                        <li>
                                            <a
                                                class="dropdown-item <?= ($isKriteriaPage && $kriteriaAktifId === $itemId) ? 'active' : ''; ?>"
                                                href="<?= base_url('/kriteria/' . $itemId); ?>"
                                            >
                                                <?= esc($item['kode'] ?? 'K'); ?> - <?= esc($item['nama_kriteria'] ?? 'Kriteria'); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><span class="dropdown-item text-muted">Belum ada kriteria</span></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (has_role(['admin', 'lpm', 'dekan', 'kaprodi'])): ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?= base_url('/laporan'); ?>">
                                <i class="bi bi-bar-chart-fill menu-icon"></i>
                                Laporan
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_role(['admin', 'lpm'])): ?>
                        <li class="nav-item dropdown">
                            <a
                                class="nav-link menu-link dropdown-toggle <?= $isMasterDataPage ? 'active' : ''; ?>"
                                href="#"
                                id="masterDataMenu"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="bi bi-database-fill menu-icon"></i>
                                Master Data
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="masterDataMenu">
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'profil-pt' ? 'active' : ''; ?>" href="<?= base_url('/profil-pt'); ?>">
                                        Profil PT
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'upps' ? 'active' : ''; ?>" href="<?= base_url('/upps'); ?>">
                                        UPPS
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'program-studi' ? 'active' : ''; ?>" href="<?= base_url('/program-studi'); ?>">
                                        Program Studi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'lembaga-akreditasi' ? 'active' : ''; ?>" href="<?= base_url('/lembaga-akreditasi'); ?>">
                                        Lembaga Akreditasi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'jenis-dokumen' ? 'active' : ''; ?>" href="<?= base_url('/jenis-dokumen'); ?>">
                                        Jenis Dokumen
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'master-dokumen-kriteria' ? 'active' : ''; ?>" href="<?= base_url('/master-dokumen-kriteria'); ?>">
                                        Master Dokumen Kriteria
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a
                                class="nav-link menu-link dropdown-toggle <?= $isPengaturanPage ? 'active' : ''; ?>"
                                href="#"
                                id="pengaturanMenu"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="bi bi-gear-fill menu-icon"></i>
                                Pengaturan
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="pengaturanMenu">
                                <?php if (has_role('admin')): ?>
                                    <li>
                                        <a class="dropdown-item <?= $uri->getSegment(1) === 'users' ? 'active' : ''; ?>" href="<?= base_url('/users'); ?>">
                                            Manajemen User
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item <?= $isAplikasiPengaturanPage ? 'active' : ''; ?>" href="<?= base_url('/pengaturan/aplikasi'); ?>">
                                        Aplikasi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $isManajemenProdiAkreditasiPage ? 'active' : ''; ?>" href="<?= base_url('/pengaturan/manajemen-prodi-akreditasi'); ?>">
                                        Manajemen Prodi Akreditasi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $uri->getSegment(1) === 'audit-trail' ? 'active' : ''; ?>" href="<?= base_url('/audit-trail'); ?>">
                                        Audit Trail
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</div>

<div class="container-fluid px-4 content-wrapper">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm js-auto-dismiss-alert" role="alert">
            <?= esc(session()->getFlashdata('success')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm js-auto-dismiss-alert" role="alert">
            <?= esc(session()->getFlashdata('error')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content'); ?>
</div>

<footer class="app-footer">
    <div class="app-footer-content">
        <span>SIPADUKAR &copy; 2026 Sistem Pengelolaan Dokumen Akreditasi Terpadu - Developed By</span>
        <a href="https://wa.me/628113821126" target="_blank" rel="noopener noreferrer">KSJ</a>
        <span class="heart-red"><i class="bi bi-heart-fill"></i></span>
    </div>
</footer>

<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-labelledby="appConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appConfirmModalLabel">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="appConfirmModalMessage">Apakah Anda yakin?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-app-confirm-cancel>Batal</button>
                <button type="button" class="btn btn-danger" data-app-confirm-ok>Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el, { trigger: 'hover focus' });
});

document.querySelectorAll('.js-auto-dismiss-alert').forEach(function (el) {
    window.setTimeout(function () {
        var instance = bootstrap.Alert.getOrCreateInstance(el);
        instance.close();
    }, 3000);
});

(function () {
    var modalEl = document.getElementById('appConfirmModal');
    if (!modalEl || !window.bootstrap || !window.bootstrap.Modal) {
        return;
    }

    var modal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false
    });
    var messageEl = document.getElementById('appConfirmModalMessage');
    var okBtn = modalEl.querySelector('[data-app-confirm-ok]');
    var cancelBtn = modalEl.querySelector('[data-app-confirm-cancel]');
    var resolver = null;

    var settle = function (result) {
        if (typeof resolver === 'function') {
            resolver(result);
            resolver = null;
        }
        modal.hide();
    };

    window.showAppConfirm = function (message) {
        return new Promise(function (resolve) {
            resolver = resolve;
            if (messageEl) {
                messageEl.textContent = String(message || 'Apakah Anda yakin?');
            }
            modal.show();
        });
    };

    if (okBtn) {
        okBtn.addEventListener('click', function () {
            settle(true);
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            settle(false);
        });
    }

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (typeof resolver === 'function') {
            resolver(false);
            resolver = null;
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.nodeName !== 'FORM') {
            return;
        }

        if (!form.matches('.js-confirm-submit, form[data-confirm]')) {
            return;
        }

        if (form.dataset.confirmed === '1') {
            form.dataset.confirmed = '';
            return;
        }

        event.preventDefault();
        var message = form.getAttribute('data-confirm') || 'Apakah Anda yakin?';
        window.showAppConfirm(message).then(function (isConfirmed) {
            if (!isConfirmed) {
                return;
            }

            form.dataset.confirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    }, true);
}());
</script>
</body>
</html>
