<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfilPtModel extends Model
{
    protected $table            = 'profil_pt';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_pt',
        'nama_singkatan',
        'status_pt',
        'badan_penyelenggara',
        'kode_pt_pddikti',
        'tahun_berdiri',
        'alamat_lengkap',
        'website_resmi',
        'email_resmi_pt',
        'nomor_telepon',
        'status_akreditasi_pt',
        'nomor_sk_akreditasi',
        'tanggal_sk',
        'tanggal_berlaku_akreditasi',
        'tanggal_berakhir_akreditasi',
        'file_sk_akreditasi_path',
        'file_sertifikat_akreditasi_path',
        'lembaga_akreditasi',
        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'            => 'integer',
        'tahun_berdiri' => '?integer',
        'updated_by'    => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSingleton(): ?array
    {
        return $this->orderBy('id', 'ASC')->first();
    }
}
