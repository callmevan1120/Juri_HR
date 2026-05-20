<?php

test('roadmap 10 coverage claims are tied to concrete tests and public docs', function () {
    $requiredPaths = [
        'tests/Feature/ManagerInboxAuthorizationTest.php',
        'tests/Feature/SecurityMatrixTest.php',
        'tests/Feature/SecurityIsolationMatrixTest.php',
        'tests/Feature/AdminRouteSplitAndHealthTest.php',
        'tests/Feature/AttendanceIntegrationApiTest.php',
        'tests/Feature/OfflineAttendanceSyncTest.php',
        'config/feature_maturity.php',
        'app/Console/Commands/FeatureMaturityAudit.php',
        'guides/attendance-integration.md',
        'RELEASE_CHECKLIST.md',
    ];

    foreach ($requiredPaths as $path) {
        expect(is_file(base_path($path)))->toBeTrue($path.' is missing');
    }

    $roadmap = file_get_contents(base_path('guides/roadmap-10-coverage.md'));

    expect($roadmap)
        ->toContain('Proof Matrix')
        ->toContain('tests/Feature/AttendanceIntegrationApiTest.php')
        ->toContain('tests/Feature/OfflineAttendanceSyncTest.php')
        ->toContain('guides/attendance-integration.md')
        ->toContain('php artisan feature:maturity')
        ->toContain('device:offline-attendance');
});

test('feature maturity audit is evidence backed and intentionally conservative', function () {
    $summary = app(\App\Support\FeatureMaturityMatrix::class)->summary();

    expect($summary['score'])->toBeGreaterThanOrEqual(80)
        ->and($summary['score'])->toBeLessThan($summary['target'])
        ->and($summary['missing_evidence'])->toBeEmpty()
        ->and($summary['production_ready'])->toBeGreaterThanOrEqual(4)
        ->and($summary['not_release_ready'])->toBeGreaterThanOrEqual(1);

    $this->artisan('feature:maturity')
        ->expectsOutputToContain('Overall score:')
        ->assertSuccessful();
});
