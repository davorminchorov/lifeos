<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    /**
     * Generate PDF for an invoice
     *
     * @param Invoice $invoice
     * @param array $options
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(Invoice $invoice, array $options = [])
    {
        $defaultOptions = [
            'paper' => 'a4',
            'orientation' => 'portrait',
        ];

        $options = array_merge($defaultOptions, $options);

        // Load invoice with all relationships
        $invoice->load([
            'customer',
            'items.taxRate',
            'items.discount',
            'payments',
        ]);

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);

        // Set paper size and orientation
        $pdf->setPaper($options['paper'], $options['orientation']);

        return $pdf;
    }

    /**
     * Download PDF for an invoice
     *
     * @param Invoice $invoice
     * @param string|null $filename
     * @return \Illuminate\Http\Response
     */
    public function download(Invoice $invoice, ?string $filename = null)
    {
        if (!$filename) {
            $filename = $this->generateFilename($invoice);
        }

        $pdf = $this->generatePdf($invoice);

        return $pdf->download($filename);
    }

    /**
     * Stream PDF to browser for viewing
     *
     * @param Invoice $invoice
     * @param string|null $filename
     * @return \Illuminate\Http\Response
     */
    public function stream(Invoice $invoice, ?string $filename = null)
    {
        if (!$filename) {
            $filename = $this->generateFilename($invoice);
        }

        $pdf = $this->generatePdf($invoice);

        return $pdf->stream($filename);
    }

    /**
     * Save PDF to storage
     *
     * @param Invoice $invoice
     * @param string $path
     * @return bool
     */
    public function save(Invoice $invoice, string $path)
    {
        $pdf = $this->generatePdf($invoice);

        return $pdf->save($path);
    }

    /**
     * Generate filename for invoice PDF
     *
     * @param Invoice $invoice
     * @return string
     */
    protected function generateFilename(Invoice $invoice): string
    {
        $number = $invoice->number ?: 'draft-' . $invoice->id;
        $number = str_replace('/', '-', $number);

        return "invoice-{$number}.pdf";
    }

    /**
     * Format money for display
     *
     * @param int $amount Amount in cents
     * @param string $currency
     * @return string
     */
    public function formatMoney(int $amount, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'MKD' => 'ден',
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        $value = number_format($amount / 100, 2);

        return $symbol . $value;
    }
}
