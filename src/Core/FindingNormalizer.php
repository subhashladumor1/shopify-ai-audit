<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\Contracts\FindingNormalizerInterface;
use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\AuditCategory;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\FindingStatus;
use Subhash\ShopifyAiAudit\Enums\Severity;

class FindingNormalizer implements FindingNormalizerInterface
{
    /**
     * @param  array<int, ScanResult>  $scanResults
     * @return array<int, AuditFinding>
     */
    public function normalize(array $scanResults): array
    {
        $findings = [];
        $deduplicationMap = [];

        foreach ($scanResults as $scanResult) {
            foreach ($scanResult->ruleResults as $ruleResult) {
                // Only failed checks produce findings
                if ($ruleResult->outcome->value !== 'failed') {
                    continue;
                }

                $ruleId = $ruleResult->ruleId;
                $filePath = $ruleResult->filePath ?? 'unknown';
                $lineStart = $ruleResult->lineStart ?? 0;

                // Composite deduplication key
                $dedupKey = md5($ruleId.'|'.$filePath.'|'.$lineStart);
                if (isset($deduplicationMap[$dedupKey])) {
                    continue;
                }
                $deduplicationMap[$dedupKey] = true;

                $findingId = 'find_'.substr(md5($dedupKey.microtime()), 0, 12);
                $category = $this->resolveCategory($ruleId);

                $findings[] = new AuditFinding(
                    id: $findingId,
                    ruleId: $ruleId,
                    title: $ruleResult->message,
                    description: $ruleResult->message,
                    category: $category,
                    severity: $ruleResult->severity ?? Severity::MEDIUM,
                    confidence: $ruleResult->confidence ?? Confidence::HIGH,
                    verificationVerdict: $ruleResult->verdict,
                    status: FindingStatus::OPEN,
                    filePath: $ruleResult->filePath,
                    lineStart: $ruleResult->lineStart,
                    lineEnd: $ruleResult->lineEnd,
                    evidence: $ruleResult->evidence,
                    rootCause: $ruleResult->rootCause,
                    technicalImpact: $ruleResult->technicalImpact,
                    businessImpact: $ruleResult->businessImpact,
                    recommendation: $ruleResult->recommendation,
                    suggestedFix: $ruleResult->suggestedFix,
                    references: $ruleResult->references,
                    verificationSteps: 'Inspect the referenced code line to confirm that sanitization or validation has been applied.',
                );
            }
        }

        return $findings;
    }

    protected function resolveCategory(string $ruleId): AuditCategory
    {
        if (str_starts_with($ruleId, 'SHOPIFY-SEC-')) {
            return AuditCategory::SHOPIFY_SECURITY;
        }
        if (str_starts_with($ruleId, 'SHOPIFY-API-')) {
            return AuditCategory::SHOPIFY_API;
        }
        if (str_starts_with($ruleId, 'SHOPIFY-APP-')) {
            return AuditCategory::SHOPIFY_APP_STORE;
        }
        if (str_starts_with($ruleId, 'SHOPIFY-GOV-')) {
            return AuditCategory::SHOPIFY_GOVERNANCE;
        }
        if (str_starts_with($ruleId, 'LARAVEL-SEC-')) {
            return AuditCategory::LARAVEL_SECURITY;
        }
        if (str_starts_with($ruleId, 'PHP-SEC-')) {
            return AuditCategory::PHP_SECURITY;
        }
        if (str_starts_with($ruleId, 'FRONTEND-')) {
            return AuditCategory::FRONTEND;
        }
        if (str_starts_with($ruleId, 'DATABASE-')) {
            return AuditCategory::DATABASE;
        }
        if (str_starts_with($ruleId, 'CLOUDFLARE-')) {
            return AuditCategory::CLOUDFLARE;
        }
        if (str_starts_with($ruleId, 'PERF-')) {
            return AuditCategory::PERFORMANCE;
        }
        if (str_starts_with($ruleId, 'SEO-')) {
            return AuditCategory::SEO;
        }

        return AuditCategory::SHOPIFY_SECURITY;
    }
}
