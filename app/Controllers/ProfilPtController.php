<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use App\Models\LembagaAkreditasiModel;
use App\Models\ProfilPtModel;

class ProfilPtController extends BaseController
{
    private const UPLOAD_DIR = 'uploads/profil-pt';

    public function index()
    {
        $model = new ProfilPtModel();
        $profil = $model->getSingleton();
        $lembagaAkreditasiList = (new LembagaAkreditasiModel())
            ->where('is_aktif', 1)
            ->orderBy('nama_lembaga_akreditasi', 'ASC')
            ->findAll();

        return view('master_data/profil_pt', [
            'title'                 => 'Profil PT',
            'profil'                => $profil,
            'lembagaAkreditasiList' => $lembagaAkreditasiList,
        ]);
    }

    public function update()
    {
        $model = new ProfilPtModel();
        $existing = $model->getSingleton();

        $rules = [
            'nama_pt'                  => 'required|min_length[3]|max_length[255]',
            'nama_singkatan'           => 'permit_empty|max_length[100]',
            'status_pt'                => 'permit_empty|in_list[PTN,PTS]',
            'badan_penyelenggara'      => 'permit_empty|max_length[255]',
            'kode_pt_pddikti'          => 'permit_empty|max_length[50]',
            'tahun_berdiri'            => 'permit_empty|regex_match[/^\d{4}$/]',
            'alamat_lengkap'           => 'permit_empty',
            'website_resmi'            => 'permit_empty|valid_url_strict',
            'email_resmi_pt'           => 'permit_empty|valid_email',
            'nomor_telepon'            => 'permit_empty|max_length[50]',
            'status_akreditasi_pt'     => 'permit_empty|in_list[Baik,Baik Sekali,Unggul]',
            'nomor_sk_akreditasi'      => 'permit_empty|max_length[150]',
            'tanggal_sk'               => 'permit_empty|valid_date',
            'tanggal_berlaku_akreditasi' => 'permit_empty|valid_date',
            'tanggal_berakhir_akreditasi' => 'permit_empty|valid_date',
            'lembaga_akreditasi'       => 'permit_empty|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data Profil PT belum valid.');
        }

        if (! $this->validateLembagaAkreditasiSelection()) {
            return redirect()->back()->withInput()->with('error', 'Lembaga akreditasi tidak valid atau tidak aktif.');
        }

        $fileRules = [
            'file_sk_akreditasi'        => 'permit_empty|uploaded[file_sk_akreditasi]|max_size[file_sk_akreditasi,4096]|ext_in[file_sk_akreditasi,pdf,jpg,jpeg,png,webp]|mime_in[file_sk_akreditasi,application/pdf,image/jpeg,image/png,image/webp]',
            'file_sertifikat_akreditasi'=> 'permit_empty|uploaded[file_sertifikat_akreditasi]|max_size[file_sertifikat_akreditasi,4096]|ext_in[file_sertifikat_akreditasi,pdf,jpg,jpeg,png,webp]|mime_in[file_sertifikat_akreditasi,application/pdf,image/jpeg,image/png,image/webp]',
        ];

        $fileSk = $this->request->getFile('file_sk_akreditasi');
        $fileSertifikat = $this->request->getFile('file_sertifikat_akreditasi');
        $validateFiles = [];
        if ($fileSk && $fileSk->isValid() && ! $fileSk->hasMoved()) {
            $validateFiles['file_sk_akreditasi'] = $fileRules['file_sk_akreditasi'];
        }
        if ($fileSertifikat && $fileSertifikat->isValid() && ! $fileSertifikat->hasMoved()) {
            $validateFiles['file_sertifikat_akreditasi'] = $fileRules['file_sertifikat_akreditasi'];
        }
        if (! empty($validateFiles) && ! $this->validate($validateFiles)) {
            return redirect()->back()->withInput()->with('error', 'File akreditasi tidak valid.');
        }

        $storagePath = WRITEPATH . self::UPLOAD_DIR;
        if (! is_dir($storagePath)) {
            @mkdir($storagePath, 0755, true);
        }

        $tahunBerdiriRaw = trim((string) $this->request->getPost('tahun_berdiri'));
        $payload = [
            'nama_pt'                    => trim((string) $this->request->getPost('nama_pt')),
            'nama_singkatan'             => trim((string) $this->request->getPost('nama_singkatan')),
            'status_pt'                  => trim((string) $this->request->getPost('status_pt')),
            'badan_penyelenggara'        => trim((string) $this->request->getPost('badan_penyelenggara')),
            'kode_pt_pddikti'            => trim((string) $this->request->getPost('kode_pt_pddikti')),
            'tahun_berdiri'              => $tahunBerdiriRaw === '' ? null : (int) $tahunBerdiriRaw,
            'alamat_lengkap'             => trim((string) $this->request->getPost('alamat_lengkap')),
            'website_resmi'              => trim((string) $this->request->getPost('website_resmi')),
            'email_resmi_pt'             => trim((string) $this->request->getPost('email_resmi_pt')),
            'nomor_telepon'              => trim((string) $this->request->getPost('nomor_telepon')),
            'status_akreditasi_pt'       => trim((string) $this->request->getPost('status_akreditasi_pt')),
            'nomor_sk_akreditasi'        => trim((string) $this->request->getPost('nomor_sk_akreditasi')),
            'tanggal_sk'                 => trim((string) $this->request->getPost('tanggal_sk')) ?: null,
            'tanggal_berlaku_akreditasi' => trim((string) $this->request->getPost('tanggal_berlaku_akreditasi')) ?: null,
            'tanggal_berakhir_akreditasi'=> trim((string) $this->request->getPost('tanggal_berakhir_akreditasi')) ?: null,
            'lembaga_akreditasi'         => trim((string) $this->request->getPost('lembaga_akreditasi')),
            'updated_by'                 => (int) (session()->get('user_id') ?? 0),
        ];

        if ($fileSk && $fileSk->isValid() && ! $fileSk->hasMoved()) {
            $payload['file_sk_akreditasi_path'] = $this->storeFile($fileSk, $storagePath, 'sk-akreditasi');
        } elseif ($existing) {
            $payload['file_sk_akreditasi_path'] = $existing['file_sk_akreditasi_path'] ?? null;
        }

        if ($fileSertifikat && $fileSertifikat->isValid() && ! $fileSertifikat->hasMoved()) {
            $payload['file_sertifikat_akreditasi_path'] = $this->storeFile($fileSertifikat, $storagePath, 'sertifikat-akreditasi');
        } elseif ($existing) {
            $payload['file_sertifikat_akreditasi_path'] = $existing['file_sertifikat_akreditasi_path'] ?? null;
        }

        if ($existing) {
            $model->update((int) $existing['id'], $payload);
            catat_audit(
                'edit_profil_pt',
                'profil_pt',
                (int) ($existing['id'] ?? 0),
                'Memperbarui profil PT: ' . (string) ($payload['nama_pt'] ?? '-')
            );
        } else {
            $model->insert($payload);
            $profilId = (int) $model->getInsertID();
            catat_audit(
                'tambah_profil_pt',
                'profil_pt',
                $profilId,
                'Menambahkan profil PT: ' . (string) ($payload['nama_pt'] ?? '-')
            );
        }

        (new AppSettingModel())->setValue('nama_pt', $payload['nama_pt'], (int) ($payload['updated_by'] ?? 0));

        return redirect()->to('/profil-pt')->with('success', 'Profil PT berhasil diperbarui.');
    }

    private function storeFile($file, string $storagePath, string $prefix): ?string
    {
        try {
            $ext = strtolower((string) $file->getExtension());
            $filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move($storagePath, $filename, true);
            return self::UPLOAD_DIR . '/' . $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function validateLembagaAkreditasiSelection(): bool
    {
        $selected = trim((string) $this->request->getPost('lembaga_akreditasi'));
        if ($selected === '') {
            return true;
        }

        return (new LembagaAkreditasiModel())
            ->where('is_aktif', 1)
            ->where('nama_singkatan', $selected)
            ->countAllResults() > 0;
    }
}
