<?php

namespace Subhash\ShopifyAiAudit\Monitoring;

class TelemetryManager
{
    protected static bool $isHandlingRecursion = false;

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected static array $inMemoryEvents = [
        'requests' => [],
        'queries' => [],
        'api_calls' => [],
        'exceptions' => [],
        'ai_executions' => [],
    ];

    public function recordRequest(array $data): void
    {
        if (self::$isHandlingRecursion || ! config('shopify-ai-audit.monitoring.enabled', true)) {
            return;
        }

        self::$isHandlingRecursion = true;
        $sanitized = $this->redactSensitive($data);
        self::$inMemoryEvents['requests'][] = array_merge($sanitized, ['recorded_at' => time()]);
        self::$isHandlingRecursion = false;
    }

    public function recordQuery(string $sql, float $timeMs, string $connection = 'default'): void
    {
        if (self::$isHandlingRecursion || ! config('shopify-ai-audit.monitoring.enabled', true)) {
            return;
        }

        self::$isHandlingRecursion = true;
        $isNPlusOne = preg_match('/SELECT.*WHERE\s+[`"]?[a-zA-Z0-9_]+[`"]?\s*=\s*\?/i', $sql);

        self::$inMemoryEvents['queries'][] = [
            'sql' => $sql,
            'time_ms' => $timeMs,
            'connection' => $connection,
            'is_n_plus_one_candidate' => (bool) $isNPlusOne,
            'recorded_at' => time(),
        ];
        self::$isHandlingRecursion = false;
    }

    public function recordApiCall(string $provider, string $endpoint, int $status, float $durationMs, array $metadata = []): void
    {
        if (self::$isHandlingRecursion || ! config('shopify-ai-audit.monitoring.enabled', true)) {
            return;
        }

        self::$isHandlingRecursion = true;
        self::$inMemoryEvents['api_calls'][] = [
            'provider' => $provider,
            'endpoint' => $endpoint,
            'status' => $status,
            'duration_ms' => $durationMs,
            'metadata' => $metadata,
            'recorded_at' => time(),
        ];
        self::$isHandlingRecursion = false;
    }

    public function recordException(\Throwable $e): void
    {
        if (self::$isHandlingRecursion) {
            return;
        }

        self::$isHandlingRecursion = true;
        self::$inMemoryEvents['exceptions'][] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'recorded_at' => time(),
        ];
        self::$isHandlingRecursion = false;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function redactSensitive(array $data): array
    {
        $sensitiveKeys = ['password', 'secret', 'token', 'authorization', 'cookie', 'shpat_', 'card'];
        $result = [];

        foreach ($data as $key => $val) {
            $isSensitive = false;
            foreach ($sensitiveKeys as $sk) {
                if (str_contains(strtolower((string) $key), $sk)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $result[$key] = '[REDACTED]';
            } elseif (is_array($val)) {
                $result[$key] = $this->redactSensitive($val);
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function events(): array
    {
        return self::$inMemoryEvents;
    }

    public static function flush(): void
    {
        self::$inMemoryEvents = [
            'requests' => [],
            'queries' => [],
            'api_calls' => [],
            'exceptions' => [],
            'ai_executions' => [],
        ];
    }
}
