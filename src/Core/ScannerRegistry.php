<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\Contracts\AuditScanner;
use Subhash\ShopifyAiAudit\DTOs\AuditContext;

class ScannerRegistry
{
    /**
     * @var array<string, AuditScanner>
     */
    protected array $scanners = [];

    public function register(AuditScanner $scanner): self
    {
        $this->scanners[$scanner->id()] = $scanner;

        return $this;
    }

    /**
     * @return array<string, AuditScanner>
     */
    public function all(): array
    {
        return $this->scanners;
    }

    public function get(string $id): ?AuditScanner
    {
        return $this->scanners[$id] ?? null;
    }

    /**
     * @return array<string, AuditScanner>
     */
    public function resolveApplicable(AuditContext $context): array
    {
        $applicable = [];

        foreach ($this->scanners as $id => $scanner) {
            if ($scanner->supports($context)) {
                $applicable[$id] = $scanner;
            }
        }

        return $applicable;
    }
}
