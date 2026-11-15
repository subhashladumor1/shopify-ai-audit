<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class HealthCommand extends Command
{
    protected $signature = 'guardian:health';

    protected $description = 'Display Guardian engine health, registered scanners, and environment configuration';

    public function handle(): int
    {
        $this->info("🛡️ Guardian Package Health Check\n");

        $scanners = Guardian::scanners()->all();
        $rules = Guardian::rules()->all();
        $apis = Guardian::apis()->all();

        $this->table(
            ['Component', 'Status', 'Details'],
            [
                ['Guardian Enabled', config('shopify-ai-audit.enabled') ? '✅ Yes' : '❌ No', 'config(shopify-ai-audit.enabled)'],
                ['Registered Scanners', '✅ '.count($scanners).' Active', implode(', ', array_keys($scanners))],
                ['Registered Rules', '✅ '.count($rules).' Rules', 'Static & Dynamic Detection'],
                ['Shopify APIs Tracked', '✅ '.count($apis).' APIs', 'Admin GraphQL, Storefront, Customer, ShopifyQL, Payments'],
                ['AI Platform', config('shopify-ai-audit.ai.enabled') ? '🤖 Enabled' : '⏸️ Disabled (Deterministic Only)', config('shopify-ai-audit.ai.default_provider')],
                ['Monitoring Engine', config('shopify-ai-audit.monitoring.enabled') ? '📊 Active' : '⏸️ Disabled', 'Sampling: '.config('shopify-ai-audit.monitoring.sampling')],
                ['PHP Version', PHP_VERSION >= '8.2.0' ? '✅ Compatible' : '⚠️ Warning', PHP_VERSION],
            ]
        );

        return 0;
    }
}
