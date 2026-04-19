<?php

namespace App\Models;

use CodeIgniter\Model;

class LembagaAkreditasiModel extends Model
{
    protected $table            = 'lembaga_akreditasi';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_lembaga_akreditasi',
        'nama_singkatan',
        'alamat_website',
        'logo_path',
        'is_aktif',
        'created_by',
        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'is_aktif'   => 'integer',
        'created_by' => '?integer',
        'updated_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
