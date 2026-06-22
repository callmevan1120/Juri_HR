<?php

namespace App\Imports;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductCategoriesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $setting = Setting::firstOrCreate(
            ['key' => 'toko_pos.product_categories'],
            ['group' => 'toko_pos', 'type' => 'json', 'value' => []]
        );
        $categories = $setting->value ?? [];

        foreach ($rows as $row) {
            $name = $row['name'] ?? $row['nama'] ?? $row['kategori'] ?? $row['category'] ?? null;
            if ($name) {
                $categories[] = [
                    'id' => (string) Str::uuid(),
                    'name' => trim($name),
                ];
            }
        }

        $uniqueCategories = [];
        $seen = [];
        foreach ($categories as $cat) {
            $lower = strtolower($cat['name']);
            if (! in_array($lower, $seen)) {
                $uniqueCategories[] = $cat;
                $seen[] = $lower;
            }
        }

        $setting->value = $uniqueCategories;
        $setting->save();
    }
}
