<?php

namespace App\Controllers;

use App\Models\ProgramStudiModel;

class ManajemenProdiAkreditasiController extends BaseController
{
    protected ProgramStudiModel $programStudiModel;

    public function __construct()
    {
        $this->programStudiModel = new ProgramStudiModel();
    }

    public function index()
    {
        return view('pengaturan/manajemen_prodi_akreditasi', [
            'title'            => 'Manajemen Prodi Akreditasi',
            'programStudiList' => $this->programStudiModel->getListWithUpps(),
        ]);
    }

    public function toggle($id)
    {
        if (! has_role(['admin', 'lpm'])) {
            return redirect()->to('/dashboard')->with('error', 'Hanya Admin/LPM yang boleh mengubah pengaturan ini.');
        }

        $row = $this->programStudiModel->find((int) $id);
        if (! $row) {
            return redirect()->to('/pengaturan/manajemen-prodi-akreditasi')->with('error', 'Program Studi tidak ditemukan.');
        }

        $current = (int) ($row['is_aktif_akreditasi'] ?? 0);
        $next = $current === 1 ? 0 : 1;
        $userId = (int) (session()->get('user_id') ?? 0);

        $this->programStudiModel->update((int) $id, [
            'is_aktif_akreditasi' => $next,
            'updated_by'          => $userId > 0 ? $userId : null,
        ]);

        catat_audit(
            $next === 1 ? 'publish_prodi_akreditasi' : 'unpublish_prodi_akreditasi',
            'pengaturan_prodi_akreditasi',
            (int) $id,
            'Mengubah status akreditasi prodi menjadi ' . ($next === 1 ? 'aktif' : 'nonaktif')
        );

        $pesan = $next === 1
            ? 'Program Studi berhasil diaktifkan untuk akreditasi.'
            : 'Program Studi dinonaktifkan dari daftar akreditasi aktif.';

        return redirect()->to('/pengaturan/manajemen-prodi-akreditasi')->with('success', $pesan);
    }
}

