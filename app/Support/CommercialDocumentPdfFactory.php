<?php

namespace App\Support;

use App\Models\DeliveryLetter;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\VendorBill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CommercialDocumentPdfFactory
{
    public function quotation(Quotation $quotation): mixed
    {
        $quotation->loadMissing(['company', 'client', 'project', 'items.product']);

        return $this->make('quotation', $quotation, [
            'title' => __('Quotation'),
            'numberLabel' => __('Quotation No.'),
            'dateLabel' => __('Issued Date'),
            'dueLabel' => __('Valid Until'),
            'partyLabel' => __('Bill To'),
            'partyName' => $quotation->client?->name ?? __('Client not assigned'),
            'partyContact' => $quotation->client?->contact_name,
            'partyEmail' => $quotation->client?->contact_email,
            'partyPhone' => $quotation->client?->contact_phone,
            'partyAddress' => $quotation->client?->address,
            'projectName' => $quotation->project?->name,
            'number' => $quotation->number,
            'issuedAt' => $quotation->issued_at,
            'dueAt' => $quotation->valid_until,
            'status' => $quotation->status,
            'notes' => $quotation->notes,
            'items' => $quotation->items,
        ]);
    }

    public function invoice(Invoice $invoice): mixed
    {
        $invoice->loadMissing(['company', 'client', 'project', 'quotation', 'items.product']);

        return $this->make('invoice', $invoice, [
            'title' => __('Invoice'),
            'numberLabel' => __('Invoice No.'),
            'dateLabel' => __('Issued Date'),
            'dueLabel' => __('Due Date'),
            'partyLabel' => __('Bill To'),
            'partyName' => $invoice->client?->name ?? __('Client not assigned'),
            'partyContact' => $invoice->client?->contact_name,
            'partyEmail' => $invoice->client?->contact_email,
            'partyPhone' => $invoice->client?->contact_phone,
            'partyAddress' => $invoice->client?->address,
            'projectName' => $invoice->project?->name,
            'sourceNumber' => $invoice->quotation?->number,
            'number' => $invoice->number,
            'issuedAt' => $invoice->issued_at,
            'dueAt' => $invoice->due_at,
            'status' => $invoice->status,
            'notes' => $invoice->notes,
            'items' => $invoice->items,
        ]);
    }

    public function vendorBill(VendorBill $bill): mixed
    {
        $bill->loadMissing(['company', 'vendor', 'items.product']);

        return $this->make('vendor-bill', $bill, [
            'title' => __('Vendor Bill'),
            'numberLabel' => __('Bill No.'),
            'dateLabel' => __('Issued Date'),
            'dueLabel' => __('Due Date'),
            'partyLabel' => __('Vendor'),
            'partyName' => $bill->vendor?->name ?? __('Vendor not assigned'),
            'partyContact' => $bill->vendor?->contact_name,
            'partyEmail' => $bill->vendor?->email,
            'partyPhone' => $bill->vendor?->phone,
            'partyTaxNumber' => $bill->vendor?->tax_number,
            'partyAddress' => $bill->vendor?->address,
            'number' => $bill->number,
            'issuedAt' => $bill->issued_at,
            'dueAt' => $bill->due_at,
            'status' => $bill->status,
            'notes' => $bill->notes,
            'items' => $bill->items,
        ]);
    }

    public function deliveryLetter(DeliveryLetter $letter): mixed
    {
        $letter->loadMissing(['company', 'client', 'invoice.items.product']);
        $invoice = $letter->invoice;

        return $this->make('delivery-letter', $letter, [
            'title' => __('Delivery Letter'),
            'numberLabel' => __('Letter No.'),
            'dateLabel' => __('Issued Date'),
            'dueLabel' => __('Invoice Date'),
            'partyLabel' => __('Deliver To'),
            'partyName' => $letter->destination ?: ($letter->client?->name ?? __('Client not assigned')),
            'partyContact' => $letter->driver_name ? __('Driver: :name', ['name' => $letter->driver_name]) : null,
            'partyEmail' => null,
            'partyPhone' => $letter->contact_phone,
            'partyAddress' => $letter->shipping_address,
            'projectName' => null,
            'sourceNumber' => $invoice?->number,
            'number' => $letter->number,
            'issuedAt' => $letter->issued_at,
            'dueAt' => $invoice?->issued_at,
            'status' => $letter->status,
            'notes' => collect([
                $letter->vehicle_number ? __('Vehicle: :number', ['number' => $letter->vehicle_number]) : null,
                $letter->driver_phone ? __('Driver phone: :phone', ['phone' => $letter->driver_phone]) : null,
                $letter->notes,
            ])->filter()->join("\n"),
            'items' => $invoice?->items ?? collect(),
        ]);
    }

    public function fileName(string $type, string $number): string
    {
        return sprintf('%s-%s.pdf', $type, Str::slug($number));
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function make(string $type, Quotation|Invoice|VendorBill|DeliveryLetter $record, array $document): mixed
    {
        $companyName = $record->company?->name ?: Setting::getValue('app.company_name', config('app.name'));
        $body = view('pdf.commercial-document', [
            'type' => $type,
            'record' => $record,
            'document' => $document,
        ])->render();

        return Pdf::loadView('pdf.employee-document-template', [
            'preview' => false,
            'companyName' => $companyName,
            'body' => $body,
            'footer' => null,
            'documentMeta' => [],
            'layoutOptions' => [
                'header_company_name' => $companyName,
                'header_tagline' => __('Commercial Document'),
                'show_accents' => true,
                'show_document_meta' => false,
            ],
        ])->setPaper('a4');
    }
}
