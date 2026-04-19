<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_role',
        'slug_role',
        'deskripsi',
        'is_aktif',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'       => 'integer',
        'is_aktif' => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAktif(): array
    {
        return $this->where('is_aktif', 1)
            ->where('slug_role !=', 'asesor')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug_role', $slug)->first();
    }
}