<?php namespace Lovata\ApiSynchronization\Components;

use Cms\Classes\ComponentBase;
use Lovata\ApiSynchronization\classes\PdfService;
use Lovata\ApiSynchronization\Models\ShippingDocument;
use Auth;

/**
 * ShippingDocuments Component
 */
class ShippingDocuments extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Shipping Documents',
            'description' => 'Display shipping documents for the current user'
        ];
    }

    public function defineProperties()
    {
        return [
            'documentsPerPage' => [
                'title' => 'Documents per page',
                'type' => 'string',
                'default' => '20',
            ],
            'showOnlyUndelivered' => [
                'title' => 'Show only undelivered',
                'type' => 'checkbox',
                'default' => false,
            ],
        ];
    }

    /**
     * Load shipping documents
     */
    public function onLoadDocuments()
    {
        $user = Auth::getUser();
        if (!$user) {
            return ['error' => 'User not authenticated'];
        }

        $page = post('page', 1);
        $perPage = $this->property('documentsPerPage', 20);
        $showOnlyUndelivered = $this->property('showOnlyUndelivered', false);

        $query = ShippingDocument::where('user_id', $user->id)
            ->orderBy('document_date', 'desc');

        if ($showOnlyUndelivered) {
            $query->where('is_fully_delivered', false);
        }

        $documents = $query->paginate($perPage, $page);

        return [
            'documents' => $documents,
            'pagination' => [
                'currentPage' => $documents->currentPage(),
                'lastPage' => $documents->lastPage(),
                'total' => $documents->total(),
            ],
        ];
    }

    /**
     * Load document details
     */
    public function onShowDetails()
    {
        $documentId = post('documentId');

        $document = ShippingDocument::with(['positions.offer', 'positions.order_position.order'])
            ->find($documentId);

        if (!$document) {
            return ['error' => 'Document not found'];
        }

        $user = Auth::getUser();
        if ($document->user_id !== $user->id) {
            return ['error' => 'Access denied'];
        }

        return [
            'document' => $document,
            'positions' => $document->positions,
        ];
    }

    /**
     * Download PDF
     */
    public function onDownloadPdf()
    {
        $documentId = post('documentId');

        $document = ShippingDocument::find($documentId);

        if (!$document) {
            return ['error' => 'Document not found'];
        }

        $user = Auth::getUser();
        if ($document->user_id !== $user->id) {
            return ['error' => 'Access denied'];
        }

        try {
            $pdfService = new PdfService();
            return $pdfService->downloadPdf($document);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get initial documents list
     */
    public function documents()
    {
        $user = Auth::getUser();
        if (!$user) {
            return [];
        }

        $perPage = $this->property('documentsPerPage', 20);
        $showOnlyUndelivered = $this->property('showOnlyUndelivered', false);

        $query = ShippingDocument::where('user_id', $user->id)
            ->orderBy('document_date', 'desc');

        if ($showOnlyUndelivered) {
            $query->where('is_fully_delivered', false);
        }

        return $query->paginate($perPage);
    }
}
