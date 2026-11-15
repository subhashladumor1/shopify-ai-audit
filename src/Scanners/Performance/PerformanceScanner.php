<?php

namespace Subhash\ShopifyAiAudit\Scanners\Performance;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class PerformanceScanner implements AuditScanner
{
    public function id(): string
    {
        return 'performance';
    }

    public function name(): string
    {
        return 'Performance & Core Web Vitals Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('performance');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        $viewsPath = $basePath.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';
        if (is_dir($viewsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($viewsPath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $scannedFiles[] = $file->getPathname();
                    $content = (string) file_get_contents($file->getPathname());
                    $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $file->getPathname());

                    // Check PERF-001: N+1 lazy loading inside @foreach
                    if (preg_match('/@foreach\s*\([^)]+\)(?:(?!@endforeach).)*?\$[a-zA-Z0-9_]+->([a-zA-Z0-9_]+)->[a-zA-Z0-9_]+/s', $content, $n1Matches)) {
                        $ruleResults[] = RuleResult::failed(
                            ruleId: 'PERF-001',
                            message: 'Potential N+1 database query in Blade loop: accessing nested relationship without eager loading.',
                            severity: Severity::MEDIUM,
                            confidence: Confidence::MEDIUM,
                            filePath: $relPath,
                            recommendation: 'Eager-load the relationship in your controller using Model::with("relation")->get()',
                            verdict: VerificationVerdict::NEEDS_VALIDATION
                        );
                    }
                }
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('PERF-001', 'Performance checks completed without detected anti-patterns.');
        }

        $durationMs = (microtime(true) - $startTime) * 1000.0;

        return new ScanResult(
            scannerClass: static::class,
            ruleResults: $ruleResults,
            scannedFiles: $scannedFiles,
            skippedFiles: $skippedFiles,
            durationMs: $durationMs
        );
    }
}
