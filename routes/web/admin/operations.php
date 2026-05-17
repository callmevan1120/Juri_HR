<?php

use App\Http\Controllers\Admin\Commercial\DownloadCommercialDocumentPdfController;
use Illuminate\Support\Facades\Route;

Route::livewire('/operations', 'admin.operational-workspace')
    ->name('admin.operations')
    ->can('viewOperationsWorkspace');

Route::livewire('/commercial', 'admin.commercial-workspace')
    ->name('admin.commercial')
    ->can('viewCommercialWorkspace');

Route::get('/commercial/quotations/{quotation}/pdf', [DownloadCommercialDocumentPdfController::class, 'quotation'])
    ->name('admin.commercial.quotations.pdf')
    ->can('viewCommercialWorkspace');

Route::get('/commercial/invoices/{invoice}/pdf', [DownloadCommercialDocumentPdfController::class, 'invoice'])
    ->name('admin.commercial.invoices.pdf')
    ->can('viewCommercialWorkspace');

Route::get('/commercial/vendor-bills/{vendorBill}/pdf', [DownloadCommercialDocumentPdfController::class, 'vendorBill'])
    ->name('admin.commercial.vendor-bills.pdf')
    ->can('viewCommercialWorkspace');

Route::livewire('/accounting', 'admin.accounting-workspace')
    ->name('admin.accounting')
    ->can('viewAccountingWorkspace');

Route::livewire('/custom-forms', 'admin.custom-form-manager')
    ->name('admin.custom-forms')
    ->can('viewCustomForms');
