<?php

namespace Subhash\ShopifyAiAudit\Tests\Feature;

use Subhash\ShopifyAiAudit\Facades\Guardian;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class AuditPipelineTest extends TestCase
{
    public function test_runs_full_audit_pipeline(): void
    {
        $result = Guardian::run([
            'base_path' => __DIR__.'/../..',
        ]);

        $this->assertNotNull($result);
        $this->assertEquals('completed', $result->status);
        $this->assertNotNull($result->riskScore);
        $this->assertNotNull($result->coverage);
        $this->assertNotEmpty($result->scanResults);
    }
}
