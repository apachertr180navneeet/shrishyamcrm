<?php

namespace App\Services;

use Illuminate\Http\Response;
use Mpdf\Mpdf;

class MpdfReceipt
{
    protected Mpdf $mpdf;

    public function __construct(Mpdf $mpdf)
    {
        $this->mpdf = $mpdf;
    }

    /**
     * Download the PDF as an attachment response.
     */
    public function download(string $filename = 'receipt.pdf'): Response
    {
        $content = $this->mpdf->Output('', 'S');
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
        ]);
    }

    /**
     * Stream the PDF inline in the browser.
     */
    public function stream(string $filename = 'receipt.pdf'): Response
    {
        $content = $this->mpdf->Output('', 'S');
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
        ]);
    }

    /**
     * Return raw binary PDF content.
     */
    public function output(): string
    {
        return $this->mpdf->Output('', 'S');
    }

    /**
     * Save PDF directly to a file path.
     */
    public function save(string $filePath): void
    {
        $this->mpdf->Output($filePath, 'F');
    }

    /**
     * Get underlying Mpdf instance.
     */
    public function getMpdf(): Mpdf
    {
        return $this->mpdf;
    }
}
