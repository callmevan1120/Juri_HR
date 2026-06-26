<?php

namespace App\Support;

use Illuminate\Support\Arr;

class TokoCsvImportTemplates
{
    /**
     * @return array<string, array{key:string,label:string,description:string,filename:string,headers:list<string>,sample:list<string>}>
     */
    public static function definitions(): array
    {
        return [
            'products' => [
                'key' => 'products',
                'label' => 'Products',
                'description' => 'Master product catalog with price and stock settings.',
                'filename' => 'toko-products-template.csv',
                'headers' => ['sku', 'name', 'unit', 'selling_price', 'cost_price', 'stock_tracking', 'reorder_point', 'status'],
                'sample' => ['CSV-001', 'Produk Contoh', 'pcs', '12500', '8000', 'yes', '3', 'active'],
            ],
            'customers' => [
                'key' => 'customers',
                'label' => 'Customers',
                'description' => 'Customer master data used by POS sales, invoices, and delivery letters.',
                'filename' => 'toko-customers-template.csv',
                'headers' => ['code', 'name', 'phone', 'email', 'address', 'status'],
                'sample' => ['CUST-001', 'Ayu Customer', '08123456789', 'ayu@example.test', 'Jl Contoh 1', 'active'],
            ],
            'vendors' => [
                'key' => 'vendors',
                'label' => 'Vendors',
                'description' => 'Supplier master data used by purchase receiving and AP.',
                'filename' => 'toko-vendors-template.csv',
                'headers' => ['code', 'name', 'phone', 'email', 'address', 'tax_number', 'status'],
                'sample' => ['VEND-001', 'Supplier Contoh', '08987654321', 'vendor@example.test', 'Jl Supplier 1', '01.234.567.8-999.000', 'active'],
            ],
            'categories' => [
                'key' => 'categories',
                'label' => 'Categories',
                'description' => 'Product category reference list.',
                'filename' => 'toko-categories-template.csv',
                'headers' => ['name'],
                'sample' => ['Suku Cadang'],
            ],
            'brands' => [
                'key' => 'brands',
                'label' => 'Brands',
                'description' => 'Product brand reference list.',
                'filename' => 'toko-brands-template.csv',
                'headers' => ['name'],
                'sample' => ['Pandan Teknik'],
            ],
        ];
    }

    /**
     * @return array{key:string,label:string,description:string,filename:string,headers:list<string>,sample:list<string>}|null
     */
    public static function find(string $type): ?array
    {
        return Arr::get(self::definitions(), $type);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }
}
