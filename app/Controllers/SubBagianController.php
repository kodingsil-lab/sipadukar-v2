<?php

namespace App\Controllers;

use App\Models\KriteriaModel;
use App\Models\SubBagianModel;
use App\Models\DokumenModel;

class SubBagianController extends BaseController
{
    public function create($kriteriaId)
    {
        $kriteriaModel = new KriteriaModel();
        $kriteria = $kriteriaModel->find((int) $kriteriaId);

        if (! $kriteria) {
            return redirect()->to('/kriteria')->with('error', 'Kriteria tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveProgramStudiIdFromRequest();
        if ($selectedProgramStudiId <= 0) {
            return redirect()->to('/kriteria/' . (int) $kriteriaId)->with('error', 'Pilih Program Studi terlebih dahulu untuk mengelola sub bagian.');
        }

        return view('sub_bagian/form', [
            'title' => 'Tambah Sub Bagian',
            'mode' => 'create',
            'kriteria' => $kriteria,
            'data' => null,
            'selectedProgramStudiId' => $selectedProgramStudiId,
        ]);
    }

    public function store($kriteriaId)
    {
        $kriteriaModel = new KriteriaModel();
        $subBagianModel = new SubBagianModel();

        $kriteria = $kriteriaModel->find((int) $kriteriaId);

        if (! $kriteria) {
            return redirect()->to('/kriteria')->with('error', 'Kriteria tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveProgramStudiIdFromRequest();
        if ($selectedProgramStudiId <= 0) {
            return redirect()->to('/kriteria/' . (int) $kriteriaId)->with('error', 'Pilih Program Studi terlebih dahulu untuk mengelola sub bagian.');
        }

        $rules = [
            'nama_sub_bagian' => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'permit_empty',
            'urutan' => 'required|integer',
            'is_aktif' => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data sub bagian belum valid.');
        }

        $deskripsi = trim((string) $this->request->getPost('deskripsi'));
        if (! $this->isWithinWordLimit($deskripsi, 30)) {
            return redirect()->back()->withInput()->with('error', 'Deskripsi maksimal 30 kata.');
        }

        $subBagianModel->insert([
            'kriteria_id' => (int) $kriteriaId,
            'program_studi_id' => $selectedProgramStudiId,
            'nama_sub_bagian' => trim((string) $this->request->getPost('nama_sub_bagian')),
            'deskripsi' => $deskripsi,
            'urutan' => (int) $this->request->getPost('urutan'),
            'dibuat_oleh' => session()->get('user_id'),
            'diupdate_oleh' => session()->get('user_id'),
            'is_aktif' => (int) $this->request->getPost('is_aktif'),
        ]);

        return redirect()->to('/kriteria/' . (int) $kriteriaId . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
            ->with('success', 'Sub bagian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $subBagianModel = new SubBagianModel();
        $kriteriaModel = new KriteriaModel();

        $data = $subBagianModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/kriteria')->with('error', 'Sub bagian tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveProgramStudiIdFromRequest();
        if (! $this->isSubBagianInSelectedScope($data, $selectedProgramStudiId)) {
            return redirect()->to('/kriteria/' . (int) ($data['kriteria_id'] ?? 0) . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
                ->with('error', 'Sub bagian tidak ada pada Program Studi yang dipilih.');
        }

        $kriteria = $kriteriaModel->find((int) $data['kriteria_id']);

        return view('sub_bagian/form', [
            'title' => 'Edit Sub Bagian',
            'mode' => 'edit',
            'kriteria' => $kriteria,
            'data' => $data,
            'selectedProgramStudiId' => $selectedProgramStudiId,
        ]);
    }

    public function update($id)
    {
        $subBagianModel = new SubBagianModel();

        $data = $subBagianModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/kriteria')->with('error', 'Sub bagian tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveProgramStudiIdFromRequest();
        if (! $this->isSubBagianInSelectedScope($data, $selectedProgramStudiId)) {
            return redirect()->to('/kriteria/' . (int) ($data['kriteria_id'] ?? 0) . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
                ->with('error', 'Sub bagian tidak ada pada Program Studi yang dipilih.');
        }

        $rules = [
            'nama_sub_bagian' => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'permit_empty',
            'urutan' => 'required|integer',
            'is_aktif' => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data sub bagian belum valid.');
        }

        $deskripsi = trim((string) $this->request->getPost('deskripsi'));
        if (! $this->isWithinWordLimit($deskripsi, 30)) {
            return redirect()->back()->withInput()->with('error', 'Deskripsi maksimal 30 kata.');
        }

        $subBagianModel->update((int) $id, [
            'nama_sub_bagian' => trim((string) $this->request->getPost('nama_sub_bagian')),
            'deskripsi' => $deskripsi,
            'urutan' => (int) $this->request->getPost('urutan'),
            'diupdate_oleh' => session()->get('user_id'),
            'is_aktif' => (int) $this->request->getPost('is_aktif'),
        ]);

        return redirect()->to('/kriteria/' . (int) ($data['kriteria_id'] ?? 0) . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
            ->with('success', 'Sub bagian berhasil diperbarui.');
    }

    public function delete($id)
    {
        $subBagianModel = new SubBagianModel();
        $dokumenModel = new DokumenModel();

        $data = $subBagianModel->find((int) $id);

        if (! $data) {
            return redirect()->to('/kriteria')->with('error', 'Sub bagian tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveProgramStudiIdFromRequest();
        if (! $this->isSubBagianInSelectedScope($data, $selectedProgramStudiId)) {
            return redirect()->to('/kriteria/' . (int) ($data['kriteria_id'] ?? 0) . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
                ->with('error', 'Sub bagian tidak ada pada Program Studi yang dipilih.');
        }

        $kriteriaId = (int) $data['kriteria_id'];
        $jumlahDokumen = $dokumenModel
            ->where('sub_bagian_id', (int) $id)
            ->where('program_studi_id', $selectedProgramStudiId)
            ->where('deleted_at', null)
            ->countAllResults();

        if ($jumlahDokumen > 0) {
            return redirect()->to('/kriteria/' . $kriteriaId . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
                ->with('error', 'Sub bagian tidak bisa dihapus karena masih memiliki ' . $jumlahDokumen . ' dokumen. Hapus semua dokumen terkait terlebih dahulu.');
        }

        $subBagianModel->delete((int) $id);

        return redirect()->to('/kriteria/' . $kriteriaId . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
            ->with('success', 'Sub bagian berhasil dihapus.');
    }

    private function isWithinWordLimit(string $text, int $maxWords): bool
    {
        if ($text === '') {
            return true;
        }

        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        return count($words) <= $maxWords;
    }

    private function resolveProgramStudiIdFromRequest(): int
    {
        $programStudiId = (int) ($this->request->getGet('program_studi_id') ?? $this->request->getPost('program_studi_id') ?? 0);
        if ($programStudiId <= 0) {
            return 0;
        }

        return can_access_program_studi($programStudiId) ? $programStudiId : 0;
    }

    private function isSubBagianInSelectedScope(array $subBagian, int $selectedProgramStudiId): bool
    {
        $subBagianProgramStudiId = (int) ($subBagian['program_studi_id'] ?? 0);
        if ($selectedProgramStudiId <= 0 || $subBagianProgramStudiId <= 0) {
            return false;
        }

        return $subBagianProgramStudiId === $selectedProgramStudiId;
    }

    private function buildProgramStudiQuerySuffix(int $selectedProgramStudiId): string
    {
        return $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
    }
}
