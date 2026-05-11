<?php

use App\Models\Company;
use App\Models\EmployeeDocumentTemplate;
use App\Models\User;
use App\Support\DocumentTemplateMarketplaceService;
use App\Support\MultiCompanyService;

test('multi company service creates tenant records and assigns users', function () {
    $owner = User::factory()->admin()->create();
    $service = app(MultiCompanyService::class);

    $company = $service->createCompany('PT Pandan Teknik', $owner, ['segment' => 'vendor']);
    $secondCompany = $service->createCompany('PT Pandan Teknik');

    expect($company->slug)->toBe('pt-pandan-teknik')
        ->and($secondCompany->slug)->toBe('pt-pandan-teknik-2')
        ->and($owner->fresh()->company_id)->toBe($company->id)
        ->and($service->suspend($company)->status)->toBe(Company::STATUS_SUSPENDED);
});

test('document template marketplace seeds and installs reusable HR templates', function () {
    $admin = User::factory()->admin()->create();
    $service = app(DocumentTemplateMarketplaceService::class);

    $seeded = $service->seedDefaultTemplates();
    $marketplaceTemplate = $service->published('contract')->first();
    $installed = $service->install($marketplaceTemplate, $admin, 'Contract - Operations');

    expect($seeded)->toHaveCount(3)
        ->and($marketplaceTemplate)->toBeInstanceOf(EmployeeDocumentTemplate::class)
        ->and($installed->is_marketplace)->toBeFalse()
        ->and($installed->source_template_id)->toBe($marketplaceTemplate->id)
        ->and($installed->created_by)->toBe($admin->id)
        ->and($service->published())->toHaveCount(3);
});
