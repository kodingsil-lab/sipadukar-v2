<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentReviewModel extends Model
{
    protected $table      = 'document_reviews';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'document_id',
        'reviewer_id',
        'role',
        'decision',
        'comment',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'document_id' => 'required|integer',
        'reviewer_id' => 'required|integer',
        'role'        => 'required|in_list[lpm]',
        'decision'    => 'required|in_list[validated,revision_required,rejected]',
        'comment'     => 'permit_empty|max_length[1000]',
    ];

    protected $validationMessages = [
        'role' => [
            'in_list' => 'Role reviewer tidak valid.',
        ],
        'decision' => [
            'in_list' => 'Keputusan review tidak valid.',
        ],
        'comment' => [
            'max_length' => 'Komentar maksimal 1000 karakter.',
        ],
    ];

    public function getReviewsByDocument(int $documentId): array
    {
        return $this->where('document_id', $documentId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function addReview(array $data): bool
    {
        return $this->insert($data) !== false;
    }
}