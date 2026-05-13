<?php

test('roadmap 10 coverage claims are tied to concrete tests and public docs', function () {
    $requiredPaths = [
        'tests/Feature/ManagerInboxAuthorizationTest.php',
        'tests/Feature/SecurityMatrixTest.php',
        'tests/Feature/SecurityIsolationMatrixTest.php',
        'tests/Feature/AdminRouteSplitAndHealthTest.php',
        'tests/Feature/AttendanceIntegrationApiTest.php',
        'tests/Feature/OfflineAttendanceSyncTest.php',
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
        ->toContain('device:offline-attendance');
});
