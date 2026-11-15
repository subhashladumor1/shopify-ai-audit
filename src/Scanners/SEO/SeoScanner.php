<?php

namespace Subhash\ShopifyAiAudit\Scanners\SEO;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class SeoScanner implements AuditScanner
{
    public function id(): string
    {
        return 'seo';
    }

    public function name(): string
    {
        return 'Technical SEO & Metadata Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('seo');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        $layoutFiles = [
            $basePath.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.'app.blade.php',
            $basePath.DIRECTORY_SEPARATOR.'layout'.DIRECTORY_SEPARATOR.'theme.liquid',
        ];

        foreach ($layoutFiles as $lf) {
            if (file_exists($lf)) {
                $scannedFiles[] = $lf;
                $content = (string) file_get_contents($lf);
                $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $lf);

                if (str_contains($content, '<head>') || str_contains($content, '<head ')) {
                    if (! str_contains($content, 'rel="canonical"') && ! str_contains($content, "rel='canonical'")) {
                        $ruleResults[] = RuleResult::failed(
                            ruleId: 'SEO-001',
                            message: 'Layout template is missing <link rel="canonical"> tag in <head>. Risks duplicate content penalties.',
                            severity: Severity::LOW,
                            confidence: Confidence::HIGH,
                            filePath: $relPath,
                            recommendation: 'Add <link rel="canonical" href="{{ url()->current() }}"> to prevent duplicate indexation.',
                            suggestedFix: '<link rel="canonical" href="{{ url()->current() }}">',
                            verdict: VerificationVerdict::CONFIRMED
                        );
                    }

                    if (! str_contains($content, 'property="og:title"') && ! str_contains($content, 'property="og:image"')) {
                        $ruleResults[] = RuleResult::failed(
                            ruleId: 'SEO-002',
                            message: 'Layout template is missing Open Graph social meta tags (og:title, og:image).',
                            severity: Severity::LOW,
                            confidence: Confidence::HIGH,
                            filePath: $relPath,
                            recommendation: 'Include standard Open Graph meta tags to enhance social previews and sharing.',
                            verdict: VerificationVerdict::CONFIRMED
                        );
                    }
                }
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('SEO-001', 'SEO and meta tags validated.');
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
