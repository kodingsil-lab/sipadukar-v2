<?php

namespace App\Models;

use CodeIgniter\Model;

class UppsModel extends Model
{
    protected $table            = 'upps';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_upps',
        'nama_singkatan',
        'jenis_unit',
        'nama_pimpinan_upps',
        'nutpk',
        'email_resmi_upps',
        'nomor_telepon',
        'created_by',
        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'created_by' => '?integer',
        'updated_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
