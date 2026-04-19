<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatDokumenModel extends Model
{
    protected $table            = 'riwayat_dokumen';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dokumen_id',
        'versi',
        'status_saat_itu',
        'keterangan',
        'nama_file',
        'path_file',
        'ekstensi_file',
        'mime_type',
        'ukuran_file',
        'diunggah_oleh',
        'waktu_upload',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'            => 'integer',
        'dokumen_id'    => 'integer',
        'versi'         => 'integer',
        'ukuran_file'   => 'integer',
        'diunggah_oleh' => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByDokumen(int $dokumenId): array
    {
        return $this->select('riwayat_dokumen.*, users.nama_lengkap as nama_pengunggah')
            ->join('users', 'users.id = riwayat_dokumen.diunggah_oleh', 'left')
            ->where('riwayat_dokumen.dokumen_id', $dokumenId)
            ->orderBy('riwayat_dokumen.versi', 'DESC')
            ->findAll();
    }
}