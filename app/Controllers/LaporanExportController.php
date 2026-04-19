<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\KriteriaModel;
use App\Models\ProgramStudiModel;
use App\Models\SubBagianModel;

class LaporanExportController extends BaseController
{
    protected DokumenModel $dokumenModel;
    protected KriteriaModel $kriteriaModel;
    protected ProgramStudiModel $programStudiModel;
    protected SubBagianModel $subBagianModel;

    public function __construct()
    {
        $this->dokumenModel   = new DokumenModel();
        $this->kriteriaModel  = new KriteriaModel();
        $this->programStudiModel = new ProgramStudiModel();
        $this->subBagianModel = new SubBagianModel();
    }

    protected function ambilFilter(): array
    {
        return [
            'kriteria_id'    => $this->request->getGet('kriteria_id'),
            'sub_bagian_id'  => $this->request->getGet('sub_bagian_id'),
            'status_dokumen' => $this->request->getGet('status_dokumen'),
            'tahun_dokumen'  => $this->request->getGet('tahun_dokumen'),
            'program_studi_id' => $this->request->getGet('program_studi_id'),
            'mode'           => $this->request->getGet('mode'),
            'sort_by'        => trim((string) $this->request->getGet('sort_by')) ?: 'updated_at',
            'sort_dir'       => in_array(strtolower(trim((string) $this->request->getGet('sort_dir'))), ['asc', 'desc'], true)
                ? strtolower(trim((string) $this->request->getGet('sort_dir')))
                : 'desc',
        ];
    }

    protected function ambilDataLaporan(): array
    {
        $filter = $this->ambilFilter();
        $programStudiList = $this->getAccessibleProgramStudiList();
        $filter['program_studi_id'] = $this->resolveProgramStudiFilter($filter['program_studi_id'], $programStudiList);
        $pakaiFilterProdiAktif = $this->dokumenModel->isFilterProdiAktifEnabled();

        return [
            'filter'        => $filter,
            'laporanList'   => $this->dokumenModel->getLaporan($filter, $pakaiFilterProdiAktif),
            'rekapStatus'   => $this->dokumenModel->getRekapStatus($filter, $pakaiFilterProdiAktif),
            'rekapKriteria' => $this->dokumenModel->getRekapKriteria($filter, $pakaiFilterProdiAktif),
            'kriteriaList'  => $this->kriteriaModel->getAktif(),
            'subBagianList' => ! empty($filter['kriteria_id'])
                ? $this->subBagianModel->getByKriteria((int) $filter['kriteria_id'], (int) ($filter['program_studi_id'] ?? 0))
                : $this->buildSubBagianListByFilter($filter),
            'programStudiList' => $programStudiList,
            'pakaiFilterProdiAktif' => $pakaiFilterProdiAktif,
        ];
    }

    public function excel()
    {
        $data = $this->ambilDataLaporan();

        $filename = 'laporan-dokumen-akreditasi-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(view('laporan/export_excel', $data));
    }

    public function pdf()
    {
        $data = $this->ambilDataLaporan();
        return view('laporan/print', $data);
    }

    public function print()
    {
        return $this->pdf();
    }

    private function getAccessibleProgramStudiList(): array
    {
        $builder = $this->programStudiModel->orderBy('nama_program_studi', 'ASC');

        if (has_role(['admin', 'lpm'])) {
            return $builder->findAll();
        }

        if (has_role('dekan')) {
            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            if ($userUppsId <= 0) {
                return [];
            }

            return $builder->where('upps_id', $userUppsId)->findAll();
        }

        $userProgramStudiId = (int) (session()->get('program_studi_id') ?? 0);
        if ($userProgramStudiId <= 0) {
            return [];
        }

        return $builder->where('id', $userProgramStudiId)->findAll();
    }

    private function resolveProgramStudiFilter(mixed $rawProgramStudiId, array $programStudiList): string
    {
        $programStudiId = (int) $rawProgramStudiId;
        if ($programStudiId <= 0) {
            if (has_role('kaprodi')) {
                $sessionProdiId = (int) (session()->get('program_studi_id') ?? 0);
                return $sessionProdiId > 0 ? (string) $sessionProdiId : '';
            }

            return '';
        }

        $allowedIds = array_map(static fn ($row) => (int) ($row['id'] ?? 0), $programStudiList);
        return in_array($programStudiId, $allowedIds, true) ? (string) $programStudiId : '';
    }

    private function buildSubBagianListByFilter(array $filter): array
    {
        $builder = $this->subBagianModel->where('deleted_at', null);
        $programStudiId = (int) ($filter['program_studi_id'] ?? 0);
        if ($programStudiId > 0) {
            $builder->where('program_studi_id', $programStudiId);
        }

        return $builder->orderBy('nama_sub_bagian', 'ASC')->findAll();
    }
}
