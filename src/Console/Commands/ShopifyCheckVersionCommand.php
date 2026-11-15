<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class ShopifyCheckVersionCommand extends Command
{
    protected $signature = 'guardian:shopify:api:check-version {version : Quarterly version string (e.g. 2025-01)}';

    protected $description = 'Verify compatibility and support status of a specific Shopify API version';

    public function handle(): int
    {
        $version = (string) $this->argument('version');
        $check = Guardian::apis()->checkVersion($version);

        $this->info("Checking Shopify API Version: {$version}\n");

        if ($check['is_deprecated']) {
            $this->error("⛔ Version {$version} is DEPRECATED and sunset!");
            if (! empty($check['warning'])) {
                $this->warn($check['warning']);
            }

            return 1;
        }

        if ($check['is_latest']) {
            $this->info("🌟 Version {$version} is the LATEST STABLE release.");
        } else {
            $this->info("✅ Version {$version} is currently SUPPORTED.");
        }

        if (! empty($check['sunset_date'])) {
            $this->line('Sunset / Deprecation Date: '.$check['sunset_date']);
        }

        return 0;
    }
}
