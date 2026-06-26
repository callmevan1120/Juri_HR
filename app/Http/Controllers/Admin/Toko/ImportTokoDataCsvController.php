<?php

namespace App\Http\Controllers\Admin\Toko;

use App\Http\Controllers\Controller;
use App\Imports\ProductBrandsImport;
use App\Imports\ProductCategoriesImport;
use App\Imports\ProductsImport;
use App\Imports\TokoCustomersImport;
use App\Imports\TokoVendorsImport;
use App\Models\Company;
use App\Models\User;
use App\Support\CommercialWorkspaceService;
use App\Support\TokoCsvImportTemplates;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ImportTokoDataCsvController extends Controller
{
    public function __construct(private readonly CommercialWorkspaceService $commerce) {}

    public function __invoke(Request $request)
    {
        $request->validate([
            'import_type' => ['required', 'string', Rule::in(TokoCsvImportTemplates::keys())],
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $companyId = $this->targetCompanyId($request);

        if ($companyId === null) {
            return redirect()->back()->with('error', __('Choose a target company before importing CSV data.'));
        }

        try {
            if ($request->import_type === 'products') {
                Excel::import(new ProductsImport($companyId), $request->file('import_file'));
            } elseif ($request->import_type === 'categories') {
                Excel::import(new ProductCategoriesImport, $request->file('import_file'));
            } elseif ($request->import_type === 'brands') {
                Excel::import(new ProductBrandsImport, $request->file('import_file'));
            } elseif ($request->import_type === 'customers') {
                Excel::import(new TokoCustomersImport($companyId), $request->file('import_file'));
            } elseif ($request->import_type === 'vendors') {
                Excel::import(new TokoVendorsImport($companyId), $request->file('import_file'));
            }

            return redirect()->back()->with('success', __('Data successfully imported!'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to import data: ').$e->getMessage());
        }
    }

    private function targetCompanyId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        if ($user->company_id !== null) {
            return (int) $user->company_id;
        }

        $companyId = $this->commerce
            ->scopeCompanies(Company::query(), $user)
            ->orderBy('name')
            ->value('id');

        return $companyId === null ? null : (int) $companyId;
    }
}
