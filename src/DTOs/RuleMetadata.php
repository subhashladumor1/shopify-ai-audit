<?php

namespace Subhash\ShopifyAiAudit\DTOs;

use Subhash\ShopifyAiAudit\Enums\AuditCategory;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;

class RuleMetadata
{
    /**
     * @param  array<int, string>  $references
     * @param  array<int, string>  $applicableAppTypes
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly AuditCategory $category,
        public readonly Severity $severity,
        public readonly Confidence $confidence,
        public readonly array $references = [],
        public readonly array $applicableAppTypes = [],
        public readonly string $remediation = '',
        public readonly bool $isManualReviewRequired = false,
    ) {}
}
