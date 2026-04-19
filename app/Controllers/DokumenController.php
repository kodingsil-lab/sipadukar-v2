<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\JenisDokumenModel;
use App\Models\KriteriaModel;
use App\Models\ProgramStudiModel;
use App\Models\ReviewDokumenModel;
use App\Models\RiwayatDokumenModel;
use App\Models\SubBagianModel;
use Throwable;

class DokumenController extends BaseController
{
    private const DEFAULT_SUB_BAGIAN_SLUG = '__default_kriteria_tanpa_subbagian__';

    protected DokumenModel $dokumenModel;
    protected KriteriaModel $kriteriaModel;
    protected SubBagianModel $subBagianModel;
    protected ProgramStudiModel $programStudiModel;
    protected JenisDokumenModel $jenisDokumenModel;
    protected RiwayatDokumenModel $riwayatDokumenModel;
    protected ReviewDokumenModel $reviewDokumenModel;

    public function __construct()
    {
        $this->dokumenModel = new DokumenModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->subBagianModel = new SubBagianModel();
        $this->programStudiModel = new ProgramStudiModel();
        $this->jenisDokumenModel = new JenisDokumenModel();
        $this->riwayatDokumenModel = new RiwayatDokumenModel();
        $this->reviewDokumenModel = new ReviewDokumenModel();
    }

    public function show($id)
    {
        $dokumen = $this->dokumenModel->getDetailById((int) $id);

        if (! $dokumen || ! can_access_dokumen($dokumen)) {
            return redirect()->to('/kriteria')->with('error', 'Anda tidak memiliki akses ke dokumen tersebut.');
        }

        $reviewList = $this->reviewDokumenModel->getByDokumen((int) $id);
        $riwayatList = $this->riwayatDokumenModel->getByDokumen((int) $id);

        return view('dokumen/show', [
            'title'       => 'Detail Dokumen',
            'dokumen'     => $dokumen,
            'reviewList'  => $reviewList,
            'riwayatList' => $riwayatList,
            'selectedProgramStudiId' => $this->resolveSelectedProgramStudiFilter(),
        ]);
    }

    public function create($subBagianId)
    {
        $subBagian = $this->subBagianModel->find((int) $subBagianId);

        if (! $subBagian) {
            return redirect()->to('/kriteria')->with('error', 'Sub bagian tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveSelectedProgramStudiFilter();
        $subBagianProgramStudiId = (int) ($subBagian['program_studi_id'] ?? 0);
        if ($selectedProgramStudiId > 0 && $subBagianProgramStudiId > 0 && $selectedProgramStudiId !== $subBagianProgramStudiId) {
            return redirect()->to('/kriteria/' . (int) ($subBagian['kriteria_id'] ?? 0) . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))
                ->with('error', 'Sub bagian tidak tersedia pada Program Studi yang dipilih.');
        }

        $kriteria = $this->kriteriaModel->find((int) $subBagian['kriteria_id']);

        return view('dokumen/form', [
            'title'     => 'Tambah Dokumen',
            'mode'      => 'create',
            'kriteria'  => $kriteria,
            'subBagian' => $subBagian,
            'data'      => null,
            'jenisDokumenList' => $this->getJenisDokumenAktif(),
            'programStudiList' => $this->getProgramStudiListForCurrentUser(),
            'selectedProgramStudiId' => $this->resolveSelectedProgramStudiFilter(),
        ]);
    }

    public function createByKriteria($kriteriaId)
    {
        $kriteria = $this->kriteriaModel->find((int) $kriteriaId);

        if (! $kriteria) {
            return redirect()->to('/kriteria')->with('error', 'Kriteria tidak ditemukan.');
        }

        $selectedProgramStudiId = $this->resolveSelectedProgramStudiFilter();
        if ($selectedProgramStudiId <= 0) {
            return redirect()->to('/kriteria/' . (int) $kriteriaId)->with('error', 'Pilih Program Studi terlebih dahulu untuk menambah dokumen.');
        }

        $defaultSubBagian = $this->getOrCreateDefaultSubBagian((int) $kriteriaId, $selectedProgramStudiId);
        return redirect()->to('/sub-bagian/' . (int) $defaultSubBagian['id'] . '/dokumen/create' . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId));
    }

    public function store($subBagianId)
    {
        $subBagian = $this->subBagianModel->find((int) $subBagianId);

        if (! $subBagian) {
            return redirect()->to('/kriteria')->with('error', 'Sub bagian tidak ditemukan.');
        }

        $kriteria = $this->kriteriaModel->find((int) $subBagian['kriteria_id']);

        $rules = [
            'judul_dokumen' => 'required|min_length[3]|max_length[255]',
            'deskripsi'     => 'permit_empty',
            'jenis_dokumen' => 'required|max_length[100]',
            'tahun_dokumen' => 'permit_empty|integer',
            'program_studi_id' => 'permit_empty|is_natural_no_zero',
            'file_dokumen'  => 'if_exist|max_size[file_dokumen,10240]|ext_in[file_dokumen,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png]|mime_in[file_dokumen,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png]',
            'link_dokumen'  => 'permit_empty|valid_url|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data dokumen belum valid atau referensi dokumen tidak sesuai.');
        }

        $jenisDokumen = trim((string) $this->request->getPost('jenis_dokumen'));
        if (! $this->isJenisDokumenAktif($jenisDokumen)) {
            return redirect()->back()->withInput()->with('error', 'Jenis dokumen tidak valid atau sudah tidak aktif.');
        }

        $programStudiId = $this->resolveProgramStudiIdForCurrentUser();
        if ($programStudiId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Program Studi wajib dipilih sesuai scope role Anda.');
        }

        $subBagianProgramStudiId = (int) ($subBagian['program_studi_id'] ?? 0);
        if ($subBagianProgramStudiId > 0 && $subBagianProgramStudiId !== $programStudiId) {
            return redirect()->back()->withInput()->with('error', 'Sub bagian hanya bisa digunakan oleh Program Studi pemiliknya.');
        }

        $unitKerja = trim((string) session()->get('unit_kerja'));
        $prodi = null;
        if ($programStudiId > 0) {
            $prodi = $this->programStudiModel->find($programStudiId);
            if (! $prodi) {
                return redirect()->back()->withInput()->with('error', 'Program Studi yang dipilih tidak valid.');
            }

            $unitKerja = trim((string) ($prodi['nama_program_studi'] ?? $unitKerja));
        }

        $file = $this->request->getFile('file_dokumen');
        $linkDokumen = trim((string) $this->request->getPost('link_dokumen'));
        if ($linkDokumen !== '' && ! is_safe_external_dokumen_link($linkDokumen)) {
            return redirect()->back()->withInput()->with('error', 'Link dokumen harus menggunakan http:// atau https:// yang valid.');
        }

        $sumberDokumen = trim((string) $this->request->getPost('sumber_dokumen'));
        if (! in_array($sumberDokumen, ['file', 'link'], true)) {
            $sumberDokumen = 'file';
        }
        $hasLink = $linkDokumen !== '';
        $hasFile = $file && (int) $file->getError() !== UPLOAD_ERR_NO_FILE;

        if (($sumberDokumen === 'file' && ! $hasFile) || ($sumberDokumen === 'link' && ! $hasLink)) {
            return redirect()->back()->withInput()->with('error', 'Referensi dokumen belum lengkap. Silakan isi sesuai sumber yang dipilih.');
        }

        if (($hasLink && $hasFile) || (! $hasLink && ! $hasFile)) {
            return redirect()->back()->withInput()->with('error', 'Pilih salah satu referensi dokumen: upload file atau upload link.');
        }

        $folderTujuan = '';
        $namaFileSimpan = '';
        $fullPathFileSimpan = null;
        $namaFileClient = null;
        $ekstensiFile = null;
        $mimeType = null;
        $ukuranFile = null;
        $tanggalUpload = null;

        try {
            if ($hasFile) {
                if (! $file || ! $file->isValid()) {
                    return redirect()->back()->withInput()->with('error', 'File dokumen gagal diunggah.');
                }

                $namaFileClient = $file->getClientName();
                $ekstensiFile = $file->getExtension();
                $mimeType = $file->getMimeType();
                $ukuranFile = $file->getSize();

                $folderTujuan = $this->buildDokumenFolderPath($kriteria, $subBagian, is_array($prodi) ? $prodi : []);
                $fullFolderPath = WRITEPATH . $folderTujuan;
                if (! is_dir($fullFolderPath) && ! @mkdir($fullFolderPath, 0755, true) && ! is_dir($fullFolderPath)) {
                    throw new \RuntimeException('Folder upload tidak dapat dibuat: ' . $fullFolderPath);
                }

                $namaFileSimpan = $file->getRandomName();
                $file->move($fullFolderPath, $namaFileSimpan);
                $fullPathFileSimpan = rtrim($fullFolderPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFileSimpan;
                $tanggalUpload = date('Y-m-d H:i:s');
            }

            $dataInsert = [
                'kriteria_id'      => (int) $kriteria['id'],
                'sub_bagian_id'    => (int) $subBagian['id'],
                'kode_dokumen'     => $this->generateKodeDokumen((int) $kriteria['id'], (int) $subBagian['id'], (string) ($kriteria['kode'] ?? ''), (string) ($subBagian['nama_sub_bagian'] ?? '')),
                'judul_dokumen'    => trim((string) $this->request->getPost('judul_dokumen')),
                'deskripsi'        => trim((string) $this->request->getPost('deskripsi')),
                'nomor_dokumen'    => $this->generateNomorDokumen(
                    (int) $kriteria['id'],
                    (int) $subBagian['id'],
                    (int) $programStudiId,
                    (string) ($kriteria['kode'] ?? ''),
                    (string) ($subBagian['nama_sub_bagian'] ?? '')
                ),
                'jenis_dokumen'    => $jenisDokumen,
                'sumber_dokumen'   => $sumberDokumen,
                'link_dokumen'     => $sumberDokumen === 'link' ? $linkDokumen : null,
                'tahun_dokumen'    => $this->request->getPost('tahun_dokumen') !== '' ? (int) $this->request->getPost('tahun_dokumen') : null,
                'nama_file'        => $namaFileClient,
                'path_file'        => $hasFile ? ($folderTujuan . '/' . $namaFileSimpan) : null,
                'ekstensi_file'    => $ekstensiFile,
                'mime_type'        => $mimeType,
                'ukuran_file'      => $hasFile ? $ukuranFile : 0,
                'versi'            => 1,
                'status_dokumen'   => 'draft',
                'catatan_terakhir' => trim((string) $this->request->getPost('catatan_terakhir')),
                'tanggal_upload'   => $tanggalUpload,
                'tanggal_submit'   => null,
                'tanggal_validasi' => null,
                'uploaded_by'      => session()->get('user_id'),
                'reviewer_id'      => null,
                'program_studi_id' => $programStudiId > 0 ? $programStudiId : null,
                'unit_kerja'       => $unitKerja,
                'is_aktif'         => 1,
            ];

            $inserted = $this->dokumenModel->insert($dataInsert);
            if ($inserted === false) {
                $modelErrors = $this->dokumenModel->errors();
                throw new \RuntimeException('Insert dokumen gagal: ' . json_encode($modelErrors, JSON_UNESCAPED_UNICODE));
            }

            $dokumenId = (int) $this->dokumenModel->getInsertID();
            if ($dokumenId <= 0) {
                throw new \RuntimeException('Insert dokumen gagal: ID dokumen tidak valid.');
            }

            $insertRiwayat = $this->riwayatDokumenModel->insert([
                'dokumen_id'      => $dokumenId,
                'versi'           => 1,
                'status_saat_itu' => $dataInsert['status_dokumen'],
                'keterangan'      => $hasLink ? 'Referensi awal dokumen menggunakan link.' : 'Upload awal dokumen',
                'nama_file'       => $dataInsert['nama_file'],
                'path_file'       => $dataInsert['path_file'],
                'ekstensi_file'   => $dataInsert['ekstensi_file'],
                'mime_type'       => $dataInsert['mime_type'],
                'ukuran_file'     => (int) ($dataInsert['ukuran_file'] ?? 0),
                'diunggah_oleh'   => session()->get('user_id'),
                'waktu_upload'    => date('Y-m-d H:i:s'),
            ]);

            if ($insertRiwayat === false) {
                $modelErrors = $this->riwayatDokumenModel->errors();
                throw new \RuntimeException('Insert riwayat dokumen gagal: ' . json_encode($modelErrors, JSON_UNESCAPED_UNICODE));
            }

            catat_audit(
                'tambah_dokumen',
                'dokumen',
                (int) $dokumenId,
                'Menambahkan dokumen: ' . $dataInsert['judul_dokumen']
            );
        } catch (Throwable $e) {
            if (is_string($fullPathFileSimpan) && $fullPathFileSimpan !== '' && is_file($fullPathFileSimpan)) {
                @unlink($fullPathFileSimpan);
            }

            log_message('error', 'Gagal menyimpan dokumen pada sub_bagian_id={subBagianId}, user_id={userId}: {message}', [
                'subBagianId' => (int) $subBagianId,
                'userId' => (int) (session()->get('user_id') ?? 0),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->withInput()->with(
                'error',
                'Upload dokumen gagal disimpan. Pastikan Program Studi dipilih, file valid, lalu coba lagi. Jika masih gagal, cek log server karena kemungkinan migrasi database atau permission folder upload belum sesuai.'
            );
        }

        $selectedProgramStudiId = $this->resolveSelectedProgramStudiFilter();
        return redirect()->to($this->kriteriaSubBagianUrl((int) $kriteria['id'], (int) $subBagianId, $selectedProgramStudiId))->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = $this->dokumenModel->find((int) $id);

        if (! $data || ! can_manage_dokumen($data)) {
            return redirect()->to('/kriteria')->with('error', 'Anda tidak memiliki akses untuk mengedit dokumen tersebut.');
        }

        $subBagian = $this->subBagianModel->find((int) $data['sub_bagian_id']);
        $kriteria = $this->kriteriaModel->find((int) $data['kriteria_id']);

        return view('dokumen/form', [
            'title'     => 'Edit Dokumen',
            'mode'      => 'edit',
            'kriteria'  => $kriteria,
            'subBagian' => $subBagian,
            'data'      => $data,
            'jenisDokumenList' => $this->getJenisDokumenAktif(),
            'programStudiList' => $this->getProgramStudiListForCurrentUser(),
            'selectedProgramStudiId' => $this->resolveSelectedProgramStudiFilter(),
        ]);
    }

    public function update($id)
    {
        $dokumen = $this->dokumenModel->find((int) $id);

        if (! $dokumen || ! can_manage_dokumen($dokumen)) {
            return redirect()->to('/kriteria')->with('error', 'Anda tidak memiliki akses untuk memperbarui dokumen tersebut.');
        }

        $subBagian = $this->subBagianModel->find((int) $dokumen['sub_bagian_id']);
        $kriteria = $this->kriteriaModel->find((int) $dokumen['kriteria_id']);

        $rules = [
            'judul_dokumen' => 'required|min_length[3]|max_length[255]',
            'deskripsi'     => 'permit_empty',
            'jenis_dokumen' => 'required|max_length[100]',
            'tahun_dokumen' => 'permit_empty|integer',
            'program_studi_id' => 'permit_empty|is_natural_no_zero',
            'file_dokumen'  => 'if_exist|max_size[file_dokumen,10240]|ext_in[file_dokumen,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png]|mime_in[file_dokumen,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png]',
            'link_dokumen'  => 'permit_empty|valid_url|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data dokumen belum valid.');
        }

        $jenisDokumen = trim((string) $this->request->getPost('jenis_dokumen'));
        if (! $this->isJenisDokumenAktif($jenisDokumen)) {
            return redirect()->back()->withInput()->with('error', 'Jenis dokumen tidak valid atau sudah tidak aktif.');
        }

        $programStudiId = $this->resolveProgramStudiIdForCurrentUser((int) ($dokumen['program_studi_id'] ?? 0));
        if ($programStudiId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Program Studi wajib dipilih sesuai scope role Anda.');
        }

        $unitKerja = trim((string) ($dokumen['unit_kerja'] ?? session()->get('unit_kerja')));
        $prodi = null;
        if ($programStudiId > 0) {
            $prodi = $this->programStudiModel->find($programStudiId);
            if (! $prodi) {
                return redirect()->back()->withInput()->with('error', 'Program Studi yang dipilih tidak valid.');
            }

            $unitKerja = trim((string) ($prodi['nama_program_studi'] ?? $unitKerja));
        }

        $statusSaatIni = trim((string) ($dokumen['status_dokumen'] ?? '')) ?: 'draft';
        $linkDokumen = trim((string) $this->request->getPost('link_dokumen'));
        if ($linkDokumen !== '' && ! is_safe_external_dokumen_link($linkDokumen)) {
            return redirect()->back()->withInput()->with('error', 'Link dokumen harus menggunakan http:// atau https:// yang valid.');
        }

        $sumberDokumen = trim((string) $this->request->getPost('sumber_dokumen'));
        if (! in_array($sumberDokumen, ['file', 'link'], true)) {
            $sumberDokumen = (string) ($dokumen['sumber_dokumen'] ?? 'file');
        }
        $file = $this->request->getFile('file_dokumen');
        $hasNewLink = $linkDokumen !== '';
        $hasNewFile = $file && (int) $file->getError() !== UPLOAD_ERR_NO_FILE;

        if ($hasNewLink && $hasNewFile) {
            return redirect()->back()->withInput()->with('error', 'Gunakan salah satu referensi dokumen saja: upload file atau upload link.');
        }

        $existingHasSource = ! empty($dokumen['path_file']) || ! empty($dokumen['link_dokumen']);
        if (! $existingHasSource && ! $hasNewLink && ! $hasNewFile) {
            return redirect()->back()->withInput()->with('error', 'Dokumen harus memiliki salah satu referensi: file atau link.');
        }

        if ($sumberDokumen === 'link' && ! $hasNewLink && ($dokumen['sumber_dokumen'] ?? 'file') !== 'link') {
            return redirect()->back()->withInput()->with('error', 'Untuk mengganti ke sumber link, isi URL dokumen terlebih dahulu.');
        }

        if ($sumberDokumen === 'file' && ! $hasNewFile && ($dokumen['sumber_dokumen'] ?? 'file') !== 'file') {
            return redirect()->back()->withInput()->with('error', 'Untuk mengganti ke sumber file, upload file dokumen terlebih dahulu.');
        }

        $isResubmittedFromRevision = $statusSaatIni === 'perlu_revisi';

        $dataUpdate = [
            'judul_dokumen'    => trim((string) $this->request->getPost('judul_dokumen')),
            'deskripsi'        => trim((string) $this->request->getPost('deskripsi')),
            'jenis_dokumen'    => $jenisDokumen,
            'tahun_dokumen'    => $this->request->getPost('tahun_dokumen') !== '' ? (int) $this->request->getPost('tahun_dokumen') : null,
            'status_dokumen'   => $isResubmittedFromRevision ? 'disubmit_ulang' : $statusSaatIni,
            'catatan_terakhir' => trim((string) $this->request->getPost('catatan_terakhir')),
            'program_studi_id' => $programStudiId > 0 ? $programStudiId : null,
            'unit_kerja'       => $unitKerja,
            'tanggal_submit'   => $isResubmittedFromRevision ? date('Y-m-d H:i:s') : ($dokumen['tanggal_submit'] ?? null),
            'tanggal_validasi' => $isResubmittedFromRevision ? null : ($dokumen['tanggal_validasi'] ?? null),
            'reviewer_id'      => $isResubmittedFromRevision ? null : ($dokumen['reviewer_id'] ?? null),
        ];

        if (trim((string) ($dokumen['kode_dokumen'] ?? '')) === '') {
            $dataUpdate['kode_dokumen'] = $this->generateKodeDokumen((int) $kriteria['id'], (int) $subBagian['id'], (string) ($kriteria['kode'] ?? ''), (string) ($subBagian['nama_sub_bagian'] ?? ''));
        }

        $versiBaru = (int) $dokumen['versi'];

        if ($hasNewFile) {
            if (! $file || ! $file->isValid() || $file->hasMoved()) {
                return redirect()->back()->withInput()->with('error', 'File dokumen gagal diunggah.');
            }

            $namaFileClient = $file->getClientName();
            $ekstensiFile = $file->getExtension();
            $mimeType = $file->getMimeType();
            $ukuranFile = $file->getSize();

            $folderTujuan = $this->buildDokumenFolderPath($kriteria, $subBagian, is_array($prodi) ? $prodi : []);
            $fullFolderPath = WRITEPATH . $folderTujuan;
            if (! is_dir($fullFolderPath)) {
                mkdir($fullFolderPath, 0755, true);
            }

            $namaFileSimpan = $file->getRandomName();
            $file->move($fullFolderPath, $namaFileSimpan);

            $versiBaru++;

            $dataUpdate['sumber_dokumen'] = 'file';
            $dataUpdate['link_dokumen'] = null;
            $dataUpdate['nama_file'] = $namaFileClient;
            $dataUpdate['path_file'] = $folderTujuan . '/' . $namaFileSimpan;
            $dataUpdate['ekstensi_file'] = $ekstensiFile;
            $dataUpdate['mime_type'] = $mimeType;
            $dataUpdate['ukuran_file'] = $ukuranFile;
            $dataUpdate['versi'] = $versiBaru;
            $dataUpdate['tanggal_upload'] = date('Y-m-d H:i:s');

            $this->riwayatDokumenModel->insert([
                'dokumen_id'      => (int) $dokumen['id'],
                'versi'           => $versiBaru,
                'status_saat_itu' => $dataUpdate['status_dokumen'],
                'keterangan'      => 'Upload revisi / update dokumen',
                'nama_file'       => $dataUpdate['nama_file'],
                'path_file'       => $dataUpdate['path_file'],
                'ekstensi_file'   => $dataUpdate['ekstensi_file'],
                'mime_type'       => $dataUpdate['mime_type'],
                'ukuran_file'     => $dataUpdate['ukuran_file'],
                'diunggah_oleh'   => session()->get('user_id'),
                'waktu_upload'    => date('Y-m-d H:i:s'),
            ]);
        } elseif ($hasNewLink) {
            $wasFileReference = ! empty($dokumen['path_file']);
            $dataUpdate['sumber_dokumen'] = 'link';
            $dataUpdate['link_dokumen'] = $linkDokumen;
            $dataUpdate['nama_file'] = null;
            $dataUpdate['path_file'] = null;
            $dataUpdate['ekstensi_file'] = null;
            $dataUpdate['mime_type'] = null;
            $dataUpdate['ukuran_file'] = 0;
            $dataUpdate['tanggal_upload'] = null;

            if ($wasFileReference) {
                $this->riwayatDokumenModel->insert([
                    'dokumen_id'      => (int) $dokumen['id'],
                    'versi'           => $versiBaru,
                    'status_saat_itu' => $statusSaatIni,
                    'keterangan'      => 'Referensi aktif diubah ke link dokumen. File lama disimpan sebagai riwayat.',
                    'nama_file'       => $dokumen['nama_file'] ?? null,
                    'path_file'       => $dokumen['path_file'] ?? null,
                    'ekstensi_file'   => $dokumen['ekstensi_file'] ?? null,
                    'mime_type'       => $dokumen['mime_type'] ?? null,
                    'ukuran_file'     => (int) ($dokumen['ukuran_file'] ?? 0),
                    'diunggah_oleh'   => session()->get('user_id'),
                    'waktu_upload'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->dokumenModel->update((int) $id, $dataUpdate);

        catat_audit(
            'edit_dokumen',
            'dokumen',
            (int) $id,
            'Memperbarui dokumen: ' . $dataUpdate['judul_dokumen']
        );

        $selectedProgramStudiId = $this->resolveSelectedProgramStudiFilter();
        $successMessage = $isResubmittedFromRevision
            ? 'Dokumen berhasil diperbarui dan disubmit ulang untuk ditinjau kembali.'
            : 'Dokumen berhasil diperbarui.';

        return redirect()->to($this->kriteriaSubBagianUrl((int) $kriteria['id'], (int) $dokumen['sub_bagian_id'], $selectedProgramStudiId))->with('success', $successMessage);
    }

    public function review($id)
    {
        $dokumen = $this->dokumenModel->getDetailById((int) $id);

        if (! $dokumen || ! can_review_dokumen($dokumen)) {
            return redirect()->to('/kriteria')->with('error', 'Anda tidak memiliki akses untuk review dokumen tersebut.');
        }

        $reviewList = $this->reviewDokumenModel->getByDokumen((int) $id);

        return view('dokumen/review', [
            'title'      => 'Review Dokumen',
            'dokumen'    => $dokumen,
            'reviewList' => $reviewList,
            'selectedProgramStudiId' => $this->resolveSelectedProgramStudiFilter(),
        ]);
    }

    public function finalisasi($id)
    {
        $dokumen = $this->dokumenModel->find((int) $id);

        if (! $dokumen || ! can_final_validate_dokumen($dokumen)) {
            return redirect()->to('/kriteria')->with('error', 'Anda tidak memiliki akses finalisasi dokumen tersebut.');
        }

        $lockedFinalStatuses = ['tervalidasi', 'perlu_revisi'];
        if (in_array($dokumen['status_dokumen'] ?? '', $lockedFinalStatuses, true)) {
            return redirect()->back()->with('error', 'Finalisasi dokumen sudah selesai dan tidak dapat diubah kembali.');
        }

        $rules = [
            'status_review'  => 'required|in_list[tervalidasi,perlu_revisi]',
            'catatan_review' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data finalisasi belum valid.');
        }

        $statusReview = (string) $this->request->getPost('status_review');
        $catatanReview = trim((string) $this->request->getPost('catatan_review'));

        $this->reviewDokumenModel->insert([
            'dokumen_id'      => (int) $dokumen['id'],
            'reviewer_id'     => (int) session()->get('user_id'),
            'status_review'   => $statusReview,
            'catatan_review'  => $catatanReview,
            'tanggal_review'  => date('Y-m-d H:i:s'),
        ]);

        $this->dokumenModel->update((int) $dokumen['id'], [
            'reviewer_id'      => (int) session()->get('user_id'),
            'status_dokumen'   => $statusReview,
            'catatan_terakhir' => $catatanReview,
            'tanggal_validasi' => $statusReview === 'tervalidasi' ? date('Y-m-d H:i:s') : null,
        ]);

        catat_audit(
            'finalisasi_dokumen',
            'dokumen',
            (int) $dokumen['id'],
            'Finalisasi dokumen dengan status: ' . $statusReview
        );

        $selectedProgramStudiId = $this->resolveSelectedProgramStudiFilter();
        return redirect()->to('/dokumen/' . $dokumen['id'] . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId))->with('success', 'Finalisasi dokumen berhasil disimpan.');
    }

    public function delete($id)
    {
        $dokumen = $this->dokumenModel->find((int) $id);

        if (! $dokumen || ! can_manage_dokumen($dokumen)) {
            return redirect()->to('/kriteria')->with('error', 'Anda tidak memiliki akses untuk menghapus dokumen tersebut.');
        }

        // Dosen tidak boleh menghapus dokumen yang sudah tervalidasi
        if (has_role('dosen') && ($dokumen['status_dokumen'] ?? '') === 'tervalidasi') {
            return redirect()->to('/kriteria')->with('error', 'Dokumen yang sudah tervalidasi tidak dapat dihapus.');
        }

        $subBagianId = (int) $dokumen['sub_bagian_id'];
        $kriteriaId = (int) ($dokumen['kriteria_id'] ?? 0);
        $judul = $dokumen['judul_dokumen'] ?? ('ID ' . $id);

        $this->dokumenModel->delete((int) $id);

        catat_audit(
            'hapus_dokumen',
            'dokumen',
            (int) $id,
            'Menghapus dokumen: ' . $judul
        );

        $selectedProgramStudiId = $this->resolveSelectedProgramStudiFilter();
        return redirect()->to($this->kriteriaSubBagianUrl($kriteriaId, $subBagianId, $selectedProgramStudiId))->with('success', 'Dokumen berhasil dihapus.');
    }

    public function bulkDelete()
    {
        $selectedIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): int => (int) $id,
            (array) $this->request->getPost('selected_ids')
        ), static fn (int $id): bool => $id > 0)));

        $kriteriaId = (int) ($this->request->getPost('kriteria_id') ?? 0);
        $subBagianId = (int) ($this->request->getPost('sub_bagian_id') ?? 0);
        $selectedProgramStudiId = (int) ($this->request->getPost('program_studi_id') ?? $this->resolveSelectedProgramStudiFilter());

        $redirectUrl = $this->kriteriaSectionUrl($kriteriaId, $subBagianId, $selectedProgramStudiId);

        if (empty($selectedIds)) {
            return redirect()->to($redirectUrl)->with('error', 'Pilih minimal satu dokumen untuk dihapus.');
        }

        $dokumenList = $this->dokumenModel
            ->whereIn('id', $selectedIds)
            ->findAll();

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($dokumenList as $dokumen) {
            if (! can_manage_dokumen($dokumen)) {
                $skippedCount++;
                continue;
            }

            // Dosen tidak boleh menghapus dokumen yang sudah tervalidasi
            if (has_role('dosen') && ($dokumen['status_dokumen'] ?? '') === 'tervalidasi') {
                $skippedCount++;
                continue;
            }

            $dokumenId = (int) ($dokumen['id'] ?? 0);
            if ($dokumenId <= 0) {
                $skippedCount++;
                continue;
            }

            $this->dokumenModel->delete($dokumenId);
            $deletedCount++;

            catat_audit(
                'hapus_dokumen_bulk',
                'dokumen',
                $dokumenId,
                'Menghapus dokumen (bulk): ' . ($dokumen['judul_dokumen'] ?? ('ID ' . $dokumenId))
            );
        }

        if ($deletedCount === 0) {
            return redirect()->to($redirectUrl)->with('error', 'Tidak ada dokumen yang berhasil dihapus.');
        }

        $message = $deletedCount . ' dokumen berhasil dihapus.';
        if ($skippedCount > 0) {
            $message .= ' ' . $skippedCount . ' dokumen dilewati karena tidak dapat diakses.';
        }

        return redirect()->to($redirectUrl)->with('success', $message);
    }

    private function kriteriaSubBagianUrl(int $kriteriaId, int $subBagianId, int $selectedProgramStudiId = 0): string
    {
        return '/kriteria/' . $kriteriaId . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId) . '#subbagian-' . $subBagianId;
    }

    private function kriteriaSectionUrl(int $kriteriaId, int $subBagianId, int $selectedProgramStudiId = 0): string
    {
        if ($kriteriaId <= 0) {
            return '/kriteria';
        }

        if ($subBagianId > 0) {
            return $this->kriteriaSubBagianUrl($kriteriaId, $subBagianId, $selectedProgramStudiId);
        }

        return '/kriteria/' . $kriteriaId . $this->buildProgramStudiQuerySuffix($selectedProgramStudiId) . '#dokumen-utama';
    }

    private function getOrCreateDefaultSubBagian(int $kriteriaId, int $programStudiId): array
    {
        if ($kriteriaId <= 0 || $programStudiId <= 0) {
            return [];
        }

        $default = $this->subBagianModel
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->where('slug_sub_bagian', self::DEFAULT_SUB_BAGIAN_SLUG)
            ->first();

        if ($default) {
            return $default;
        }

        $this->subBagianModel->insert([
            'kriteria_id'      => $kriteriaId,
            'program_studi_id' => $programStudiId,
            'nama_sub_bagian'  => 'Dokumen Utama',
            'slug_sub_bagian'  => self::DEFAULT_SUB_BAGIAN_SLUG,
            'deskripsi'        => 'Sub bagian otomatis untuk kriteria tanpa sub bagian.',
            'urutan'           => 1,
            'dibuat_oleh'      => session()->get('user_id'),
            'diupdate_oleh'    => session()->get('user_id'),
            'is_aktif'         => 1,
        ]);

        $id = (int) $this->subBagianModel->getInsertID();
        return $this->subBagianModel->find($id) ?? [];
    }

    private function generateKodeDokumen(int $kriteriaId, int $subBagianId, string $kodeKriteria, string $namaSubBagian): string
    {
        $kodeKriteria = strtoupper(trim($kodeKriteria)) ?: ('K' . $kriteriaId);

        $subBagianWords = preg_split('/\s+/', strtoupper(trim($namaSubBagian))) ?: [];
        $subBagianCode = '';
        foreach ($subBagianWords as $word) {
            $word = preg_replace('/[^A-Z0-9]/', '', $word);
            if ($word === '') {
                continue;
            }
            $subBagianCode .= substr($word, 0, 1);
            if (strlen($subBagianCode) >= 3) {
                break;
            }
        }

        if ($subBagianCode === '') {
            $subBagianCode = 'SB' . $subBagianId;
        }

        $builder = $this->dokumenModel->builder();
        $totalDiSubBagian = (int) $builder
            ->where('kriteria_id', $kriteriaId)
            ->where('sub_bagian_id', $subBagianId)
            ->where('deleted_at', null)
            ->countAllResults();

        $urutan = str_pad((string) ($totalDiSubBagian + 1), 3, '0', STR_PAD_LEFT);
        return $kodeKriteria . '-' . $subBagianCode . '-' . $urutan;
    }

    private function generateNomorDokumen(int $kriteriaId, int $subBagianId, int $programStudiId, string $kodeKriteria, string $namaSubBagian): string
    {
        $kodeKriteria = strtoupper(trim($kodeKriteria)) ?: ('K' . $kriteriaId);

        $subBagianWords = preg_split('/\s+/', strtoupper(trim($namaSubBagian))) ?: [];
        $subBagianCode = '';
        foreach ($subBagianWords as $word) {
            $word = preg_replace('/[^A-Z0-9]/', '', $word);
            if ($word === '') {
                continue;
            }

            $subBagianCode .= substr($word, 0, 1);
            if (strlen($subBagianCode) >= 3) {
                break;
            }
        }

        if ($subBagianCode === '') {
            $subBagianCode = 'SB' . $subBagianId;
        }

        $builder = $this->dokumenModel->builder()
            ->where('kriteria_id', $kriteriaId)
            ->where('sub_bagian_id', $subBagianId)
            ->where('deleted_at', null);

        if ($programStudiId > 0) {
            $builder->where('program_studi_id', $programStudiId);
        }

        $totalDiSubBagian = (int) $builder->countAllResults();
        $urutan = str_pad((string) ($totalDiSubBagian + 1), 4, '0', STR_PAD_LEFT);

        return 'ND/' . $kodeKriteria . '/' . $subBagianCode . '/' . $urutan;
    }

    private function buildDokumenFolderPath(array $kriteria, array $subBagian, array $prodi): string
    {
        $prodiName = trim((string) ($prodi['nama_singkatan'] ?? ''));
        if ($prodiName === '') {
            $prodiName = trim((string) ($prodi['nama_program_studi'] ?? ''));
        }
        if ($prodiName === '') {
            $prodiName = 'prodi-' . (int) ($prodi['id'] ?? 0);
        }

        $kodeKriteria = trim((string) ($kriteria['kode'] ?? ''));
        if ($kodeKriteria === '') {
            $kodeKriteria = 'kriteria-' . (int) ($kriteria['id'] ?? 0);
        }

        $subBagianSlug = trim((string) ($subBagian['slug_sub_bagian'] ?? ''));
        $isDefaultSubBagian = $subBagianSlug === '' || $subBagianSlug === self::DEFAULT_SUB_BAGIAN_SLUG;

        $segments = [
            'uploads',
            'dokumen',
            $this->normalizeFolderSegment($prodiName, 'prodi'),
            $this->normalizeFolderSegment($kodeKriteria, 'kriteria'),
        ];

        if (! $isDefaultSubBagian) {
            $segments[] = $this->normalizeFolderSegment($subBagianSlug, 'sub-bagian');
        }

        return implode('/', $segments);
    }

    private function normalizeFolderSegment(string $value, string $fallback): string
    {
        $candidate = function_exists('buat_slug')
            ? buat_slug($value)
            : strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $value));

        $candidate = trim((string) $candidate, "-_ \t\n\r\0\x0B");
        return $candidate !== '' ? $candidate : $fallback;
    }

    private function getJenisDokumenAktif(): array
    {
        return $this->jenisDokumenModel
            ->where('is_aktif', 1)
            ->orderBy('nama_jenis_dokumen', 'ASC')
            ->findAll();
    }

    private function isJenisDokumenAktif(string $namaJenisDokumen): bool
    {
        if ($namaJenisDokumen === '') {
            return false;
        }

        return $this->jenisDokumenModel
            ->where('is_aktif', 1)
            ->where('nama_jenis_dokumen', $namaJenisDokumen)
            ->first() !== null;
    }

    private function getProgramStudiListForCurrentUser(): array
    {
        if (has_role(['admin', 'lpm'])) {
            return $this->programStudiModel->orderBy('nama_program_studi', 'ASC')->findAll();
        }

        if (has_role('dekan')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (! empty($accessibleProgramStudiIds)) {
                return $this->programStudiModel
                    ->whereIn('id', $accessibleProgramStudiIds)
                    ->orderBy('nama_program_studi', 'ASC')
                    ->findAll();
            }

            $uppsId = (int) (session()->get('upps_id') ?? 0);
            if ($uppsId > 0) {
                return $this->programStudiModel
                    ->where('upps_id', $uppsId)
                    ->orderBy('nama_program_studi', 'ASC')
                    ->findAll();
            }
        }

        if (has_role('kaprodi')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (! empty($accessibleProgramStudiIds)) {
                return $this->programStudiModel
                    ->whereIn('id', $accessibleProgramStudiIds)
                    ->orderBy('nama_program_studi', 'ASC')
                    ->findAll();
            }
        }

        if (has_role('dosen')) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (! empty($accessibleProgramStudiIds)) {
                return $this->programStudiModel
                    ->whereIn('id', $accessibleProgramStudiIds)
                    ->orderBy('nama_program_studi', 'ASC')
                    ->findAll();
            }
        }

        return [];
    }

    private function resolveProgramStudiIdForCurrentUser(int $fallback = 0): int
    {
        $requestedProdiId = (int) ($this->request->getPost('program_studi_id') ?? 0);

        if (has_role(['admin', 'lpm'])) {
            $programStudiId = $requestedProdiId > 0 ? $requestedProdiId : $fallback;
            if ($programStudiId <= 0) {
                return 0;
            }

            return can_upload_to_program_studi($programStudiId) ? $programStudiId : 0;
        }

        if (has_role('dekan')) {
            if ($requestedProdiId <= 0) {
                return 0;
            }

            return can_upload_to_program_studi($requestedProdiId) ? $requestedProdiId : 0;
        }

        if (has_role('kaprodi')) {
            $primaryProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
            $programStudiId = $requestedProdiId > 0
                ? $requestedProdiId
                : ($fallback > 0 ? $fallback : $primaryProgramStudiId);

            return can_upload_to_program_studi($programStudiId) ? $programStudiId : 0;
        }

        if (has_role('dosen')) {
            $primaryProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
            $programStudiId = $requestedProdiId > 0
                ? $requestedProdiId
                : ($fallback > 0 ? $fallback : $primaryProgramStudiId);

            return can_upload_to_program_studi($programStudiId) ? $programStudiId : 0;
        }

        return 0;
    }

    private function resolveSelectedProgramStudiFilter(): int
    {
        $selectedProgramStudiId = (int) ($this->request->getGet('program_studi_id') ?? 0);
        if ($selectedProgramStudiId <= 0) {
            return 0;
        }

        return can_access_program_studi($selectedProgramStudiId) ? $selectedProgramStudiId : 0;
    }

    private function buildProgramStudiQuerySuffix(int $selectedProgramStudiId): string
    {
        return $selectedProgramStudiId > 0 ? ('?program_studi_id=' . $selectedProgramStudiId) : '';
    }
}
