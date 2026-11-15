<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Monitoring\TelemetryManager;

class MonitoringCommand extends Command
{
    protected $signature = 'guardian:monitor';

    protected $description = 'View recent application telemetry, SQL queries, and Shopify GraphQL API metrics';

    public function handle(): int
    {
        $this->info("🛡️ Guardian Observability Dashboard\n");

        $events = TelemetryManager::events();
        $reqCount = count($events['requests']);
        $queryCount = count($events['queries']);
        $apiCount = count($events['api_calls']);
        $excCount = count($events['exceptions']);

        $this->table(
            ['Collector', 'Status', 'Recorded Events'],
            [
                ['HTTP Requests', config('shopify-ai-audit.monitoring.collectors.requests') ? '🟢 Enabled' : '⏸️ Disabled', $reqCount],
                ['SQL Queries & N+1', config('shopify-ai-audit.monitoring.collectors.queries') ? '🟢 Enabled' : '⏸️ Disabled', $queryCount],
                ['Shopify GraphQL Calls', config('shopify-ai-audit.monitoring.collectors.api_calls') ? '🟢 Enabled' : '⏸️ Disabled', $apiCount],
                ['Exceptions', config('shopify-ai-audit.monitoring.collectors.exceptions') ? '🟢 Enabled' : '⏸️ Disabled', $excCount],
            ]
        );

        return 0;
    }
}
