<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class ShopifyPartnerEnforcementRiskCommand extends Command
{
    protected $signature = 'guardian:shopify:partner:enforcement-risk {--path= : Base path of Shopify app}';

    protected $description = 'Assess Shopify Partner account and app enforcement risk (delisting, app removal, revocation)';

    public function handle(): int
    {
        $this->info("Assessing Shopify Partner Enforcement Risk...\n");

        $basePath = $this->option('path') ? realpath($this->option('path')) : base_path();

        $result = Guardian::run([
            'base_path' => $basePath,
            'categories' => ['shopify_governance', 'shopify_security'],
        ]);

        $criticalCount = $result->criticalCount();
        $highCount = $result->highCount();

        $riskLevel = 'Low';
        if ($criticalCount > 0) {
            $riskLevel = 'CRITICAL (High probability of merchant app removal or delisting)';
        } elseif ($highCount > 1) {
            $riskLevel = 'HIGH (Risk of review removal or promotional badge delisting)';
        } elseif ($highCount > 0) {
            $riskLevel = 'MODERATE';
        }

        $this->table(
            ['Metric', 'Assessment'],
            [
                ['Enforcement Risk Level', $riskLevel],
                ['Critical Compliance Failures', $criticalCount],
                ['High Severity Issues', $highCount],
                ['Partner Policies Tracked', count(Guardian::governance()->policies())],
                ['Enforcement Types Modeled', count(Guardian::governance()->enforcementActions())],
            ]
        );

        if ($criticalCount > 0) {
            $this->error("\nImmediate corrective action required to avoid disciplinary actions from Shopify Partner Support.");

            return 2;
        }

        $this->info("\nNo critical enforcement risk indicators detected.");

        return 0;
    }
}
