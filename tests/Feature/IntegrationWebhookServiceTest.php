<?php

use App\Models\IntegrationDelivery;
use App\Models\IntegrationEndpoint;
use App\Support\IntegrationWebhookService;
use Illuminate\Support\Facades\Http;

test('integration webhook service dispatches matching events with signature headers', function () {
    Http::fake([
        'https://accounting.example.test/hooks/hrms' => Http::response(['ok' => true], 202),
    ]);

    $endpoint = IntegrationEndpoint::create([
        'name' => 'Accounting Bridge',
        'provider' => IntegrationEndpoint::PROVIDER_ACCOUNTING,
        'event_keys' => ['payroll.generated'],
        'url' => 'https://accounting.example.test/hooks/hrms',
        'secret' => 'shared-secret',
        'headers' => ['X-Workspace' => 'finance'],
    ]);

    $deliveries = app(IntegrationWebhookService::class)->dispatch('payroll.generated', [
        'payroll_id' => 10,
        'period' => '2026-06',
    ]);

    expect($deliveries)->toHaveCount(1)
        ->and($deliveries->first()->status)->toBe(IntegrationDelivery::STATUS_DELIVERED)
        ->and($endpoint->fresh()->last_success_at)->not->toBeNull();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://accounting.example.test/hooks/hrms'
            && $request->hasHeader('X-HRMS-Event', 'payroll.generated')
            && $request->hasHeader('X-Workspace', 'finance')
            && str_starts_with($request->header('X-HRMS-Signature')[0] ?? '', 'sha256=')
            && $request['event'] === 'payroll.generated'
            && $request['data']['payroll_id'] === 10;
    });
});

test('integration webhook service records failed deliveries', function () {
    Http::fake([
        'https://gateway.example.test/hooks/*' => Http::response('unavailable', 503),
    ]);

    $endpoint = IntegrationEndpoint::create([
        'name' => 'Gateway',
        'provider' => IntegrationEndpoint::PROVIDER_WHATSAPP,
        'event_keys' => ['*'],
        'url' => 'https://gateway.example.test/hooks/hrms',
    ]);

    $delivery = app(IntegrationWebhookService::class)
        ->dispatch('leave.approved', ['leave_id' => 55])
        ->first();

    expect($delivery->status)->toBe(IntegrationDelivery::STATUS_FAILED)
        ->and($delivery->response_status)->toBe(503)
        ->and($endpoint->fresh()->failure_count)->toBe(1);
});
