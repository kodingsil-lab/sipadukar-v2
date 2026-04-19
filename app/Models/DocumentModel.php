<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table      = 'documents';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'title',
        'file_path',
        'uploaded_by',
        'prodi_id',
        'current_status',
        'version',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'title'          => 'required|min_length[3]|max_length[255]',
        'uploaded_by'    => 'required|integer',
        'current_status' => 'required|in_list[draft,diajukan,perlu_revisi,disubmit_ulang,tervalidasi,ditolak]',
        'version'        => 'integer',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Judul dokumen wajib diisi.',
            'min_length' => 'Judul minimal 3 karakter.',
            'max_length' => 'Judul maksimal 255 karakter.',
        ],
        'current_status' => [
            'in_list' => 'Status dokumen tidak valid.',
        ],
    ];

    public function getDocumentsByUploader(int $userId): array
    {
        return $this->where('uploaded_by', $userId)->findAll();
    }

    public function getAllDocuments(): array
    {
        return $this->findAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['current_status' => $status]);
    }

    public function incrementVersion(int $id): bool
    {
        $document = $this->find($id);
        if (!$document) {
            return false;
        }

        return $this->update($id, ['version' => $document['version'] + 1]);
    }
}