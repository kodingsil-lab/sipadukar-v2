<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=UTF-8">
    <title>Laporan Dokumen Akreditasi</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 16px;
        }

        .sheet-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .sheet-subtitle {
            font-size: 12px;
            color: #475569;
            margin: 0 0 14px 0;
        }

        .meta-grid {
            margin-bottom: 14px;
        }

        .meta-grid table {
            width: auto;
            margin: 0;
        }

        .meta-grid td {
            border: none;
            padding: 2px 8px 2px 0;
            font-size: 12px;
        }

        .section-title {
            margin: 14px 0 6px 0;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 14px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
        }

        .num {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .detail-table {
            font-size: 11px;
        }

        .detail-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .small-note {
            font-size: 11px;
            color: #64748b;
        }

        .w-no { width: 36px; }
        .w-judul { width: 220px; }
        .w-kode { width: 90px; }
        .w-nomor { width: 120px; }
        .w-kriteria { width: 190px; }
        .w-sub { width: 150px; }
        .w-nama { width: 140px; }
        .w-jenis { width: 100px; }
        .w-tahun { width: 54px; }
        .w-status { width: 90px; }
        .w-versi { width: 44px; }
        .w-file { width: 130px; }
        .w-size { width: 80px; }
        .w-tgl { width: 116px; }
        .w-catatan { width: 220px; }
    </style>
</head>
<body>
    <div class="sheet-title">Laporan Dokumen Akreditasi</div>
    <div class="sheet-subtitle">Rekap status, rekap kriteria, dan detail dokumen sesuai filter laporan.</div>

    <div class="meta-grid">
        <table>
            <tr>
                <td><strong>Tanggal Export</strong></td>
                <td>: <?= date('d-m-Y H:i:s'); ?></td>
            </tr>
            <tr>
                <td><strong>Total Data</strong></td>
                <td>: <?= count($laporanList); ?></td>
            </tr>
        </table>
    </div>

    <div class="section-title">Rekap Per Status</div>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th width="90">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rekapStatus)): ?>
                <?php foreach ($rekapStatus as $row): ?>
                    <tr>
                        <td><?= esc(label_status_dokumen($row['status_dokumen'])); ?></td>
                        <td class="num"><?= esc($row['total']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="center">Belum ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Rekap Per Kriteria</div>
    <table>
        <thead>
            <tr>
                <th width="80">Kode</th>
                <th>Kriteria</th>
                <th width="70">Total</th>
                <th width="70">Valid</th>
                <th width="70">Revisi</th>
                <th width="70">Draft</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rekapKriteria)): ?>
                <?php foreach ($rekapKriteria as $row): ?>
                    <tr>
                        <td class="center nowrap"><?= esc($row['kode']); ?></td>
                        <td><?= esc($row['nama_kriteria']); ?></td>
                        <td class="num"><?= esc($row['total_dokumen'] ?? 0); ?></td>
                        <td class="num"><?= esc($row['tervalidasi'] ?? 0); ?></td>
                        <td class="num"><?= esc($row['perlu_revisi'] ?? 0); ?></td>
                        <td class="num"><?= esc($row['draft'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="center">Belum ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Detail Dokumen</div>
    <div class="small-note">Tip: gunakan filter/sort Excel untuk menelusuri dokumen dengan cepat.</div>
    <table class="detail-table">
        <colgroup>
            <col class="w-no">
            <col class="w-judul">
            <col class="w-kode">
            <col class="w-nomor">
            <col class="w-kriteria">
            <col class="w-sub">
            <col class="w-nama">
            <col class="w-nama">
            <col class="w-jenis">
            <col class="w-tahun">
            <col class="w-status">
            <col class="w-versi">
            <col class="w-file">
            <col class="w-size">
            <col class="w-tgl">
            <col class="w-tgl">
            <col class="w-tgl">
            <col class="w-catatan">
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Dokumen</th>
                <th>Kode</th>
                <th>Nomor</th>
                <th>Kriteria</th>
                <th>Sub Bagian</th>
                <th>Pengunggah</th>
                <th>Reviewer</th>
                <th>Jenis</th>
                <th>Tahun</th>
                <th>Status</th>
                <th>Versi</th>
                <th>Nama File</th>
                <th>Ukuran</th>
                <th>Tanggal Upload</th>
                <th>Tanggal Submit</th>
                <th>Tanggal Validasi</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($laporanList)): ?>
                <?php foreach ($laporanList as $i => $row): ?>
                    <tr>
                        <td class="center"><?= $i + 1; ?></td>
                        <td><?= esc($row['judul_dokumen']); ?></td>
                        <td class="nowrap"><?= esc($row['kode_dokumen'] ?: '-'); ?></td>
                        <td><?= esc($row['nomor_dokumen'] ?: '-'); ?></td>
                        <td><?= esc(($row['kode_kriteria'] ?? '-') . ' - ' . ($row['nama_kriteria'] ?? '-')); ?></td>
                        <td><?= esc($row['nama_sub_bagian'] ?? '-'); ?></td>
                        <td><?= esc($row['nama_pengunggah'] ?? '-'); ?></td>
                        <td><?= esc($row['nama_reviewer'] ?? '-'); ?></td>
                        <td><?= esc($row['jenis_dokumen'] ?: '-'); ?></td>
                        <td class="center"><?= esc($row['tahun_dokumen'] ?: '-'); ?></td>
                        <td class="center nowrap"><?= esc(label_status_dokumen($row['status_dokumen'])); ?></td>
                        <td class="center"><?= esc('v' . $row['versi']); ?></td>
                        <td><?= esc($row['nama_file'] ?: '-'); ?></td>
                        <td class="nowrap"><?= esc(format_ukuran_file($row['ukuran_file'] ?? null)); ?></td>
                        <td class="nowrap"><?= esc($row['tanggal_upload'] ?: '-'); ?></td>
                        <td class="nowrap"><?= esc($row['tanggal_submit'] ?: '-'); ?></td>
                        <td class="nowrap"><?= esc($row['tanggal_validasi'] ?: '-'); ?></td>
                        <td><?= esc($row['catatan_terakhir'] ?: '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="18" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
