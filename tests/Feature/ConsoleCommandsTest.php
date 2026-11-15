<?php

namespace Subhash\ShopifyAiAudit\Tests\Feature;

use Subhash\ShopifyAiAudit\Tests\TestCase;

class ConsoleCommandsTest extends TestCase
{
    public function test_health_command(): void
    {
        $this->artisan('guardian:health')
            ->expectsOutputToContain('Guardian Package Health Check')
            ->assertExitCode(0);
    }

    public function test_shopify_apis_command(): void
    {
        $this->artisan('guardian:shopify:apis')
            ->expectsOutputToContain('Official Shopify APIs Registry')
            ->assertExitCode(0);
    }

    public function test_shopify_api_versions_command(): void
    {
        $this->artisan('guardian:shopify:api:versions')
            ->expectsOutputToContain('Shopify Quarterly API Version Lifecycle')
            ->assertExitCode(0);
    }

    public function test_shopify_check_version_command(): void
    {
        $this->artisan('guardian:shopify:api:check-version', ['version' => '2025-01'])
            ->expectsOutputToContain('Checking Shopify API Version: 2025-01')
            ->assertExitCode(0);
    }

    public function test_shopify_partner_policies_command(): void
    {
        $this->artisan('guardian:shopify:partner:policies')
            ->expectsOutputToContain('Shopify Partner Program Policies & Agreements')
            ->assertExitCode(0);
    }

    public function test_shopify_partner_sunset_plan_command(): void
    {
        $this->artisan('guardian:shopify:partner:sunset-plan', ['app_name' => 'Demo App'])
            ->expectsOutputToContain('Voluntary App Sunset Roadmap for [Demo App]')
            ->assertExitCode(0);
    }

    public function test_monitoring_command(): void
    {
        $this->artisan('guardian:monitor')
            ->expectsOutputToContain('Guardian Observability Dashboard')
            ->assertExitCode(0);
    }

    public function test_remediation_prompt_command(): void
    {
        $this->artisan('guardian:fix:prompt', ['rule_id' => 'SHOPIFY-SEC-006'])
            ->expectsOutputToContain('SECURITY REMEDIATION REQUEST')
            ->assertExitCode(0);
    }
}
