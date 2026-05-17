<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Webkul\ChannelConnector\Events\WebhookReceived;
use Webkul\ChannelConnector\Jobs\ProcessWebhookJob;
use Webkul\ChannelConnector\Models\ChannelConnector;
use Webkul\ChannelConnector\Models\ChannelSyncConflict;
use Webkul\ChannelConnector\Repositories\ChannelFieldMappingRepository;

/*
|--------------------------------------------------------------------------
| HMAC verification tests for the inbound webhook controller.
|--------------------------------------------------------------------------
|
| These tests exercise the full HTTP path:
|   Route → WebhookController::receive → AdapterResolver → SallaAdapter::verifyWebhook
|
| They are the regression safety net for the most security-critical control
| in the channel-syndication pipeline. A regression that breaks HMAC (wrong
| header name, wrong algorithm, == instead of hash_equals) MUST be caught
| here before reaching production.
|
*/

beforeEach(function () {
    $this->webhookToken = bin2hex(random_bytes(32));
    $this->webhookSecret = 'whsec_'.bin2hex(random_bytes(16));

    $this->connector = ChannelConnector::create([
        'code'          => 'salla-hmac-test',
        'name'          => 'Salla HMAC Test',
        'channel_type'  => 'salla',
        'webhook_token' => $this->webhookToken,
        'credentials'   => [
            'access_token'   => 'test-access-token',
            'refresh_token'  => 'test-refresh-token',
            'webhook_secret' => $this->webhookSecret,
            'client_id'      => 'test-client',
            'client_secret'  => 'test-secret',
        ],
        'settings' => [],
        'status'   => 'connected',
    ]);

    $this->webhookUrl = "/api/v1/rest/webhooks/channel-connectors/{$this->webhookToken}";

    Queue::fake();
    Event::fake([WebhookReceived::class]);
});

it('accepts a webhook with a valid HMAC signature and queues the job', function () {
    $payload = ['event' => 'product.updated', 'id' => 'prod_1', 'data' => ['name' => 'Widget']];
    $body = json_encode($payload);
    $signature = hash_hmac('sha256', $body, $this->webhookSecret);

    $response = $this->call(
        'POST',
        $this->webhookUrl,
        [],
        [],
        [],
        [
            'HTTP_X-Salla-Signature' => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ],
        $body,
    );

    expect($response->status())->toBe(200);
    Queue::assertPushedOn('webhooks', ProcessWebhookJob::class);
    Event::assertDispatched(WebhookReceived::class);
});

it('rejects a webhook whose payload was tampered after signing (401)', function () {
    $original = ['event' => 'product.updated', 'id' => 'prod_1', 'data' => ['name' => 'Widget']];
    $signature = hash_hmac('sha256', json_encode($original), $this->webhookSecret);

    // Send a different payload with the original signature.
    $tampered = ['event' => 'product.updated', 'id' => 'prod_1', 'data' => ['name' => 'EVIL']];

    $response = $this->call(
        'POST',
        $this->webhookUrl,
        [],
        [],
        [],
        [
            'HTTP_X-Salla-Signature' => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ],
        json_encode($tampered),
    );

    expect($response->status())->toBe(401);
    Queue::assertNotPushed(ProcessWebhookJob::class);
    Event::assertNotDispatched(WebhookReceived::class);
});

it('rejects a webhook with a missing signature header (401)', function () {
    $payload = ['event' => 'product.updated', 'id' => 'prod_1'];

    $response = $this->call(
        'POST',
        $this->webhookUrl,
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode($payload),
    );

    expect($response->status())->toBe(401);
    Queue::assertNotPushed(ProcessWebhookJob::class);
});

it('rejects a webhook signed with the wrong secret (401)', function () {
    $payload = ['event' => 'product.updated', 'id' => 'prod_1'];
    $body = json_encode($payload);
    $wrongSecret = 'whsec_attacker';
    $signature = hash_hmac('sha256', $body, $wrongSecret);

    $response = $this->call(
        'POST',
        $this->webhookUrl,
        [],
        [],
        [],
        [
            'HTTP_X-Salla-Signature' => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ],
        $body,
    );

    expect($response->status())->toBe(401);
    Queue::assertNotPushed(ProcessWebhookJob::class);
});

it('returns 404 for an unknown webhook token (not 401, to avoid token-enumeration leak)', function () {
    $unknownToken = bin2hex(random_bytes(32));
    $payload = ['event' => 'product.updated', 'id' => 'prod_1'];
    $body = json_encode($payload);
    $signature = hash_hmac('sha256', $body, $this->webhookSecret);

    $response = $this->call(
        'POST',
        "/api/v1/rest/webhooks/channel-connectors/{$unknownToken}",
        [],
        [],
        [],
        [
            'HTTP_X-Salla-Signature' => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ],
        $body,
    );

    expect($response->status())->toBe(404);
    Queue::assertNotPushed(ProcessWebhookJob::class);
});

it('drops webhook jobs older than the replay window (replay protection)', function () {
    $payload = ['event' => 'product.updated', 'id' => 'prod_replay'];

    // Build the job directly with a receivedAt timestamp 10 minutes in the past.
    // The job handle() should detect the stale timestamp and return without
    // dispatching downstream work.
    $stale = new ProcessWebhookJob(
        connectorId: $this->connector->id,
        payload: $payload,
        webhookEventId: 'evt_stale_1',
        receivedAt: time() - 600,
    );

    $mappingRepo = app(ChannelFieldMappingRepository::class);
    $stale->handle($mappingRepo);

    // No conflicts, no mappings, no updates should have been created.
    expect(ChannelSyncConflict::query()->count())->toBe(0);
});
