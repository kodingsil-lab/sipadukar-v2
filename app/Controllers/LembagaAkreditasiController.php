<?php

namespace App\Controllers;

use App\Models\LembagaAkreditasiModel;

class LembagaAkreditasiController extends BaseController
{
    private const UPLOAD_DIR = 'uploads/lembaga-akreditasi';

    protected LembagaAkreditasiModel $model;

    public function __construct()
    {
        $this->model = new LembagaAkreditasiModel();
    }

    public function index()
    {
        $list = $this->model->orderBy('nama_lembaga_akreditasi', 'ASC')->findAll();

        return view('lembaga_akreditasi/index', [
            'title' => 'Lembaga Akreditasi',
            'list'  => $list,
        ]);
    }

    public function create()
    {
        return view('lembaga_akreditasi/form', [
            'title' => 'Tambah Lembaga Akreditasi',
            'mode'  => 'create',
            'data'  => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Data lembaga akreditasi belum valid.');
        }

        if (! $this->validateLogoIfUploaded()) {
            return redirect()->back()->withInput()->with('error', 'File logo lembaga tidak valid.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $payload = [
            'nama_lembaga_akreditasi' => trim((string) $this->request->getPost('nama_lembaga_akreditasi')),
            'nama_singkatan'          => trim((string) $this->request->getPost('nama_singkatan')),
            'alamat_website'          => trim((string) $this->request->getPost('alamat_website')),
            'is_aktif'                => (int) $this->request->getPost('is_aktif'),
            'created_by'              => $userId > 0 ? $userId : null,
            'updated_by'              => $userId > 0 ? $userId : null,
        ];

        $logoFile = $this->request->getFile('logo_lembaga');
        if ($this->hasUploadedFile($logoFile)) {
            $storedPath = $this->storeAsset($logoFile, 'logo-lembaga');
            if ($storedPath === null) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file logo lembaga.');
            }

            $payload['logo_path'] = $storedPath;
        }

        $this->model->insert($payload);
        $lembagaId = (int) $this->model->getInsertID();

        catat_audit(
            'tambah_lembaga_akreditasi',
            'lembaga_akreditasi',
            $lembagaId,
            'Menambahkan lembaga akreditasi: ' . (string) ($payload['nama_lembaga_akreditasi'] ?? '-')
        );

        return redirect()->to('/lembaga-akreditasi')->with('success', 'Lembaga akreditasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = $this->model->find((int) $id);

        if (! $data) {
            return redirect()->to('/lembaga-akreditasi')->with('error', 'Data lembaga akreditasi tidak ditemukan.');
        }

        return view('lembaga_akreditasi/form', [
            'title' => 'Edit Lembaga Akreditasi',
            'mode'  => 'edit',
            'data'  => $data,
        ]);
    }

    public function update($id)
    {
        $row = $this->model->find((int) $id);

        if (! $row) {
            return redirect()->to('/lembaga-akreditasi')->with('error', 'Data lembaga akreditasi tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Data lembaga akreditasi belum valid.');
        }

        if (! $this->validateLogoIfUploaded()) {
            return redirect()->back()->withInput()->with('error', 'File logo lembaga tidak valid.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $payload = [
            'nama_lembaga_akreditasi' => trim((string) $this->request->getPost('nama_lembaga_akreditasi')),
            'nama_singkatan'          => trim((string) $this->request->getPost('nama_singkatan')),
            'alamat_website'          => trim((string) $this->request->getPost('alamat_website')),
            'is_aktif'                => (int) $this->request->getPost('is_aktif'),
            'updated_by'              => $userId > 0 ? $userId : null,
        ];

        $logoFile = $this->request->getFile('logo_lembaga');
        if ($this->hasUploadedFile($logoFile)) {
            $storedPath = $this->storeAsset($logoFile, 'logo-lembaga');
            if ($storedPath === null) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file logo lembaga.');
            }

            $payload['logo_path'] = $storedPath;
            $this->removeAsset((string) ($row['logo_path'] ?? ''));
        }

        $this->model->update((int) $id, $payload);

        catat_audit(
            'edit_lembaga_akreditasi',
            'lembaga_akreditasi',
            (int) $id,
            'Memperbarui lembaga akreditasi: ' . (string) ($payload['nama_lembaga_akreditasi'] ?? ($row['nama_lembaga_akreditasi'] ?? '-'))
        );

        return redirect()->to('/lembaga-akreditasi')->with('success', 'Lembaga akreditasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $row = $this->model->find((int) $id);

        if (! $row) {
            return redirect()->to('/lembaga-akreditasi')->with('error', 'Data lembaga akreditasi tidak ditemukan.');
        }

        $this->removeAsset((string) ($row['logo_path'] ?? ''));
        $this->model->delete((int) $id);

        catat_audit(
            'hapus_lembaga_akreditasi',
            'lembaga_akreditasi',
            (int) $id,
            'Menghapus lembaga akreditasi: ' . (string) ($row['nama_lembaga_akreditasi'] ?? ('ID ' . $id))
        );

        return redirect()->to('/lembaga-akreditasi')->with('success', 'Lembaga akreditasi berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'nama_lembaga_akreditasi' => 'required|min_length[2]|max_length[150]',
            'nama_singkatan'          => 'permit_empty|max_length[50]',
            'alamat_website'          => 'permit_empty|valid_url_strict|max_length[255]',
            'is_aktif'                => 'required|in_list[0,1]',
        ];
    }

    private function validateLogoIfUploaded(): bool
    {
        $logoFile = $this->request->getFile('logo_lembaga');
        if (! $logoFile || (int) $logoFile->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        return $this->validate([
            'logo_lembaga' => 'uploaded[logo_lembaga]|max_size[logo_lembaga,2048]|is_image[logo_lembaga]|mime_in[logo_lembaga,image/png,image/jpeg,image/webp]',
        ]);
    }

    private function hasUploadedFile($file): bool
    {
        return $file && $file->isValid() && ! $file->hasMoved() && (int) $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    private function storeAsset($file, string $prefix): ?string
    {
        $storagePath = FCPATH . self::UPLOAD_DIR;
        if (! is_dir($storagePath)) {
            @mkdir($storagePath, 0775, true);
        }

        try {
            $ext = strtolower((string) ($file->getExtension() ?: $file->getClientExtension()));
            if ($ext === '') {
                return null;
            }

            $filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move($storagePath, $filename, true);

            return self::UPLOAD_DIR . '/' . $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function removeAsset(?string $relativePath): void
    {
        $relativePath = ltrim(trim((string) ($relativePath ?? '')), '/');
        if ($relativePath === '' || preg_match('#^https?://#i', $relativePath)) {
            return;
        }

        $uploadPrefix = self::UPLOAD_DIR . '/';
        if (! str_starts_with(str_replace('\\', '/', $relativePath), $uploadPrefix)) {
            return;
        }

        $fullPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
