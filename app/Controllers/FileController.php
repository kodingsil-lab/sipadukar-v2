<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\DocumentModel;
use App\Models\InstrumenModel;
use App\Models\PeraturanModel;
use App\Models\ProfilPtModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class FileController extends BaseController
{
    private const ALLOWED_PREVIEW_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'text/plain',
    ];

    private function ensureMasterFileReadable(): void
    {
        if (! session()->get('isLoggedIn') || ! has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen'])) {
            throw PageNotFoundException::forPageNotFound('File tidak dapat diakses.');
        }
    }

    private function ensureAdminOrLpmReadable(): void
    {
        if (! session()->get('isLoggedIn') || ! has_role(['admin', 'lpm'])) {
            throw PageNotFoundException::forPageNotFound('File tidak dapat diakses.');
        }
    }

    private function isDokumenPublicAccessible(array $dokumen): bool
    {
        return ($dokumen['status_dokumen'] ?? '') === 'tervalidasi'
            && (int) ($dokumen['is_aktif'] ?? 0) === 1;
    }

    private function getUploadsRootPath(): string
    {
        $uploadsRoot = realpath(WRITEPATH . 'uploads');
        if ($uploadsRoot === false) {
            throw PageNotFoundException::forPageNotFound('Direktori upload tidak tersedia.');
        }

        return rtrim(str_replace('\\', '/', $uploadsRoot), '/');
    }

    private function resolveStoredFilePath(?string $storedPath): string
    {
        $storedPath = trim((string) $storedPath);
        if ($storedPath === '') {
            throw PageNotFoundException::forPageNotFound('Referensi file tidak valid.');
        }

        $normalizedRelativePath = str_replace('\\', '/', $storedPath);
        if (str_contains($normalizedRelativePath, "\0")
            || str_starts_with($normalizedRelativePath, '/')
            || preg_match('/^[A-Za-z]:\//', $normalizedRelativePath) === 1
            || ! str_starts_with($normalizedRelativePath, 'uploads/')) {
            throw PageNotFoundException::forPageNotFound('Referensi file tidak valid.');
        }

        $absolutePath = realpath(WRITEPATH . $normalizedRelativePath);
        if ($absolutePath === false || ! is_file($absolutePath)) {
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);
        $uploadsRoot = $this->getUploadsRootPath();
        if ($normalizedAbsolutePath !== $uploadsRoot && ! str_starts_with($normalizedAbsolutePath, $uploadsRoot . '/')) {
            throw PageNotFoundException::forPageNotFound('Akses file ditolak.');
        }

        return $absolutePath;
    }

    private function resolvePublicStoredFilePath(?string $storedPath, string $allowedPrefix): string
    {
        $storedPath = trim((string) $storedPath);
        if ($storedPath === '') {
            throw PageNotFoundException::forPageNotFound('Referensi file tidak valid.');
        }

        $normalizedRelativePath = str_replace('\\', '/', $storedPath);
        if (str_contains($normalizedRelativePath, "\0")
            || str_starts_with($normalizedRelativePath, '/')
            || preg_match('/^[A-Za-z]:\//', $normalizedRelativePath) === 1
            || ! str_starts_with($normalizedRelativePath, $allowedPrefix)) {
            throw PageNotFoundException::forPageNotFound('Referensi file tidak valid.');
        }

        $absolutePath = realpath(FCPATH . $normalizedRelativePath);
        if ($absolutePath === false || ! is_file($absolutePath)) {
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

        $publicPrefixAbsolute = realpath(FCPATH . rtrim($allowedPrefix, '/'));
        if ($publicPrefixAbsolute === false) {
            throw PageNotFoundException::forPageNotFound('Direktori file tidak tersedia.');
        }

        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);
        $normalizedAllowedRoot = rtrim(str_replace('\\', '/', $publicPrefixAbsolute), '/');
        if ($normalizedAbsolutePath !== $normalizedAllowedRoot && ! str_starts_with($normalizedAbsolutePath, $normalizedAllowedRoot . '/')) {
            throw PageNotFoundException::forPageNotFound('Akses file ditolak.');
        }

        return $absolutePath;
    }

    private function resolveProfilPtStoredFilePath(?string $storedPath): string
    {
        try {
            return $this->resolveStoredFilePath($storedPath);
        } catch (\Throwable $e) {
            $legacyPath = $this->resolvePublicStoredFilePath($storedPath, 'uploads/profil-pt/');

            // Best-effort migration from legacy public storage into writable/private storage.
            $relativePath = str_replace('\\', '/', trim((string) $storedPath));
            $targetPath = WRITEPATH . $relativePath;
            $targetDir = dirname($targetPath);
            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            if (! is_file($targetPath)) {
                @copy($legacyPath, $targetPath);
            }

            if (is_file($targetPath)) {
                return $targetPath;
            }

            return $legacyPath;
        }
    }

    private function sanitizeDownloadFilename(?string $originalName, string $fallbackPath): string
    {
        $filename = trim((string) $originalName);
        if ($filename === '') {
            $filename = basename($fallbackPath);
        }

        $filename = str_replace(["\r", "\n", '"'], ['', '', ''], $filename);
        return $filename !== '' ? $filename : basename($fallbackPath);
    }

    private function respondWithInlineFile(string $filePath, ?string $fileName)
    {
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        if (! in_array($mime, self::ALLOWED_PREVIEW_MIME_TYPES, true)) {
            return redirect()->back()->with('error', 'Tipe file ini belum mendukung preview langsung. Silakan download file.');
        }

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $this->sanitizeDownloadFilename($fileName, $filePath) . '"')
            ->setBody(file_get_contents($filePath));
    }

    public function dokumen($id)
    {
        $dokumenModel = new DokumenModel();
        $dokumen = $dokumenModel->find((int) $id);

        if (! $dokumen) {
            throw PageNotFoundException::forPageNotFound('File dokumen tidak ditemukan.');
        }

        if (! $this->isDokumenPublicAccessible($dokumen)) {
            if (! session()->get('isLoggedIn') || ! can_access_dokumen($dokumen)) {
                throw PageNotFoundException::forPageNotFound('File dokumen tidak dapat diakses.');
            }
        }

        if (! session()->get('isLoggedIn') && ! $this->isDokumenPublicAccessible($dokumen)) {
            throw PageNotFoundException::forPageNotFound('File dokumen publik tidak ditemukan.');
        }

        $safeExternalLink = sanitize_external_dokumen_link((string) ($dokumen['link_dokumen'] ?? ''));
        if (($dokumen['sumber_dokumen'] ?? '') === 'link' && $safeExternalLink !== '') {
            return redirect()->to($safeExternalLink);
        }

        if (empty($dokumen['path_file'])) {
            throw PageNotFoundException::forPageNotFound('File dokumen tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($dokumen['path_file'] ?? null);

        return $this->response->download($filePath, null)->setFileName($this->sanitizeDownloadFilename($dokumen['nama_file'] ?? null, $filePath));
    }

    public function previewDokumen($id)
    {
        $dokumenModel = new DokumenModel();
        $dokumen = $dokumenModel->find((int) $id);

        if (! $dokumen) {
            throw PageNotFoundException::forPageNotFound('File dokumen tidak ditemukan.');
        }

        if (! $this->isDokumenPublicAccessible($dokumen)) {
            if (! session()->get('isLoggedIn') || ! can_access_dokumen($dokumen)) {
                throw PageNotFoundException::forPageNotFound('File dokumen tidak dapat diakses.');
            }
        }

        if (! session()->get('isLoggedIn') && ! $this->isDokumenPublicAccessible($dokumen)) {
            throw PageNotFoundException::forPageNotFound('File dokumen publik tidak ditemukan.');
        }

        $safeExternalLink = sanitize_external_dokumen_link((string) ($dokumen['link_dokumen'] ?? ''));
        if (($dokumen['sumber_dokumen'] ?? '') === 'link' && $safeExternalLink !== '') {
            return redirect()->to($safeExternalLink);
        }

        if (empty($dokumen['path_file'])) {
            throw PageNotFoundException::forPageNotFound('File dokumen tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($dokumen['path_file'] ?? null);

        return $this->respondWithInlineFile($filePath, $dokumen['nama_file'] ?? null);
    }

    public function peraturan($id)
    {
        $this->ensureMasterFileReadable();

        $peraturanModel = new PeraturanModel();
        $peraturan = $peraturanModel->find((int) $id);

        if (! $peraturan || empty($peraturan['path_file'])) {
            throw PageNotFoundException::forPageNotFound('File peraturan tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($peraturan['path_file'] ?? null);

        return $this->response->download($filePath, null)->setFileName($this->sanitizeDownloadFilename($peraturan['nama_file'] ?? null, $filePath));
    }

    public function previewPeraturan($id)
    {
        $this->ensureMasterFileReadable();

        $peraturanModel = new PeraturanModel();
        $peraturan = $peraturanModel->find((int) $id);

        if (! $peraturan || empty($peraturan['path_file'])) {
            throw PageNotFoundException::forPageNotFound('File peraturan tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($peraturan['path_file'] ?? null);

        return $this->respondWithInlineFile($filePath, $peraturan['nama_file'] ?? null);
    }

    public function instrumen($id)
    {
        $this->ensureMasterFileReadable();

        $instrumenModel = new InstrumenModel();
        $instrumen = $instrumenModel->find((int) $id);

        if (! $instrumen || empty($instrumen['path_file'])) {
            throw PageNotFoundException::forPageNotFound('File instrumen tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($instrumen['path_file'] ?? null);

        return $this->response->download($filePath, null)->setFileName($this->sanitizeDownloadFilename($instrumen['nama_file'] ?? null, $filePath));
    }

    public function previewInstrumen($id)
    {
        $this->ensureMasterFileReadable();

        $instrumenModel = new InstrumenModel();
        $instrumen = $instrumenModel->find((int) $id);

        if (! $instrumen || empty($instrumen['path_file'])) {
            throw PageNotFoundException::forPageNotFound('File instrumen tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($instrumen['path_file'] ?? null);

        return $this->respondWithInlineFile($filePath, $instrumen['nama_file'] ?? null);
    }

    public function profilPtSk()
    {
        $this->ensureAdminOrLpmReadable();

        $profil = (new ProfilPtModel())->getSingleton();
        if (! $profil || empty($profil['file_sk_akreditasi_path'])) {
            throw PageNotFoundException::forPageNotFound('File SK akreditasi tidak ditemukan.');
        }

        $filePath = $this->resolveProfilPtStoredFilePath($profil['file_sk_akreditasi_path']);

        return $this->response->download($filePath, null)->setFileName($this->sanitizeDownloadFilename('SK-Akreditasi-PT.' . pathinfo($filePath, PATHINFO_EXTENSION), $filePath));
    }

    public function profilPtSertifikat()
    {
        $this->ensureAdminOrLpmReadable();

        $profil = (new ProfilPtModel())->getSingleton();
        if (! $profil || empty($profil['file_sertifikat_akreditasi_path'])) {
            throw PageNotFoundException::forPageNotFound('File sertifikat akreditasi tidak ditemukan.');
        }

        $filePath = $this->resolveProfilPtStoredFilePath($profil['file_sertifikat_akreditasi_path']);

        return $this->response->download($filePath, null)->setFileName($this->sanitizeDownloadFilename('Sertifikat-Akreditasi-PT.' . pathinfo($filePath, PATHINFO_EXTENSION), $filePath));
    }

    public function legacyDocument($id)
    {
        $this->ensureAdminOrLpmReadable();

        $document = (new DocumentModel())->find((int) $id);
        if (! $document || empty($document['file_path'])) {
            throw PageNotFoundException::forPageNotFound('File dokumen tidak ditemukan.');
        }

        $filePath = $this->resolveStoredFilePath($document['file_path'] ?? null);
        return $this->response->download($filePath, null)->setFileName($this->sanitizeDownloadFilename($document['title'] ?? null, $filePath));
    }
}
