<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;

class AuditCommand extends Command
{
    protected $signature = 'guardian:audit
                            {--path= : Base path of project to audit}
                            {--security : Run only security checks}
                            {--shopify : Run only Shopify checks}
                            {--laravel : Run only Laravel checks}
                            {--frontend : Run only frontend checks}
                            {--database : Run only database checks}
                            {--cloudflare : Run only Cloudflare checks}
                            {--performance : Run only performance checks}
                            {--seo : Run only SEO checks}
                            {--min-severity=low : Minimum severity threshold (critical, high, medium, low)}
                            {--ai : Include guarded AI analysis}
                            {--format=table : Output format (table, json)}';

    protected $description = 'Execute automated Shopify and Laravel security and compliance audit';

    public function handle(): int
    {
        $this->info("🛡️ Starting Guardian Security & Compliance Audit...\n");

        $basePath = $this->option('path') ? realpath($this->option('path')) : base_path();
        if (! $basePath || ! is_dir($basePath)) {
            $this->error("Invalid base path: {$this->option('path')}");

            return 1;
        }

        $categories = [];
        if ($this->option('security')) {
            $categories[] = 'shopify_security';
            $categories[] = 'laravel_security';
            $categories[] = 'php_security';
        }
        if ($this->option('shopify')) {
            $categories[] = 'shopify_security';
            $categories[] = 'shopify_api';
            $categories[] = 'shopify_app_store';
            $categories[] = 'shopify_governance';
        }
        if ($this->option('laravel')) {
            $categories[] = 'laravel_security';
            $categories[] = 'php_security';
        }
        if ($this->option('frontend')) {
            $categories[] = 'frontend';
        }
        if ($this->option('database')) {
            $categories[] = 'database';
        }
        if ($this->option('cloudflare')) {
            $categories[] = 'cloudflare';
        }
        if ($this->option('performance')) {
            $categories[] = 'performance';
        }
        if ($this->option('seo')) {
            $categories[] = 'seo';
        }

        $options = [
            'base_path' => $basePath,
            'categories' => $categories,
            'min_severity' => $this->option('min-severity') ?: 'low',
            'include_ai' => (bool) $this->option('ai'),
        ];

        $result = Guardian::run($options);

        if ($this->option('format') === 'json') {
            $this->line(json_encode($result->toSummaryArray(), JSON_PRETTY_PRINT));

            return $result->criticalCount() > 0 ? 2 : 0;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Audit ID', $result->auditId],
                ['Status', $result->status],
                ['Duration', round($result->durationMs, 2).' ms'],
                ['Risk Score', "{$result->riskScore->score}/100 (Grade: {$result->riskScore->grade})"],
                ['Risk Level', $result->riskScore->level],
                ['Files Scanned', "{$result->coverage->totalFilesScanned} / {$result->coverage->totalFilesDiscovered} (".round($result->coverage->coveragePercentage, 1).'%)'],
                ['Total Findings', count($result->findings)],
                ['Critical', $result->criticalCount()],
                ['High', $result->highCount()],
                ['Medium', $result->mediumCount()],
                ['Low', $result->lowCount()],
            ]
        );

        if (! empty($result->findings)) {
            $this->newLine();
            $this->warn('⚠️ Findings Summary:');

            $rows = [];
            foreach ($result->findings as $f) {
                $rows[] = [
                    $f->ruleId,
                    $f->severity->label(),
                    $f->title,
                    ($f->filePath ?? 'unknown').($f->lineStart ? ":{$f->lineStart}" : ''),
                ];
            }

            $this->table(['Rule ID', 'Severity', 'Title', 'Location'], $rows);
        } else {
            $this->newLine();
            $this->info('✅ Clean bill of health! No vulnerabilities detected.');
        }

        return $result->criticalCount() > 0 ? 2 : 0;
    }
}
