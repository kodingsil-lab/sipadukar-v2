<?php

if (! function_exists('buat_slug')) {
    function buat_slug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');

        return $text ?: 'tanpa-slug';
    }
}

if (! function_exists('label_status_dokumen')) {
    function label_status_dokumen(?string $status): string
    {
        return match ($status) {
            'draft'          => 'Draft',
            'diajukan'       => 'Diajukan',
            'ditinjau'       => 'Ditinjau',
            'perlu_revisi'   => 'Perlu Revisi',
            'disubmit_ulang' => 'Disubmit Ulang',
            'tervalidasi'    => 'Tervalidasi',
            'ditolak'        => 'Perlu Revisi',
            default          => '-',
        };
    }
}

if (! function_exists('badge_status_dokumen')) {
    function badge_status_dokumen(?string $status): string
    {
        return match ($status) {
            'draft'          => 'secondary',
            'diajukan'       => 'primary',
            'ditinjau'       => 'warning',
            'perlu_revisi'   => 'danger',
            'disubmit_ulang' => 'info',
            'tervalidasi'    => 'success',
            'ditolak'        => 'danger',
            default          => 'secondary',
        };
    }
}

if (! function_exists('label_status_review')) {
    function label_status_review(?string $status): string
    {
        return match ($status) {
            'diajukan'      => 'Diajukan',
            'ditinjau'      => 'Ditinjau',
            'perlu_revisi'  => 'Perlu Revisi',
            'tervalidasi'   => 'Tervalidasi',
            'ditolak'       => 'Perlu Revisi',
            default         => '-',
        };
    }
}

if (! function_exists('badge_status_review')) {
    function badge_status_review(?string $status): string
    {
        return match ($status) {
            'diajukan'      => 'primary',
            'ditinjau'      => 'warning',
            'perlu_revisi'  => 'danger',
            'tervalidasi'   => 'success',
            'ditolak'       => 'danger',
            default         => 'secondary',
        };
    }
}

if (! function_exists('label_role')) {
    function label_role(?string $slugRole): string
    {
        return match ($slugRole) {
            'admin'   => 'Admin',
            'lpm'     => 'LPM',
            'dekan'   => 'Dekan',
            'kaprodi' => 'Kaprodi',
            'dosen'   => 'Dosen',

            default   => '-',
        };
    }
}
if (! function_exists('user_login')) {
    function user_login(): ?array
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return null;
        }

        return [
            'id'           => $session->get('user_id'),
            'nama_lengkap' => $session->get('nama_lengkap'),
            'username'     => $session->get('username'),
            'email'        => $session->get('email'),
        ];
    }
}

if (! function_exists('is_login')) {
    function is_login(): bool
    {
        return (bool) session()->get('isLoggedIn');
    }
}

if (! function_exists('user_roles')) {
    function user_roles(): array
    {
        return session()->get('roles') ?? [];
    }
}

if (! function_exists('has_role')) {
    function has_role(string|array $roles): bool
    {
        $userRoles = user_roles();

        if (is_string($roles)) {
            return in_array($roles, $userRoles, true);
        }

        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('nama_user_login')) {
    function nama_user_login(): string
    {
        return session()->get('nama_lengkap') ?? 'Pengguna';
    }
}

if (! function_exists('format_ukuran_file')) {
    function format_ukuran_file(?int $bytes): string
    {
        if (empty($bytes)) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (! function_exists('bisa_preview_file')) {
    function bisa_preview_file(?string $ekstensi): bool
    {
        if (empty($ekstensi)) {
            return false;
        }

        $ekstensi = strtolower($ekstensi);

        return in_array($ekstensi, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'txt'], true);
    }
}

if (! function_exists('sanitize_external_dokumen_link')) {
    function sanitize_external_dokumen_link(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '' || str_contains($url, "\0")) {
            return '';
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = trim((string) ($parts['host'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return '';
        }

        return $url;
    }
}

if (! function_exists('is_safe_external_dokumen_link')) {
    function is_safe_external_dokumen_link(?string $url): bool
    {
        return sanitize_external_dokumen_link($url) !== '';
    }
}

if (! function_exists('dokumen_preview_embed_link')) {
    function dokumen_preview_embed_link(?string $url): string
    {
        $safeUrl = sanitize_external_dokumen_link($url);
        if ($safeUrl === '') {
            return '';
        }

        $parts = parse_url($safeUrl);
        if (! is_array($parts)) {
            return $safeUrl;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');

        if (str_contains($host, 'drive.google.com')) {
            if (preg_match('#/file/d/([^/]+)#', $path, $matches)) {
                return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
            }

            parse_str($query, $queryParams);
            $id = trim((string) ($queryParams['id'] ?? ''));
            if ($id !== '') {
                return 'https://drive.google.com/file/d/' . $id . '/preview';
            }
        }

        if (str_contains($host, 'docs.google.com') && preg_match('#/(document|spreadsheets|presentation)/d/([^/]+)#', $path, $matches)) {
            return 'https://docs.google.com/' . $matches[1] . '/d/' . $matches[2] . '/preview';
        }

        return $safeUrl;
    }
}

if (! function_exists('unit_kerja_user_login')) {
    function unit_kerja_user_login(): string
    {
        return (string) (session()->get('unit_kerja') ?? '');
    }
}

if (! function_exists('user_accessible_program_studi_ids')) {
    function user_accessible_program_studi_ids(): array
    {
        if (! is_login()) {
            return [];
        }

        $programStudiIds = [];
        $primaryProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
        if ($primaryProgramStudiId > 0) {
            $programStudiIds[$primaryProgramStudiId] = $primaryProgramStudiId;
        }

        $assignedProgramStudiIds = session()->get('assigned_program_studi_ids');
        if (is_array($assignedProgramStudiIds)) {
            foreach ($assignedProgramStudiIds as $assignedProgramStudiId) {
                $safeId = (int) $assignedProgramStudiId;
                if ($safeId > 0) {
                    $programStudiIds[$safeId] = $safeId;
                }
            }
        }

        return array_values($programStudiIds);
    }
}

if (! function_exists('can_access_dokumen')) {
    function can_access_dokumen(array $dokumen): bool
    {
        if (! is_login()) {
            return false;
        }

        if (has_role(['admin', 'lpm'])) {
            return true;
        }

        $userId    = (int) (session()->get('user_id') ?? 0);
        $docProdiId = (int) ($dokumen['program_studi_id'] ?? 0);
        $uploadedBy = (int) ($dokumen['uploaded_by'] ?? 0);

        if (has_role('dekan')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if ($docProdiId > 0 && in_array($docProdiId, $accessibleProgramStudiIds, true)) {
                return true;
            }

            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            if ($userUppsId <= 0 || $docProdiId <= 0) {
                return false;
            }

            static $cacheProdiUpps = [];
            if (! array_key_exists($docProdiId, $cacheProdiUpps)) {
                try {
                    $prodi = (new \App\Models\ProgramStudiModel())->find($docProdiId);
                    $cacheProdiUpps[$docProdiId] = (int) ($prodi['upps_id'] ?? 0);
                } catch (\Throwable $e) {
                    $cacheProdiUpps[$docProdiId] = 0;
                }
            }

            return $cacheProdiUpps[$docProdiId] > 0 && $cacheProdiUpps[$docProdiId] === $userUppsId;
        }

        if (has_role('kaprodi')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            return $docProdiId > 0 && in_array($docProdiId, $accessibleProgramStudiIds, true);
        }

        if (has_role('dosen')) {
            if ($uploadedBy === $userId) {
                return true;
            }

            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            return $docProdiId > 0 && in_array($docProdiId, $accessibleProgramStudiIds, true);
        }

        return false;
    }
}

if (! function_exists('can_access_program_studi')) {
    function can_access_program_studi(int $programStudiId): bool
    {
        if (! is_login() || $programStudiId <= 0) {
            return false;
        }

        if (has_role(['admin', 'lpm'])) {
            return true;
        }

        if (has_role('dekan')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (in_array($programStudiId, $accessibleProgramStudiIds, true)) {
                return true;
            }

            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            if ($userUppsId <= 0) {
                return false;
            }

            try {
                $prodi = (new \App\Models\ProgramStudiModel())->find($programStudiId);
                return (int) ($prodi['upps_id'] ?? 0) === $userUppsId;
            } catch (\Throwable $e) {
                return false;
            }
        }

        if (has_role('kaprodi')) {
            return in_array($programStudiId, user_accessible_program_studi_ids(), true);
        }

        if (has_role('dosen')) {
            return in_array($programStudiId, user_accessible_program_studi_ids(), true);
        }

        return false;
    }
}

if (! function_exists('can_upload_to_program_studi')) {
    function can_upload_to_program_studi(int $programStudiId): bool
    {
        return can_access_program_studi($programStudiId);
    }
}

if (! function_exists('can_manage_dokumen')) {
    function can_manage_dokumen(array $dokumen): bool
    {
        if (! is_login()) {
            return false;
        }

        if (has_role(['admin', 'lpm'])) {
            return true;
        }

        $userId    = (int) (session()->get('user_id') ?? 0);
        $docProdiId = (int) ($dokumen['program_studi_id'] ?? 0);
        $uploadedBy = (int) ($dokumen['uploaded_by'] ?? 0);

        if (has_role('dekan')) {
            return false;
        }

        if (has_role('kaprodi')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            return $docProdiId > 0 && in_array($docProdiId, $accessibleProgramStudiIds, true);
        }

        if (has_role('dosen')) {
            return $uploadedBy === $userId;
        }

        return false;
    }
}

if (! function_exists('can_review_dokumen')) {
    function can_review_dokumen(array $dokumen): bool
    {
        if (! is_login()) {
            return false;
        }

        return has_role(['admin', 'lpm']) && can_access_dokumen($dokumen);
    }
}

if (! function_exists('can_final_validate_dokumen')) {
    function can_final_validate_dokumen(array $dokumen): bool
    {
        if (! is_login()) {
            return false;
        }

        if (! has_role(['admin', 'lpm'])) {
            return false;
        }

        return can_access_dokumen($dokumen);
    }
}
if (! function_exists('catat_audit')) {
    function catat_audit(
        string $aktivitas,
        ?string $modul = null,
        ?int $targetId = null,
        ?string $deskripsi = null,
        ?array $overrideUser = null
    ): void {
        try {
            $auditModel = new \App\Models\AuditTrailModel();

            $request = service('request');

            $userId     = $overrideUser['user_id'] ?? session()->get('user_id');
            $namaUser   = $overrideUser['nama_user'] ?? session()->get('nama_lengkap');
            $username   = $overrideUser['username'] ?? session()->get('username');
            $roleUser   = $overrideUser['role_user'] ?? implode(', ', session()->get('role_names') ?? []);
            $unitKerja  = $overrideUser['unit_kerja'] ?? session()->get('unit_kerja');

            $auditModel->insert([
                'user_id'    => $userId ?: null,
                'nama_user'  => $namaUser ?: null,
                'username'   => $username ?: null,
                'role_user'  => $roleUser ?: null,
                'unit_kerja' => $unitKerja ?: null,
                'aktivitas'  => $aktivitas,
                'modul'      => $modul,
                'target_id'  => $targetId,
                'deskripsi'  => $deskripsi,
                'ip_address' => method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
                'user_agent' => method_exists($request, 'getUserAgent') ? (string) $request->getUserAgent() : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // sengaja diam agar audit gagal tidak memutus proses utama
        }
    }
}

if (! function_exists('app_settings_map')) {
    function app_settings_map(): array
    {
        static $cache = null;
        if (is_array($cache)) {
            return $cache;
        }

        try {
            $cache = (new \App\Models\AppSettingModel())->getAllAsMap();
        } catch (\Throwable $e) {
            $cache = [];
        }

        return $cache;
    }
}

if (! function_exists('app_setting')) {
    function app_setting(string $key, ?string $default = null): ?string
    {
        $map = app_settings_map();
        if (! array_key_exists($key, $map)) {
            return $default;
        }

        $value = trim((string) $map[$key]);
        return $value !== '' ? $value : $default;
    }
}

if (! function_exists('app_asset_url')) {
    function app_asset_url(?string $path): string
    {
        $path = trim((string) ($path ?? ''));
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return base_url('/' . ltrim($path, '/'));
    }
}

if (! function_exists('app_logo_header_url')) {
    function app_logo_header_url(): string
    {
        return app_asset_url(app_setting('logo_header_path', ''));
    }
}

if (! function_exists('app_favicon_url')) {
    function app_favicon_url(): string
    {
        $favicon = app_asset_url(app_setting('favicon_path', ''));
        if ($favicon !== '') {
            return $favicon;
        }

        return base_url('/favicon.ico');
    }
}

if (! function_exists('profil_pt_data')) {
    function profil_pt_data(): ?array
    {
        static $cache = null;
        if (is_array($cache) || $cache === null) {
            try {
                $cache = (new \App\Models\ProfilPtModel())->getSingleton();
            } catch (\Throwable $e) {
                $cache = null;
            }
        }

        return $cache;
    }
}

if (! function_exists('profil_pt_nama')) {
    function profil_pt_nama(string $default = 'Universitas San Pedro'): string
    {
        $profil = profil_pt_data();
        $nama = trim((string) ($profil['nama_pt'] ?? ''));
        return $nama !== '' ? $nama : $default;
    }
}
