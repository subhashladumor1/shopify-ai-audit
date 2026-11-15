<?php

namespace Subhash\ShopifyAiAudit\Tests\Unit\Core;

use Subhash\ShopifyAiAudit\Core\ProjectDiscovery;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class ProjectDiscoveryTest extends TestCase
{
    public function test_discovers_laravel_project(): void
    {
        $discovery = new ProjectDiscovery;
        $manifest = $discovery->discover(__DIR__.'/../../..');

        $this->assertNotNull($manifest);
        $this->assertIsArray($manifest->detectedFrameworks);
    }
}
