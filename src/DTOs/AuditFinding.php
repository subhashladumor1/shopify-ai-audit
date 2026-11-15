<?php

namespace Subhash\ShopifyAiAudit\DTOs;

use Subhash\ShopifyAiAudit\Enums\AuditCategory;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\FindingStatus;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;

class AuditFinding
{
    /**
     * @param  array<string, mixed>  $evidence
     * @param  array<int, string>  $references
     */
    public function __construct(
        public readonly string $id,
        public readonly string $ruleId,
        public readonly string $title,
        public readonly string $description,
        public readonly AuditCategory $category,
        public readonly Severity $severity,
        public readonly Confidence $confidence,
        public readonly VerificationVerdict $verificationVerdict,
        public readonly FindingStatus $status,
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
        public readonly ?string $verificationSteps = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rule_id' => $this->ruleId,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category->value,
            'severity' => $this->severity->value,
            'confidence' => $this->confidence->value,
            'verification_verdict' => $this->verificationVerdict->value,
            'status' => $this->status->value,
            'file_path' => $this->filePath,
            'line_start' => $this->lineStart,
            'line_end' => $this->lineEnd,
            'evidence' => $this->evidence,
            'root_cause' => $this->rootCause,
            'technical_impact' => $this->technicalImpact,
            'business_impact' => $this->businessImpact,
            'recommendation' => $this->recommendation,
            'suggested_fix' => $this->suggestedFix,
            'references' => $this->references,
            'verification_steps' => $this->verificationSteps,
        ];
    }
}
