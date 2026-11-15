<?php

namespace Subhash\ShopifyAiAudit\Tests\Unit\Shopify;

use Subhash\ShopifyAiAudit\Shopify\Governance\PartnerPolicyRegistry;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class PartnerPolicyRegistryTest extends TestCase
{
    public function test_tracks_partner_policies_and_enforcement_actions(): void
    {
        $registry = new PartnerPolicyRegistry;
        $policies = $registry->policies();
        $actions = $registry->enforcementActions();

        $this->assertArrayHasKey('partner_program_agreement', $policies);
        $this->assertArrayHasKey('acceptable_use_policy', $policies);
        $this->assertArrayHasKey('review_policy', $policies);

        $this->assertArrayHasKey('merchant_app_removal', $actions);
        $this->assertArrayHasKey('api_revocation', $actions);
        $this->assertArrayHasKey('partner_account_termination', $actions);
    }
}
