<?php

namespace Subhash\ShopifyAiAudit\Tests\Feature;

use Subhash\ShopifyAiAudit\Facades\Guardian;
use Subhash\ShopifyAiAudit\Reports\ReportManager;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class ReportExportTest extends TestCase
{
    public function test_generates_all_supported_report_formats(): void
    {
        $result = Guardian::run(['base_path' => __DIR__.'/../..']);
        $manager = new ReportManager;

        $html = $manager->generate($result, 'html');
        $this->assertStringContainsString('<!DOCTYPE html>', $html);

        $json = $manager->generate($result, 'json');
        $this->assertJson($json);

        $sarif = $manager->generate($result, 'sarif');
        $this->assertJson($sarif);
        $this->assertStringContainsString('2.1.0', $sarif);

        $markdown = $manager->generate($result, 'markdown');
        $this->assertStringContainsString('# Shopify AI Audit Report', $markdown);

        $csv = $manager->generate($result, 'csv');
        $this->assertStringContainsString('Finding ID', $csv);
        $this->assertStringContainsString('Rule ID', $csv);
    }
}
