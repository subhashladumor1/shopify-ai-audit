<?php

namespace Subhash\ShopifyAiAudit\Scanners\Shopify;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class ShopifyAppStoreScanner implements AuditScanner
{
    public function id(): string
    {
        return 'shopify_app_store';
    }

    public function name(): string
    {
        return 'Shopify App Store Readiness Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('shopify_app_store') && $context->manifest->isShopifyApp;
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        // Check shopify.app.toml if present
        $tomlPath = $basePath.DIRECTORY_SEPARATOR.'shopify.app.toml';
        $hasPrivacyPolicy = false;
        $hasOffPlatformBilling = false;

        if (file_exists($tomlPath)) {
            $scannedFiles[] = $tomlPath;
            $tomlContent = (string) file_get_contents($tomlPath);
            if (preg_match('/privacy_policy_url\s*=\s*["\']https:\/\/[^"\']+["\']/', $tomlContent)) {
                $hasPrivacyPolicy = true;
            }
        }

        // Scan code for off-platform billing (e.g. Stripe, PayPal charged for Shopify app subscription)
        $routesPath = $basePath.DIRECTORY_SEPARATOR.'routes';
        if (is_dir($routesPath)) {
            $files = glob($routesPath.DIRECTORY_SEPARATOR.'*.php') ?: [];
            foreach ($files as $rf) {
                $scannedFiles[] = $rf;
                $content = (string) file_get_contents($rf);
                if (
                    (str_contains($content, 'stripe') || str_contains($content, 'paypal') || str_contains($content, 'braintree')) &&
                    (str_contains($content, 'subscription') || str_contains($content, 'charge') || str_contains($content, 'billing'))
                ) {
                    $hasOffPlatformBilling = true;
                    $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $rf);
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'SHOPIFY-GOV-003',
                        message: 'Potential off-platform billing detected in routes. Shopify App Store requires 100% on-platform billing via Shopify Billing API.',
                        severity: Severity::CRITICAL,
                        confidence: Confidence::MEDIUM,
                        filePath: $relPath,
                        recommendation: 'Use Shopify RecurringApplicationCharge or AppSubscription mutations. Off-platform billing leads to permanent App Store delisting.',
                        verdict: VerificationVerdict::NEEDS_VALIDATION
                    );
                }
            }
        }

        // Check privacy policy
        if (! $hasPrivacyPolicy && file_exists($tomlPath)) {
            $ruleResults[] = RuleResult::failed(
                ruleId: 'SHOPIFY-GOV-001',
                message: 'Missing or non-HTTPS privacy_policy_url in shopify.app.toml.',
                severity: Severity::MEDIUM,
                confidence: Confidence::HIGH,
                filePath: 'shopify.app.toml',
                recommendation: 'Specify a valid HTTPS privacy policy URL in your shopify.app.toml configuration.',
                suggestedFix: 'privacy_policy_url = "https://example.com/privacy"',
                verdict: VerificationVerdict::CONFIRMED
            );
        } else {
            $ruleResults[] = RuleResult::passed('SHOPIFY-GOV-001', 'Privacy policy URL declared.');
        }

        $failedCount = count(array_filter($ruleResults, fn ($r) => $r->outcome->value === 'failed'));
        if ($failedCount === 0) {
            $ruleResults[] = RuleResult::passed('SHOPIFY-GOV-002', 'App Store readiness configuration verified.');
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
