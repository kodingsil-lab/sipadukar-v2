<?php

namespace App\Models;

use CodeIgniter\Model;

class InstrumenModel extends Model
{
    protected $table            = 'instrumen';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'judul',
        'slug',
        'kategori',
        'deskripsi',
        'versi_instrumen',
        'nama_file',
        'path_file',
        'ekstensi_file',
        'ukuran_file',
        'tanggal_berlaku',
        'is_aktif',
        'dibuat_oleh',
        'diupdate_oleh',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'            => 'integer',
        'ukuran_file'   => '?integer',
        'dibuat_oleh'   => '?integer',
        'diupdate_oleh' => '?integer',
        'is_aktif'      => 'integer',
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
        if (! empty($data['data']['judul']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = buat_slug($data['data']['judul']);
        }

        return $data;
    }

    public function getAktif(): array
    {
        return $this->where('is_aktif', 1)
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}
