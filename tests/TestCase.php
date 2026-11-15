<?php

namespace Subhash\ShopifyAiAudit\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Subhash\ShopifyAiAudit\ShopifyAiAuditServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ShopifyAiAuditServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('shopify-ai-audit.enabled', true);
        $app['config']->set('shopify-ai-audit.dashboard.enabled', true);
        $app['config']->set('shopify-ai-audit.api.enabled', true);
    }
}
