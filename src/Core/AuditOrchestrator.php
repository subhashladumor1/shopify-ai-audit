<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\Contracts\FindingNormalizerInterface;
use Subhash\ShopifyAiAudit\Contracts\RiskScorerInterface;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\AuditResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;

class AuditOrchestrator
{
    public function __construct(
        protected readonly ScannerRegistry $scannerRegistry,
        protected readonly FindingNormalizerInterface $normalizer = new FindingNormalizer,
        protected readonly RiskScorerInterface $riskScorer = new RiskScorer,
        protected readonly CoverageTracker $coverageTracker = new CoverageTracker
    ) {}

    public function run(AuditContext $context): AuditResult
    {
        $startTime = microtime(true);
        $applicableScanners = $this->scannerRegistry->resolveApplicable($context);

        /** @var array<int, ScanResult> $scanResults */
        $scanResults = [];

        foreach ($applicableScanners as $scanner) {
            $scanResults[] = $scanner->scan($context);
        }

        // Normalize and deduplicate findings
        $findings = $this->normalizer->normalize($scanResults);

        // Filter by minimum severity threshold
        $minWeight = $context->minSeverity->weight();
        $filteredFindings = array_filter(
            $findings,
            fn ($f) => $f->severity->weight() >= $minWeight
        );

        // Compute coverage report
        $totalFiles = $this->estimateTotalFiles($context->basePath);
        $coverage = $this->coverageTracker->compute($scanResults, $totalFiles);

        // Compute risk score
        $riskScore = $this->riskScorer->calculate($filteredFindings, $coverage);

        $durationMs = (microtime(true) - $startTime) * 1000.0;
        $auditId = 'aud_'.substr(md5(microtime().$context->basePath), 0, 16);

        return new AuditResult(
            auditId: $auditId,
            status: 'completed',
            manifest: $context->manifest,
            riskScore: $riskScore,
            coverage: $coverage,
            findings: array_values($filteredFindings),
            scanResults: $scanResults,
            durationMs: $durationMs,
            metadata: [
                'mode' => $context->mode,
                'scanners_executed' => count($applicableScanners),
                'php_version' => PHP_VERSION,
                'timestamp' => time(),
            ]
        );
    }

    protected function estimateTotalFiles(string $basePath): int
    {
        if (! is_dir($basePath)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $path = $item->getPathname();
                if (
                    str_contains($path, DIRECTORY_SEPARATOR.'vendor') ||
                    str_contains($path, DIRECTORY_SEPARATOR.'node_modules') ||
                    str_contains($path, DIRECTORY_SEPARATOR.'.git')
                ) {
                    continue;
                }
                $count++;
            }
        }

        return max(1, $count);
    }
}
