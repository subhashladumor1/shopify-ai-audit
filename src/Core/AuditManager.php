<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\AuditResult;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Shopify\API\ApiRegistry;
use Subhash\ShopifyAiAudit\Shopify\Governance\PartnerPolicyRegistry;

class AuditManager
{
    public function __construct(
        protected readonly ProjectDiscovery $discovery,
        protected readonly ScannerRegistry $scannerRegistry,
        protected readonly RuleRegistry $ruleRegistry,
        protected readonly AuditOrchestrator $orchestrator,
        protected readonly ApiRegistry $apiRegistry,
        protected readonly PartnerPolicyRegistry $partnerPolicyRegistry
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function run(array $options = []): AuditResult
    {
        $basePath = $options['base_path'] ?? base_path();
        $manifest = $this->discovery->discover($basePath);

        $categories = $options['categories'] ?? [];
        $minSeverityStr = $options['min_severity'] ?? 'low';
        $minSeverity = Severity::tryFrom($minSeverityStr) ?? Severity::LOW;
        $excludedPaths = $options['excluded_paths'] ?? config('shopify-ai-audit.audit.excluded_paths', []);

        $context = new AuditContext(
            basePath: $basePath,
            manifest: $manifest,
            mode: $options['mode'] ?? 'read_only',
            categories: $categories,
            minSeverity: $minSeverity,
            excludedPaths: $excludedPaths,
            includeAi: $options['include_ai'] ?? false,
            options: $options
        );

        return $this->orchestrator->run($context);
    }

    public function orchestrator(): AuditOrchestrator
    {
        return $this->orchestrator;
    }

    public function discovery(): ProjectDiscovery
    {
        return $this->discovery;
    }

    public function scanners(): ScannerRegistry
    {
        return $this->scannerRegistry;
    }

    public function rules(): RuleRegistry
    {
        return $this->ruleRegistry;
    }

    public function apis(): ApiRegistry
    {
        return $this->apiRegistry;
    }

    public function governance(): PartnerPolicyRegistry
    {
        return $this->partnerPolicyRegistry;
    }
}
