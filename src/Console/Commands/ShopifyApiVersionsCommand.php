<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class ShopifyApiVersionsCommand extends Command
{
    protected $signature = 'guardian:shopify:api:versions';

    protected $description = 'List Shopify quarterly API release versions, lifecycle status, and sunset dates';

    public function handle(): int
    {
        $this->info("Shopify Quarterly API Version Lifecycle\n");

        $versions = Guardian::apis()->versions();
        $rows = [];

        foreach ($versions as $ver => $meta) {
            $statusLabel = match ($meta['status']) {
                'latest_stable' => '🌟 Latest Stable',
                'supported' => '✅ Supported',
                'sunset' => '⛔ Sunset / Deprecated',
                default => '🧪 Unstable / Dev',
            };

            $rows[] = [
                $ver,
                $statusLabel,
                $meta['release_date'] ?? 'N/A',
                $meta['sunset_date'] ?? 'N/A',
            ];
        }

        $this->table(['Version', 'Status', 'Release Date', 'Sunset Date'], $rows);

        return 0;
    }
}
