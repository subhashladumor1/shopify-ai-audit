<?php

namespace Subhash\ShopifyAiAudit\DTOs;

use Subhash\ShopifyAiAudit\Enums\AuditOutcome;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class RuleResult
{
    /**
     * @param  array<string, mixed>  $evidence
     * @param  array<int, string>  $references
     */
    public function __construct(
        public readonly string $ruleId,
        public readonly AuditOutcome $outcome,
        public readonly string $message,
        public readonly ?Severity $severity = null,
        public readonly ?Confidence $confidence = null,
        public readonly VerificationVerdict $verdict = VerificationVerdict::NEEDS_VALIDATION,
        public readonly ?string $filePath = null,
        public readonly ?int $lineStart = null,
        public readonly ?int $lineEnd = null,
        public readonly array $evidence = [],
        public readonly ?string $rootCause = null,
        public readonly ?string $technicalImpact = null,
        public readonly ?string $businessImpact = null,
        public readonly ?string $recommendation = null,
        public readonly ?string $suggestedFix = null,
        public readonly array $references = [],
    ) {}

    public static function passed(string $ruleId, string $message = 'Rule check passed without issues.'): self
    {
        return new self(
            ruleId: $ruleId,
            outcome: AuditOutcome::PASSED,
            message: $message,
            verdict: VerificationVerdict::CONFIRMED
        );
    }

    public static function failed(
        string $ruleId,
        string $message,
        Severity $severity,
        Confidence $confidence = Confidence::HIGH,
        ?string $filePath = null,
        ?int $lineStart = null,
        ?int $lineEnd = null,
        array $evidence = [],
        ?string $recommendation = null,
        ?string $suggestedFix = null,
        VerificationVerdict $verdict = VerificationVerdict::CONFIRMED
    ): self {
        return new self(
            ruleId: $ruleId,
            outcome: AuditOutcome::FAILED,
            message: $message,
            severity: $severity,
            confidence: $confidence,
            verdict: $verdict,
            filePath: $filePath,
            lineStart: $lineStart,
            lineEnd: $lineEnd,
            evidence: $evidence,
            recommendation: $recommendation,
            suggestedFix: $suggestedFix
        );
    }

    public static function notApplicable(string $ruleId, string $reason): self
    {
        return new self(
            ruleId: $ruleId,
            outcome: AuditOutcome::NOT_APPLICABLE,
            message: $reason,
            verdict: VerificationVerdict::CONFIRMED
        );
    }
}
