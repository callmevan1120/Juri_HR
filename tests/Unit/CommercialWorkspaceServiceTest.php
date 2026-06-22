<?php

use App\Models\SalesOpportunity;
use App\Support\AccountingWorkspaceService;
use App\Support\CommercialWorkspaceService;

test('it returns correct opportunity stages', function () {
    $service = new CommercialWorkspaceService(Mockery::mock(AccountingWorkspaceService::class));
    $stages = $service->opportunityStages();

    expect($stages)->toContain(
        SalesOpportunity::STAGE_LEAD,
        SalesOpportunity::STAGE_QUALIFIED,
        SalesOpportunity::STAGE_PROPOSAL,
        SalesOpportunity::STAGE_WON,
        SalesOpportunity::STAGE_LOST
    )->toHaveCount(5);
});

test('it calculates quotation totals correctly', function () {
    $service = new CommercialWorkspaceService(Mockery::mock(AccountingWorkspaceService::class));

    $method = new ReflectionMethod(CommercialWorkspaceService::class, 'calculateTotals');

    $items = [
        ['quantity' => 2, 'unit_price' => 1000, 'tax_rate' => 10], // subtotal: 2000, tax: 200
        ['quantity' => 1, 'unit_price' => 500, 'tax_rate' => 0],  // subtotal: 500, tax: 0
    ];

    $result = $method->invoke($service, $items);

    expect($result)->toBe([
        'subtotal' => 2500.0,
        'tax_total' => 200.0,
        'grand_total' => 2700.0,
    ]);
});

test('it calculates vendor bill totals correctly', function () {
    $service = new CommercialWorkspaceService(Mockery::mock(AccountingWorkspaceService::class));

    $method = new ReflectionMethod(CommercialWorkspaceService::class, 'calculateVendorBillTotals');

    $items = [
        ['quantity' => 1, 'unit_cost' => 100, 'tax_rate' => 11, 'line_subtotal' => 100, 'line_total' => 111],
        ['quantity' => 2, 'unit_cost' => 50, 'tax_rate' => 0, 'line_subtotal' => 100, 'line_total' => 100],
    ];

    $result = $method->invoke($service, $items);

    expect($result)->toBe([
        'subtotal' => 200.0,
        'tax_total' => 11.0,
        'grand_total' => 211.0,
    ]);
});

test('it returns default probabilities for stages', function () {
    $service = new CommercialWorkspaceService(Mockery::mock(AccountingWorkspaceService::class));

    $method = new ReflectionMethod(CommercialWorkspaceService::class, 'defaultProbability');

    expect($method->invoke($service, SalesOpportunity::STAGE_LEAD))->toBe(25)
        ->and($method->invoke($service, SalesOpportunity::STAGE_QUALIFIED))->toBe(45)
        ->and($method->invoke($service, SalesOpportunity::STAGE_PROPOSAL))->toBe(70)
        ->and($method->invoke($service, SalesOpportunity::STAGE_WON))->toBe(100)
        ->and($method->invoke($service, SalesOpportunity::STAGE_LOST))->toBe(0)
        ->and($method->invoke($service, 'unknown'))->toBe(25);
});
