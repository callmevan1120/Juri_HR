<?php

namespace App\Support;

use App\Models\IntegrationDelivery;
use App\Models\IntegrationEndpoint;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class IntegrationWebhookService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return Collection<int, IntegrationDelivery>
     */
    public function dispatch(string $eventKey, array $payload): Collection
    {
        return $this->matchingEndpoints($eventKey)
            ->map(fn (IntegrationEndpoint $endpoint): IntegrationDelivery => $this->deliver($endpoint, $eventKey, $payload));
    }

    /**
     * @return Collection<int, IntegrationEndpoint>
     */
    public function matchingEndpoints(string $eventKey): Collection
    {
        return IntegrationEndpoint::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (IntegrationEndpoint $endpoint): bool => in_array('*', (array) $endpoint->event_keys, true)
                || in_array($eventKey, (array) $endpoint->event_keys, true))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function deliver(IntegrationEndpoint $endpoint, string $eventKey, array $payload): IntegrationDelivery
    {
        $timestamp = (string) now()->timestamp;
        $body = [
            'event' => $eventKey,
            'occurred_at' => now()->toISOString(),
            'data' => $payload,
        ];
        $signature = $this->signature($endpoint, $timestamp, $body);

        $delivery = IntegrationDelivery::query()->create([
            'integration_endpoint_id' => $endpoint->id,
            'event_key' => $eventKey,
            'payload' => $body,
            'signature' => $signature,
            'attempts' => 1,
        ]);

        try {
            $response = Http::timeout(8)
                ->withHeaders(array_merge(
                    (array) ($endpoint->headers ?? []),
                    [
                        'X-HRMS-Event' => $eventKey,
                        'X-HRMS-Timestamp' => $timestamp,
                        'X-HRMS-Signature' => $signature,
                    ],
                ))
                ->post($endpoint->url, $body)
                ->throw();

            $delivery->forceFill([
                'status' => IntegrationDelivery::STATUS_DELIVERED,
                'response_status' => $response->status(),
                'response_body' => str($response->body())->limit(2000)->toString(),
                'dispatched_at' => now(),
            ])->save();

            $endpoint->forceFill([
                'last_success_at' => now(),
                'last_error' => null,
                'failure_count' => 0,
            ])->save();
        } catch (RequestException $exception) {
            $this->markFailed($endpoint, $delivery, $exception->response?->status(), $exception->getMessage());
        } catch (\Throwable $exception) {
            $this->markFailed($endpoint, $delivery, null, $exception->getMessage());
        }

        return $delivery->fresh();
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function signature(IntegrationEndpoint $endpoint, string $timestamp, array $body): string
    {
        $secret = (string) ($endpoint->secret ?: config('app.key'));

        return 'sha256='.hash_hmac('sha256', $timestamp.'.'.json_encode($body, JSON_UNESCAPED_SLASHES), $secret);
    }

    protected function markFailed(IntegrationEndpoint $endpoint, IntegrationDelivery $delivery, ?int $status, string $error): void
    {
        $delivery->forceFill([
            'status' => IntegrationDelivery::STATUS_FAILED,
            'response_status' => $status,
            'response_body' => str($error)->limit(2000)->toString(),
            'failed_at' => now(),
        ])->save();

        $endpoint->forceFill([
            'last_failure_at' => now(),
            'last_error' => str($error)->limit(2000)->toString(),
            'failure_count' => $endpoint->failure_count + 1,
        ])->save();
    }
}
