<?php

namespace App\Models;

use CodeIgniter\Model;

class KriteriaModel extends Model
{
    protected $table            = 'kriterias';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nomor_kriteria',
        'kode',
        'nama_kriteria',
        'deskripsi',
        'urutan',
        'is_aktif',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'             => 'integer',
        'nomor_kriteria' => 'integer',
        'urutan'         => 'integer',
        'is_aktif'       => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAktif(): array
    {
        return $this->where('is_aktif', 1)
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }

    public function getByKode(string $kode): ?array
    {
        return $this->where('kode', $kode)->first();
    }
}