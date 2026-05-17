<?php

namespace Webkul\ChannelConnector\Adapters;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Webkul\ChannelConnector\Contracts\ChannelAdapterContract;
use Webkul\ChannelConnector\ValueObjects\BatchSyncResult;
use Webkul\ChannelConnector\ValueObjects\ConnectionResult;
use Webkul\ChannelConnector\ValueObjects\RateLimitConfig;
use Webkul\ChannelConnector\ValueObjects\SyncResult;
use Webkul\Product\Contracts\Product;

abstract class AbstractChannelAdapter implements ChannelAdapterContract
{
    protected const RTL_LOCALES = [
        'ar_AE', 'ar_BH', 'ar_DZ', 'ar_EG', 'ar_IQ', 'ar_JO', 'ar_KW',
        'ar_LB', 'ar_LY', 'ar_MA', 'ar_OM', 'ar_QA', 'ar_SA', 'ar_SD',
        'ar_SY', 'ar_TN', 'ar_YE', 'he_IL', 'fa_IR', 'ur_PK',
    ];

    /**
     * Origin marker for change events. When sync work is triggered by an inbound
     * webhook, set this to ChangeOrigin::WEBHOOK so the outbound path can skip
     * re-pushing the same change back to the channel (loop prevention).
     */
    public const CHANGE_ORIGIN_LOCAL = 'local';

    public const CHANGE_ORIGIN_WEBHOOK = 'webhook';

    /**
     * Hard cap on how long handleRateLimitResponse() will sleep when a channel
     * returns 429 with a long Retry-After. Beyond this, the caller should fail
     * fast and let the job-level backoff reschedule the request.
     */
    protected const MAX_RATE_LIMIT_SLEEP_SECONDS = 120;

    protected array $credentials = [];

    protected ?int $connectorId = null;

    protected string $changeOrigin = self::CHANGE_ORIGIN_LOCAL;

    public function setCredentials(array $credentials): static
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function setConnectorId(int $connectorId): static
    {
        $this->connectorId = $connectorId;

        return $this;
    }

    /**
     * Tag this adapter run with its change origin (loop prevention).
     * Callers triggering sync from an inbound webhook MUST pass CHANGE_ORIGIN_WEBHOOK
     * so adapters can skip re-emitting the same change back to the channel.
     */
    public function setChangeOrigin(string $origin): static
    {
        $this->changeOrigin = $origin === self::CHANGE_ORIGIN_WEBHOOK
            ? self::CHANGE_ORIGIN_WEBHOOK
            : self::CHANGE_ORIGIN_LOCAL;

        return $this;
    }

    public function getChangeOrigin(): string
    {
        return $this->changeOrigin;
    }

    /**
     * True if this sync was triggered by an inbound webhook from the same channel.
     * Adapters should consult this before pushing the change back to the channel.
     */
    public function isOriginWebhook(): bool
    {
        return $this->changeOrigin === self::CHANGE_ORIGIN_WEBHOOK;
    }

    public function isRtlLocale(string $localeCode): bool
    {
        return in_array($localeCode, static::RTL_LOCALES);
    }

    /**
     * Returns true if the channel API returned a rate-limit response (HTTP 429).
     * Adapters MUST check this after every outbound HTTP request and, if true,
     * either retry via handleRateLimitResponse() or surface the error as a
     * SyncResult::failed so the job-level backoff can reschedule.
     */
    protected function isRateLimitResponse(Response $response): bool
    {
        return $response->status() === 429;
    }

    /**
     * Extracts the channel's Retry-After hint in seconds. Falls back to the
     * adapter's RateLimitConfig if the channel omitted the header.
     * Capped at MAX_RATE_LIMIT_SLEEP_SECONDS to prevent runaway sleeps.
     */
    protected function getRetryAfterSeconds(Response $response): int
    {
        $headerValue = $response->header('Retry-After');

        if ($headerValue !== null && $headerValue !== '') {
            // Retry-After can be a delta-seconds integer or an HTTP-date.
            if (is_numeric($headerValue)) {
                $seconds = (int) $headerValue;
            } else {
                $timestamp = strtotime($headerValue);
                $seconds = $timestamp !== false ? max(0, $timestamp - time()) : 60;
            }
        } else {
            // Channel didn't tell us; back off conservatively.
            $config = $this->getRateLimitConfig();
            $seconds = $config->requestsPerSecond > 0
                ? (int) ceil(1 / $config->requestsPerSecond) * 5
                : 60;
        }

        return max(1, min($seconds, self::MAX_RATE_LIMIT_SLEEP_SECONDS));
    }

    /**
     * Honour a 429 rate-limit response: sleep for the channel-specified window,
     * then return so the caller can retry the request. Logs the event with
     * connector context but never credentials.
     *
     * For most callers, prefer executeWithRateLimitRetry() which handles the
     * detect-sleep-retry loop in one call.
     */
    protected function handleRateLimitResponse(Response $response): void
    {
        $seconds = $this->getRetryAfterSeconds($response);

        Log::warning('[ChannelConnector] Channel rate limit hit; backing off', [
            'adapter'       => static::class,
            'connector_id'  => $this->connectorId,
            'sleep_seconds' => $seconds,
            'http_status'   => $response->status(),
        ]);

        sleep($seconds);
    }

    /**
     * Execute an HTTP request with automatic 429 rate-limit handling.
     *
     * On a 429 response, sleeps for the Retry-After window and retries up to
     * $maxAttempts - 1 additional times. Returns the final Response (which may
     * still be 429 if retries are exhausted — caller decides how to surface that
     * to the user, typically via SyncResult::failed()).
     *
     * Usage in an adapter:
     *   $response = $this->executeWithRateLimitRetry(
     *       fn () => Http::withToken($accessToken)->post($url, $payload)
     *   );
     *
     * The callable should return an Illuminate\Http\Client\Response.
     */
    protected function executeWithRateLimitRetry(callable $request, int $maxAttempts = 2): Response
    {
        $attempt = 0;

        while (true) {
            $attempt++;
            $response = $request();

            if (! $this->isRateLimitResponse($response)) {
                return $response;
            }

            if ($attempt >= $maxAttempts) {
                Log::warning('[ChannelConnector] Rate limit retries exhausted', [
                    'adapter'      => static::class,
                    'attempts'     => $attempt,
                    'connector_id' => $this->connectorId,
                ]);

                return $response;
            }

            $this->handleRateLimitResponse($response);
        }
    }

    public function syncProducts(Collection $products, array $localeMappedData): BatchSyncResult
    {
        $results = [];
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($products as $product) {
            $productData = $localeMappedData[$product->id] ?? null;

            if ($productData === null) {
                $skippedCount++;

                continue;
            }

            $this->throttle();

            $result = $this->syncProduct($product, $productData);
            $results[] = $result;

            if ($result->success) {
                $successCount++;
            } else {
                $failedCount++;
                $errors = array_merge($errors, $result->errors);
            }
        }

        return new BatchSyncResult(
            totalProcessed: count($results) + $skippedCount,
            successCount: $successCount,
            failedCount: $failedCount,
            skippedCount: $skippedCount,
            results: $results,
            errors: $errors,
        );
    }

    protected function throttle(?RateLimitConfig $config = null): void
    {
        $config = $config ?? $this->getRateLimitConfig();

        if (! $config->requestsPerSecond) {
            return;
        }

        $key = 'channel_connector_throttle_'.static::class;

        $executed = RateLimiter::attempt($key, $config->requestsPerSecond, fn () => true, 1);

        if (! $executed) {
            $seconds = RateLimiter::availableIn($key);
            Log::debug('[ChannelConnector] Rate limit throttle', ['adapter' => static::class, 'delay_seconds' => $seconds]);
            usleep($seconds * 1_000_000);
        }
    }

    abstract public function testConnection(array $credentials): ConnectionResult;

    abstract public function syncProduct(Product $product, array $localeMappedData): SyncResult;

    abstract public function fetchProduct(string $externalId, ?string $locale = null): ?array;

    abstract public function deleteProduct(string $externalId): bool;

    abstract public function getChannelFields(?string $locale = null): array;

    abstract public function getSupportedLocales(): array;

    abstract public function getSupportedCurrencies(): array;

    abstract public function registerWebhooks(array $events, string $callbackUrl): bool;

    abstract public function verifyWebhook(Request $request): bool;

    abstract public function refreshCredentials(): ?array;

    abstract public function getRateLimitConfig(): RateLimitConfig;
}
