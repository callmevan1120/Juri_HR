<?php

namespace App\Http\Controllers\Admin\Toko;

use App\Http\Controllers\Controller;
use App\Imports\ProductBrandsImport;
use App\Imports\ProductCategoriesImport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportTokoDataCsvController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'import_type' => ['required', 'string', 'in:products,categories,brands'],
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        try {
            if ($request->import_type === 'products') {
                Excel::import(new ProductsImport, $request->file('import_file'));
            } elseif ($request->import_type === 'categories') {
                Excel::import(new ProductCategoriesImport, $request->file('import_file'));
            } elseif ($request->import_type === 'brands') {
                Excel::import(new ProductBrandsImport, $request->file('import_file'));
            }

            return redirect()->back()->with('success', __('Data successfully imported!'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to import data: ').$e->getMessage());
        }
    }
}
