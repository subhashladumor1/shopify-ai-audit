<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\Facades\Guardian;
use Subhash\ShopifyAiAudit\Reports\ReportManager;

class ReportExportCommand extends Command
{
    protected $signature = 'guardian:report
                            {--path= : Base path of project}
                            {--format=html : Export format (html, json, sarif, markdown, csv)}
                            {--output= : Output file destination}';

    protected $description = 'Generate and export an audit report in HTML, JSON, SARIF, Markdown, or CSV format';

    public function handle(): int
    {
        $format = (string) $this->option('format');
        $basePath = $this->option('path') ? realpath($this->option('path')) : base_path();

        $this->info("Generating {$format} report for project at: {$basePath}...");

        $result = Guardian::run(['base_path' => $basePath]);

        $manager = new ReportManager;
        $content = $manager->generate($result, $format);

        $output = $this->option('output');
        if ($output) {
            file_put_contents($output, $content);
            $this->info("✅ Report successfully saved to: {$output}");
        } else {
            $defaultFile = 'guardian-audit-'.date('Ymd-His').'.'.($format === 'markdown' ? 'md' : $format);
            file_put_contents($defaultFile, $content);
            $this->info('✅ Report successfully saved to: '.getcwd().DIRECTORY_SEPARATOR.$defaultFile);
        }

        return 0;
    }
}
