<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\RuleMetadata;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;

interface AuditRule
{
    /**
     * Unique alphanumeric identifier for this rule (e.g. SHOPIFY-SEC-001).
     */
    public function id(): string;

    /**
     * Rule metadata containing title, severity, category, references, etc.
     */
    public function metadata(): RuleMetadata;

    /**
     * Executes the rule logic against the provided project context.
     */
    public function check(AuditContext $context): RuleResult;
}
