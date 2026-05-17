<?php

namespace App\Support;

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

    public function fileName(string $type, string $number): string
    {
        return sprintf('%s-%s.pdf', $type, Str::slug($number));
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function make(string $type, Quotation|Invoice|VendorBill $record, array $document): mixed
    {
        $companyName = $record->company?->name ?: Setting::getValue('app.company_name', config('app.name'));

        return Pdf::loadView('pdf.commercial-document', [
            'type' => $type,
            'record' => $record,
            'document' => $document,
            'companyName' => $companyName,
            'companyAddress' => Setting::getValue('app.company_address', ''),
            'companyPhone' => Setting::getValue('app.company_phone', ''),
            'companyWebsite' => Setting::getValue('app.company_website', ''),
            'supportContact' => Setting::getValue('app.support_contact', config('mail.from.address')),
            'logoSrc' => MailBranding::logoPdfSource(),
        ])->setPaper('a4');
    }
}
