<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\Contracts\AuditRule;
use Subhash\ShopifyAiAudit\Enums\AuditCategory;

class RuleRegistry
{
    /**
     * @var array<string, AuditRule>
     */
    protected array $rules = [];

    public function register(AuditRule $rule): self
    {
        $this->rules[$rule->id()] = $rule;

        return $this;
    }

    /**
     * @return array<string, AuditRule>
     */
    public function all(): array
    {
        return $this->rules;
    }

    public function get(string $id): ?AuditRule
    {
        return $this->rules[$id] ?? null;
    }

    /**
     * @return array<string, AuditRule>
     */
    public function byCategory(AuditCategory $category): array
    {
        return array_filter($this->rules, fn (AuditRule $rule) => $rule->metadata()->category === $category);
    }
}
