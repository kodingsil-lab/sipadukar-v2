<?php

namespace App\Models;

use CodeIgniter\Model;

class SubBagianModel extends Model
{
    protected $table            = 'sub_bagian';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kriteria_id',
        'program_studi_id',
        'nama_sub_bagian',
        'slug_sub_bagian',
        'deskripsi',
        'urutan',
        'dibuat_oleh',
        'diupdate_oleh',
        'is_aktif',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'               => 'integer',
        'kriteria_id'      => 'integer',
        'program_studi_id' => '?integer',
        'urutan'           => 'integer',
        'dibuat_oleh'      => '?integer',
        'diupdate_oleh'    => '?integer',
        'is_aktif'         => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['siapkanSlug'];
    protected $beforeUpdate = ['siapkanSlug'];

    protected function siapkanSlug(array $data): array
    {
        if (! empty($data['data']['nama_sub_bagian']) && empty($data['data']['slug_sub_bagian'])) {
            $data['data']['slug_sub_bagian'] = buat_slug($data['data']['nama_sub_bagian']);
        }

        return $data;
    }

    public function getByKriteria(int $kriteriaId, ?int $programStudiId = null): array
    {
        $builder = $this->where('kriteria_id', $kriteriaId)
            ->where('is_aktif', 1);

        $programStudiId = (int) ($programStudiId ?? 0);
        if ($programStudiId > 0) {
            $builder->where('program_studi_id', $programStudiId);
        }

        return $builder
            ->orderBy('urutan', 'ASC')
            ->orderBy('nama_sub_bagian', 'ASC')
            ->findAll();
    }

    public function getWithKriteria(): array
    {
        return $this->select('sub_bagian.*, kriterias.nama_kriteria, kriterias.kode')
            ->join('kriterias', 'kriterias.id = sub_bagian.kriteria_id', 'left')
            ->where('sub_bagian.deleted_at', null)
            ->orderBy('kriterias.urutan', 'ASC')
            ->orderBy('sub_bagian.urutan', 'ASC')
            ->findAll();
    }
}
