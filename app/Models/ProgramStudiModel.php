<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramStudiModel extends Model
{
    protected $table            = 'program_studi';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'upps_id',
        'nama_program_studi',
        'nama_singkatan',
        'kode_program_studi_pddikti',
        'jenjang',
        'website_resmi',
        'email_resmi_program_studi',
        'nomor_telepon',
        'nama_ketua_program_studi',
        'nuptk',
        'status_akreditasi',
        'nomor_sk_akreditasi',
        'tanggal_sk',
        'tanggal_mulai_berlaku',
        'tanggal_berakhir',
        'lembaga_akreditasi',
        'is_aktif_akreditasi',
        'created_by',
        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'upps_id'    => 'integer',
        'is_aktif_akreditasi' => 'boolean',
        'created_by' => '?integer',
        'updated_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getListWithUpps(): array
    {
        return $this->select('program_studi.*, upps.nama_upps, upps.nama_singkatan AS nama_singkatan_upps')
            ->join('upps', 'upps.id = program_studi.upps_id', 'left')
            ->orderBy('program_studi.nama_program_studi', 'ASC')
            ->findAll();
    }
}
