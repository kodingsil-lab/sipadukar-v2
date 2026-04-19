<?php

namespace App\Controllers;

use App\Models\JenisDokumenModel;

class JenisDokumenController extends BaseController
{
    protected JenisDokumenModel $model;

    public function __construct()
    {
        $this->model = new JenisDokumenModel();
    }

    public function index()
    {
        $list = $this->model->orderBy('nama_jenis_dokumen', 'ASC')->findAll();

        return view('jenis_dokumen/index', [
            'title' => 'Jenis Dokumen',
            'list'  => $list,
        ]);
    }

    public function create()
    {
        return view('jenis_dokumen/form', [
            'title' => 'Tambah Jenis Dokumen',
            'mode'  => 'create',
            'data'  => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Data jenis dokumen belum valid.');
        }

        $namaJenisDokumen = trim((string) $this->request->getPost('nama_jenis_dokumen'));
        if ($this->isNamaSudahDipakai($namaJenisDokumen)) {
            return redirect()->back()->withInput()->with('error', 'Nama jenis dokumen sudah digunakan.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $this->model->insert([
            'nama_jenis_dokumen' => $namaJenisDokumen,
            'is_aktif'           => (int) $this->request->getPost('is_aktif'),
            'created_by'         => $userId > 0 ? $userId : null,
            'updated_by'         => $userId > 0 ? $userId : null,
        ]);

        return redirect()->to('/jenis-dokumen')->with('success', 'Jenis dokumen berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = $this->model->find((int) $id);
        if (! $data) {
            return redirect()->to('/jenis-dokumen')->with('error', 'Data jenis dokumen tidak ditemukan.');
        }

        return view('jenis_dokumen/form', [
            'title' => 'Edit Jenis Dokumen',
            'mode'  => 'edit',
            'data'  => $data,
        ]);
    }

    public function update($id)
    {
        $row = $this->model->find((int) $id);
        if (! $row) {
            return redirect()->to('/jenis-dokumen')->with('error', 'Data jenis dokumen tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Data jenis dokumen belum valid.');
        }

        $namaJenisDokumen = trim((string) $this->request->getPost('nama_jenis_dokumen'));
        if ($this->isNamaSudahDipakai($namaJenisDokumen, (int) $id)) {
            return redirect()->back()->withInput()->with('error', 'Nama jenis dokumen sudah digunakan.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $this->model->update((int) $id, [
            'nama_jenis_dokumen' => $namaJenisDokumen,
            'is_aktif'           => (int) $this->request->getPost('is_aktif'),
            'updated_by'         => $userId > 0 ? $userId : null,
        ]);

        return redirect()->to('/jenis-dokumen')->with('success', 'Jenis dokumen berhasil diperbarui.');
    }

    public function delete($id)
    {
        $row = $this->model->find((int) $id);
        if (! $row) {
            return redirect()->to('/jenis-dokumen')->with('error', 'Data jenis dokumen tidak ditemukan.');
        }

        $this->model->delete((int) $id);
        return redirect()->to('/jenis-dokumen')->with('success', 'Jenis dokumen berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'nama_jenis_dokumen' => 'required|min_length[2]|max_length[100]',
            'is_aktif'           => 'required|in_list[0,1]',
        ];
    }

    private function isNamaSudahDipakai(string $nama, int $exceptId = 0): bool
    {
        $builder = $this->model->where('LOWER(nama_jenis_dokumen)', strtolower($nama));
        if ($exceptId > 0) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->first() !== null;
    }
}

