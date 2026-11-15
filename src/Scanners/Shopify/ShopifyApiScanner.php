<?php

namespace Subhash\ShopifyAiAudit\Scanners\Shopify;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;
use Subhash\ShopifyAiAudit\Shopify\API\ApiRegistry;

class ShopifyApiScanner implements AuditScanner
{
    public function __construct(
        protected readonly ApiRegistry $apiRegistry = new ApiRegistry
    ) {}

    public function id(): string
    {
        return 'shopify_api';
    }

    public function name(): string
    {
        return 'Shopify API Intelligence & Deprecation Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('shopify_api');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        $targetDirs = ['app', 'config', 'resources', 'routes'];
        $filesToScan = [];

        foreach ($targetDirs as $dir) {
            $fullDir = $basePath.DIRECTORY_SEPARATOR.$dir;
            if (is_dir($fullDir)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($fullDir, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $ext = strtolower($file->getExtension());
                        if (in_array($ext, ['php', 'js', 'ts', 'tsx', 'blade.php'], true)) {
                            $filesToScan[] = $file->getPathname();
                        }
                    }
                }
            }
        }

        foreach ($filesToScan as $filePath) {
            $scannedFiles[] = $filePath;
            $content = (string) file_get_contents($filePath);
            $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $filePath);

            // Check SHOPIFY-API-002: Legacy Admin REST API usage
            if (preg_match('/\/admin\/api\/(20[0-9]{2}-[0-9]{2})\/[a-zA-Z0-9_\-]+\.json/', $content, $restMatches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($content, 0, $restMatches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-API-002',
                    message: "Legacy Shopify Admin REST endpoint detected ({$restMatches[0][0]}). REST Admin API is deprecated by Shopify.",
                    severity: Severity::HIGH,
                    confidence: Confidence::HIGH,
                    filePath: $relPath,
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['endpoint' => $restMatches[0][0]],
                    recommendation: 'Migrate to the Shopify Admin GraphQL API to ensure long-term stability and access to new Shopify features.',
                    suggestedFix: 'https://{shop}.myshopify.com/admin/api/2025-01/graphql.json',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }

            // Check SHOPIFY-API-001: Deprecated API Version
            if (preg_match('/[\'"](20[1-2][0-9]-[0-1][0-9])[\'"]/', $content, $verMatches, PREG_OFFSET_CAPTURE)) {
                $detectedVer = $verMatches[1][0];
                $verCheck = $this->apiRegistry->checkVersion($detectedVer);
                if ($verCheck['is_deprecated']) {
                    $line = substr_count(substr($content, 0, $verMatches[0][1]), "\n") + 1;
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'SHOPIFY-API-001',
                        message: "Deprecated Shopify API version {$detectedVer} detected in code. This version has reached its sunset date.",
                        severity: Severity::HIGH,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        lineStart: $line,
                        lineEnd: $line,
                        evidence: ['version' => $detectedVer, 'sunset_date' => $verCheck['sunset_date']],
                        recommendation: 'Upgrade to a currently supported quarterly version (e.g. 2025-01).',
                        suggestedFix: "'2025-01'",
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }

            // Check SHOPIFY-API-003: Storefront Private Token Frontend Leak
            if (str_ends_with($filePath, '.js') || str_ends_with($filePath, '.ts') || str_ends_with($filePath, '.tsx')) {
                if (preg_match('/(shpat_[a-fA-F0-9]{32,}|shopify_private_token|private_access_token)/i', $content, $leakMatches, PREG_OFFSET_CAPTURE)) {
                    $line = substr_count(substr($content, 0, $leakMatches[0][1]), "\n") + 1;
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'SHOPIFY-API-003',
                        message: 'Private Shopify access token or secret exposed in client-side frontend bundle.',
                        severity: Severity::CRITICAL,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        lineStart: $line,
                        lineEnd: $line,
                        evidence: ['match' => substr($leakMatches[0][0], 0, 10).'...[REDACTED]'],
                        recommendation: 'Storefront clients must only use public Storefront Access Tokens (X-Shopify-Storefront-Access-Token), never Admin or Private tokens.',
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }

            // Check SHOPIFY-API-005: ShopifyQL Raw Parameter Injection
            if (str_contains($content, 'shopifyqlQuery') && preg_match('/shopifyqlQuery\s*\([^)]*\$|\"SELECT\s+.*\"/', $content, $qlMatches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($content, 0, $qlMatches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-API-005',
                    message: 'Potential string interpolation detected in ShopifyQL query. Risk of query tampering.',
                    severity: Severity::HIGH,
                    confidence: Confidence::MEDIUM,
                    filePath: $relPath,
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['snippet' => substr($qlMatches[0][0], 0, 50)],
                    recommendation: 'Always pass dynamic ShopifyQL parameters as GraphQL variables rather than concatenating raw input.',
                    verdict: VerificationVerdict::NEEDS_VALIDATION
                );
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('SHOPIFY-API-001', 'Shopify API versions and endpoints are current and supported.');
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
