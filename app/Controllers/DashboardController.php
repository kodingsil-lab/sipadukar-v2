<?php

namespace App\Controllers;

use App\Models\DokumenModel;
use App\Models\KriteriaModel;
use App\Models\LembagaAkreditasiModel;
use App\Models\ProfilPtModel;
use App\Models\ProgramStudiModel;
use App\Models\SubBagianModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $dokumenModel   = new DokumenModel();
        $kriteriaModel  = new KriteriaModel();
        $subBagianModel = new SubBagianModel();
        $profilPtModel  = new ProfilPtModel();
        $lembagaAkreditasiModel = new LembagaAkreditasiModel();
        $programStudiModel = new ProgramStudiModel();

        $jumlahProdiAktifAkreditasi = $programStudiModel->where('is_aktif_akreditasi', 1)->countAllResults();
        $pakaiFilterProdiAktif = $jumlahProdiAktifAkreditasi > 0;

        $roleUser = user_roles();
        $isAdmin = in_array('admin', $roleUser, true);
        $isLpm = in_array('lpm', $roleUser, true);
        $isDekan = in_array('dekan', $roleUser, true);
        $isKaprodi = in_array('kaprodi', $roleUser, true);
        $isDosen = in_array('dosen', $roleUser, true);
        $isAdminOrLpm = $isAdmin || $isLpm;

        $prodiAktifBuilder = $programStudiModel
            ->select('program_studi.*, upps.nama_upps, upps.nama_singkatan AS nama_singkatan_upps')
            ->join('upps', 'upps.id = program_studi.upps_id', 'left')
            ->where('program_studi.is_aktif_akreditasi', 1)
            ->orderBy('program_studi.nama_program_studi', 'ASC');

        if (! $isAdminOrLpm && $isDekan) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (! empty($accessibleProgramStudiIds)) {
                $prodiAktifBuilder->whereIn('program_studi.id', $accessibleProgramStudiIds);
            } else {
                $userUppsId = (int) (session()->get('upps_id') ?? 0);
                if ($userUppsId > 0) {
                    $prodiAktifBuilder->where('program_studi.upps_id', $userUppsId);
                } else {
                    $prodiAktifBuilder->where('program_studi.id', 0);
                }
            }
        } elseif (! $isAdminOrLpm && $isKaprodi) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (! empty($accessibleProgramStudiIds)) {
                $prodiAktifBuilder->whereIn('program_studi.id', $accessibleProgramStudiIds);
            } else {
                $prodiAktifBuilder->where('program_studi.id', 0);
            }
        } elseif (! $isAdminOrLpm && $isDosen) {
            $accessibleProgramStudiIds = user_accessible_program_studi_ids();
            if (! empty($accessibleProgramStudiIds)) {
                $prodiAktifBuilder->whereIn('program_studi.id', $accessibleProgramStudiIds);
            } else {
                $prodiAktifBuilder->where('program_studi.id', 0);
            }
        }

        $prodiAktifAkreditasi = $prodiAktifBuilder->findAll();

        $singkatanLembagaProdi = [];
        foreach ($prodiAktifAkreditasi as $prodiRow) {
            $singkatan = trim((string) ($prodiRow['lembaga_akreditasi'] ?? ''));
            if ($singkatan !== '') {
                $singkatanLembagaProdi[$singkatan] = true;
            }
        }

        $logoBySingkatan = [];
        if (! empty($singkatanLembagaProdi)) {
            $listLembaga = $lembagaAkreditasiModel
                ->select('nama_singkatan, nama_lembaga_akreditasi, logo_path')
                ->whereIn('nama_singkatan', array_keys($singkatanLembagaProdi))
                ->findAll();

            foreach ($listLembaga as $lembaga) {
                $singkatan = trim((string) ($lembaga['nama_singkatan'] ?? ''));
                if ($singkatan !== '' && ! array_key_exists($singkatan, $logoBySingkatan)) {
                    $logoBySingkatan[$singkatan] = trim((string) ($lembaga['logo_path'] ?? ''));
                }
            }
        }

        $namaLembagaBySingkatan = [];
        if (! empty($singkatanLembagaProdi)) {
            $listLembagaNama = $lembagaAkreditasiModel
                ->select('nama_singkatan, nama_lembaga_akreditasi')
                ->whereIn('nama_singkatan', array_keys($singkatanLembagaProdi))
                ->findAll();

            foreach ($listLembagaNama as $lembagaNama) {
                $singkatan = trim((string) ($lembagaNama['nama_singkatan'] ?? ''));
                if ($singkatan !== '' && ! array_key_exists($singkatan, $namaLembagaBySingkatan)) {
                    $namaLembagaBySingkatan[$singkatan] = trim((string) ($lembagaNama['nama_lembaga_akreditasi'] ?? ''));
                }
            }
        }

        foreach ($prodiAktifAkreditasi as $idx => $prodiRow) {
            $singkatan = trim((string) ($prodiRow['lembaga_akreditasi'] ?? ''));
            $prodiAktifAkreditasi[$idx]['logo_lembaga_path'] = $logoBySingkatan[$singkatan] ?? '';
            $prodiAktifAkreditasi[$idx]['nama_lembaga_akreditasi'] = $namaLembagaBySingkatan[$singkatan] ?? ($prodiRow['lembaga_akreditasi'] ?? '');
        }

        $prodiAktifIds = array_map(static fn ($row) => (int) ($row['id'] ?? 0), $prodiAktifAkreditasi);
        $prodiAktifIds = array_values(array_filter($prodiAktifIds, static fn ($id) => $id > 0));

        $selectedProdiProgressId = 0;
        if ($isAdmin) {
            $requestedProdiProgressId = (int) ($this->request->getGet('prodi_progress_id') ?? 0);
            if ($requestedProdiProgressId > 0 && in_array($requestedProdiProgressId, $prodiAktifIds, true)) {
                $selectedProdiProgressId = $requestedProdiProgressId;
            }
        }

        $statStatus         = $dokumenModel->hitungPerStatus($pakaiFilterProdiAktif);
        $totalDokumen       = $dokumenModel->hitungTotalDokumen($pakaiFilterProdiAktif);
        $kriteriaList       = $kriteriaModel->getAktif();

        // Hitung sub_bagian sesuai scope prodi yang bisa diakses user
        $sbQuery = $subBagianModel->where('deleted_at', null);
        if ($pakaiFilterProdiAktif && ! empty($prodiAktifIds)) {
            $sbQuery->whereIn('program_studi_id', $prodiAktifIds);
        }
        $totalSubBagian = $sbQuery->countAllResults();
        $dokumenTerbaru     = $dokumenModel->getTerbaru(8, $pakaiFilterProdiAktif);
        $dokumenPerluRevisi = $dokumenModel->getByStatus('perlu_revisi', 8, $pakaiFilterProdiAktif);
        $dokumenTervalidasi = $dokumenModel->getByStatus('tervalidasi', 8, $pakaiFilterProdiAktif);
        $ringkasanKriteria  = $dokumenModel->getRingkasanPerKriteria(
            $pakaiFilterProdiAktif,
            $isAdmin && $selectedProdiProgressId > 0 ? $selectedProdiProgressId : null
        );
        $progressPersen     = $totalDokumen > 0
            ? (int) round((($statStatus['tervalidasi'] ?? 0) / $totalDokumen) * 100)
            : 0;
        $profilData = $profilPtModel->getSingleton();
        $hitungPersen = static function (int $numerator, int $denominator): int {
            if ($denominator <= 0) {
                return 0;
            }

            $value = (int) round(($numerator / $denominator) * 100);
            if ($value < 0) {
                return 0;
            }

            if ($value > 100) {
                return 100;
            }

            return $value;
        };

        $summaryCards = [
            [
                'label' => 'Total Dokumen',
                'count' => (int) $totalDokumen,
                'note'  => 'Seluruh dokumen aktif di sistem',
                'tone'  => 'primary',
                'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
            ],
            [
                'label' => 'Sub Bagian',
                'count' => (int) $totalSubBagian,
                'note'  => 'Total struktur sub bagian aktif',
                'tone'  => 'primary',
                'percent' => 100,
            ],
            [
                'label' => 'Perlu Revisi',
                'count' => (int) ($statStatus['perlu_revisi'] ?? 0),
                'note'  => 'Dokumen yang butuh tindak lanjut',
                'tone'  => 'warning',
                'percent' => $hitungPersen((int) ($statStatus['perlu_revisi'] ?? 0), (int) $totalDokumen),
            ],
            [
                'label' => 'Tervalidasi',
                'count' => (int) ($statStatus['tervalidasi'] ?? 0),
                'note'  => 'Dokumen final yang sudah lolos',
                'tone'  => 'success',
                'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
            ],
        ];

        if ($isAdmin) {
            $userModel = new UserModel();
            $totalUser = (int) $userModel->countAllResults();
            $userAktif = (int) $userModel->where('is_aktif', 1)->countAllResults();
            $totalProgramStudi = (int) (new ProgramStudiModel())->countAllResults();

            $summaryCards = [
                [
                    'label' => 'User Aktif',
                    'count' => $userAktif,
                    'note'  => 'Pengguna aktif yang dapat mengakses sistem',
                    'tone'  => 'primary',
                    'percent' => $hitungPersen($userAktif, $totalUser),
                ],
                [
                    'label' => 'Program Studi',
                    'count' => $totalProgramStudi,
                    'note'  => 'Total data program studi terdaftar',
                    'tone'  => 'primary',
                    'percent' => 100,
                ],
                [
                    'label' => 'Prodi Persiapan',
                    'count' => (int) $jumlahProdiAktifAkreditasi,
                    'note'  => 'Program studi aktif persiapan akreditasi',
                    'tone'  => 'warning',
                    'percent' => $hitungPersen((int) $jumlahProdiAktifAkreditasi, $totalProgramStudi),
                ],
                [
                    'label' => 'Total Dokumen',
                    'count' => (int) $totalDokumen,
                    'note'  => 'Volume dokumen aktif di sistem',
                    'tone'  => 'success',
                    'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
                ],
            ];
        } elseif ($isLpm) {
            $summaryCards = [
                [
                    'label' => 'Antrian Finalisasi',
                    'count' => (int) (($statStatus['diajukan'] ?? 0) + ($statStatus['ditinjau'] ?? 0) + ($statStatus['disubmit_ulang'] ?? 0)),
                    'note'  => 'Klik untuk memvalidasi dokumen siap final',
                    'tone'  => 'primary',
                    'percent' => $hitungPersen(
                        (int) (($statStatus['diajukan'] ?? 0) + ($statStatus['ditinjau'] ?? 0) + ($statStatus['disubmit_ulang'] ?? 0)),
                        (int) $totalDokumen
                    ),
                    'url'   => base_url('/laporan?mode=antrian_final'),
                ],
                [
                    'label' => 'Perlu Revisi',
                    'count' => (int) ($statStatus['perlu_revisi'] ?? 0),
                    'note'  => 'Klik untuk monitoring perbaikan dokumen',
                    'tone'  => 'warning',
                    'percent' => $hitungPersen((int) ($statStatus['perlu_revisi'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan?status_dokumen=perlu_revisi'),
                ],
                [
                    'label' => 'Ditolak',
                    'count' => (int) ($statStatus['ditolak'] ?? 0),
                    'note'  => 'Klik untuk evaluasi dokumen yang ditolak',
                    'tone'  => 'danger',
                    'percent' => $hitungPersen((int) ($statStatus['ditolak'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan?status_dokumen=ditolak'),
                ],
                [
                    'label' => 'Tervalidasi',
                    'count' => (int) ($statStatus['tervalidasi'] ?? 0),
                    'note'  => 'Klik untuk melihat dokumen final',
                    'tone'  => 'success',
                    'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan?status_dokumen=tervalidasi'),
                ],
            ];
        } elseif ($isKaprodi) {
            $summaryCards = [
                [
                    'label' => 'Total Dokumen Prodi',
                    'count' => (int) $totalDokumen,
                    'note'  => 'Seluruh dokumen dalam program studi',
                    'tone'  => 'primary',
                    'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan'),
                ],
                [
                    'label' => 'Draft',
                    'count' => (int) ($statStatus['draft'] ?? 0),
                    'note'  => 'Klik untuk monitor dokumen dalam pengerjaan',
                    'tone'  => 'info',
                    'percent' => $hitungPersen((int) ($statStatus['draft'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan?status_dokumen=draft'),
                ],
                [
                    'label' => 'Perlu Revisi',
                    'count' => (int) ($statStatus['perlu_revisi'] ?? 0),
                    'note'  => 'Klik untuk tindak lanjut perbaikan dokumen',
                    'tone'  => 'warning',
                    'percent' => $hitungPersen((int) ($statStatus['perlu_revisi'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan?status_dokumen=perlu_revisi'),
                ],
                [
                    'label' => 'Tervalidasi',
                    'count' => (int) ($statStatus['tervalidasi'] ?? 0),
                    'note'  => 'Klik untuk melihat dokumen yang sudah final',
                    'tone'  => 'success',
                    'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
                    'url'   => base_url('/laporan?status_dokumen=tervalidasi'),
                ],
            ];
        } elseif ($isDosen) {
            $summaryCards = [
                [
                    'label' => 'Total Tugas Dokumen',
                    'count' => (int) $totalDokumen,
                    'note'  => 'Dokumen yang ditugaskan kepada Anda',
                    'tone'  => 'primary',
                    'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
                ],
                [
                    'label' => 'Draft',
                    'count' => (int) ($statStatus['draft'] ?? 0),
                    'note'  => 'Dokumen dalam pengerjaan Anda',
                    'tone'  => 'info',
                    'percent' => $hitungPersen((int) ($statStatus['draft'] ?? 0), (int) $totalDokumen),
                ],
                [
                    'label' => 'Perlu Revisi',
                    'count' => (int) ($statStatus['perlu_revisi'] ?? 0),
                    'note'  => 'Dokumen yang memerlukan perbaikan',
                    'tone'  => 'warning',
                    'percent' => $hitungPersen((int) ($statStatus['perlu_revisi'] ?? 0), (int) $totalDokumen),
                ],
                [
                    'label' => 'Tervalidasi',
                    'count' => (int) ($statStatus['tervalidasi'] ?? 0),
                    'note'  => 'Dokumen yang telah final',
                    'tone'  => 'success',
                    'percent' => $hitungPersen((int) ($statStatus['tervalidasi'] ?? 0), (int) $totalDokumen),
                ],
            ];
        }

        $antrianKerja = [];
        $judulAntrian = 'Antrian Kerja';
        $subtitleAntrian = 'Akses cepat ke daftar dokumen yang perlu ditindaklanjuti.';

        if ($isLpm) {
            $judulAntrian = 'Antrian Validasi LPM';
            $subtitleAntrian = 'Fokus ke dokumen yang menunggu finalisasi dan evaluasi mutu.';
            $antrianKerja = [
                [
                    'label' => 'Menunggu Finalisasi',
                    'count' => (int) (($statStatus['diajukan'] ?? 0) + ($statStatus['ditinjau'] ?? 0) + ($statStatus['disubmit_ulang'] ?? 0)),
                    'note'  => 'Status: diajukan, ditinjau, disubmit ulang',
                    'url'   => base_url('/laporan?mode=antrian_final'),
                    'tone'  => 'primary',
                ],
                [
                    'label' => 'Perlu Revisi',
                    'count' => (int) ($statStatus['perlu_revisi'] ?? 0),
                    'note'  => 'Dokumen perlu perbaikan',
                    'url'   => base_url('/laporan?status_dokumen=perlu_revisi'),
                    'tone'  => 'warning',
                ],
                [
                    'label' => 'Ditolak',
                    'count' => (int) ($statStatus['ditolak'] ?? 0),
                    'note'  => 'Dokumen ditolak final',
                    'url'   => base_url('/laporan?status_dokumen=ditolak'),
                    'tone'  => 'danger',
                ],
                [
                    'label' => 'Tervalidasi',
                    'count' => (int) ($statStatus['tervalidasi'] ?? 0),
                    'note'  => 'Dokumen sudah final',
                    'url'   => base_url('/laporan?status_dokumen=tervalidasi'),
                    'tone'  => 'success',
                ],
            ];
        } elseif ($isAdmin) {
            // Admin tidak memiliki wewenang validasi dokumen, jadi blok antrian validasi disembunyikan.
            $antrianKerja = [];
        } elseif ($isDekan) {
            $userUppsId = (int) (session()->get('upps_id') ?? 0);
            $totalProdiFakultas = 0;
            $progresFakultas = 0;
            $dokumenTervalidasiFakultas = 0;
            $prodiSiapAkreditasi = 0;

            if ($userUppsId > 0) {
                // Total Program Studi di Fakultas
                $totalProdiFakultas = (int) $programStudiModel->where('upps_id', $userUppsId)->countAllResults();

                // Prodi aktif akreditasi di fakultas
                $prodiAktifFakultas = $programStudiModel
                    ->select('id, nama_program_studi')
                    ->where('upps_id', $userUppsId)
                    ->where('is_aktif_akreditasi', 1)
                    ->findAll();

                $totalProgres = 0;
                $prodiCount = count($prodiAktifFakultas);
                $prodiSiapCount = 0;

                foreach ($prodiAktifFakultas as $prodi) {
                    $prodiId = (int) $prodi['id'];

                    // Hitung progres per prodi
                    $totalDokumenProdi = $dokumenModel->where('program_studi_id', $prodiId)->countAllResults();
                    $dokumenTervalidasiProdi = $dokumenModel
                        ->where('program_studi_id', $prodiId)
                        ->where('status_dokumen', 'tervalidasi')
                        ->countAllResults();

                    if ($totalDokumenProdi > 0) {
                        $progresProdi = ($dokumenTervalidasiProdi / $totalDokumenProdi) * 100;
                        $totalProgres += $progresProdi;

                        if ($dokumenTervalidasiProdi === $totalDokumenProdi) {
                            $prodiSiapCount++;
                        }
                    }

                    $dokumenTervalidasiFakultas += $dokumenTervalidasiProdi;
                }

                if ($prodiCount > 0) {
                    $progresFakultas = round($totalProgres / $prodiCount);
                }
                $prodiSiapAkreditasi = $prodiSiapCount;
            }

            $summaryCards = [
                [
                    'label' => 'Total Program Studi',
                    'count' => $totalProdiFakultas,
                    'note'  => 'Program studi di fakultas Anda',
                    'tone'  => 'primary',
                    'percent' => 100,
                ],
                [
                    'label' => 'Progres Akreditasi',
                    'count' => $progresFakultas,
                    'note'  => 'Rata-rata progres prodi aktif',
                    'tone'  => 'primary',
                    'percent' => $progresFakultas,
                ],
                [
                    'label' => 'Dokumen Tervalidasi',
                    'count' => $dokumenTervalidasiFakultas,
                    'note'  => 'Total dokumen final di fakultas',
                    'tone'  => 'success',
                    'percent' => $hitungPersen($dokumenTervalidasiFakultas, $totalDokumen),
                ],
                [
                    'label' => 'Prodi Siap Akreditasi',
                    'count' => $prodiSiapAkreditasi,
                    'note'  => 'Prodi dengan semua dokumen tervalidasi',
                    'tone'  => 'success',
                    'percent' => $hitungPersen($prodiSiapAkreditasi, $totalProdiFakultas),
                ],
            ];
        } else {
            if ($isDosen) {
                $judulAntrian = 'Antrian Kerja Dosen';
                $subtitleAntrian = 'Prioritas dokumen operasional di Program Studi Anda.';
            } elseif ($isKaprodi) {
                $judulAntrian = 'Antrian Kerja Kaprodi';
                $subtitleAntrian = 'Monitor dan koordinasi penyusunan dokumen program studi Anda.';
            } elseif ($isDekan) {
                $judulAntrian = 'Antrian Kerja Dekan';
                $subtitleAntrian = 'Monitoring dan tindak lanjut dokumen pada UPPS Anda.';
            }

            $antrianKerja = [
                [
                    'label' => 'Perlu Tindakan',
                    'count' => (int) (($statStatus['draft'] ?? 0) + ($statStatus['perlu_revisi'] ?? 0) + ($statStatus['ditolak'] ?? 0)),
                    'note'  => 'Status: draft, perlu revisi, ditolak',
                    'url'   => base_url('/laporan?mode=perlu_tindakan'),
                    'tone'  => 'warning',
                ],
                [
                    'label' => 'Tervalidasi',
                    'count' => (int) ($statStatus['tervalidasi'] ?? 0),
                    'note'  => 'Dokumen selesai divalidasi',
                    'url'   => base_url('/laporan?status_dokumen=tervalidasi'),
                    'tone'  => 'success',
                ],
            ];
        }

        $profilInstitusi    = [
            'nama_pt'         => $profilData['nama_pt'] ?? env('app.namaPT', 'Universitas San Pedro'),
            'nama_upps'       => $profilData['nama_singkatan'] ?? env('app.namaUPPS', 'UPPS FKIP'),
            'nama_prodi'      => env('app.namaProdi', 'Pendidikan Guru Sekolah Dasar'),
            'badan_penyelenggara' => $profilData['badan_penyelenggara'] ?? '-',
            'kode_pt_pddikti' => $profilData['kode_pt_pddikti'] ?? '-',
            'lembaga_akreditasi' => $profilData['lembaga_akreditasi'] ?? '-',
            'nomor_sk'        => $profilData['nomor_sk_akreditasi'] ?? env('app.nomorSKAkreditasi', '-'),
            'mulai_berlaku'   => $profilData['tanggal_berlaku_akreditasi'] ?? env('app.akreditasiMulai', '-'),
            'berlaku_sampai'  => $profilData['tanggal_berakhir_akreditasi'] ?? env('app.akreditasiSampai', '-'),
            'peringkat'       => $profilData['status_akreditasi_pt'] ?? env('app.peringkatAkreditasi', 'Baik'),
            'tahun_akreditasi'=> ! empty($profilData['tanggal_sk']) ? date('Y', strtotime((string) $profilData['tanggal_sk'])) : env('app.tahunAkreditasi', date('Y')),
            'logo_lembaga_path' => '',
        ];

        $singkatanLembaga = trim((string) ($profilInstitusi['lembaga_akreditasi'] ?? ''));
        if ($singkatanLembaga !== '' && $singkatanLembaga !== '-') {
            $lembagaTerpilih = $lembagaAkreditasiModel
                ->select('logo_path')
                ->where('nama_singkatan', $singkatanLembaga)
                ->first();

            $profilInstitusi['logo_lembaga_path'] = trim((string) ($lembagaTerpilih['logo_path'] ?? ''));
        }

        return view('dashboard/index', [
            'title'              => 'Dashboard',
            'statStatus'         => $statStatus,
            'totalDokumen'       => $totalDokumen,
            'totalSubBagian'     => $totalSubBagian,
            'kriteriaList'       => $kriteriaList,
            'dokumenTerbaru'     => $dokumenTerbaru,
            'dokumenPerluRevisi' => $dokumenPerluRevisi,
            'dokumenTervalidasi' => $dokumenTervalidasi,
            'ringkasanKriteria'  => $ringkasanKriteria,
            'progressPersen'     => $progressPersen,
            'profilInstitusi'    => $profilInstitusi,
            'prodiAktifAkreditasi' => $prodiAktifAkreditasi,
            'judulAntrian'       => $judulAntrian,
            'subtitleAntrian'    => $subtitleAntrian,
            'antrianKerja'       => $antrianKerja,
            'pakaiFilterProdiAktif' => $pakaiFilterProdiAktif,
            'jumlahProdiAktifAkreditasi' => $jumlahProdiAktifAkreditasi,
            'summaryCards'       => $summaryCards,
            'selectedProdiProgressId' => $selectedProdiProgressId,
        ]);
    }
}
