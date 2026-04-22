<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login'); ?></title>
    <link rel="icon" type="image/x-icon" href="<?= esc(app_favicon_url()); ?>">
    <link rel="shortcut icon" href="<?= esc(app_favicon_url()); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        html {
            font-size: 17px;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #3468cb 0%, #5a86db 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.10);
        }

        .login-inner {
            width: 100%;
            max-width: 340px;
            margin: 0 auto;
        }

        .login-header {
            max-width: 460px;
            margin: 0 auto;
        }

        .login-title {
            color: #3468cb;
            font-weight: 700;
        }

        .login-brand-link {
            display: inline-block;
            text-decoration: none;
        }

        .login-brand-link:hover .login-title,
        .login-brand-link:hover .login-subtitle {
            color: #2c59af !important;
        }

        .login-logo {
            width: 86px;
            height: 86px;
            object-fit: contain;
            object-position: center;
            padding: 0;
            background: transparent;
            border-radius: 0;
            border: 0;
            margin-bottom: 12px;
        }

        .login-subtitle {
            font-size: 0.8rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .login-campus {
            font-size: 1.05rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 14px;
        }

        .login-input-group {
            border: 1px solid #ced7e6;
            border-radius: 12px;
            overflow: hidden;
            background: #f7faff;
        }

        .login-input-group .input-group-text {
            width: 44px;
            border: 0;
            border-right: 1px solid #d7e1ef;
            border-radius: 0;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .login-input-group .input-group-text iconify-icon {
            font-size: 18px;
            line-height: 1;
        }

        .login-input-group .form-control {
            border: 0;
            border-radius: 0;
            background: transparent;
            padding: 12px 14px;
        }

        .login-input-group .form-control:focus {
            box-shadow: none;
        }

        .password-toggle-btn {
            width: 46px;
            border: 0;
            border-left: 1px solid #d7e1ef;
            border-radius: 0;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .password-toggle-btn i {
            font-size: 18px;
        }

        .password-toggle-btn iconify-icon {
            font-size: 18px;
            line-height: 1;
        }

        .password-toggle-btn:focus {
            border: 0;
            box-shadow: none;
        }

        .password-toggle-btn:hover {
            background: #f3f7fd;
            color: #1e40af;
        }

        .btn-login {
            background: #3468cb;
            border-color: #3468cb;
            border-radius: 12px;
            padding: 12px 14px;
            font-weight: 600;
        }

        .btn-login:hover {
            background: #2c59af;
            border-color: #2c59af;
        }

        .reset-password-text {
            margin-top: 12px;
            text-align: center;
            font-size: 0.84rem;
            color: #64748b;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="card login-card p-3 p-md-4">
        <div class="card-body">
            <div class="login-inner">
                <div class="text-center mb-4 login-header">
                    <?php $logoPtUrl = app_logo_header_url(); ?>
                    <?php if ($logoPtUrl !== ''): ?>
                        <img src="<?= esc($logoPtUrl); ?>" alt="Logo Perguruan Tinggi" class="login-logo">
                    <?php endif; ?>
                    <div class="login-campus"><?= esc(profil_pt_nama('Universitas San Pedro')); ?></div>
                    <a href="<?= site_url('/'); ?>" class="login-brand-link" aria-label="Buka halaman public SIPADUKAR">
                        <h3 class="login-title mb-1">SIPADUKAR</h3>
                        <p class="text-muted mb-0 login-subtitle">Sistem Pengelolaan Dokumen Akreditasi Terpadu</p>
                    </a>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')); ?></div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')); ?></div>
                <?php endif; ?>

                <form action="<?= base_url('/login'); ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="mb-3">
                        <label for="identity" class="form-label">Nama Pengguna</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text">
                                <iconify-icon icon="solar:user-linear"></iconify-icon>
                            </span>
                            <input
                                type="text"
                                class="form-control"
                                id="identity"
                                name="identity"
                                value="<?= esc((string) (old('identity') ?: session()->getFlashdata('old_identity') ?: '')); ?>"
                                placeholder="Masukkan Username"
                                required
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text">
                                <iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon>
                            </span>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Masukkan Password"
                                required
                            >
                            <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Tampilkan/Sembunyikan Password">
                                <iconify-icon icon="solar:eye-linear"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
(() => {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');
    if (!passwordInput || !toggleButton) return;

    toggleButton.addEventListener('click', () => {
        const icon = toggleButton.querySelector('iconify-icon');
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        if (icon) {
            icon.setAttribute('icon', isHidden ? 'solar:eye-closed-linear' : 'solar:eye-linear');
        }
    });
})();
</script>
</html>
