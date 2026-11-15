<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class ShopifyPartnerPoliciesCommand extends Command
{
    protected $signature = 'guardian:shopify:partner:policies';

    protected $description = 'List official Shopify Partner Program policies, agreements, and key compliance clauses';

    public function handle(): int
    {
        $this->info("Shopify Partner Program Policies & Agreements\n");

        $policies = Guardian::governance()->policies();
        $rows = [];

        foreach ($policies as $p) {
            $rows[] = [
                $p['id'],
                $p['title'],
                $p['url'],
                implode("\n", $p['key_clauses']),
            ];
        }

        $this->table(['Policy ID', 'Title', 'Official URL', 'Key Clauses'], $rows);

        return 0;
    }
}
