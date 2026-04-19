<?php

namespace App\Controllers;

use App\Models\UserProgramStudiAssignmentModel;
use App\Models\UserModel;
use Config\Services;

class AuthController extends BaseController
{
    private const LOGIN_MAX_ATTEMPTS_PER_IP = 10;
    private const LOGIN_MAX_ATTEMPTS_PER_IDENTITY = 5;
    private const LOGIN_ATTEMPT_WINDOW_SECONDS = 900;
    private const DUMMY_PASSWORD_HASH = '$2y$10$w4LqWnO4U4f3P3kD6v6ZLe8M66A1sH5t7m4XqQ3i9l4m9JmQ4y8QW';

    public function login()
    {
        if (is_login()) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'title' => 'Login SIPADUKAR v2',
        ]);
    }

    public function prosesLogin()
    {
        $session = session();
        $rules = [
            'identity' => 'required|max_length[150]',
            'password' => 'required|min_length[6]|max_length[72]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username/email dan password wajib diisi dengan benar.');
        }

        $identity = trim((string) $this->request->getPost('identity'));
        $normalizedIdentity = mb_strtolower($identity);
        $password = (string) $this->request->getPost('password');
        $identityFingerprint = $this->getIdentityFingerprint($normalizedIdentity);

        if ($this->isLoginLocked($normalizedIdentity)) {
            catat_audit(
                'login_lockout',
                'auth',
                null,
                'Percobaan login ditolak karena lockout. identity_fp=' . $identityFingerprint
            );
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.');
        }

        $userModel = new UserModel();

        $user = $userModel
            ->groupStart()
                ->where('username', $identity)
                ->orWhere('email', $identity)
            ->groupEnd()
            ->where('is_aktif', 1)
            ->first();

        if (! $user) {
            password_verify($password, self::DUMMY_PASSWORD_HASH);
            $this->recordFailedLoginAttempt($normalizedIdentity);
            catat_audit(
                'login_gagal',
                'auth',
                null,
                'Percobaan login gagal. identity_fp=' . $identityFingerprint
            );
            return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
        }

        if (! password_verify($password, $user['password_hash'])) {
            $this->recordFailedLoginAttempt($normalizedIdentity);
            catat_audit(
                'login_gagal',
                'auth',
                (int) ($user['id'] ?? 0),
                'Percobaan login gagal. identity_fp=' . $identityFingerprint
            );
            return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
        }

        $ipAttemptsBeforeClear = $this->getAttemptCount($this->getIpAttemptKey());
        $identityAttemptsBeforeClear = $this->getAttemptCount($this->getIdentityAttemptKey($normalizedIdentity));
        $this->clearLoginAttempts($normalizedIdentity);

        if ($ipAttemptsBeforeClear > 0 || $identityAttemptsBeforeClear > 0) {
            catat_audit(
                'login_reset_attempt',
                'auth',
                (int) ($user['id'] ?? 0),
                'Counter percobaan login direset setelah login sukses. identity_fp=' . $identityFingerprint
            );
        }

        $session->regenerate(true);

        $roles = $userModel->getRolesByUserId((int) $user['id']);
        $assignedProgramStudiIds = (new UserProgramStudiAssignmentModel())
            ->getProgramStudiIdsByUserId((int) $user['id']);
        $roleSlugs = array_map(static fn ($role) => $role['slug_role'], $roles);
        $roleNames = array_map(static fn ($role) => $role['nama_role'], $roles);

        $session->set([
            'isLoggedIn'   => true,
            'user_id'      => $user['id'],
            'nama_lengkap' => $user['nama_lengkap'],
            'username'     => $user['username'],
            'email'        => $user['email'],
            'unit_kerja'   => $user['unit_kerja'],
            'program_studi_id' => $user['program_studi_id'] ?? null,
            'assigned_program_studi_ids' => $assignedProgramStudiIds,
            'upps_id'      => $user['upps_id'] ?? null,
            'roles'        => $roleSlugs,
            'role_names'   => $roleNames,
        ]);

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            $userModel->update((int) $user['id'], [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        $userModel->update($user['id'], [
            'terakhir_login' => date('Y-m-d H:i:s'),
        ]);

        catat_audit(
            'login',
            'auth',
            (int) $user['id'],
            'User login ke sistem.'
        );

        return redirect()->to('/dashboard')->with('success', 'Login berhasil.');
    }

    public function logout()
    {
        $session = session();
        $overrideUser = [
            'user_id'    => $session->get('user_id'),
            'nama_user'  => $session->get('nama_lengkap'),
            'username'   => $session->get('username'),
            'role_user'  => implode(', ', $session->get('role_names') ?? []),
            'unit_kerja' => $session->get('unit_kerja'),
        ];

        catat_audit(
            'logout',
            'auth',
            (int) ($session->get('user_id') ?? 0),
            'User logout dari sistem.',
            $overrideUser
        );

        $session->destroy();

        return redirect()->to('/login')->with('success', 'Anda berhasil logout.');
    }

    private function getClientIpAddress(): string
    {
        return trim((string) $this->request->getIPAddress()) ?: 'unknown';
    }

    private function getIpAttemptKey(): string
    {
        return 'auth_login_ip_' . sha1($this->getClientIpAddress());
    }

    private function getIdentityAttemptKey(string $identity): string
    {
        return 'auth_login_identity_' . sha1($this->getClientIpAddress() . '|' . $identity);
    }

    private function getIdentityFingerprint(string $identity): string
    {
        $normalized = trim(mb_strtolower($identity));
        if ($normalized === '') {
            return 'unknown';
        }

        return substr(hash('sha256', $normalized), 0, 16);
    }

    private function getAttemptCount(string $key): int
    {
        $value = cache($key);
        return is_int($value) ? $value : (int) $value;
    }

    private function isLoginLocked(string $identity): bool
    {
        return $this->getAttemptCount($this->getIpAttemptKey()) >= self::LOGIN_MAX_ATTEMPTS_PER_IP
            || $this->getAttemptCount($this->getIdentityAttemptKey($identity)) >= self::LOGIN_MAX_ATTEMPTS_PER_IDENTITY;
    }

    private function recordFailedLoginAttempt(string $identity): void
    {
        $ipKey = $this->getIpAttemptKey();
        $identityKey = $this->getIdentityAttemptKey($identity);

        Services::cache()->save($ipKey, $this->getAttemptCount($ipKey) + 1, self::LOGIN_ATTEMPT_WINDOW_SECONDS);
        Services::cache()->save($identityKey, $this->getAttemptCount($identityKey) + 1, self::LOGIN_ATTEMPT_WINDOW_SECONDS);
    }

    private function clearLoginAttempts(string $identity): void
    {
        Services::cache()->delete($this->getIpAttemptKey());
        Services::cache()->delete($this->getIdentityAttemptKey($identity));
    }
}
