<?php

namespace App\Controllers;

use App\Models\UppsModel;

class UppsController extends BaseController
{
    protected UppsModel $uppsModel;

    public function __construct()
    {
        $this->uppsModel = new UppsModel();
    }

    public function index()
    {
        $uppsList = $this->uppsModel
            ->orderBy('nama_upps', 'ASC')
            ->findAll();

        return view('upps/index', [
            'title'    => 'UPPS',
            'uppsList' => $uppsList,
        ]);
    }

    public function create()
    {
        return view('upps/form', [
            'title' => 'Tambah UPPS',
            'mode'  => 'create',
            'data'  => null,
        ]);
    }

    public function store()
    {
        $rules = $this->validationRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data UPPS belum valid.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);

        $payload = [
            'nama_upps'           => trim((string) $this->request->getPost('nama_upps')),
            'nama_singkatan'      => trim((string) $this->request->getPost('nama_singkatan')),
            'jenis_unit'          => trim((string) $this->request->getPost('jenis_unit')),
            'nama_pimpinan_upps'  => trim((string) $this->request->getPost('nama_pimpinan_upps')),
            'nutpk'               => trim((string) $this->request->getPost('nutpk')),
            'email_resmi_upps'    => trim((string) $this->request->getPost('email_resmi_upps')),
            'nomor_telepon'       => trim((string) $this->request->getPost('nomor_telepon')),
            'created_by'          => $userId > 0 ? $userId : null,
            'updated_by'          => $userId > 0 ? $userId : null,
        ];

        $this->uppsModel->insert($payload);
        $uppsId = (int) $this->uppsModel->getInsertID();

        catat_audit(
            'tambah_upps',
            'upps',
            $uppsId,
            'Menambahkan UPPS: ' . (string) ($payload['nama_upps'] ?? '-')
        );

        return redirect()->to('/upps')->with('success', 'Data UPPS berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = $this->uppsModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/upps')->with('error', 'Data UPPS tidak ditemukan.');
        }

        return view('upps/form', [
            'title' => 'Edit UPPS',
            'mode'  => 'edit',
            'data'  => $data,
        ]);
    }

    public function update($id)
    {
        $row = $this->uppsModel->find((int) $id);

        if (! $row) {
            return redirect()->to('/upps')->with('error', 'Data UPPS tidak ditemukan.');
        }

        $rules = $this->validationRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data UPPS belum valid.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);

        $payload = [
            'nama_upps'           => trim((string) $this->request->getPost('nama_upps')),
            'nama_singkatan'      => trim((string) $this->request->getPost('nama_singkatan')),
            'jenis_unit'          => trim((string) $this->request->getPost('jenis_unit')),
            'nama_pimpinan_upps'  => trim((string) $this->request->getPost('nama_pimpinan_upps')),
            'nutpk'               => trim((string) $this->request->getPost('nutpk')),
            'email_resmi_upps'    => trim((string) $this->request->getPost('email_resmi_upps')),
            'nomor_telepon'       => trim((string) $this->request->getPost('nomor_telepon')),
            'updated_by'          => $userId > 0 ? $userId : null,
        ];

        $this->uppsModel->update((int) $id, $payload);

        catat_audit(
            'edit_upps',
            'upps',
            (int) $id,
            'Memperbarui UPPS: ' . (string) ($payload['nama_upps'] ?? ($row['nama_upps'] ?? '-'))
        );

        return redirect()->to('/upps')->with('success', 'Data UPPS berhasil diperbarui.');
    }

    public function delete($id)
    {
        $row = $this->uppsModel->find((int) $id);

        if (! $row) {
            return redirect()->to('/upps')->with('error', 'Data UPPS tidak ditemukan.');
        }

        $this->uppsModel->delete((int) $id);

        catat_audit(
            'hapus_upps',
            'upps',
            (int) $id,
            'Menghapus UPPS: ' . (string) ($row['nama_upps'] ?? ('ID ' . $id))
        );

        return redirect()->to('/upps')->with('success', 'Data UPPS berhasil dihapus.');
    }

    private function validationRules(): array
    {
        return [
            'nama_upps'          => 'required|min_length[3]|max_length[255]',
            'nama_singkatan'     => 'permit_empty|max_length[100]',
            'jenis_unit'         => 'permit_empty|in_list[Fakultas,Jurusan,Pascasarjana,Sekolah Tinggi]',
            'nama_pimpinan_upps' => 'permit_empty|max_length[150]',
            'nutpk'              => 'permit_empty|max_length[50]',
            'email_resmi_upps'   => 'permit_empty|valid_email|max_length[150]',
            'nomor_telepon'      => 'permit_empty|max_length[50]',
        ];
    }
}
