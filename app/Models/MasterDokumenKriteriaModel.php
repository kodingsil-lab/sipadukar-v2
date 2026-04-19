<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterDokumenKriteriaModel extends Model
{
    protected $table            = 'master_dokumen_kriteria';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kriteria_id',
        'sub_bagian_id',
        'nama_sub_bagian',
        'judul_dokumen',
        'deskripsi',
        'jenis_dokumen',
        'tahun_dokumen',
        'is_aktif',
        'created_by',
        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'kriteria_id' => 'integer',
        'sub_bagian_id' => '?integer',
        'tahun_dokumen' => '?integer',
        'is_aktif' => 'integer',
        'created_by' => '?integer',
        'updated_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getWithRelasi(): array
    {
        return $this->select(
                "master_dokumen_kriteria.*, kriterias.kode, kriterias.nama_kriteria, COALESCE(NULLIF(master_dokumen_kriteria.nama_sub_bagian, ''), sub_bagian.nama_sub_bagian) AS nama_sub_bagian_tampil",
                false
            )
            ->join('kriterias', 'kriterias.id = master_dokumen_kriteria.kriteria_id', 'left')
            ->join('sub_bagian', 'sub_bagian.id = master_dokumen_kriteria.sub_bagian_id', 'left')
            ->where('master_dokumen_kriteria.deleted_at', null)
            ->orderBy('kriterias.urutan', 'ASC')
            ->orderBy('master_dokumen_kriteria.id', 'ASC')
            ->findAll();
    }

    public function getWithRelasiPaginated(int $perPage = 25): array
    {
        $this->select(
                "master_dokumen_kriteria.*, kriterias.kode, kriterias.nama_kriteria, COALESCE(NULLIF(master_dokumen_kriteria.nama_sub_bagian, ''), sub_bagian.nama_sub_bagian) AS nama_sub_bagian_tampil",
                false
            )
            ->join('kriterias', 'kriterias.id = master_dokumen_kriteria.kriteria_id', 'left')
            ->join('sub_bagian', 'sub_bagian.id = master_dokumen_kriteria.sub_bagian_id', 'left')
            ->where('master_dokumen_kriteria.deleted_at', null)
            ->orderBy('kriterias.urutan', 'ASC')
            ->orderBy('master_dokumen_kriteria.id', 'ASC');

        return $this->paginate($perPage);
    }

    public function getAktifByFilter(int $kriteriaId = 0, string $namaSubBagian = ''): array
    {
        $builder = $this->where('is_aktif', 1);

        if ($kriteriaId > 0) {
            $builder->where('kriteria_id', $kriteriaId);
        }

        $namaSubBagian = trim($namaSubBagian);
        if ($namaSubBagian !== '') {
            $builder->groupStart()
                ->like('nama_sub_bagian', $namaSubBagian)
                ->orLike('judul_dokumen', $namaSubBagian)
                ->groupEnd();
        }

        return $builder
            ->orderBy('kriteria_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
