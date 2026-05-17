<?php

use App\Livewire\Admin\CommandCenter;
use App\Models\CustomFormSubmission;
use App\Models\CustomFormTemplate;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\SalesOpportunity;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WorkFromHomeRequest;
use App\Support\CommandCenterService;
use App\Support\MultiCompanyService;
use Livewire\Livewire;

test('command center route requires explicit permission', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::query()->create([
        'name' => 'Dashboard Only Command Center Regression',
        'slug' => 'dashboard_only_command_center_regression',
        'permissions' => ['admin.dashboard.view'],
    ]);

    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.command-center'))
        ->assertForbidden();
});

test('command center summarizes company scoped operational signals', function () {
    $companyA = app(MultiCompanyService::class)->createCompany('PT Command A');
    $companyB = app(MultiCompanyService::class)->createCompany('PT Command B');
    $admin = User::factory()->admin()->create(['company_id' => $companyA->id]);
    $employeeA = User::factory()->create(['company_id' => $companyA->id]);
    $employeeB = User::factory()->create(['company_id' => $companyB->id]);
    $role = Role::query()->create([
        'name' => 'Command Center Viewer',
        'slug' => 'command_center_viewer',
        'permissions' => ['admin.command_center.view'],
    ]);

    $admin->roles()->sync([$role->id]);

    WorkFromHomeRequest::query()->create([
        'company_id' => $companyA->id,
        'user_id' => $employeeA->id,
        'date' => now()->addDay()->toDateString(),
        'reason' => 'Client support',
        'status' => WorkFromHomeRequest::STATUS_PENDING,
    ]);
    WorkFromHomeRequest::query()->create([
        'company_id' => $companyB->id,
        'user_id' => $employeeB->id,
        'date' => now()->addDay()->toDateString(),
        'reason' => 'Other tenant support',
        'status' => WorkFromHomeRequest::STATUS_PENDING,
    ]);

    $templateA = CustomFormTemplate::query()->create([
        'company_id' => $companyA->id,
        'title' => 'Visit A',
        'fields' => [['key' => 'site', 'label' => 'Site', 'type' => 'text', 'required' => true, 'options' => []]],
        'is_active' => true,
    ]);
    $templateB = CustomFormTemplate::query()->create([
        'company_id' => $companyB->id,
        'title' => 'Visit B',
        'fields' => [['key' => 'site', 'label' => 'Site', 'type' => 'text', 'required' => true, 'options' => []]],
        'is_active' => true,
    ]);

    CustomFormSubmission::query()->create([
        'custom_form_template_id' => $templateA->id,
        'company_id' => $companyA->id,
        'submitted_by' => $employeeA->id,
        'status' => CustomFormSubmission::STATUS_SUBMITTED,
        'payload' => ['site' => 'Bandung'],
    ]);
    CustomFormSubmission::query()->create([
        'custom_form_template_id' => $templateB->id,
        'company_id' => $companyB->id,
        'submitted_by' => $employeeB->id,
        'status' => CustomFormSubmission::STATUS_SUBMITTED,
        'payload' => ['site' => 'Jakarta'],
    ]);

    $projectA = Project::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Project A',
        'status' => Project::STATUS_ACTIVE,
    ]);
    $projectB = Project::query()->create([
        'company_id' => $companyB->id,
        'name' => 'Project B',
        'status' => Project::STATUS_ACTIVE,
    ]);

    ProjectTask::query()->create([
        'company_id' => $companyA->id,
        'project_id' => $projectA->id,
        'title' => 'Overdue A',
        'status' => ProjectTask::STATUS_TODO,
        'due_date' => now()->subDay()->toDateString(),
    ]);
    ProjectTask::query()->create([
        'company_id' => $companyB->id,
        'project_id' => $projectB->id,
        'title' => 'Overdue B',
        'status' => ProjectTask::STATUS_TODO,
        'due_date' => now()->subDay()->toDateString(),
    ]);

    $productA = Product::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Low Stock A',
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 0,
        'stock_tracking' => true,
        'reorder_point' => 5,
    ]);
    $productB = Product::query()->create([
        'company_id' => $companyB->id,
        'name' => 'Low Stock B',
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 0,
        'stock_tracking' => true,
        'reorder_point' => 5,
    ]);

    StockMovement::query()->create([
        'company_id' => $companyA->id,
        'product_id' => $productA->id,
        'user_id' => $admin->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 3,
        'occurred_at' => now(),
    ]);
    StockMovement::query()->create([
        'company_id' => $companyB->id,
        'product_id' => $productB->id,
        'user_id' => $employeeB->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 3,
        'occurred_at' => now(),
    ]);

    Invoice::query()->create([
        'company_id' => $companyA->id,
        'number' => 'INV-A',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->subDays(10)->toDateString(),
        'due_at' => now()->subDay()->toDateString(),
        'subtotal' => 1000000,
        'tax_total' => 0,
        'grand_total' => 1000000,
    ]);
    Invoice::query()->create([
        'company_id' => $companyB->id,
        'number' => 'INV-B',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->subDays(10)->toDateString(),
        'due_at' => now()->subDay()->toDateString(),
        'subtotal' => 2000000,
        'tax_total' => 0,
        'grand_total' => 2000000,
    ]);

    SalesOpportunity::query()->create([
        'company_id' => $companyA->id,
        'owner_id' => $admin->id,
        'title' => 'Deal A',
        'stage' => SalesOpportunity::STAGE_PROPOSAL,
        'expected_value' => 5000000,
        'probability' => 60,
        'next_follow_up_at' => now()->subDay(),
    ]);
    SalesOpportunity::query()->create([
        'company_id' => $companyB->id,
        'owner_id' => $employeeB->id,
        'title' => 'Deal B',
        'stage' => SalesOpportunity::STAGE_PROPOSAL,
        'expected_value' => 7000000,
        'probability' => 60,
        'next_follow_up_at' => now()->subDay(),
    ]);

    $summary = app(CommandCenterService::class)->summary($admin->fresh());

    expect($summary['companies'])->toBe(1)
        ->and($summary['pending_wfh'])->toBe(1)
        ->and($summary['pending_forms'])->toBe(1)
        ->and($summary['overdue_project_tasks'])->toBe(1)
        ->and($summary['outstanding_invoices'])->toBe(1)
        ->and($summary['outstanding_invoice_total'])->toBe(1000000.0)
        ->and($summary['low_stock_products'])->toBe(1)
        ->and($summary['open_pipeline'])->toBe(5000000.0)
        ->and($summary['overdue_follow_ups'])->toBe(1);

    Livewire::actingAs($admin->fresh())
        ->test(CommandCenter::class)
        ->assertSee(__('Command Center'))
        ->assertSee(__('WFH Requests'))
        ->assertSee(__('Custom Form Reviews'));
});
