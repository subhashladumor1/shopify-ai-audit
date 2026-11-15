<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class AuditResult
{
    /**
     * @param  array<int, AuditFinding>  $findings
     * @param  array<int, ScanResult>  $scanResults
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $auditId,
        public readonly string $status,
        public readonly ProjectManifest $manifest,
        public readonly RiskScore $riskScore,
        public readonly CoverageReport $coverage,
        public readonly array $findings = [],
        public readonly array $scanResults = [],
        public readonly float $durationMs = 0.0,
        public readonly array $metadata = [],
    ) {}

    public function isClean(): bool
    {
        return empty($this->findings);
    }

    public function criticalCount(): int
    {
        return count(array_filter($this->findings, fn ($f) => $f->severity->value === 'critical'));
    }

    public function highCount(): int
    {
        return count(array_filter($this->findings, fn ($f) => $f->severity->value === 'high'));
    }

    public function mediumCount(): int
    {
        return count(array_filter($this->findings, fn ($f) => $f->severity->value === 'medium'));
    }

    public function lowCount(): int
    {
        return count(array_filter($this->findings, fn ($f) => $f->severity->value === 'low'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummaryArray(): array
    {
        return [
            'audit_id' => $this->auditId,
            'status' => $this->status,
            'duration_ms' => round($this->durationMs, 2),
            'risk' => $this->riskScore->toArray(),
            'coverage' => $this->coverage->toArray(),
            'findings_count' => count($this->findings),
            'critical' => $this->criticalCount(),
            'high' => $this->highCount(),
            'medium' => $this->mediumCount(),
            'low' => $this->lowCount(),
        ];
    }
}
