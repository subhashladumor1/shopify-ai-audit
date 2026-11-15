<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class ShopifyAppStoreAuditCommand extends Command
{
    protected $signature = 'guardian:shopify:app-store:audit {--path= : Base path of Shopify app}';

    protected $description = 'Audit Shopify app against official App Store Review requirements and best practices';

    public function handle(): int
    {
        $this->info("Evaluating Shopify App Store Readiness...\n");

        $basePath = $this->option('path') ? realpath($this->option('path')) : base_path();

        $result = Guardian::run([
            'base_path' => $basePath,
            'categories' => ['shopify_app_store', 'shopify_security', 'shopify_governance'],
        ]);

        $failedGov = array_filter($result->findings, fn ($f) => str_starts_with($f->ruleId, 'SHOPIFY-GOV-') || str_starts_with($f->ruleId, 'SHOPIFY-APP-'));

        if (empty($failedGov)) {
            $this->info('🌟 Ready for Review! No blocking App Store compliance issues detected.');

            return 0;
        }

        $this->warn('⚠️ Found '.count($failedGov).' App Store compliance findings that need attention before submission:');

        $rows = [];
        foreach ($failedGov as $f) {
            $rows[] = [$f->ruleId, $f->severity->label(), $f->title, $f->recommendation];
        }

        $this->table(['Rule', 'Severity', 'Requirement', 'Action Required'], $rows);

        return 1;
    }
}
