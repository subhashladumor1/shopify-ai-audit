<?php

namespace Subhash\ShopifyAiAudit\Scanners\React;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class FrontendScanner implements AuditScanner
{
    public function id(): string
    {
        return 'frontend';
    }

    public function name(): string
    {
        return 'Frontend Security & Quality Scanner';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('frontend');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        $scanDirs = ['resources/js', 'resources/ts', 'src', 'frontend', 'app'];
        $filesToScan = [];

        foreach ($scanDirs as $dir) {
            $dirPath = $basePath.DIRECTORY_SEPARATOR.$dir;
            if (is_dir($dirPath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['js', 'jsx', 'ts', 'tsx', 'vue'], true)) {
                        $filesToScan[] = $file->getPathname();
                    }
                }
            }
        }

        foreach ($filesToScan as $filePath) {
            $scannedFiles[] = $filePath;
            $content = (string) file_get_contents($filePath);
            $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $filePath);

            // Check FRONTEND-001: dangerouslySetInnerHTML without DOMPurify
            if (str_contains($content, 'dangerouslySetInnerHTML')) {
                if (! str_contains($content, 'DOMPurify') && ! str_contains($content, 'sanitize')) {
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'FRONTEND-001',
                        message: 'React dangerouslySetInnerHTML used without DOMPurify HTML sanitization.',
                        severity: Severity::HIGH,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        recommendation: 'Sanitize HTML strings before injecting into DOM: dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(dirty) }}',
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }

            // Check FRONTEND-002: Client-side secret exposure in VITE_ or NEXT_PUBLIC_
            if (preg_match('/(VITE|NEXT_PUBLIC|MIX)_[A-Z0-9_]*(SECRET|PRIVATE_KEY|PASSWORD|TOKEN)/i', $content, $secMatches)) {
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'FRONTEND-002',
                    message: 'Potential secret key exposed in client-accessible frontend environment variable: '.$secMatches[0],
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    filePath: $relPath,
                    recommendation: 'Never prefix private backend secrets with VITE_ or NEXT_PUBLIC_. These are baked into client bundles.',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('FRONTEND-001', 'Frontend code quality and security checks passed.');
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
