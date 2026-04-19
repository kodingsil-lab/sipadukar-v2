<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export PDF Laporan Dokumen Akreditasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            font-size: 12px;
            margin: 24px;
        }

        h2, h3 {
            margin: 0 0 10px 0;
        }

        .meta {
            margin-bottom: 18px;
        }

        .meta div {
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        th, td {
            border: 1px solid #444;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #efefef;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .no-print {
            margin-bottom: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <h2>Laporan Dokumen Akreditasi (PDF)</h2>
    <div class="meta">
        <div><strong>Tanggal Cetak:</strong> <?= date('d-m-Y H:i:s'); ?></div>
        <div><strong>Total Data:</strong> <?= count($laporanList); ?></div>
    </div>

    <h3>Rekap Per Status</h3>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rekapStatus)): ?>
                <?php foreach ($rekapStatus as $row): ?>
                    <tr>
                        <td><?= esc(label_status_dokumen($row['status_dokumen'])); ?></td>
                        <td class="text-center"><?= esc($row['total']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center">Belum ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>Rekap Per Kriteria</h3>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Kriteria</th>
                <th>Total</th>
                <th>Valid</th>
                <th>Revisi</th>
                <th>Draft</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($rekapKriteria)): ?>
                <?php foreach ($rekapKriteria as $row): ?>
                    <tr>
                        <td class="text-center"><?= esc($row['kode']); ?></td>
                        <td><?= esc($row['nama_kriteria']); ?></td>
                        <td class="text-center"><?= esc($row['total_dokumen'] ?? 0); ?></td>
                        <td class="text-center"><?= esc($row['tervalidasi'] ?? 0); ?></td>
                        <td class="text-center"><?= esc($row['perlu_revisi'] ?? 0); ?></td>
                        <td class="text-center"><?= esc($row['draft'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>Detail Dokumen</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Dokumen</th>
                <th>Kriteria</th>
                <th>Sub Bagian</th>
                <th>Pengunggah</th>
                <th>Tahun</th>
                <th>Status</th>
                <th>Versi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($laporanList)): ?>
                <?php foreach ($laporanList as $i => $row): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1; ?></td>
                        <td>
                            <?= esc($row['judul_dokumen']); ?><br>
                            <small><?= esc($row['kode_dokumen'] ?: '-'); ?> / <?= esc($row['nomor_dokumen'] ?: '-'); ?></small>
                        </td>
                        <td><?= esc($row['kode_kriteria'] ?? '-'); ?></td>
                        <td><?= esc($row['nama_sub_bagian'] ?? '-'); ?></td>
                        <td><?= esc($row['nama_pengunggah'] ?? '-'); ?></td>
                        <td class="text-center"><?= esc($row['tahun_dokumen'] ?: '-'); ?></td>
                        <td><?= esc(label_status_dokumen($row['status_dokumen'])); ?></td>
                        <td class="text-center"><?= esc('v' . $row['versi']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data yang sesuai filter.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
