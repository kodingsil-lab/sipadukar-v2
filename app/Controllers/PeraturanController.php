<?php

namespace App\Controllers;

use App\Models\PeraturanModel;
use CodeIgniter\HTTP\RedirectResponse;

class PeraturanController extends BaseController
{
    protected PeraturanModel $peraturanModel;

    public function __construct()
    {
        $this->peraturanModel = new PeraturanModel();
    }

    private function ensureReadable(): ?RedirectResponse
    {
        if (has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen'])) {
            return null;
        }

        return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke data peraturan.');
    }

    private function ensureManageable(): ?RedirectResponse
    {
        if (has_role(['admin', 'lpm'])) {
            return null;
        }

        return redirect()->to('/peraturan')->with('error', 'Hanya Admin/LPM yang dapat mengubah data peraturan.');
    }

    public function index()
    {
        if ($guard = $this->ensureReadable()) {
            return $guard;
        }

        $peraturanList = $this->peraturanModel
            ->where('deleted_at', null)
            ->orderBy('tahun', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('peraturan/index', [
            'title'         => 'Peraturan',
            'peraturanList' => $peraturanList,
        ]);
    }

    public function create()
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        return view('peraturan/form', [
            'title' => 'Tambah Peraturan',
            'mode'  => 'create',
            'data'  => null,
        ]);
    }

    public function store()
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $rules = [
            'judul'            => 'required|min_length[3]|max_length[255]',
            'kategori'         => 'permit_empty|max_length[100]',
            'nomor_peraturan'  => 'permit_empty|max_length[100]',
            'tahun'            => 'permit_empty|integer',
            'deskripsi'        => 'permit_empty',
            'tanggal_terbit'   => 'permit_empty|valid_date',
            'is_aktif'         => 'required|in_list[0,1]',
            'file_peraturan'   => 'uploaded[file_peraturan]|max_size[file_peraturan,10240]|ext_in[file_peraturan,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar]|mime_in[file_peraturan,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,application/zip,application/x-rar-compressed,application/x-zip-compressed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data peraturan belum valid.');
        }

        $file = $this->request->getFile('file_peraturan');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'File peraturan gagal diunggah.');
        }

        $folderTujuan = 'uploads/peraturan';

        if (! is_dir(WRITEPATH . $folderTujuan)) {
            mkdir(WRITEPATH . $folderTujuan, 0755, true);
        }

        $namaFileSimpan = $file->getRandomName();
        $file->move(WRITEPATH . $folderTujuan, $namaFileSimpan);

        $payload = [
            'judul'           => trim((string) $this->request->getPost('judul')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'nomor_peraturan' => trim((string) $this->request->getPost('nomor_peraturan')),
            'tahun'           => $this->request->getPost('tahun') !== '' ? (int) $this->request->getPost('tahun') : null,
            'deskripsi'       => trim((string) $this->request->getPost('deskripsi')),
            'nama_file'       => $file->getClientName(),
            'path_file'       => $folderTujuan . '/' . $namaFileSimpan,
            'ekstensi_file'   => $file->getExtension(),
            'ukuran_file'     => $file->getSize(),
            'tanggal_terbit'  => $this->request->getPost('tanggal_terbit') ?: null,
            'is_aktif'        => (int) $this->request->getPost('is_aktif'),
            'dibuat_oleh'     => session()->get('user_id'),
            'diupdate_oleh'   => session()->get('user_id'),
        ];

        $this->peraturanModel->insert($payload);
        $peraturanId = (int) $this->peraturanModel->getInsertID();

        catat_audit(
            'tambah_peraturan',
            'peraturan',
            $peraturanId,
            'Menambahkan peraturan: ' . (string) ($payload['judul'] ?? '-')
        );

        return redirect()->to('/peraturan')->with('success', 'Peraturan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $data = $this->peraturanModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/peraturan')->with('error', 'Data peraturan tidak ditemukan.');
        }

        return view('peraturan/form', [
            'title' => 'Edit Peraturan',
            'mode'  => 'edit',
            'data'  => $data,
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $data = $this->peraturanModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/peraturan')->with('error', 'Data peraturan tidak ditemukan.');
        }

        $rules = [
            'judul'            => 'required|min_length[3]|max_length[255]',
            'kategori'         => 'permit_empty|max_length[100]',
            'nomor_peraturan'  => 'permit_empty|max_length[100]',
            'tahun'            => 'permit_empty|integer',
            'deskripsi'        => 'permit_empty',
            'tanggal_terbit'   => 'permit_empty|valid_date',
            'is_aktif'         => 'required|in_list[0,1]',
            'file_peraturan'   => 'if_exist|max_size[file_peraturan,10240]|ext_in[file_peraturan,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar]|mime_in[file_peraturan,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,application/zip,application/x-rar-compressed,application/x-zip-compressed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data peraturan belum valid.');
        }

        $dataUpdate = [
            'judul'           => trim((string) $this->request->getPost('judul')),
            'kategori'        => trim((string) $this->request->getPost('kategori')),
            'nomor_peraturan' => trim((string) $this->request->getPost('nomor_peraturan')),
            'tahun'           => $this->request->getPost('tahun') !== '' ? (int) $this->request->getPost('tahun') : null,
            'deskripsi'       => trim((string) $this->request->getPost('deskripsi')),
            'tanggal_terbit'  => $this->request->getPost('tanggal_terbit') ?: null,
            'is_aktif'        => (int) $this->request->getPost('is_aktif'),
            'diupdate_oleh'   => session()->get('user_id'),
        ];

        $file = $this->request->getFile('file_peraturan');

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $folderTujuan = 'uploads/peraturan';

            if (! is_dir(WRITEPATH . $folderTujuan)) {
                mkdir(WRITEPATH . $folderTujuan, 0755, true);
            }

            $namaFileSimpan = $file->getRandomName();
            $file->move(WRITEPATH . $folderTujuan, $namaFileSimpan);

            $dataUpdate['nama_file'] = $file->getClientName();
            $dataUpdate['path_file'] = $folderTujuan . '/' . $namaFileSimpan;
            $dataUpdate['ekstensi_file'] = $file->getExtension();
            $dataUpdate['ukuran_file'] = $file->getSize();
        }

        $this->peraturanModel->update((int) $id, $dataUpdate);

        catat_audit(
            'edit_peraturan',
            'peraturan',
            (int) $id,
            'Memperbarui peraturan: ' . (string) ($dataUpdate['judul'] ?? ($data['judul'] ?? '-'))
        );

        return redirect()->to('/peraturan')->with('success', 'Peraturan berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $data = $this->peraturanModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/peraturan')->with('error', 'Data peraturan tidak ditemukan.');
        }

        $this->peraturanModel->delete((int) $id);

        catat_audit(
            'hapus_peraturan',
            'peraturan',
            (int) $id,
            'Menghapus peraturan: ' . (string) ($data['judul'] ?? ('ID ' . $id))
        );

        return redirect()->to('/peraturan')->with('success', 'Peraturan berhasil dihapus.');
    }
}
