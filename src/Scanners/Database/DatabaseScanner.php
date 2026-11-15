<?php

namespace Subhash\ShopifyAiAudit\Scanners\Database;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class DatabaseScanner implements AuditScanner
{
    public function id(): string
    {
        return 'database';
    }

    public function name(): string
    {
        return 'Database & Query Engine Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('database');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        // Check routes or web server configs for phpMyAdmin exposure
        $routesPath = $basePath.DIRECTORY_SEPARATOR.'routes';
        if (is_dir($routesPath)) {
            $routeFiles = glob($routesPath.DIRECTORY_SEPARATOR.'*.php') ?: [];
            foreach ($routeFiles as $rf) {
                $scannedFiles[] = $rf;
                $content = (string) file_get_contents($rf);
                if (preg_match('/[\'"]\/(phpmyadmin|pma|myadmin)[\'"]/i', $content, $pmaMatches)) {
                    $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $rf);
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'DATABASE-001',
                        message: 'phpMyAdmin endpoint publicly routed: '.$pmaMatches[0],
                        severity: Severity::CRITICAL,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        recommendation: 'Do not expose phpMyAdmin or database administration tools on public routes without VPN / IP allowlisting.',
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }
        }

        // Check migrations for foreign keys without index
        $migPath = $basePath.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
        if (is_dir($migPath)) {
            $migFiles = glob($migPath.DIRECTORY_SEPARATOR.'*.php') ?: [];
            foreach ($migFiles as $mf) {
                $scannedFiles[] = $mf;
                $content = (string) file_get_contents($mf);
                if (str_contains($content, 'foreignId(') && ! str_contains($content, '->index()') && ! str_contains($content, '->constrained()')) {
                    $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $mf);
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'DATABASE-002',
                        message: 'Foreign key column missing index in migration.',
                        severity: Severity::MEDIUM,
                        confidence: Confidence::MEDIUM,
                        filePath: $relPath,
                        recommendation: 'Use ->constrained()->index() to prevent table lockups and slow foreign key lookups.',
                        verdict: VerificationVerdict::NEEDS_VALIDATION
                    );
                }
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('DATABASE-001', 'Database configuration and schema checks passed.');
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
