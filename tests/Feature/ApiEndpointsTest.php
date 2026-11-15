<?php

namespace Subhash\ShopifyAiAudit\Tests\Feature;

use Subhash\ShopifyAiAudit\Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson(route('guardian.api.health'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'package' => 'subhash/shopify-ai-audit',
            ]);
    }

    public function test_api_shopify_apis_endpoint(): void
    {
        $response = $this->getJson(route('guardian.api.shopify.apis'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'versions',
            ]);
    }

    public function test_api_governance_endpoint(): void
    {
        $response = $this->getJson(route('guardian.api.shopify.governance'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'policies',
                'enforcement_actions',
            ]);
    }
}
