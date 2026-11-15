<?php

namespace Subhash\ShopifyAiAudit\Tests\Unit\Shopify;

use Subhash\ShopifyAiAudit\Shopify\API\ApiRegistry;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class ShopifyApiRegistryTest extends TestCase
{
    public function test_registers_five_official_shopify_apis(): void
    {
        $registry = new ApiRegistry;
        $apis = $registry->all();

        $this->assertCount(5, $apis);
        $this->assertArrayHasKey('admin_graphql', $apis);
        $this->assertArrayHasKey('storefront_graphql', $apis);
        $this->assertArrayHasKey('customer_account', $apis);
        $this->assertArrayHasKey('shopifyql', $apis);
        $this->assertArrayHasKey('payments_apps', $apis);
    }

    public function test_checks_version_lifecycle_and_deprecations(): void
    {
        $registry = new ApiRegistry;

        $latestCheck = $registry->checkVersion('2025-01');
        $this->assertTrue($latestCheck['is_supported']);
        $this->assertTrue($latestCheck['is_latest']);

        $sunsetCheck = $registry->checkVersion('2024-01');
        $this->assertTrue($sunsetCheck['is_deprecated']);
        $this->assertNotNull($sunsetCheck['warning']);
    }
}
