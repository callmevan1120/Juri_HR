<?php

namespace App\Imports;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductBrandsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $setting = Setting::firstOrCreate(
            ['key' => 'toko_pos.product_brands'],
            ['group' => 'toko_pos', 'type' => 'json', 'value' => '[]']
        );
        $brands = json_decode((string) $setting->value, true);
        $brands = is_array($brands) ? $brands : [];

        foreach ($rows as $row) {
            $name = $row['name'] ?? $row['nama'] ?? $row['brand'] ?? $row['merek'] ?? null;
            if ($name) {
                $brands[] = [
                    'id' => (string) Str::uuid(),
                    'name' => trim($name),
                ];
            }
        }

        $uniqueBrands = [];
        $seen = [];
        foreach ($brands as $brand) {
            $lower = strtolower($brand['name']);
            if (! in_array($lower, $seen)) {
                $uniqueBrands[] = $brand;
                $seen[] = $lower;
            }
        }

        $setting->value = json_encode($uniqueBrands, JSON_THROW_ON_ERROR);
        $setting->save();
        Setting::flushCache('toko_pos.product_brands');
    }
}
