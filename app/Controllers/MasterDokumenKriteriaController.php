<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\KriteriaModel;
use App\Models\MasterDokumenKriteriaModel;
use App\Models\ProgramStudiModel;
use App\Models\SubBagianModel;

class MasterDokumenKriteriaController extends BaseController
{
    private const DEFAULT_SUB_BAGIAN_SLUG = '__default_kriteria_tanpa_subbagian__';

    protected MasterDokumenKriteriaModel $model;
    protected KriteriaModel $kriteriaModel;
    protected SubBagianModel $subBagianModel;
    protected ProgramStudiModel $programStudiModel;
    protected DokumenModel $dokumenModel;

    public function __construct()
    {
        $this->model = new MasterDokumenKriteriaModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->subBagianModel = new SubBagianModel();
        $this->programStudiModel = new ProgramStudiModel();
        $this->dokumenModel = new DokumenModel();
    }

    public function index()
    {
        $perPage = 25;
        $list    = $this->model->getWithRelasiPaginated($perPage);
        $pager   = $this->model->pager;

        return view('master_dokumen_kriteria/index', [
            'title'                 => 'Master Dokumen Kriteria',
            'list'                  => $list,
            'pager'                 => $pager,
            'perPage'               => $perPage,
            'kriteriaList'          => $this->kriteriaModel->getAktif(),
            'programStudiAktifList' => $this->getProgramStudiAktifAkreditasi(),
        ]);
    }

    public function create()
    {
        return view('master_dokumen_kriteria/form', [
            'title' => 'Tambah Master Dokumen Kriteria',
            'mode' => 'create',
            'data' => null,
            'kriteriaList' => $this->kriteriaModel->getAktif(),
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Data master dokumen kriteria belum valid.');
        }

        $kriteriaId = (int) $this->request->getPost('kriteria_id');
        $useSubBagian = (int) ($this->request->getPost('use_sub_bagian') ?? 0) === 1;
        $namaSubBagianInput = trim((string) $this->request->getPost('nama_sub_bagian'));
        $namaSubBagian = $useSubBagian ? $this->normalizeOptionalField($namaSubBagianInput) : null;
        if ($useSubBagian && $namaSubBagian === null) {
            return redirect()->back()->withInput()->with('error', 'Nama sub bagian wajib diisi jika Gunakan Sub Bagian = Ya.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $this->model->insert([
            'kriteria_id' => $kriteriaId,
            'sub_bagian_id' => null,
            'nama_sub_bagian' => $namaSubBagian,
            'judul_dokumen' => trim((string) $this->request->getPost('judul_dokumen')),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'jenis_dokumen' => $this->normalizeOptionalField((string) $this->request->getPost('jenis_dokumen')),
            'tahun_dokumen' => (int) $this->request->getPost('tahun_dokumen'),
            'is_aktif' => (int) $this->request->getPost('is_aktif'),
            'created_by' => $userId > 0 ? $userId : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        return redirect()->to('/master-dokumen-kriteria')->with('success', 'Master dokumen kriteria berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = $this->model->find((int) $id);
        if (! $data) {
            return redirect()->to('/master-dokumen-kriteria')->with('error', 'Data master dokumen kriteria tidak ditemukan.');
        }

        return view('master_dokumen_kriteria/form', [
            'title' => 'Edit Master Dokumen Kriteria',
            'mode' => 'edit',
            'data' => $data,
            'kriteriaList' => $this->kriteriaModel->getAktif(),
        ]);
    }

    public function update($id)
    {
        $row = $this->model->find((int) $id);
        if (! $row) {
            return redirect()->to('/master-dokumen-kriteria')->with('error', 'Data master dokumen kriteria tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', 'Data master dokumen kriteria belum valid.');
        }

        $kriteriaId = (int) $this->request->getPost('kriteria_id');
        $useSubBagian = (int) ($this->request->getPost('use_sub_bagian') ?? 0) === 1;
        $namaSubBagianInput = trim((string) $this->request->getPost('nama_sub_bagian'));
        $namaSubBagian = $useSubBagian ? $this->normalizeOptionalField($namaSubBagianInput) : null;
        if ($useSubBagian && $namaSubBagian === null) {
            return redirect()->back()->withInput()->with('error', 'Nama sub bagian wajib diisi jika Gunakan Sub Bagian = Ya.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $this->model->update((int) $id, [
            'kriteria_id' => $kriteriaId,
            'sub_bagian_id' => null,
            'nama_sub_bagian' => $namaSubBagian,
            'judul_dokumen' => trim((string) $this->request->getPost('judul_dokumen')),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'jenis_dokumen' => $this->normalizeOptionalField((string) $this->request->getPost('jenis_dokumen')),
            'tahun_dokumen' => (int) $this->request->getPost('tahun_dokumen'),
            'is_aktif' => (int) $this->request->getPost('is_aktif'),
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        return redirect()->to('/master-dokumen-kriteria')->with('success', 'Master dokumen kriteria berhasil diperbarui.');
    }

    public function delete($id)
    {
        $row = $this->model->find((int) $id);
        if (! $row) {
            return redirect()->to('/master-dokumen-kriteria')->with('error', 'Data master dokumen kriteria tidak ditemukan.');
        }

        $this->model->delete((int) $id);
        return redirect()->to('/master-dokumen-kriteria')->with('success', 'Master dokumen kriteria berhasil dihapus.');
    }

    public function bulkDelete()
    {
        $selectedIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): int => (int) $id,
            (array) $this->request->getPost('selected_ids')
        ), static fn (int $id): bool => $id > 0)));

        if (empty($selectedIds)) {
            return redirect()->to('/master-dokumen-kriteria')->with('error', 'Pilih minimal satu master dokumen untuk dihapus.');
        }

        $deleted = 0;
        foreach ($selectedIds as $id) {
            if ($this->model->find($id)) {
                $this->model->delete($id);
                $deleted++;
            }
        }

        return redirect()->to('/master-dokumen-kriteria')->with('success', $deleted . ' master dokumen berhasil dihapus.');
    }

    public function generate()
    {
        $rules = [
            'program_studi_id' => 'required|is_natural_no_zero',
            'kriteria_id' => 'permit_empty|is_natural_no_zero',
            'nama_sub_bagian_filter' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/master-dokumen-kriteria')->withInput()->with('error', 'Parameter generate belum valid.');
        }

        $programStudiId = (int) $this->request->getPost('program_studi_id');
        $kriteriaId = (int) ($this->request->getPost('kriteria_id') ?? 0);
        $namaSubBagianFilter = trim((string) ($this->request->getPost('nama_sub_bagian_filter') ?? ''));
        $isPreview = (int) ($this->request->getPost('preview_only') ?? 0) === 1;

        $programStudi = $this->programStudiModel
            ->where('id', $programStudiId)
            ->where('is_aktif_akreditasi', 1)
            ->first();

        if (! $programStudi) {
            return redirect()->to('/master-dokumen-kriteria')->withInput()->with('error', 'Program Studi tidak valid atau belum aktif akreditasi.');
        }

        $masterItems = $this->model->getAktifByFilter($kriteriaId, $namaSubBagianFilter);
        if (empty($masterItems)) {
            return redirect()->to('/master-dokumen-kriteria')->withInput()->with('error', 'Tidak ada master dokumen aktif untuk filter yang dipilih.');
        }

        $generated = 0;
        $skipped = 0;
        $generatedTitles = [];
        $skippedTitles = [];
        $unitKerja = trim((string) ($programStudi['nama_program_studi'] ?? 'Program Studi'));

        foreach ($masterItems as $item) {
            $kriteriaIdRef = (int) ($item['kriteria_id'] ?? 0);
            $subBagianIdRef = $this->resolveTargetSubBagianId($item, $programStudiId, ! $isPreview);

            if (! $isPreview && $subBagianIdRef <= 0 && $kriteriaIdRef > 0) {
                // Fallback aman agar dokumen tetap bisa dibuat pada Dokumen Utama prodi terpilih.
                $subBagianIdRef = $this->getOrCreateDefaultSubBagian($kriteriaIdRef, $programStudiId);
            }

            if ($subBagianIdRef <= 0 || $kriteriaIdRef <= 0) {
                $skipped++;
                $judulSkipped = trim((string) ($item['judul_dokumen'] ?? 'Dokumen tanpa judul'));
                $skippedTitles[] = $judulSkipped . ' (Sub bagian tidak valid)';
                continue;
            }

            if ($this->isDokumenSudahAda($programStudiId, $kriteriaIdRef, $subBagianIdRef, (string) ($item['judul_dokumen'] ?? ''))) {
                $skipped++;
                $judulSkipped = trim((string) ($item['judul_dokumen'] ?? 'Dokumen tanpa judul'));
                $skippedTitles[] = $judulSkipped . ' (Sudah ada)';
                continue;
            }

            $judul = trim((string) ($item['judul_dokumen'] ?? ''));

            if ($isPreview) {
                $generated++;
                $generatedTitles[] = $judul !== '' ? $judul : 'Dokumen tanpa judul';
                continue;
            }

            $slug = $this->buildUniqueSlug($judul, $programStudiId, (int) ($item['id'] ?? 0));
            $kriteriaCode = $this->resolveKriteriaCode($kriteriaIdRef);

            $this->dokumenModel->insert([
                'kriteria_id' => $kriteriaIdRef,
                'sub_bagian_id' => $subBagianIdRef,
                'kode_dokumen' => $kriteriaCode . '-GEN-' . $programStudiId . '-' . ((int) ($item['id'] ?? 0)),
                'judul_dokumen' => $judul,
                'slug_dokumen' => $slug,
                'deskripsi' => trim((string) ($item['deskripsi'] ?? '')),
                'nomor_dokumen' => null,
                'jenis_dokumen' => $this->normalizeOptionalField((string) ($item['jenis_dokumen'] ?? '')),
                'sumber_dokumen' => 'file',
                'link_dokumen' => null,
                'tahun_dokumen' => ! empty($item['tahun_dokumen']) ? (int) $item['tahun_dokumen'] : null,
                'nama_file' => null,
                'path_file' => null,
                'ekstensi_file' => null,
                'mime_type' => null,
                'ukuran_file' => null,
                'versi' => 1,
                'status_dokumen' => 'draft',
                'catatan_terakhir' => 'Digenerate dari Master Dokumen Kriteria.',
                'tanggal_upload' => null,
                'tanggal_submit' => null,
                'tanggal_validasi' => null,
                'uploaded_by' => null,
                'reviewer_id' => null,
                'program_studi_id' => $programStudiId,
                'unit_kerja' => $unitKerja,
                'is_aktif' => 1,
            ]);

            if (! empty($this->dokumenModel->errors())) {
                $skipped++;
                $judulSkipped = $judul !== '' ? $judul : 'Dokumen tanpa judul';
                $skippedTitles[] = $judulSkipped . ' (Gagal insert)';
                continue;
            }

            $generated++;
            $generatedTitles[] = $judul !== '' ? $judul : 'Dokumen tanpa judul';
        }

        if ($isPreview) {
            return redirect()->to('/master-dokumen-kriteria')
                ->with(
                    'success',
                    'Preview generate untuk prodi ' . $unitKerja . ': ' . $generated . ' dokumen akan dibuat, ' . $skipped . ' dokumen akan dilewati.'
                )
                ->with('generate_mode', 'preview')
                ->with('generate_detail', [
                    'generated_titles' => $generatedTitles,
                    'skipped_titles' => $skippedTitles,
                ]);
        }

        return redirect()->to('/master-dokumen-kriteria')
            ->with(
                'success',
                'Generate selesai untuk prodi ' . $unitKerja . ': ' . $generated . ' dokumen dibuat, ' . $skipped . ' dokumen dilewati.'
            )
            ->with('generate_mode', 'execute')
            ->with('generate_detail', [
                'generated_titles' => $generatedTitles,
                'skipped_titles' => $skippedTitles,
            ]);
    }

    public function templateExcel()
    {
        $templatePath = FCPATH . 'uploads/template_master_dokumen_kriteria.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheetData = $spreadsheet->getActiveSheet();
        $sheetData->setTitle('Data Import');
        $sheetData->setCellValue('A1', 'Kriteria');
        $sheetData->setCellValue('B1', 'Sub Bagian (Opsional)');
        $sheetData->setCellValue('C1', 'Judul Dokumen');
        $sheetData->setCellValue('D1', 'Deskripsi (Opsional)');
        $sheetData->setCellValue('E1', 'Jenis Dokumen (Otomatis)');
        $sheetData->setCellValue('F1', 'Tahun Dokumen');

        $sheetData->setCellValue('A2', 'K1');
        $sheetData->setCellValue('B2', 'Ketepatan Rumusan Visi Keilmuan PS');
        $sheetData->setCellValue('C2', 'Dokumen Kurikulum Program Studi');
        $sheetData->setCellValue('D2', 'Template dari master dokumen kriteria.');
        $sheetData->setCellValue('E2', 'Dokumen Pendukung');
        $sheetData->setCellValue('F2', date('Y'));
        $kriteriaValidationFormula = '"K1,K2,K3,K4,K5,K6,K7,K8,K9"';
        for ($row = 2; $row <= 500; $row++) {
            $validation = $sheetData->getCell('A' . $row)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Kriteria tidak valid');
            $validation->setError('Isi kolom Kriteria hanya dengan K1 sampai K9.');
            $validation->setPromptTitle('Pilih Kriteria');
            $validation->setPrompt('Gunakan salah satu nilai: K1, K2, K3, K4, K5, K6, K7, K8, K9.');
            $validation->setFormula1($kriteriaValidationFormula);
        }

        $sheetGuide = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Petunjuk');
        $spreadsheet->addSheet($sheetGuide);
        $sheetGuide->setCellValue('A1', 'Petunjuk Pengisian Template Master Dokumen Kriteria');
        $sheetGuide->setCellValue('A3', '1. Isi data pada sheet "Data Import".');
        $sheetGuide->setCellValue('A4', '2. Kolom wajib diisi: Kriteria, Judul Dokumen, Tahun Dokumen.');
        $sheetGuide->setCellValue('A5', '3. Kolom Kriteria WAJIB diisi kode: K1, K2, K3, K4, K5, K6, K7, K8, atau K9.');
        $sheetGuide->setCellValue('A6', '4. Kolom Sub Bagian bersifat opsional. Jika kosong, sistem otomatis menganggap Dokumen Utama.');
        $sheetGuide->setCellValue('A7', '5. Kolom Jenis Dokumen opsional. Jika tidak cocok dengan master jenis dokumen, nilainya dikosongkan.');
        $sheetGuide->setCellValue('A8', '6. Kolom Tahun Dokumen diisi angka tahun, contoh: 2026.');
        $sheetGuide->setCellValue('A9', '7. Jangan mengubah nama kolom pada baris pertama sheet "Data Import".');
        $sheetGuide->setCellValue('A10', '8. Simpan file dalam format Excel (.xlsx) sebelum diimpor.');

        if (! is_dir(FCPATH . 'uploads')) {
            mkdir(FCPATH . 'uploads', 0777, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($templatePath);

        return $this->response
            ->download($templatePath, null)
            ->setFileName('template_master_dokumen_kriteria.xlsx');
    }

    public function impor()
    {
        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau gagal diunggah.');
        }

        $extension = strtolower((string) ($file->getClientExtension() ?: $file->getExtension()));
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan file Excel (.xlsx/.xls).');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            log_message('error', 'Gagal membaca file import master dokumen kriteria: {message}', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'File Excel tidak dapat dibaca. Pastikan file tidak rusak dan formatnya benar.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $header = $rows[1] ?? [];
        $map = [
            'kriteria' => null,
            'sub_bagian' => null,
            'judul_dokumen' => null,
            'deskripsi' => null,
            'jenis_dokumen' => null,
            'tahun_dokumen' => null,
        ];
        $headerAliases = [
            'kriteria' => ['kriteria', 'kode kriteria', 'nomor kriteria'],
            'sub_bagian' => ['sub bagian (opsional)', 'sub bagian', 'nama sub bagian'],
            'judul_dokumen' => ['judul dokumen'],
            'deskripsi' => ['deskripsi (opsional)', 'deskripsi'],
            'jenis_dokumen' => ['jenis dokumen (otomatis)', 'jenis dokumen'],
            'tahun_dokumen' => ['tahun dokumen', 'tahun'],
        ];

        foreach ($header as $col => $val) {
            $normalizedHeader = $this->normalizeImportHeader((string) $val);
            if ($normalizedHeader === '') {
                continue;
            }

            foreach ($headerAliases as $key => $aliases) {
                if ($map[$key] !== null) {
                    continue;
                }

                if (in_array($normalizedHeader, $aliases, true)) {
                    $map[$key] = $col;
                    break;
                }
            }
        }

        if (! $map['kriteria'] || ! $map['judul_dokumen'] || ! $map['tahun_dokumen']) {
            return redirect()->back()->with('error', 'Kolom wajib (Kriteria, Judul Dokumen, Tahun Dokumen) tidak ditemukan.');
        }

        $jenisDokumenModel = new \App\Models\JenisDokumenModel();
        $jenisList = $jenisDokumenModel->findAll();
        $jenisMap = [];
        foreach ($jenisList as $j) {
            $namaJenisDokumen = trim((string) ($j['nama_jenis_dokumen'] ?? ''));
            if ($namaJenisDokumen !== '') {
                $jenisMap[strtolower($namaJenisDokumen)] = $namaJenisDokumen;
            }
        }

        $kriteriaLookup = [];
        $kriteriaList = $this->kriteriaModel
            ->whereIn('kode', ['K1', 'K2', 'K3', 'K4', 'K5', 'K6', 'K7', 'K8', 'K9'])
            ->findAll();
        foreach ($kriteriaList as $kriteria) {
            $id = (int) ($kriteria['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $kode = strtoupper(trim((string) ($kriteria['kode'] ?? '')));
            if (preg_match('/^K[1-9]$/', $kode) === 1) {
                $kriteriaLookup[$kode] = $id;
            }
        }

        $success = 0;
        $failed = 0;
        $failedRows = [];

        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? [];

            if ($this->isImportRowEmpty($row)) {
                continue;
            }

            $kriteriaValue = trim((string) ($row[$map['kriteria']] ?? ''));
            $judul = trim((string) ($row[$map['judul_dokumen']] ?? ''));
            $tahun = (int) ($row[$map['tahun_dokumen']] ?? 0);
            if ($kriteriaValue === '' || $judul === '' || $tahun === 0) {
                $failed++;
                $failedRows[] = $i . ' (kolom wajib kosong)';
                continue;
            }
            $subBagian = trim((string) ($row[$map['sub_bagian']] ?? ''));
            $deskripsi = trim((string) ($row[$map['deskripsi']] ?? ''));
            $jenis = trim((string) ($row[$map['jenis_dokumen']] ?? ''));
            $jenisDok = $jenis !== '' && isset($jenisMap[strtolower($jenis)]) ? $jenisMap[strtolower($jenis)] : null;

            $kriteriaLookupKey = strtoupper(trim((string) preg_replace('/\s+/', '', $kriteriaValue)));
            if (preg_match('/^K[1-9]$/', $kriteriaLookupKey) !== 1) {
                $failed++;
                $failedRows[] = $i . ' (kriteria wajib K1 sampai K9)';
                continue;
            }

            $kriteriaId = (int) ($kriteriaLookup[$kriteriaLookupKey] ?? 0);
            if ($kriteriaId <= 0) {
                $failed++;
                $failedRows[] = $i . ' (kode ' . $kriteriaLookupKey . ' belum tersedia di master kriteria)';
                continue;
            }

            $data = [
                'kriteria_id' => $kriteriaId,
                'nama_sub_bagian' => $subBagian !== '' ? $subBagian : null,
                'judul_dokumen' => $judul,
                'deskripsi' => $deskripsi,
                'jenis_dokumen' => $jenisDok,
                'tahun_dokumen' => $tahun,
                'is_aktif' => 1,
                'created_by' => session()->get('user_id') ?? null,
                'updated_by' => session()->get('user_id') ?? null,
            ];

            $inserted = $this->model->insert($data, true);
            if ($inserted === false) {
                $failed++;
                $reason = implode('; ', array_values($this->model->errors()));
                if ($reason === '') {
                    $dbError = $this->model->db->error();
                    $reason = (string) ($dbError['message'] ?? 'gagal menyimpan data');
                }

                $failedRows[] = $i . ' (' . $reason . ')';
                continue;
            }

            $success++;
        }

        $msg = $success . ' baris berhasil diimpor. ' . $failed . ' baris gagal.';
        if ($failed > 0) {
            $msg .= ' Detail gagal: ' . implode(', ', $failedRows);
        }

        if ($success > 0) {
            return redirect()->to('/master-dokumen-kriteria')->with('success', $msg);
        }

        return redirect()->to('/master-dokumen-kriteria')->with('error', $msg);
    }

    private function normalizeImportHeader(string $header): string
    {
        $header = trim(str_replace("\xC2\xA0", ' ', $header));
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;
        return strtolower($header);
    }

    private function isImportRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function rules(): array
    {
        return [
            'kriteria_id' => 'required|is_natural_no_zero',
            'nama_sub_bagian' => 'permit_empty|max_length[255]',
            'judul_dokumen' => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'permit_empty',
            'jenis_dokumen' => 'permit_empty|max_length[100]',
            'tahun_dokumen' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            'is_aktif' => 'required|in_list[0,1]',
        ];
    }

    private function getProgramStudiAktifAkreditasi(): array
    {
        return $this->programStudiModel
            ->where('is_aktif_akreditasi', 1)
            ->orderBy('nama_program_studi', 'ASC')
            ->findAll();
    }

    private function isDokumenSudahAda(int $programStudiId, int $kriteriaId, int $subBagianId, string $judulDokumen): bool
    {
        if ($programStudiId <= 0 || $kriteriaId <= 0 || $subBagianId <= 0 || trim($judulDokumen) === '') {
            return true;
        }

        $judulLower = strtolower(trim($judulDokumen));

        return $this->dokumenModel->db->table('dokumen')
            ->where('program_studi_id', $programStudiId)
            ->where('kriteria_id', $kriteriaId)
            ->where('sub_bagian_id', $subBagianId)
            ->where('LOWER(judul_dokumen) = ' . $this->dokumenModel->db->escape($judulLower), null, false)
            ->where('dokumen.deleted_at', null)
            ->countAllResults() > 0;
    }

    private function buildUniqueSlug(string $judul, int $programStudiId, int $masterId): string
    {
        $baseSlug = buat_slug(trim($judul) !== '' ? $judul : ('master-' . $masterId));
        $candidate = $baseSlug . '-ps' . $programStudiId . '-m' . $masterId;
        $suffix = 1;

        while ($this->dokumenModel->db->table('dokumen')->where('slug_dokumen', $candidate)->countAllResults() > 0) {
            $candidate = $baseSlug . '-ps' . $programStudiId . '-m' . $masterId . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function normalizeOptionalField(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function resolveKriteriaCode(int $kriteriaId): string
    {
        $kriteria = $this->kriteriaModel->find($kriteriaId);
        $kode = strtoupper(trim((string) ($kriteria['kode'] ?? 'K')));

        return $kode !== '' ? $kode : 'K';
    }

    private function resolveTargetSubBagianId(array $item, int $programStudiId, bool $allowMutations = true): int
    {
        $kriteriaId = (int) ($item['kriteria_id'] ?? 0);
        if ($kriteriaId <= 0 || $programStudiId <= 0) {
            return 0;
        }

        $legacySubBagianId = (int) ($item['sub_bagian_id'] ?? 0);
        if ($legacySubBagianId > 0) {
            $subBagian = $this->subBagianModel->withDeleted()->find($legacySubBagianId);
            if (
                $subBagian
                && (int) ($subBagian['kriteria_id'] ?? 0) === $kriteriaId
                && (int) ($subBagian['program_studi_id'] ?? 0) === $programStudiId
            ) {
                if ($allowMutations && (! empty($subBagian['deleted_at']) || (int) ($subBagian['is_aktif'] ?? 0) !== 1)) {
                    $this->restoreSubBagian($legacySubBagianId);
                }

                return $legacySubBagianId;
            }
        }

        $namaSubBagian = trim((string) ($item['nama_sub_bagian'] ?? ''));
        if ($namaSubBagian !== '') {
            if ($allowMutations) {
                return $this->getOrCreateSubBagianByName($kriteriaId, $programStudiId, $namaSubBagian);
            }

            return $this->findSubBagianIdByName($kriteriaId, $programStudiId, $namaSubBagian);
        }

        if ($allowMutations) {
            return $this->getOrCreateDefaultSubBagian($kriteriaId, $programStudiId);
        }

        return $this->findDefaultSubBagianId($kriteriaId, $programStudiId);
    }

    private function findDefaultSubBagianId(int $kriteriaId, int $programStudiId): int
    {
        if ($kriteriaId <= 0 || $programStudiId <= 0) {
            return 0;
        }

        $row = $this->subBagianModel
            ->withDeleted()
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->where('slug_sub_bagian', self::DEFAULT_SUB_BAGIAN_SLUG)
            ->first();

        return (int) ($row['id'] ?? 0);
    }

    private function findSubBagianIdByName(int $kriteriaId, int $programStudiId, string $namaSubBagian): int
    {
        $namaSubBagian = trim($namaSubBagian);
        if ($kriteriaId <= 0 || $programStudiId <= 0 || $namaSubBagian === '') {
            return 0;
        }

        $namaLower = strtolower($namaSubBagian);
        $row = $this->subBagianModel
            ->withDeleted()
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->where('LOWER(nama_sub_bagian) = ' . $this->subBagianModel->db->escape($namaLower), null, false)
            ->first();

        return (int) ($row['id'] ?? 0);
    }

    private function getOrCreateDefaultSubBagian(int $kriteriaId, int $programStudiId): int
    {
        if ($kriteriaId <= 0 || $programStudiId <= 0) {
            return 0;
        }

        $existing = $this->subBagianModel
            ->withDeleted()
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->where('slug_sub_bagian', self::DEFAULT_SUB_BAGIAN_SLUG)
            ->first();

        if ($existing) {
            if (! empty($existing['deleted_at']) || (int) ($existing['is_aktif'] ?? 0) !== 1) {
                $this->restoreSubBagian((int) ($existing['id'] ?? 0));
            }

            return (int) ($existing['id'] ?? 0);
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $this->subBagianModel->insert([
            'kriteria_id' => $kriteriaId,
            'program_studi_id' => $programStudiId,
            'nama_sub_bagian' => 'Dokumen Utama',
            'slug_sub_bagian' => self::DEFAULT_SUB_BAGIAN_SLUG,
            'deskripsi' => 'Dokumen utama pada kriteria tanpa sub bagian khusus.',
            'urutan' => 1,
            'dibuat_oleh' => $userId > 0 ? $userId : null,
            'diupdate_oleh' => $userId > 0 ? $userId : null,
            'is_aktif' => 1,
        ]);

        return (int) $this->subBagianModel->getInsertID();
    }

    private function getOrCreateSubBagianByName(int $kriteriaId, int $programStudiId, string $namaSubBagian): int
    {
        $namaSubBagian = trim($namaSubBagian);
        if ($kriteriaId <= 0 || $programStudiId <= 0 || $namaSubBagian === '') {
            return 0;
        }

        $namaLower = strtolower($namaSubBagian);

        $existing = $this->subBagianModel
            ->withDeleted()
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->where('LOWER(nama_sub_bagian) = ' . $this->subBagianModel->db->escape($namaLower), null, false)
            ->first();

        if ($existing) {
            if (! empty($existing['deleted_at']) || (int) ($existing['is_aktif'] ?? 0) !== 1) {
                $this->restoreSubBagian((int) ($existing['id'] ?? 0), $namaSubBagian);
            }

            return (int) ($existing['id'] ?? 0);
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $slug = $this->buildUniqueSubBagianSlug($kriteriaId, $programStudiId, $namaSubBagian);
        $inserted = $this->subBagianModel->insert([
            'kriteria_id' => $kriteriaId,
            'program_studi_id' => $programStudiId,
            'nama_sub_bagian' => $namaSubBagian,
            'slug_sub_bagian' => $slug,
            'deskripsi' => 'Sub bagian otomatis dari Master Dokumen Kriteria.',
            'urutan' => $this->nextSubBagianUrutan($kriteriaId, $programStudiId),
            'dibuat_oleh' => $userId > 0 ? $userId : null,
            'diupdate_oleh' => $userId > 0 ? $userId : null,
            'is_aktif' => 1,
        ]);

        if (! $inserted) {
            // Jika insert gagal (mis. race/constraint), coba ambil ulang data yang sudah ada.
            $retry = $this->subBagianModel
                ->withDeleted()
                ->where('kriteria_id', $kriteriaId)
                ->where('program_studi_id', $programStudiId)
                ->groupStart()
                    ->where('LOWER(nama_sub_bagian) = ' . $this->subBagianModel->db->escape($namaLower), null, false)
                    ->orWhere('slug_sub_bagian', $slug)
                ->groupEnd()
                ->first();

            if ($retry && (! empty($retry['deleted_at']) || (int) ($retry['is_aktif'] ?? 0) !== 1)) {
                $this->restoreSubBagian((int) ($retry['id'] ?? 0), $namaSubBagian);
            }

            return (int) ($retry['id'] ?? 0);
        }

        return (int) $this->subBagianModel->getInsertID();
    }

    private function buildUniqueSubBagianSlug(int $kriteriaId, int $programStudiId, string $namaSubBagian): string
    {
        $baseSlug = buat_slug($namaSubBagian);
        if ($baseSlug === '') {
            $baseSlug = 'sub-bagian';
        }

        $candidate = $baseSlug;
        $suffix = 1;
        while ($this->subBagianModel
            ->withDeleted()
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->where('slug_sub_bagian', $candidate)
            ->countAllResults() > 0
        ) {
            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function nextSubBagianUrutan(int $kriteriaId, int $programStudiId): int
    {
        $row = $this->subBagianModel
            ->select('MAX(urutan) AS urutan_max', false)
            ->where('kriteria_id', $kriteriaId)
            ->where('program_studi_id', $programStudiId)
            ->first();

        $urutanMax = (int) ($row['urutan_max'] ?? 0);
        return $urutanMax + 1;
    }

    private function restoreSubBagian(int $subBagianId, ?string $namaSubBagian = null): void
    {
        if ($subBagianId <= 0) {
            return;
        }

        $data = [
            'deleted_at' => null,
            'is_aktif' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $namaSubBagian = trim((string) ($namaSubBagian ?? ''));
        if ($namaSubBagian !== '') {
            $data['nama_sub_bagian'] = $namaSubBagian;
        }

        $this->subBagianModel->db
            ->table('sub_bagian')
            ->where('id', $subBagianId)
            ->update($data);
    }
}

