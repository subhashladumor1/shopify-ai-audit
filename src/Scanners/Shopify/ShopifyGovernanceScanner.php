<?php

namespace Subhash\ShopifyAiAudit\Scanners\Shopify;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;
use Subhash\ShopifyAiAudit\Shopify\Governance\PartnerPolicyRegistry;

class ShopifyGovernanceScanner implements AuditScanner
{
    public function __construct(
        protected readonly PartnerPolicyRegistry $policyRegistry = new PartnerPolicyRegistry
    ) {}

    public function id(): string
    {
        return 'shopify_governance';
    }

    public function name(): string
    {
        return 'Shopify Partner Governance & Enforcement Risk Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('shopify_governance') && $context->manifest->isShopifyApp;
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        // Check if shop redaction jobs perform data deletion or anonymization
        $appPath = $basePath.DIRECTORY_SEPARATOR.'app';
        $hasDeletionRoutine = false;

        if (is_dir($appPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($appPath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $scannedFiles[] = $file->getPathname();
                    $content = (string) file_get_contents($file->getPathname());
                    if (str_contains($content, 'shop/redact') || str_contains($content, 'ShopRedact')) {
                        if (str_contains($content, 'delete()') || str_contains($content, 'truncate()') || str_contains($content, 'anonymize')) {
                            $hasDeletionRoutine = true;
                        }
                    }
                }
            }
        }

        // Check SHOPIFY-GOV-004: Prohibited Data Persistence Post-Redact
        if (! $hasDeletionRoutine && $context->manifest->isShopifyApp) {
            $ruleResults[] = RuleResult::failed(
                ruleId: 'SHOPIFY-GOV-004',
                message: 'No data erasure or anonymization routine detected in shop/redact webhook handler. Unlawful retention violates Shopify Partner Agreement Section 8.',
                severity: Severity::HIGH,
                confidence: Confidence::MEDIUM,
                recommendation: 'Purge or anonymize all merchant and customer data associated with the uninstalled shop within 48 hours of receiving the shop/redact webhook.',
                verdict: VerificationVerdict::NEEDS_VALIDATION
            );
        } else {
            $ruleResults[] = RuleResult::passed('SHOPIFY-GOV-004', 'Shop redaction data cleanup routine detected.');
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
