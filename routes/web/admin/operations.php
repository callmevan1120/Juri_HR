<?php

use App\Http\Controllers\Admin\Collaboration\DownloadCloudFileController;
use App\Http\Controllers\Admin\Commercial\DownloadCommercialDocumentPdfController;
use App\Http\Controllers\Admin\Toko\DownloadTokoDeliveryLetterPdfController;
use App\Http\Controllers\Admin\Toko\DownloadTokoInvoicePdfController;
use App\Http\Controllers\Admin\Toko\DownloadTokoQuotationPdfController;
use App\Http\Controllers\Admin\Toko\ExportTokoTransactionsCsvController;
use App\Http\Controllers\Admin\Toko\ImportTokoDataCsvController;
use App\Http\Controllers\Admin\Toko\PrintTokoInvoiceThermalController;
use App\Http\Controllers\Admin\Toko\PrintTokoProductBarcodesController;
use App\Http\Controllers\Admin\Toko\PrintTokoStockAdjustmentReportController;
use Illuminate\Support\Facades\Route;

Route::livewire('/operations', 'admin.operational-workspace')
    ->name('admin.operations')
    ->can('viewOperationsWorkspace');

Route::livewire('/commercial', 'admin.commercial-workspace')
    ->name('admin.commercial')
    ->can('viewCommercialWorkspace');

Route::livewire('/collaboration', 'admin.collaboration-workspace')
    ->name('admin.collaboration')
    ->can('viewCollaborationWorkspace');

Route::get('/collaboration/files/{file}/download', DownloadCloudFileController::class)
    ->name('admin.collaboration.files.download')
    ->can('download', 'file');

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

Route::livewire('/toko', 'admin.toko-pos-addon')
    ->name('admin.toko')
    ->defaults('page', 'dashboard')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

foreach ([
    'pos',
    'products',
    'customers',
    'vendors',
    'purchases',
    'inventory',
    'returns',
    'quotations',
    'delivery-letters',
    'cash',
    'reports',
] as $tokoPage) {
    Route::livewire('/toko/'.$tokoPage, 'admin.toko-pos-addon')
        ->name('admin.toko.'.$tokoPage)
        ->defaults('page', $tokoPage)
        ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
        ->can('viewTokoPosAddon');
}

Route::livewire('/toko/migration', 'admin.toko-pos-addon')
    ->name('admin.toko.migration')
    ->defaults('page', 'migration')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.import,admin.dashboard')
    ->can('importTokoPosAddon');

Route::get('/toko/invoices/{invoice}/pdf', [DownloadTokoInvoicePdfController::class, '__invoke'])
    ->name('admin.toko.invoices.pdf')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/invoices/{invoice}/thermal', [PrintTokoInvoiceThermalController::class, '__invoke'])
    ->name('admin.toko.invoices.thermal')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/quotations/{quotation}/pdf', [DownloadTokoQuotationPdfController::class, '__invoke'])
    ->name('admin.toko.quotations.pdf')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/delivery-letters/{deliveryLetter}/pdf', [DownloadTokoDeliveryLetterPdfController::class, '__invoke'])
    ->name('admin.toko.delivery-letters.pdf')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/purchases/{vendorBill}/pdf', [DownloadCommercialDocumentPdfController::class, 'vendorBill'])
    ->name('admin.toko.purchases.pdf')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/products/barcodes', [PrintTokoProductBarcodesController::class, '__invoke'])
    ->name('admin.toko.products.barcodes')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/stock-adjustments/print', [PrintTokoStockAdjustmentReportController::class, '__invoke'])
    ->name('admin.toko.stock-adjustments.print')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.view,admin.dashboard')
    ->can('viewTokoPosAddon');

Route::get('/toko/exports/sales.csv', [ExportTokoTransactionsCsvController::class, 'sales'])
    ->name('admin.toko.exports.sales')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::post('/toko/import', [ImportTokoDataCsvController::class, '__invoke'])
    ->name('admin.toko.import')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.import,admin.dashboard')
    ->can('importTokoPosAddon');

Route::get('/toko/exports/purchases.csv', [ExportTokoTransactionsCsvController::class, 'purchases'])
    ->name('admin.toko.exports.purchases')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/sales-lines.csv', [ExportTokoTransactionsCsvController::class, 'salesLines'])
    ->name('admin.toko.exports.sales-lines')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/purchase-lines.csv', [ExportTokoTransactionsCsvController::class, 'purchaseLines'])
    ->name('admin.toko.exports.purchase-lines')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/payments.csv', [ExportTokoTransactionsCsvController::class, 'payments'])
    ->name('admin.toko.exports.payments')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/customer-income.csv', [ExportTokoTransactionsCsvController::class, 'customerIncome'])
    ->name('admin.toko.exports.customer-income')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/products.csv', [ExportTokoTransactionsCsvController::class, 'products'])
    ->name('admin.toko.exports.products')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-sales.csv', [ExportTokoTransactionsCsvController::class, 'reportSales'])
    ->name('admin.toko.exports.report-sales')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-purchases.csv', [ExportTokoTransactionsCsvController::class, 'reportPurchases'])
    ->name('admin.toko.exports.report-purchases')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-gross-profit.csv', [ExportTokoTransactionsCsvController::class, 'reportGrossProfit'])
    ->name('admin.toko.exports.report-gross-profit')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-operational-expenses.csv', [ExportTokoTransactionsCsvController::class, 'reportOperationalExpenses'])
    ->name('admin.toko.exports.report-operational-expenses')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-product-movements.csv', [ExportTokoTransactionsCsvController::class, 'reportProductMovements'])
    ->name('admin.toko.exports.report-product-movements')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-inventory-valuation.csv', [ExportTokoTransactionsCsvController::class, 'reportInventoryValuation'])
    ->name('admin.toko.exports.report-inventory-valuation')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-profit-loss.csv', [ExportTokoTransactionsCsvController::class, 'reportProfitLoss'])
    ->name('admin.toko.exports.report-profit-loss')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-ar-aging.csv', [ExportTokoTransactionsCsvController::class, 'reportArAging'])
    ->name('admin.toko.exports.report-ar-aging')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');

Route::get('/toko/exports/report-ap-aging.csv', [ExportTokoTransactionsCsvController::class, 'reportApAging'])
    ->name('admin.toko.exports.report-ap-aging')
    ->middleware('feature.lock:toko_pos,admin.toko_pos.export,admin.dashboard')
    ->can('exportTokoPosAddon');
