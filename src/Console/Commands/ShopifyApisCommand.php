<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class ShopifyApisCommand extends Command
{
    protected $signature = 'guardian:shopify:apis';

    protected $description = 'List all official Shopify APIs tracked by Guardian intelligence registry';

    public function handle(): int
    {
        $this->info("Official Shopify APIs Registry\n");

        $apis = Guardian::apis()->all();
        $rows = [];

        foreach ($apis as $api) {
            $rows[] = [
                $api['api_id'],
                $api['name'],
                $api['latest_stable_version'],
                $api['authentication_type'],
                $api['access_restrictions'],
            ];
        }

        $this->table(['ID', 'Name', 'Latest Version', 'Authentication', 'Restrictions'], $rows);

        return 0;
    }
}
