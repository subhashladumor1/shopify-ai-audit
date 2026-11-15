<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class ScanResult
{
    /**
     * @param  array<int, RuleResult>  $ruleResults
     * @param  array<int, string>  $scannedFiles
     * @param  array<string, string>  $skippedFiles
     * @param  array<string, mixed>  $telemetry
     */
    public function __construct(
        public readonly string $scannerClass,
        public readonly array $ruleResults,
        public readonly array $scannedFiles = [],
        public readonly array $skippedFiles = [],
        public readonly float $durationMs = 0.0,
        public readonly array $telemetry = [],
    ) {}

    public function passedCount(): int
    {
        return count(array_filter($this->ruleResults, fn ($r) => $r->outcome->value === 'passed'));
    }

    public function failedCount(): int
    {
        return count(array_filter($this->ruleResults, fn ($r) => $r->outcome->value === 'failed'));
    }
}
