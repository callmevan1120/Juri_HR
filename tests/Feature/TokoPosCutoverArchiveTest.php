<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\Company;
use App\Models\ImportExportRun;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosCutoverArchiveService;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function setTokoPosCutoverArchiveLicenseFeatures(array $features): void
{
    Setting::updateOrCreate(
        ['key' => 'app.company_name'],
        ['value' => 'PT. PasPapan Indonesia', 'group' => 'identity', 'type' => 'text']
    );
    Setting::updateOrCreate(
        ['key' => 'app.support_contact'],
        ['value' => 'https://t.me/RiprLutuk', 'group' => 'identity', 'type' => 'text']
    );
    Setting::updateOrCreate(
        ['key' => 'enterprise_license_key'],
        [
            'value' => makeEnterpriseTestLicense(['features' => $features]),
            'group' => 'enterprise',
            'type' => 'textarea',
        ]
    );

    Setting::flushCache('app.company_name');
    Setting::flushCache('app.support_contact');
    Setting::flushCache('enterprise_license_key');
    LicenseGuard::clearLicenseCache();
}

test('toko cutover archive stores legacy dump copy and migration report', function (): void {
    Storage::fake('local');

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-archive',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'BRG001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6000,
        'stock_tracking' => true,
    ]);
    $path = base_path('storage/framework/testing/toko-archive.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, "INSERT INTO `barang` (`no`, `kode`, `sku`, `nama`, `kategori`, `brand`, `barcode`, `hargabeli`, `hargajual`, `terjual`, `terbeli`, `sisa`, `retur`, `stokmin`, `ukuran`, `warna`, `expired`, `satuan`, `lokasi`, `keterangan`, `avatar`) VALUES\n(1, 'BRG001', 'BRG001', 'Filter AC', '', '', '', 6000, 10000, 0, 0, 3, 0, 1, '', '', '', 'pcs', '', '', '');");
    ImportExportRun::query()->create([
        'resource' => 'toko_pos_history',
        'operation' => 'import',
        'status' => 'completed',
        'requested_by_user_id' => $actor->id,
        'source_path' => $path,
        'processed_rows' => 1,
        'total_rows' => 1,
        'meta' => [
            'company_id' => $company->id,
            'rollback_report' => [
                'reversible' => true,
                'targets' => [
                    'invoices' => ['model' => Invoice::class, 'count' => 1, 'ids' => [99], 'rollback_order' => 20],
                ],
                'notes' => ['rollback fixture'],
            ],
        ],
        'completed_at' => now(),
    ]);

    $run = app(TokoPosCutoverArchiveService::class)->archive($actor, $path, $company->id);
    $report = json_decode(Storage::disk('local')->get($run->file_path), true);

    expect($run)->toBeInstanceOf(ImportExportRun::class)
        ->and($run->status)->toBe('completed')
        ->and($run->resource)->toBe('toko_pos_cutover_archive')
        ->and($run->file_path)->not->toBeNull()
        ->and($run->meta['company_id'])->toBe($company->id)
        ->and($run->meta['legacy_dump_sha256'])->toBe(hash_file('sha256', $path))
        ->and($run->meta['readiness']['checks']['products']['legacy'])->toBe(1)
        ->and($run->meta['rollback_report']['reversible'])->toBeTrue()
        ->and($report['rollback_report']['targets']['invoices']['count'])->toBe(1);

    Storage::disk('local')->assertExists($run->file_path);
    Storage::disk('local')->assertExists($run->meta['legacy_dump_archive_path']);
});

test('toko add-on can archive cutover report from selected dump', function (): void {
    setTokoPosCutoverArchiveLicenseFeatures(['toko_pos']);
    Storage::fake('local');

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-archive-livewire',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->call('archiveCutoverReport');

    $run = ImportExportRun::query()
        ->where('resource', 'toko_pos_cutover_archive')
        ->where('status', 'completed')
        ->latest('id')
        ->firstOrFail();

    Storage::disk('local')->assertExists($run->file_path);
});
