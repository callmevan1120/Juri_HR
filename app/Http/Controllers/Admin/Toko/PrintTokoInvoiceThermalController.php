<?php

namespace App\Http\Controllers\Admin\Toko;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Support\CommercialWorkspaceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintTokoInvoiceThermalController extends Controller
{
    public function __invoke(
        Request $request,
        Invoice $invoice,
        CommercialWorkspaceService $commercial,
    ): View {
        $this->authorize('viewTokoPosAddon');

        $user = $request->user();

        abort_unless($user instanceof User, 403);
        abort_unless($commercial->canAccessCompany($user, (int) $invoice->company_id), 403);
        abort_unless(($invoice->metadata['source'] ?? null) === 'toko_pos_counter_sale', 404);

        $invoice->loadMissing(['company', 'client', 'lines', 'lines.product']);

        return view('admin.toko.print-thermal-receipt', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'client' => $invoice->client,
        ]);
    }
}
