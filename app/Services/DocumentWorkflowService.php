<?php

namespace App\Services;

use App\Models\DocumentModel;
use App\Models\DocumentReviewModel;
use Exception;

class DocumentWorkflowService
{
    protected DocumentModel $documentModel;
    protected DocumentReviewModel $reviewModel;

    public function __construct()
    {
        $this->documentModel = new DocumentModel();
        $this->reviewModel   = new DocumentReviewModel();
    }

    /**
     * Allowed status transitions
     */
    protected array $transitions = [
        'draft'          => ['diajukan'],
        'diajukan'       => ['tervalidasi', 'perlu_revisi', 'ditolak'],
        'perlu_revisi'   => ['disubmit_ulang'],
        'disubmit_ulang' => ['tervalidasi', 'perlu_revisi', 'ditolak'],
        'tervalidasi'    => [], // Final state
        'ditolak'        => [], // Final state
    ];

    /**
     * Check if transition is allowed
     */
    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions[$from] ?? []);
    }

    /**
     * Submit document (draft -> diajukan)
     */
    public function submitDocument(int $documentId, int $userId): bool
    {
        $document = $this->documentModel->find($documentId);
        if (!$document || $document['uploaded_by'] !== $userId || $document['current_status'] !== 'draft') {
            return false;
        }

        return $this->documentModel->update($documentId, ['current_status' => 'diajukan']);
    }

    /**
     * Review document by LPM
     */
    public function reviewDocument(int $documentId, int $reviewerId, string $decision, string $comment = ''): bool
    {
        $document = $this->documentModel->find($documentId);
        if (!$document || $document['current_status'] !== 'diajukan' && $document['current_status'] !== 'disubmit_ulang') {
            return false;
        }

        $statusMap = [
            'validated'         => 'tervalidasi',
            'revision_required' => 'perlu_revisi',
            'rejected'          => 'ditolak',
        ];

        if (!isset($statusMap[$decision])) {
            return false;
        }

        $newStatus = $statusMap[$decision];

        if (!$this->canTransition($document['current_status'], $newStatus)) {
            return false;
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update document status
            $this->documentModel->update($documentId, ['current_status' => $newStatus]);

            // Add review record
            $this->reviewModel->addReview([
                'document_id' => $documentId,
                'reviewer_id' => $reviewerId,
                'role'        => 'lpm',
                'decision'    => $decision,
                'comment'     => $comment,
            ]);

            $db->transComplete();

            return $db->transStatus();
        } catch (Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    /**
     * Resubmit document after revision (perlu_revisi -> disubmit_ulang)
     */
    public function resubmitDocument(int $documentId, int $userId): bool
    {
        $document = $this->documentModel->find($documentId);
        if (!$document || $document['uploaded_by'] !== $userId || $document['current_status'] !== 'perlu_revisi') {
            return false;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $this->documentModel->update($documentId, [
                'current_status' => 'disubmit_ulang',
                'version'        => $document['version'] + 1,
            ]);

            $db->transComplete();

            return $db->transStatus();
        } catch (Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    /**
     * Check if user can submit document
     */
    public function canSubmit(int $documentId, int $userId): bool
    {
        $document = $this->documentModel->find($documentId);
        return $document && $document['uploaded_by'] === $userId && $document['current_status'] === 'draft';
    }

    /**
     * Check if user can review document (LPM only)
     */
    public function canReview(int $documentId, int $userId): bool
    {
        $document = $this->documentModel->find($documentId);
        return $document && in_array($document['current_status'], ['diajukan', 'disubmit_ulang']);
    }

    /**
     * Check if user can revise document
     */
    public function canRevise(int $documentId, int $userId): bool
    {
        $document = $this->documentModel->find($documentId);
        return $document && $document['uploaded_by'] === $userId && $document['current_status'] === 'perlu_revisi';
    }

    /**
     * Check if user can edit document
     */
    public function canEdit(int $documentId, int $userId): bool
    {
        $document = $this->documentModel->find($documentId);
        return $document && $document['uploaded_by'] === $userId && in_array($document['current_status'], ['draft', 'perlu_revisi']);
    }
}