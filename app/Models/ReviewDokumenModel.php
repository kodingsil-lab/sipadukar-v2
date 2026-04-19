<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewDokumenModel extends Model
{
    protected $table            = 'review_dokumen';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dokumen_id',
        'reviewer_id',
        'status_review',
        'catatan_review',
        'tanggal_review',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'           => 'integer',
        'dokumen_id'   => 'integer',
        'reviewer_id'  => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByDokumen(int $dokumenId): array
    {
        return $this->select('review_dokumen.*, users.nama_lengkap as nama_reviewer')
            ->join('users', 'users.id = review_dokumen.reviewer_id', 'left')
            ->where('review_dokumen.dokumen_id', $dokumenId)
            ->orderBy('review_dokumen.id', 'DESC')
            ->findAll();
    }
}