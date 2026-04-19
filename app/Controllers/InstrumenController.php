<?php

namespace App\Controllers;

use App\Models\InstrumenModel;
use CodeIgniter\HTTP\RedirectResponse;

class InstrumenController extends BaseController
{
    protected InstrumenModel $instrumenModel;

    public function __construct()
    {
        $this->instrumenModel = new InstrumenModel();
    }

    private function ensureReadable(): ?RedirectResponse
    {
        if (has_role(['admin', 'lpm', 'dekan', 'kaprodi', 'dosen'])) {
            return null;
        }

        return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke data instrumen.');
    }

    private function ensureManageable(): ?RedirectResponse
    {
        if (has_role(['admin', 'lpm'])) {
            return null;
        }

        return redirect()->to('/instrumen')->with('error', 'Hanya Admin/LPM yang dapat mengubah data instrumen.');
    }

    public function index()
    {
        if ($guard = $this->ensureReadable()) {
            return $guard;
        }

        $instrumenList = $this->instrumenModel
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('instrumen/index', [
            'title'         => 'Instrumen',
            'instrumenList' => $instrumenList,
        ]);
    }

    public function create()
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        return view('instrumen/form', [
            'title' => 'Tambah Instrumen',
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
            'deskripsi'        => 'permit_empty',
            'versi_instrumen'  => 'permit_empty|max_length[100]',
            'tanggal_berlaku'  => 'permit_empty|valid_date',
            'is_aktif'         => 'required|in_list[0,1]',
            'file_instrumen'   => 'uploaded[file_instrumen]|max_size[file_instrumen,10240]|ext_in[file_instrumen,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar]|mime_in[file_instrumen,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,application/zip,application/x-rar-compressed,application/x-zip-compressed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data instrumen belum valid.');
        }

        $file = $this->request->getFile('file_instrumen');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'File instrumen gagal diunggah.');
        }

        $folderTujuan = 'uploads/instrumen';

        if (! is_dir(WRITEPATH . $folderTujuan)) {
            mkdir(WRITEPATH . $folderTujuan, 0755, true);
        }

        $namaFileSimpan = $file->getRandomName();
        $file->move(WRITEPATH . $folderTujuan, $namaFileSimpan);

        $payload = [
            'judul'            => trim((string) $this->request->getPost('judul')),
            'kategori'         => trim((string) $this->request->getPost('kategori')),
            'deskripsi'        => trim((string) $this->request->getPost('deskripsi')),
            'versi_instrumen'  => trim((string) $this->request->getPost('versi_instrumen')),
            'nama_file'        => $file->getClientName(),
            'path_file'        => $folderTujuan . '/' . $namaFileSimpan,
            'ekstensi_file'    => $file->getExtension(),
            'ukuran_file'      => $file->getSize(),
            'tanggal_berlaku'  => $this->request->getPost('tanggal_berlaku') ?: null,
            'is_aktif'         => (int) $this->request->getPost('is_aktif'),
            'dibuat_oleh'      => session()->get('user_id'),
            'diupdate_oleh'    => session()->get('user_id'),
        ];

        $this->instrumenModel->insert($payload);
        $instrumenId = (int) $this->instrumenModel->getInsertID();

        catat_audit(
            'tambah_instrumen',
            'instrumen',
            $instrumenId,
            'Menambahkan instrumen: ' . (string) ($payload['judul'] ?? '-')
        );

        return redirect()->to('/instrumen')->with('success', 'Instrumen berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $data = $this->instrumenModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/instrumen')->with('error', 'Data instrumen tidak ditemukan.');
        }

        return view('instrumen/form', [
            'title' => 'Edit Instrumen',
            'mode'  => 'edit',
            'data'  => $data,
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $data = $this->instrumenModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/instrumen')->with('error', 'Data instrumen tidak ditemukan.');
        }

        $rules = [
            'judul'            => 'required|min_length[3]|max_length[255]',
            'kategori'         => 'permit_empty|max_length[100]',
            'deskripsi'        => 'permit_empty',
            'versi_instrumen'  => 'permit_empty|max_length[100]',
            'tanggal_berlaku'  => 'permit_empty|valid_date',
            'is_aktif'         => 'required|in_list[0,1]',
            'file_instrumen'   => 'if_exist|max_size[file_instrumen,10240]|ext_in[file_instrumen,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar]|mime_in[file_instrumen,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,application/zip,application/x-rar-compressed,application/x-zip-compressed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data instrumen belum valid.');
        }

        $dataUpdate = [
            'judul'            => trim((string) $this->request->getPost('judul')),
            'kategori'         => trim((string) $this->request->getPost('kategori')),
            'deskripsi'        => trim((string) $this->request->getPost('deskripsi')),
            'versi_instrumen'  => trim((string) $this->request->getPost('versi_instrumen')),
            'tanggal_berlaku'  => $this->request->getPost('tanggal_berlaku') ?: null,
            'is_aktif'         => (int) $this->request->getPost('is_aktif'),
            'diupdate_oleh'    => session()->get('user_id'),
        ];

        $file = $this->request->getFile('file_instrumen');

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $folderTujuan = 'uploads/instrumen';

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

        $this->instrumenModel->update((int) $id, $dataUpdate);

        catat_audit(
            'edit_instrumen',
            'instrumen',
            (int) $id,
            'Memperbarui instrumen: ' . (string) ($dataUpdate['judul'] ?? ($data['judul'] ?? '-'))
        );

        return redirect()->to('/instrumen')->with('success', 'Instrumen berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($guard = $this->ensureManageable()) {
            return $guard;
        }

        $data = $this->instrumenModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/instrumen')->with('error', 'Data instrumen tidak ditemukan.');
        }

        $this->instrumenModel->delete((int) $id);

        catat_audit(
            'hapus_instrumen',
            'instrumen',
            (int) $id,
            'Menghapus instrumen: ' . (string) ($data['judul'] ?? ('ID ' . $id))
        );

        return redirect()->to('/instrumen')->with('success', 'Instrumen berhasil dihapus.');
    }
}
