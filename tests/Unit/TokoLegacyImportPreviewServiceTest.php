<?php

use App\Support\TokoLegacyImportPreviewService;

test('toko legacy preview counts mapped rows from mysql dump', function () {
    requireEnterpriseRuntimeSourceForTests('toko_pos');

    $dump = <<<'SQL'
CREATE TABLE `barang` (`kode` varchar(20), `nama` varchar(50));
INSERT INTO `barang` (`kode`, `nama`, `kategori`, `brand`, `hargabeli`, `hargajual`, `sisa`) VALUES
('000001', 'Kapasitor AC 35 uf', 'Sparepart AC', 'Sigma', 34000, 45000, 5),
('000002', 'Filter, kecil', 'Sparepart Kulkas', 'Samsung', 6500, 8000, 424);
INSERT INTO `pelanggan` (`kode`, `nama`, `alamat`) VALUES
('0001', 'WARDI', 'Jakarta Barat');
INSERT INTO `supplier` (`kode`, `nama`) VALUES
('0001', 'Digital Teknik'),('0002', 'TANJUNG JAYA TEKNIK');
INSERT INTO `sale` (`nota`, `nomor`, `total`) VALUES
('100001', 'INV100001', 260000);
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-dump-');
    file_put_contents($path, $dump);

    $preview = app(TokoLegacyImportPreviewService::class)->preview($path);

    expect($preview['available'])->toBeTrue()
        ->and($preview['file']['path'])->toBe($path)
        ->and($preview['totals']['legacy_rows'])->toBe(6)
        ->and($preview['tables']['barang']['rows'])->toBe(2)
        ->and($preview['tables']['barang']['target'])->toBe('products')
        ->and($preview['tables']['barang']['sample']['nama'])->toBe('Kapasitor AC 35 uf')
        ->and($preview['tables']['pelanggan']['rows'])->toBe(1)
        ->and($preview['tables']['supplier']['rows'])->toBe(2)
        ->and($preview['tables']['sale']['target'])->toBe('invoices + payments')
        ->and($preview['warnings'])->toBe([]);

    unlink($path);
});

test('toko legacy preview reports missing dump without throwing', function () {
    requireEnterpriseRuntimeSourceForTests('toko_pos');

    $preview = app(TokoLegacyImportPreviewService::class)->preview('/nope/toko.sql');

    expect($preview['available'])->toBeFalse()
        ->and($preview['totals']['legacy_rows'])->toBe(0)
        ->and($preview['warnings'])->toContain('Legacy SQL dump was not found.');
});

test('toko legacy preview reports master data readiness and issues', function () {
    requireEnterpriseRuntimeSourceForTests('toko_pos');

    $dump = <<<'SQL'
INSERT INTO `barang` (`kode`, `sku`, `nama`, `kategori`, `brand`, `hargabeli`, `hargajual`, `sisa`, `stokmin`, `satuan`) VALUES
('000001', 'SKU000001', 'Kapasitor AC', 'Sparepart AC', 'Sigma', 34000, 45000, 5, 1, 'pcs'),
('000002', 'SKU000001', '', 'Sparepart AC', 'Sigma', 10000, 15000, -2, 1, 'pcs');
INSERT INTO `pelanggan` (`kode`, `nama`, `alamat`, `nohp`, `email`) VALUES
('0001', 'WARDI', 'Jakarta', '0812', ''),
('0002', '', 'Depok', '', '');
INSERT INTO `supplier` (`kode`, `nama`, `alamat`, `nohp`) VALUES
('0001', 'Digital Teknik', 'Jakarta', '021'),
('0002', '', '', '');
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-readiness-');
    file_put_contents($path, $dump);

    $preview = app(TokoLegacyImportPreviewService::class)->preview($path);

    expect($preview['readiness']['products']['ready'])->toBe(1)
        ->and($preview['readiness']['products']['issues'])->toBe(2)
        ->and($preview['readiness']['customers']['ready'])->toBe(1)
        ->and($preview['readiness']['vendors']['ready'])->toBe(1)
        ->and($preview['readiness']['opening_stock']['ready'])->toBe(1)
        ->and($preview['issues'])->toContain('barang: duplicate sku SKU000001 appears 2 times.')
        ->and($preview['issues'])->toContain('barang: row 2 has no product name.')
        ->and($preview['issues'])->toContain('pelanggan: row 2 has no customer name.')
        ->and($preview['issues'])->toContain('supplier: row 2 has no vendor name.');

    unlink($path);
});

test('toko legacy preview exposes unmapped table coverage gaps', function () {
    requireEnterpriseRuntimeSourceForTests('toko_pos');

    $dump = <<<'SQL'
INSERT INTO `barang` (`kode`, `sku`, `nama`, `sisa`) VALUES
('000001', 'SKU000001', 'Kapasitor AC', 5);
INSERT INTO `backup` (`id`, `nama`) VALUES
(1, 'backup-2026');
INSERT INTO `pengumuman` (`id`, `judul`) VALUES
(1, 'Libur toko'),(2, 'Stok opname');
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-coverage-');
    file_put_contents($path, $dump);

    $preview = app(TokoLegacyImportPreviewService::class)->preview($path);

    expect($preview['totals']['legacy_rows'])->toBe(4)
        ->and($preview['totals']['legacy_tables'])->toBe(3)
        ->and($preview['totals']['mapped_rows'])->toBe(1)
        ->and($preview['totals']['unmapped_rows'])->toBe(3)
        ->and($preview['totals']['unmapped_tables'])->toBe(2)
        ->and($preview['tables']['backup']['target'])->toBe('unmapped')
        ->and($preview['tables']['backup']['mapped'])->toBeFalse()
        ->and($preview['coverage']['unmapped'][0]['table'])->toBe('pengumuman')
        ->and($preview['coverage']['unmapped'][0]['rows'])->toBe(2);

    unlink($path);
});

test('toko legacy preview lists selectable dump sources', function () {
    requireEnterpriseRuntimeSourceForTests('toko_pos');

    $root = sys_get_temp_dir().'/toko-source-'.bin2hex(random_bytes(4));
    mkdir($root, 0777, true);
    file_put_contents($root.'/toko.sql', 'INSERT INTO `barang` (`kode`) VALUES (\'0001\');');
    file_put_contents($root.'/proplus.sql', '');

    $sources = app(TokoLegacyImportPreviewService::class)->availableDumpSources($root);

    expect($sources)->toHaveCount(3)
        ->and($sources[0]['key'])->toBe('toko')
        ->and($sources[0]['exists'])->toBeTrue()
        ->and($sources[0]['path'])->toBe($root.'/toko.sql')
        ->and($sources[1]['key'])->toBe('panh7986_toko')
        ->and($sources[1]['exists'])->toBeFalse()
        ->and($sources[2]['key'])->toBe('proplus')
        ->and($sources[2]['exists'])->toBeTrue()
        ->and($sources[2]['size_bytes'])->toBe(0);

    unlink($root.'/toko.sql');
    unlink($root.'/proplus.sql');
    rmdir($root);
});
