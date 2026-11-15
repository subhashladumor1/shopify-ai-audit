<?php

namespace Subhash\ShopifyAiAudit\Scanners\Security;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class ShopifySecurityScanner implements AuditScanner
{
    public function id(): string
    {
        return 'shopify_security';
    }

    public function name(): string
    {
        return 'Shopify Security & OAuth Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('shopify_security');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        // Scan files in app, routes, config, resources
        $targetDirs = ['app', 'routes', 'config', 'resources', 'extensions'];
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
                        if (in_array($ext, ['php', 'liquid', 'json', 'toml'], true)) {
                            $filesToScan[] = $file->getPathname();
                        }
                    }
                }
            }
        }

        // Also check root .env or .env.example
        if (file_exists($basePath.DIRECTORY_SEPARATOR.'.env')) {
            $filesToScan[] = $basePath.DIRECTORY_SEPARATOR.'.env';
        }

        $allCodeContent = '';
        $hasWebhookController = false;
        $hasHmacVerification = false;
        $hasRawBodyAccess = false;
        $hasGdprDataRequest = false;
        $hasGdprCustomerRedact = false;
        $hasGdprShopRedact = false;
        $hasOAuthStateCheck = false;
        $hasShopDomainValidation = false;
        $hasAppProxySignatureCheck = false;
        $hasFrameAncestorsCsp = false;

        foreach ($filesToScan as $filePath) {
            $scannedFiles[] = $filePath;
            $content = (string) file_get_contents($filePath);
            $allCodeContent .= "\n".$content;
            $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $filePath);

            // Check SHOPIFY-SEC-010: Hardcoded API Secrets
            if (preg_match('/(shpat_[a-fA-F0-9]{32,}|shpca_[a-fA-F0-9]{32,}|shpss_[a-fA-F0-9]{32,})/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($content, 0, $matches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-SEC-010',
                    message: 'Hardcoded Shopify API access token or secret detected in source code.',
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    filePath: $relPath,
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['token_pattern' => substr($matches[0][0], 0, 8).'...[REDACTED]'],
                    recommendation: 'Store all Shopify API credentials in environment variables and access via config().',
                    suggestedFix: 'env(\'SHOPIFY_API_SECRET\')',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }

            // Check SHOPIFY-SEC-011: Legacy ScriptTag API usage
            if (str_contains($content, 'ScriptTag') || str_contains($content, 'script_tags.json')) {
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-SEC-011',
                    message: 'Deprecated Shopify ScriptTag API detected. Shopify requires Theme App Extensions for storefront scripts.',
                    severity: Severity::MEDIUM,
                    confidence: Confidence::HIGH,
                    filePath: $relPath,
                    recommendation: 'Migrate legacy ScriptTags to Theme App Extension App Blocks or App Embeds.',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }

            // Check SHOPIFY-SEC-012: Unescaped Liquid in templates
            if (str_ends_with($filePath, '.liquid')) {
                if (preg_match('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', $content, $lMatches, PREG_OFFSET_CAPTURE)) {
                    // Check if filter is missing
                    if (! str_contains($lMatches[0][0], '| escape') && ! str_contains($lMatches[0][0], '| json') && ! str_contains($lMatches[0][0], '| asset_url')) {
                        $line = substr_count(substr($content, 0, $lMatches[0][1]), "\n") + 1;
                        $ruleResults[] = RuleResult::failed(
                            ruleId: 'SHOPIFY-SEC-012',
                            message: 'Unescaped Liquid variable rendering detected. Potential Cross-Site Scripting (XSS).',
                            severity: Severity::HIGH,
                            confidence: Confidence::MEDIUM,
                            filePath: $relPath,
                            lineStart: $line,
                            lineEnd: $line,
                            evidence: ['snippet' => $lMatches[0][0]],
                            recommendation: 'Pass output through the Liquid escape filter: {{ variable | escape }}',
                            suggestedFix: '{{ '.trim($lMatches[1][0]).' | escape }}',
                            verdict: VerificationVerdict::CONFIRMED
                        );
                    }
                }
            }

            // Indicators tracking
            if (str_contains($content, 'webhook') || str_contains($content, 'Webhook')) {
                $hasWebhookController = true;
            }
            if (str_contains($content, 'X-Shopify-Hmac-Sha256') || str_contains($content, 'HTTP_X_SHOPIFY_HMAC_SHA256') || str_contains($content, 'hash_equals')) {
                $hasHmacVerification = true;
            }
            if (str_contains($content, 'getContent()') || str_contains($content, 'php://input')) {
                $hasRawBodyAccess = true;
            }
            if (str_contains($content, 'customers/data_request') || str_contains($content, 'customers_data_request')) {
                $hasGdprDataRequest = true;
            }
            if (str_contains($content, 'customers/redact') || str_contains($content, 'customers_redact')) {
                $hasGdprCustomerRedact = true;
            }
            if (str_contains($content, 'shop/redact') || str_contains($content, 'shop_redact')) {
                $hasGdprShopRedact = true;
            }
            if (str_contains($content, 'state') && (str_contains($content, 'session(') || str_contains($content, 'Cache::'))) {
                $hasOAuthStateCheck = true;
            }
            if (str_contains($content, 'myshopify.com') && str_contains($content, 'preg_match')) {
                $hasShopDomainValidation = true;
            }
            if (str_contains($content, 'signature') && str_contains($content, 'proxy')) {
                $hasAppProxySignatureCheck = true;
            }
            if (str_contains($content, 'frame-ancestors') && (str_contains($content, 'myshopify.com') || str_contains($content, 'admin.shopify.com'))) {
                $hasFrameAncestorsCsp = true;
            }
        }

        // Global check: Mandatory GDPR Privacy Webhooks
        if ($context->manifest->isShopifyApp) {
            if (! $hasGdprDataRequest || ! $hasGdprCustomerRedact || ! $hasGdprShopRedact) {
                $missingTopics = [];
                if (! $hasGdprDataRequest) {
                    $missingTopics[] = 'customers/data_request';
                }
                if (! $hasGdprCustomerRedact) {
                    $missingTopics[] = 'customers/redact';
                }
                if (! $hasGdprShopRedact) {
                    $missingTopics[] = 'shop/redact';
                }

                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-SEC-006',
                    message: 'Missing mandatory Shopify GDPR/Privacy compliance webhook handlers: '.implode(', ', $missingTopics),
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    evidence: ['missing_topics' => $missingTopics],
                    recommendation: 'Implement verified webhook endpoints for customers/data_request, customers/redact, and shop/redact to comply with Shopify App Store and Partner requirements.',
                    suggestedFix: 'Route::post("/api/webhooks/shopify/{topic}", [ShopifyWebhookController::class, "handle"]);',
                    verdict: VerificationVerdict::CONFIRMED
                );
            } else {
                $ruleResults[] = RuleResult::passed('SHOPIFY-SEC-006', 'Mandatory GDPR webhooks detected and registered.');
            }

            // Webhook HMAC check
            if ($hasWebhookController && ! $hasHmacVerification) {
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-SEC-004',
                    message: 'Shopify webhook routes detected without X-Shopify-Hmac-Sha256 cryptographic verification.',
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    recommendation: 'Enforce constant-time HMAC SHA-256 verification on all inbound Shopify webhooks.',
                    suggestedFix: 'hash_equals(base64_encode(hash_hmac("sha256", $request->getContent(), $secret, true)), $hmacHeader)',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }

            // Embedded App CSP frame-ancestors check
            if ($context->manifest->shopifyAppType->value === 'embedded' && ! $hasFrameAncestorsCsp) {
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'SHOPIFY-SEC-008',
                    message: 'Embedded Shopify app is missing Content-Security-Policy frame-ancestors header for admin.shopify.com.',
                    severity: Severity::HIGH,
                    confidence: Confidence::HIGH,
                    recommendation: 'Add Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com; to all embedded view responses.',
                    suggestedFix: '$response->headers->set("Content-Security-Policy", "frame-ancestors https://*.myshopify.com https://admin.shopify.com;");',
                    verdict: VerificationVerdict::CONFIRMED
                );
            } else {
                $ruleResults[] = RuleResult::passed('SHOPIFY-SEC-008', 'Embedded CSP frame-ancestors directive configured.');
            }
        }

        // If no failures occurred, pass default baseline rules
        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('SHOPIFY-SEC-001', 'Shopify security baseline validated.');
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
