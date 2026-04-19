<?php

namespace App\Controllers;

use App\Models\LembagaAkreditasiModel;
use App\Models\ProgramStudiModel;
use App\Models\UppsModel;

class ProgramStudiController extends BaseController
{
    protected ProgramStudiModel $programStudiModel;
    protected UppsModel $uppsModel;
    protected LembagaAkreditasiModel $lembagaAkreditasiModel;

    public function __construct()
    {
        $this->programStudiModel = new ProgramStudiModel();
        $this->uppsModel = new UppsModel();
        $this->lembagaAkreditasiModel = new LembagaAkreditasiModel();
    }

    public function index()
    {
        return view('program_studi/index', [
            'title'            => 'Program Studi',
            'programStudiList' => $this->programStudiModel->getListWithUpps(),
        ]);
    }

    public function create()
    {
        return view('program_studi/form', [
            'title'    => 'Tambah Program Studi',
            'mode'     => 'create',
            'data'     => null,
            'uppsList' => $this->uppsModel->orderBy('nama_upps', 'ASC')->findAll(),
            'lembagaAkreditasiList' => $this->lembagaAkreditasiModel->where('is_aktif', 1)->orderBy('nama_lembaga_akreditasi', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->validationRules())) {
            return redirect()->back()->withInput()->with('error', 'Data Program Studi belum valid.');
        }

        if (! $this->validateLembagaAkreditasiSelection()) {
            return redirect()->back()->withInput()->with('error', 'Lembaga akreditasi tidak valid atau tidak aktif.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $payload = $this->collectPayload($userId, true);
        $this->programStudiModel->insert($payload);
        $programStudiId = (int) $this->programStudiModel->getInsertID();

        catat_audit(
            'tambah_program_studi',
            'program_studi',
            $programStudiId,
            'Menambahkan program studi: ' . (string) ($payload['nama_program_studi'] ?? '-')
        );

        return redirect()->to('/program-studi')->with('success', 'Data Program Studi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = $this->programStudiModel->find((int) $id);
        if (! $data) {
            return redirect()->to('/program-studi')->with('error', 'Data Program Studi tidak ditemukan.');
        }

        return view('program_studi/form', [
            'title'    => 'Edit Program Studi',
            'mode'     => 'edit',
            'data'     => $data,
            'uppsList' => $this->uppsModel->orderBy('nama_upps', 'ASC')->findAll(),
            'lembagaAkreditasiList' => $this->lembagaAkreditasiModel->where('is_aktif', 1)->orderBy('nama_lembaga_akreditasi', 'ASC')->findAll(),
        ]);
    }

    public function update($id)
    {
        $row = $this->programStudiModel->find((int) $id);
        if (! $row) {
            return redirect()->to('/program-studi')->with('error', 'Data Program Studi tidak ditemukan.');
        }

        if (! $this->validate($this->validationRules())) {
            return redirect()->back()->withInput()->with('error', 'Data Program Studi belum valid.');
        }

        if (! $this->validateLembagaAkreditasiSelection()) {
            return redirect()->back()->withInput()->with('error', 'Lembaga akreditasi tidak valid atau tidak aktif.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $payload = $this->collectPayload($userId, false);
        $this->programStudiModel->update((int) $id, $payload);

        catat_audit(
            'edit_program_studi',
            'program_studi',
            (int) $id,
            'Memperbarui program studi: ' . (string) ($payload['nama_program_studi'] ?? ($row['nama_program_studi'] ?? '-'))
        );

        return redirect()->to('/program-studi')->with('success', 'Data Program Studi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $row = $this->programStudiModel->find((int) $id);
        if (! $row) {
            return redirect()->to('/program-studi')->with('error', 'Data Program Studi tidak ditemukan.');
        }

        $this->programStudiModel->delete((int) $id);

        catat_audit(
            'hapus_program_studi',
            'program_studi',
            (int) $id,
            'Menghapus program studi: ' . (string) ($row['nama_program_studi'] ?? ('ID ' . $id))
        );

        return redirect()->to('/program-studi')->with('success', 'Data Program Studi berhasil dihapus.');
    }

    private function validationRules(): array
    {
        return [
            'upps_id'                   => 'required|is_natural_no_zero',
            'nama_program_studi'        => 'required|min_length[3]|max_length[255]',
            'nama_singkatan'            => 'permit_empty|max_length[100]',
            'kode_program_studi_pddikti'=> 'permit_empty|max_length[50]',
            'jenjang'                   => 'permit_empty|in_list[D3,S1,S2,S3]',
            'website_resmi'             => 'permit_empty|valid_url_strict|max_length[255]',
            'email_resmi_program_studi' => 'permit_empty|valid_email|max_length[150]',
            'nomor_telepon'             => 'permit_empty|max_length[50]',
            'nama_ketua_program_studi'  => 'permit_empty|max_length[150]',
            'nuptk'                     => 'permit_empty|max_length[50]',
            'status_akreditasi'         => 'permit_empty|in_list[Baik,Baik Sekali,Unggul]',
            'nomor_sk_akreditasi'       => 'permit_empty|max_length[150]',
            'tanggal_sk'                => 'permit_empty|valid_date',
            'tanggal_mulai_berlaku'     => 'permit_empty|valid_date',
            'tanggal_berakhir'          => 'permit_empty|valid_date',
            'lembaga_akreditasi'        => 'permit_empty|max_length[30]',
        ];
    }

    private function validateLembagaAkreditasiSelection(): bool
    {
        $selected = trim((string) $this->request->getPost('lembaga_akreditasi'));
        if ($selected === '') {
            return true;
        }

        return $this->lembagaAkreditasiModel
            ->where('is_aktif', 1)
            ->where('nama_singkatan', $selected)
            ->countAllResults() > 0;
    }

    private function collectPayload(int $userId, bool $isCreate): array
    {
        $payload = [
            'upps_id'                    => (int) $this->request->getPost('upps_id'),
            'nama_program_studi'         => trim((string) $this->request->getPost('nama_program_studi')),
            'nama_singkatan'             => trim((string) $this->request->getPost('nama_singkatan')),
            'kode_program_studi_pddikti' => trim((string) $this->request->getPost('kode_program_studi_pddikti')),
            'jenjang'                    => trim((string) $this->request->getPost('jenjang')),
            'website_resmi'              => trim((string) $this->request->getPost('website_resmi')),
            'email_resmi_program_studi'  => trim((string) $this->request->getPost('email_resmi_program_studi')),
            'nomor_telepon'              => trim((string) $this->request->getPost('nomor_telepon')),
            'nama_ketua_program_studi'   => trim((string) $this->request->getPost('nama_ketua_program_studi')),
            'nuptk'                      => trim((string) $this->request->getPost('nuptk')),
            'status_akreditasi'          => trim((string) $this->request->getPost('status_akreditasi')),
            'nomor_sk_akreditasi'        => trim((string) $this->request->getPost('nomor_sk_akreditasi')),
            'tanggal_sk'                 => trim((string) $this->request->getPost('tanggal_sk')) ?: null,
            'tanggal_mulai_berlaku'      => trim((string) $this->request->getPost('tanggal_mulai_berlaku')) ?: null,
            'tanggal_berakhir'           => trim((string) $this->request->getPost('tanggal_berakhir')) ?: null,
            'lembaga_akreditasi'         => trim((string) $this->request->getPost('lembaga_akreditasi')),
            'updated_by'                 => $userId > 0 ? $userId : null,
        ];

        if ($isCreate) {
            $payload['created_by'] = $userId > 0 ? $userId : null;
        }

        return $payload;
    }
}
