<?php

namespace Subhash\ShopifyAiAudit\Facades;

use Illuminate\Support\Facades\Facade;
use Subhash\ShopifyAiAudit\Core\AuditManager;

/**
 * @method static \Subhash\ShopifyAiAudit\DTOs\AuditResult run(array $options = [])
 * @method static \Subhash\ShopifyAiAudit\Core\AuditOrchestrator orchestrator()
 * @method static \Subhash\ShopifyAiAudit\Core\ProjectDiscovery discovery()
 * @method static \Subhash\ShopifyAiAudit\Core\ScannerRegistry scanners()
 * @method static \Subhash\ShopifyAiAudit\Core\RuleRegistry rules()
 * @method static \Subhash\ShopifyAiAudit\Shopify\API\ApiRegistry apis()
 * @method static \Subhash\ShopifyAiAudit\Shopify\Governance\PartnerPolicyRegistry governance()
 *
 * @see AuditManager
 */
class Guardian extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuditManager::class;
    }
}
