<?php

namespace Subhash\ShopifyAiAudit\Scanners\Laravel;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class LaravelSecurityScanner implements AuditScanner
{
    public function id(): string
    {
        return 'laravel_security';
    }

    public function name(): string
    {
        return 'Laravel Security Engine';
    }

    public function supports(AuditContext $context): bool
    {
        return $context->shouldScanCategory('laravel_security');
    }

    public function scan(AuditContext $context): ScanResult
    {
        $startTime = microtime(true);
        $basePath = $context->basePath;

        $ruleResults = [];
        $scannedFiles = [];
        $skippedFiles = [];

        // Check 1: APP_DEBUG in .env
        $envPath = $basePath.DIRECTORY_SEPARATOR.'.env';
        if (file_exists($envPath)) {
            $scannedFiles[] = $envPath;
            $envContent = (string) file_get_contents($envPath);
            if (preg_match('/^APP_DEBUG\s*=\s*true/mi', $envContent, $matches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($envContent, 0, $matches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'LARAVEL-SEC-003',
                    message: 'Production debug mode is enabled (APP_DEBUG=true). Sensitive stack traces, passwords, and environment keys will be exposed to attackers on unhandled errors.',
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    filePath: '.env',
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['line' => 'APP_DEBUG=true'],
                    recommendation: 'Set APP_DEBUG=false in production environments.',
                    suggestedFix: 'APP_DEBUG=false',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }
        }

        // Scan PHP and Blade files in app, routes, resources
        $scanDirs = ['app', 'routes', 'resources/views'];
        $filesToScan = [];

        foreach ($scanDirs as $dir) {
            $dirPath = $basePath.DIRECTORY_SEPARATOR.$dir;
            if (is_dir($dirPath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['php', 'blade.php'], true)) {
                        $filesToScan[] = $file->getPathname();
                    }
                }
            }
        }

        foreach ($filesToScan as $filePath) {
            $scannedFiles[] = $filePath;
            $content = (string) file_get_contents($filePath);
            $relPath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $filePath);

            // Check LARAVEL-SEC-001: SQL Injection in DB::raw or whereRaw
            if (preg_match('/(DB::raw|whereRaw|havingRaw|orderByRaw)\s*\(\s*[\'"].*\$[a-zA-Z0-9_]+.*[\'"]\s*\)/i', $content, $sqliMatches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($content, 0, $sqliMatches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'LARAVEL-SEC-001',
                    message: 'Potential SQL Injection: unescaped variable concatenated directly into raw database query.',
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    filePath: $relPath,
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['snippet' => substr($sqliMatches[0][0], 0, 80)],
                    recommendation: 'Use parameterized query bindings: DB::raw("... = ?", [$parameter])',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }

            // Check LARAVEL-SEC-002: Unescaped Blade output of user input
            if (str_ends_with($filePath, '.blade.php')) {
                if (preg_match('/\{!!\s*(\$request->[a-zA-Z0-9_\-\>]+|\$_GET\[|\$_POST\[|request\()->*\s*!!\}/i', $content, $xssMatches, PREG_OFFSET_CAPTURE)) {
                    $line = substr_count(substr($content, 0, $xssMatches[0][1]), "\n") + 1;
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'LARAVEL-SEC-002',
                        message: 'Cross-Site Scripting (XSS) vulnerability: unescaped user input rendered via {!! !!} in Blade template.',
                        severity: Severity::HIGH,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        lineStart: $line,
                        lineEnd: $line,
                        evidence: ['snippet' => $xssMatches[0][0]],
                        recommendation: 'Use HTML-escaped Blade tags {{ $var }} or e($var) for user input.',
                        suggestedFix: '{{ '.trim($xssMatches[1][0]).' }}',
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }

            // Check LARAVEL-SEC-004: Mass assignment $guarded = []
            if (str_contains($relPath, 'Models')) {
                if (preg_match('/protected\s+\$guarded\s*=\s*\[\s*\];/i', $content, $guardMatches, PREG_OFFSET_CAPTURE)) {
                    $line = substr_count(substr($content, 0, $guardMatches[0][1]), "\n") + 1;
                    $ruleResults[] = RuleResult::failed(
                        ruleId: 'LARAVEL-SEC-004',
                        message: 'Eloquent Mass Assignment Risk: Model defines completely empty $guarded = [].',
                        severity: Severity::MEDIUM,
                        confidence: Confidence::HIGH,
                        filePath: $relPath,
                        lineStart: $line,
                        lineEnd: $line,
                        evidence: ['snippet' => 'protected $guarded = [];'],
                        recommendation: 'Explicitly specify protected $fillable = [...] to restrict mass-assignable attributes.',
                        verdict: VerificationVerdict::CONFIRMED
                    );
                }
            }

            // Check LARAVEL-SEC-005: Arbitrary command execution
            if (preg_match('/(exec|shell_exec|system|passthru|proc_open)\s*\(\s*.*(\$request|\$_GET|\$_POST|\$input).*;/i', $content, $cmdMatches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($content, 0, $cmdMatches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'LARAVEL-SEC-005',
                    message: 'Remote Command Execution (RCE): user input passed directly to shell execution function without sanitization.',
                    severity: Severity::CRITICAL,
                    confidence: Confidence::HIGH,
                    filePath: $relPath,
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['snippet' => substr($cmdMatches[0][0], 0, 70)],
                    recommendation: 'Avoid shell execution. If unavoidable, use escapeshellcmd() or escapeshellarg().',
                    verdict: VerificationVerdict::CONFIRMED
                );
            }

            // Check LARAVEL-SEC-008: SSRF in Http client
            if (preg_match('/Http::(get|post|put|delete)\s*\(\s*(\$request->[a-zA-Z0-9_\-\>]+|\$input|\$_GET|\$_POST)/i', $content, $ssrfMatches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count(substr($content, 0, $ssrfMatches[0][1]), "\n") + 1;
                $ruleResults[] = RuleResult::failed(
                    ruleId: 'LARAVEL-SEC-008',
                    message: 'Potential Server-Side Request Forgery (SSRF): user-controlled URL passed directly to HTTP client.',
                    severity: Severity::HIGH,
                    confidence: Confidence::MEDIUM,
                    filePath: $relPath,
                    lineStart: $line,
                    lineEnd: $line,
                    evidence: ['snippet' => substr($ssrfMatches[0][0], 0, 60)],
                    recommendation: 'Validate target URL against a strict whitelist of allowed domains and block private/loopback IP addresses.',
                    verdict: VerificationVerdict::NEEDS_VALIDATION
                );
            }
        }

        if (empty($ruleResults)) {
            $ruleResults[] = RuleResult::passed('LARAVEL-SEC-001', 'Laravel security checks passed.');
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
