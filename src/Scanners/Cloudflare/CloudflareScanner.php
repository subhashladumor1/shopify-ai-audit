<?php

namespace Subhash\ShopifyAiAudit\Scanners\Cloudflare;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class CloudflareScanner implements AuditScanner
{
    public function id(): string
    {
        return 'cloudflare';
    }

    public function name(): string
    {
        return 'Cloudflare & Infrastructure Security Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('cloudflare');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        $configFiles = [
            $basePath.DIRECTORY_SEPARATOR.'wrangler.toml',
            $basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'cloudflare.php',
            $basePath.DIRECTORY_SEPARATOR.'.env',
        ];

        foreach ($configFiles as $cf) {
            if (file_exists($cf)) {
                $scannedFiles[] = $cf;
                $content = (string) file_get_contents($cf);
                $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $cf);

                // Check CLOUDFLARE-001: Flexible SSL mode
                if (preg_match('/ssl(_mode)?\s*=\s*[\'"]?flexible[\'"]?/i', $content)) {
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'CLOUDFLARE-001',
                        message: 'Cloudflare SSL is configured in "Flexible" mode. Traffic between Cloudflare and your origin server is transmitted in plaintext HTTP.',
                        severity: Severity::HIGH,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        recommendation: 'Configure Cloudflare SSL/TLS to "Full (Strict)" and install a valid origin CA certificate.',
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('CLOUDFLARE-001', 'Cloudflare configuration checks passed.');
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
