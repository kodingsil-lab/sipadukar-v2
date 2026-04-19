<?php

namespace App\Controllers;

use App\Models\DocumentModel;
use App\Services\DocumentWorkflowService;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentController extends BaseController
{
    protected DocumentModel $documentModel;
    protected DocumentWorkflowService $workflowService;

    public function __construct()
    {
        $this->documentModel    = new DocumentModel();
        $this->workflowService  = new DocumentWorkflowService();
    }

    private function ensureAdminOrLpm(): ?ResponseInterface
    {
        if (has_role(['admin', 'lpm'])) {
            return null;
        }

        return redirect()->to('/dashboard')->with('error', 'Hanya Admin/LPM yang boleh mengelola dokumen.');
    }

    private function resolveManagedDocumentPath(?string $storedPath): ?string
    {
        $storedPath = trim((string) $storedPath);
        if ($storedPath === '') {
            return null;
        }

        $normalizedRelativePath = str_replace('\\', '/', $storedPath);
        if (str_contains($normalizedRelativePath, "\0")
            || str_starts_with($normalizedRelativePath, '/')
            || preg_match('/^[A-Za-z]:\//', $normalizedRelativePath) === 1
            || ! str_starts_with($normalizedRelativePath, 'uploads/documents/')) {
            return null;
        }

        $absolutePath = realpath(WRITEPATH . $normalizedRelativePath);
        if ($absolutePath === false || ! is_file($absolutePath)) {
            return null;
        }

        $documentsRoot = realpath(WRITEPATH . 'uploads/documents');
        if ($documentsRoot === false) {
            return null;
        }

        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);
        $normalizedDocumentsRoot = rtrim(str_replace('\\', '/', $documentsRoot), '/');
        if ($normalizedAbsolutePath !== $normalizedDocumentsRoot && ! str_starts_with($normalizedAbsolutePath, $normalizedDocumentsRoot . '/')) {
            return null;
        }

        return $absolutePath;
    }

    /**
     * List documents for uploader
     */
    public function index(): string|ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $userId = session()->get('user_id');
        $documents = $this->documentModel->getDocumentsByUploader($userId);

        return view('documents/index', [
            'documents' => $documents,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): string|ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        return view('documents/create');
    }

    /**
     * Store new document
     */
    public function store(): ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'file'  => 'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,jpg,jpeg,png]|mime_in[file,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');
        $filePath = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/documents', $newName);
            $filePath = 'uploads/documents/' . $newName;
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'file_path'   => $filePath,
            'uploaded_by' => session()->get('user_id'),
            'prodi_id'    => $this->request->getPost('prodi_id'),
        ];

        if ($this->documentModel->insert($data)) {
            $documentId = (int) $this->documentModel->getInsertID();
            catat_audit(
                'tambah_document_legacy',
                'documents',
                $documentId,
                'Menambahkan dokumen legacy: ' . (string) ($data['title'] ?? '-')
            );
            return redirect()->to('/documents')->with('success', 'Dokumen berhasil dibuat.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal membuat dokumen.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): string|ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $document = $this->documentModel->find($id);
        $userId = session()->get('user_id');

        if (!$document || !$this->workflowService->canEdit($id, $userId)) {
            return redirect()->to('/documents')->with('error', 'Tidak dapat mengedit dokumen.');
        }

        return view('documents/edit', [
            'document' => $document,
        ]);
    }

    /**
     * Update document
     */
    public function update(int $id): ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $document = $this->documentModel->find($id);
        $userId = session()->get('user_id');

        if (!$document || !$this->workflowService->canEdit($id, $userId)) {
            return redirect()->to('/documents')->with('error', 'Tidak dapat mengupdate dokumen.');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'file'  => 'permit_empty|max_size[file,10240]|ext_in[file,pdf,doc,docx,jpg,jpeg,png]|mime_in[file,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');
        $filePath = $document['file_path'];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old file if exists
            $existingFilePath = $this->resolveManagedDocumentPath($filePath);
            if ($existingFilePath !== null) {
                unlink($existingFilePath);
            }

            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/documents', $newName);
            $filePath = 'uploads/documents/' . $newName;
        }

        $data = [
            'title'     => $this->request->getPost('title'),
            'file_path' => $filePath,
        ];

        if ($this->documentModel->update($id, $data)) {
            catat_audit(
                'edit_document_legacy',
                'documents',
                $id,
                'Memperbarui dokumen legacy: ' . (string) ($data['title'] ?? ($document['title'] ?? '-'))
            );
            return redirect()->to('/documents')->with('success', 'Dokumen berhasil diupdate.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal update dokumen.');
    }

    /**
     * Submit document
     */
    public function submit(int $id): ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $userId = session()->get('user_id');

        if ($this->workflowService->submitDocument($id, $userId)) {
            catat_audit(
                'submit_document_legacy',
                'documents',
                $id,
                'Mengajukan dokumen legacy untuk review.'
            );
            return redirect()->to('/documents')->with('success', 'Dokumen berhasil diajukan.');
        }

        return redirect()->to('/documents')->with('error', 'Gagal mengajukan dokumen.');
    }

    /**
     * Resubmit document after revision
     */
    public function resubmit(int $id): ResponseInterface
    {
        if ($guard = $this->ensureAdminOrLpm()) {
            return $guard;
        }

        $userId = session()->get('user_id');

        if ($this->workflowService->resubmitDocument($id, $userId)) {
            catat_audit(
                'resubmit_document_legacy',
                'documents',
                $id,
                'Mengajukan ulang dokumen legacy setelah revisi.'
            );
            return redirect()->to('/documents')->with('success', 'Dokumen berhasil disubmit ulang.');
        }

        return redirect()->to('/documents')->with('error', 'Gagal resubmit dokumen.');
    }
}