<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;

class ShopifyPartnerSunsetPlanCommand extends Command
{
    protected $signature = 'guardian:shopify:partner:sunset-plan {app_name : Name of the app to sunset}';

    protected $description = 'Generate a compliant voluntary app deprecation and merchant continuity sunset roadmap';

    public function handle(): int
    {
        $appName = (string) $this->argument('app_name');

        $this->info("🛡️ Voluntary App Sunset Roadmap for [{$appName}]\n");

        $steps = [
            ['T-90 Days', 'Announce Retirement', 'Publish in-app banner, notify active merchants by email, and set listing to Unpublished.'],
            ['T-60 Days', 'Migration & Export', 'Provide automated data export (JSON/CSV) and publish migration guides to alternative apps.'],
            ['T-30 Days', 'Second Merchant Notice', 'Send final merchant reminder. Advise uninstallation date and contact support details.'],
            ['T-14 Days', 'Billing Freeze', 'Halt any new billing charges; issue refunds for remaining unused billing cycles if applicable.'],
            ['T-0 Days', 'App Shutdown', 'Cancel active AppSubscriptions via API, deregister webhooks, and purge merchant tokens.'],
            ['T+48 Hours', 'Data Erasure', 'Execute final GDPR/Privacy cleanup for all shops per Shopify Partner Agreement Section 8.'],
        ];

        $this->table(['Milestone', 'Action', 'Compliance Guidance'], $steps);

        $this->info("\nRoadmap generated according to official Shopify App Sunset guidelines.");

        return 0;
    }
}
