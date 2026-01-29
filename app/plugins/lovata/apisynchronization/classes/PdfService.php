<?php namespace Lovata\ApiSynchronization\classes;

use Lovata\ApiSynchronization\Models\ShippingDocument;
use GuzzleHttp\Client;
use Log;

/**
 * PdfService
 *
 * Service for handling PDF downloads from ERP
 */
class PdfService
{
    protected ApiClientService $api;

    public function __construct()
    {
        $this->api = new ApiClientService();
    }

    /**
     * Get PDF URL for shipping document
     *
     * @param string $seipeeDocumentId
     * @return string|null
     */
    public function getDocumentPdfUrl(string $seipeeDocumentId): ?string
    {
        try {
            $this->api->authenticate();

            $endpoint = "transport-documents/{$seipeeDocumentId}/pdf";
            $response = $this->api->get($endpoint);

            if (isset($response['pdf_url'])) {
                return $response['pdf_url'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting PDF URL: ' . $e->getMessage(), [
                'seipee_document_id' => $seipeeDocumentId,
            ]);
            return null;
        }
    }

    /**
     * Download PDF from ERP and return response
     *
     * @param ShippingDocument $document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception
     */
    public function downloadPdf(ShippingDocument $document)
    {
        $pdfUrl = $document->pdf_url;

        if (!$pdfUrl) {
            $pdfUrl = $this->getDocumentPdfUrl($document->seipee_document_id);

            if (!$pdfUrl) {
                throw new \Exception('PDF URL not found for document: ' . $document->document_number);
            }

            $document->pdf_url = $pdfUrl;
            $document->save();
        }

        try {
            $client = new Client([
                'verify' => false,
                'timeout' => 60,
            ]);

            $response = $client->get($pdfUrl);
            $content = $response->getBody()->getContents();
            $contentType = $response->getHeaderLine('Content-Type') ?: 'application/pdf';

            $filename = $this->generatePdfFilename($document);

            return response()->streamDownload(function() use ($content) {
                echo $content;
            }, $filename, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading PDF: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'pdf_url' => $pdfUrl,
            ]);
            throw new \Exception('Failed to download PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF filename
     *
     * @param ShippingDocument $document
     * @return string
     */
    protected function generatePdfFilename(ShippingDocument $document): string
    {
        $documentNumber = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document->document_number);
        $date = $document->document_date ? $document->document_date->format('Ymd') : 'nodate';

        return "shipping_document_{$documentNumber}_{$date}.pdf";
    }
}
