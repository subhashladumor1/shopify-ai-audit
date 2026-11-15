<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class GuardDecision
{
    public function __construct(
        public readonly bool $isAllowed,
        public readonly string $reason = '',
        public readonly ?string $budgetPolicy = null,
        public readonly ?float $remainingBudgetUsd = null,
    ) {}

    public static function approved(string $reason = 'Request within budget limits.', ?float $remaining = null): self
    {
        return new self(true, $reason, 'STANDARD', $remaining);
    }

    public static function rejected(string $reason, ?string $policy = 'BUDGET_EXCEEDED'): self
    {
        return new self(false, $reason, $policy, 0.0);
    }
}
