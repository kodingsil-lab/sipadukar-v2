<?php

namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\DocumentReviewModel;
use App\Services\DocumentWorkflowService;
use CodeIgniter\HTTP\ResponseInterface;

class ReviewController extends BaseController
{
    protected DocumentModel $documentModel;
    protected DocumentReviewModel $reviewModel;
    protected DocumentWorkflowService $workflowService;

    public function __construct()
    {
        $this->documentModel    = new DocumentModel();
        $this->reviewModel      = new DocumentReviewModel();
        $this->workflowService  = new DocumentWorkflowService();
    }

    private function ensureAdminOrLpm(): ?ResponseInterface
    {
        if (has_role(['admin', 'lpm'])) {
            return null;
        }

        return redirect()->to('/dashboard')->with('error', 'Hanya Admin/LPM yang boleh mengakses review dokumen.');
    }

    /**
     * List all documents for LPM review
     */
    public function index(): string|ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $documents = $this->documentModel->getAllDocuments();

        return view('reviews/index', [
            'documents' => $documents,
        ]);
    }

    /**
     * Show review form for specific document
     */
    public function show(int $id): string|ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $document = $this->documentModel->find($id);
        $reviews = $this->reviewModel->getReviewsByDocument($id);

        if (!$document) {
            return redirect()->to('/reviews')->with('error', 'Dokumen tidak ditemukan.');
        }

        return view('reviews/show', [
            'document' => $document,
            'reviews'  => $reviews,
        ]);
    }

    /**
     * Process review decision
     */
    public function review(int $id): ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $rules = [
            'decision' => 'required|in_list[validated,revision_required,rejected]',
            'comment'  => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $reviewerId = session()->get('user_id');
        $decision = $this->request->getPost('decision');
        $comment = $this->request->getPost('comment') ?? '';

        if ($this->workflowService->reviewDocument($id, $reviewerId, $decision, $comment)) {
            catat_audit(
                'review_document_legacy',
                'reviews',
                $id,
                'Memproses review dokumen legacy dengan keputusan: ' . (string) $decision
            );

            $message = match ($decision) {
                'validated'         => 'Dokumen berhasil divalidasi.',
                'revision_required' => 'Dokumen dikembalikan untuk revisi.',
                'rejected'          => 'Dokumen ditolak.',
            };

            return redirect()->to('/reviews')->with('success', $message);
        }

        return redirect()->back()->with('error', 'Gagal memproses review.');
    }
}