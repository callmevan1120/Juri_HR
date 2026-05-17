<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use App\Models\VendorBill;
use App\Support\CommercialDocumentPdfFactory;
use App\Support\CommercialWorkspaceService;
use Illuminate\Http\Request;

class DownloadCommercialDocumentPdfController extends Controller
{
    public function quotation(
        Request $request,
        Quotation $quotation,
        CommercialWorkspaceService $commercial,
        CommercialDocumentPdfFactory $pdfFactory,
    ) {
        $this->authorizeCommercialDocument($request, $commercial, $quotation->company_id);
        $pdf = $pdfFactory->quotation($quotation);

        return $pdf->download($pdfFactory->fileName('quotation', $quotation->number));
    }

    public function invoice(
        Request $request,
        Invoice $invoice,
        CommercialWorkspaceService $commercial,
        CommercialDocumentPdfFactory $pdfFactory,
    ) {
        $this->authorizeCommercialDocument($request, $commercial, $invoice->company_id);
        $pdf = $pdfFactory->invoice($invoice);

        return $pdf->download($pdfFactory->fileName('invoice', $invoice->number));
    }

    public function vendorBill(
        Request $request,
        VendorBill $vendorBill,
        CommercialWorkspaceService $commercial,
        CommercialDocumentPdfFactory $pdfFactory,
    ) {
        $this->authorizeCommercialDocument($request, $commercial, $vendorBill->company_id);
        $pdf = $pdfFactory->vendorBill($vendorBill);

        return $pdf->download($pdfFactory->fileName('vendor-bill', $vendorBill->number));
    }

    private function authorizeCommercialDocument(Request $request, CommercialWorkspaceService $commercial, int|string|null $companyId): void
    {
        $this->authorize('viewCommercialWorkspace');

        $user = $request->user();

        abort_unless($user instanceof User && $companyId !== null, 403);
        abort_unless($commercial->canAccessCompany($user, (int) $companyId), 403);
    }
}
