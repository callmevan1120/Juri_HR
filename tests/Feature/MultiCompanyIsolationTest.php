<?php

use App\Exports\LeaveRequestsExport;
use App\Exports\PayrollSummaryExport;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\CompanyAsset;
use App\Models\EmployeeDocumentRequest;
use App\Models\HrChecklistTemplate;
use App\Models\HrChecklistTemplateItem;
use App\Models\ImportExportRun;
use App\Models\Payroll;
use App\Models\Reimbursement;
use App\Models\Role;
use App\Models\User;
use App\Support\HrChecklistService;
use App\Support\MultiCompanyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

function tenantFixture(): array
{
    $tenants = app(MultiCompanyService::class);

    $adminA = User::factory()->admin()->create();
    $companyA = $tenants->createCompany('PT Isolation A', $adminA);
    $adminB = User::factory()->admin()->create();
    $companyB = $tenants->createCompany('PT Isolation B', $adminB);

    $employeeA = User::factory()->create(['company_id' => $companyA->id]);
    $employeeB = User::factory()->create(['company_id' => $companyB->id]);

    return compact('adminA', 'adminB', 'companyA', 'companyB', 'employeeA', 'employeeB');
}

test('company scoped user and attendance queries never include another tenant', function () {
    ['adminA' => $adminA, 'employeeA' => $employeeA, 'employeeB' => $employeeB] = tenantFixture();

    $attendanceA = Attendance::create([
        'user_id' => $employeeA->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);
    $attendanceB = Attendance::create([
        'user_id' => $employeeB->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);

    expect(User::query()->managedBy($adminA)->pluck('id')->all())
        ->toContain($employeeA->id)
        ->not->toContain($employeeB->id);

    expect(Attendance::query()->managedBy($adminA)->pluck('id')->all())
        ->toContain($attendanceA->id)
        ->not->toContain($attendanceB->id);
});

test('company scoped policies deny cross tenant sensitive HR finance and asset resources', function () {
    ['adminA' => $adminA, 'employeeA' => $employeeA, 'employeeB' => $employeeB] = tenantFixture();

    $attendanceB = Attendance::create([
        'user_id' => $employeeB->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);
    $payrollB = Payroll::create([
        'user_id' => $employeeB->id,
        'month' => now()->month,
        'year' => now()->year,
        'basic_salary' => 1000000,
        'allowances' => [],
        'deductions' => [],
        'overtime_pay' => 0,
        'net_salary' => 1000000,
        'status' => 'paid',
    ]);
    $reimbursementB = Reimbursement::create([
        'user_id' => $employeeB->id,
        'date' => now()->toDateString(),
        'type' => 'medical',
        'amount' => 100000,
        'description' => 'Tenant B claim',
        'status' => 'pending',
    ]);
    $cashAdvanceB = CashAdvance::create([
        'user_id' => $employeeB->id,
        'amount' => 100000,
        'purpose' => 'Tenant B advance',
        'status' => 'pending',
        'payment_month' => now()->month,
        'payment_year' => now()->year,
    ]);
    $assetB = CompanyAsset::create([
        'name' => 'Tenant B Laptop',
        'type' => 'Laptop',
        'user_id' => $employeeB->id,
        'status' => CompanyAsset::STATUS_ASSIGNED,
    ]);

    expect(Gate::forUser($adminA)->denies('view', $attendanceB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('download', $payrollB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('view', $reimbursementB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('approve', $reimbursementB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('view', $cashAdvanceB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('approve', $cashAdvanceB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('view', $assetB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('returnAsset', $assetB))->toBeTrue();

    $attendanceA = Attendance::create([
        'user_id' => $employeeA->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);

    expect(Gate::forUser($adminA)->allows('view', $attendanceA))->toBeTrue();
});

test('company scoped document HR checklist and import export downloads deny other tenants', function () {
    Storage::fake('local');

    ['adminA' => $adminA, 'adminB' => $adminB, 'employeeB' => $employeeB] = tenantFixture();

    $template = HrChecklistTemplate::create([
        'type' => HrChecklistTemplate::TYPE_ONBOARDING,
        'name' => 'Tenant Checklist',
        'is_active' => true,
        'created_by' => $adminB->id,
    ]);
    $template->items()->create([
        'title' => 'Tenant task',
        'category' => 'security',
        'default_assignee_type' => HrChecklistTemplateItem::ASSIGNEE_HR,
        'due_offset_days' => 0,
        'is_required' => true,
        'sort_order' => 1,
    ]);
    $caseB = app(HrChecklistService::class)->createCase($employeeB, $template->fresh('items'), $adminB, now());
    $taskB = $caseB->tasks()->firstOrFail();

    Storage::disk('local')->put('documents/tenant-b.pdf', 'tenant-b');
    $documentRequestB = EmployeeDocumentRequest::create([
        'user_id' => $employeeB->id,
        'document_type' => EmployeeDocumentRequest::TYPE_EMPLOYMENT_CERTIFICATE,
        'requested_by' => $employeeB->id,
        'request_source' => EmployeeDocumentRequest::SOURCE_EMPLOYEE,
        'purpose' => 'Tenant B',
        'status' => EmployeeDocumentRequest::STATUS_READY,
        'generated_path' => 'documents/tenant-b.pdf',
        'generated_at' => now(),
    ]);

    $role = Role::create([
        'name' => 'Tenant Export Admin',
        'slug' => 'tenant_export_admin',
        'description' => 'Can export tenant data.',
        'permissions' => ['admin.import_export_users.export'],
    ]);
    $adminA->roles()->syncWithoutDetaching([$role->id]);

    $runB = ImportExportRun::create([
        'resource' => 'users',
        'operation' => 'export',
        'status' => 'completed',
        'requested_by_user_id' => $adminB->id,
        'file_disk' => 'local',
        'file_path' => 'exports/tenant-b.xlsx',
        'file_name' => 'tenant-b.xlsx',
    ]);

    expect(Gate::forUser($adminA)->denies('view', $caseB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('view', $taskB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('download', $documentRequestB))->toBeTrue()
        ->and(Gate::forUser($adminA)->denies('download', $runB))->toBeTrue();
});

test('tenant scoped activity log reporting can be constrained by actor company', function () {
    ['adminA' => $adminA, 'employeeA' => $employeeA, 'employeeB' => $employeeB] = tenantFixture();

    $logA = ActivityLog::create([
        'user_id' => $employeeA->id,
        'action' => 'Tenant A',
        'description' => 'Tenant A action',
    ]);
    $logB = ActivityLog::create([
        'user_id' => $employeeB->id,
        'action' => 'Tenant B',
        'description' => 'Tenant B action',
    ]);

    $visibleLogIds = ActivityLog::query()
        ->whereHas('user', fn ($query) => $query->managedBy($adminA))
        ->pluck('id')
        ->all();

    expect($visibleLogIds)
        ->toContain($logA->id)
        ->not->toContain($logB->id);
});

test('company scoped report exports exclude other tenant rows', function () {
    ['adminA' => $adminA, 'employeeA' => $employeeA, 'employeeB' => $employeeB] = tenantFixture();

    $leaveA = Attendance::create([
        'user_id' => $employeeA->id,
        'date' => now()->toDateString(),
        'status' => 'sick',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);
    $leaveB = Attendance::create([
        'user_id' => $employeeB->id,
        'date' => now()->toDateString(),
        'status' => 'sick',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);
    $payrollA = Payroll::create([
        'user_id' => $employeeA->id,
        'month' => now()->month,
        'year' => now()->year,
        'basic_salary' => 1000000,
        'allowances' => [],
        'deductions' => [],
        'overtime_pay' => 0,
        'net_salary' => 1000000,
        'status' => 'paid',
    ]);
    $payrollB = Payroll::create([
        'user_id' => $employeeB->id,
        'month' => now()->month,
        'year' => now()->year,
        'basic_salary' => 1000000,
        'allowances' => [],
        'deductions' => [],
        'overtime_pay' => 0,
        'net_salary' => 1000000,
        'status' => 'paid',
    ]);

    expect((new LeaveRequestsExport($adminA))->query()->pluck('id')->all())
        ->toContain($leaveA->id)
        ->not->toContain($leaveB->id);

    expect((new PayrollSummaryExport($adminA))->query()->pluck('id')->all())
        ->toContain($payrollA->id)
        ->not->toContain($payrollB->id);
});
