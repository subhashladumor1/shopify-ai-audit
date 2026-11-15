<?php

namespace Subhash\ShopifyAiAudit;

use Illuminate\Support\ServiceProvider;
use Subhash\ShopifyAiAudit\AI\Guard\StandaloneTokenGuard;
use Subhash\ShopifyAiAudit\AI\Skills\SkillsSecuritySupervisor;
use Subhash\ShopifyAiAudit\AI\Tools\ToolRegistry;
use Subhash\ShopifyAiAudit\Console\Commands\AuditCommand;
use Subhash\ShopifyAiAudit\Console\Commands\HealthCommand;
use Subhash\ShopifyAiAudit\Console\Commands\MonitoringCommand;
use Subhash\ShopifyAiAudit\Console\Commands\RemediationPromptCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ReportExportCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyApisCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyApiVersionsCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyAppStoreAuditCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyCheckVersionCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyPartnerEnforcementRiskCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyPartnerPoliciesCommand;
use Subhash\ShopifyAiAudit\Console\Commands\ShopifyPartnerSunsetPlanCommand;
use Subhash\ShopifyAiAudit\Contracts\TokenGuard;
use Subhash\ShopifyAiAudit\Core\AuditManager;
use Subhash\ShopifyAiAudit\Core\AuditOrchestrator;
use Subhash\ShopifyAiAudit\Core\ProjectDiscovery;
use Subhash\ShopifyAiAudit\Core\RuleRegistry;
use Subhash\ShopifyAiAudit\Core\ScannerRegistry;
use Subhash\ShopifyAiAudit\Monitoring\TelemetryManager;
use Subhash\ShopifyAiAudit\Remediation\RemediationManager;
use Subhash\ShopifyAiAudit\Reports\ReportManager;
use Subhash\ShopifyAiAudit\Scanners\Cloudflare\CloudflareScanner;
use Subhash\ShopifyAiAudit\Scanners\Database\DatabaseScanner;
use Subhash\ShopifyAiAudit\Scanners\Laravel\LaravelSecurityScanner;
use Subhash\ShopifyAiAudit\Scanners\Performance\PerformanceScanner;
use Subhash\ShopifyAiAudit\Scanners\React\FrontendScanner;
use Subhash\ShopifyAiAudit\Scanners\Security\ShopifySecurityScanner;
use Subhash\ShopifyAiAudit\Scanners\SEO\SeoScanner;
use Subhash\ShopifyAiAudit\Scanners\Shopify\ShopifyApiScanner;
use Subhash\ShopifyAiAudit\Scanners\Shopify\ShopifyAppStoreScanner;
use Subhash\ShopifyAiAudit\Scanners\Shopify\ShopifyGovernanceScanner;
use Subhash\ShopifyAiAudit\Shopify\API\ApiRegistry;
use Subhash\ShopifyAiAudit\Shopify\Governance\PartnerPolicyRegistry;

class ShopifyAiAuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shopify-ai-audit.php', 'shopify-ai-audit');

        $this->app->singleton(ProjectDiscovery::class, function () {
            return new ProjectDiscovery;
        });

        $this->app->singleton(ScannerRegistry::class, function () {
            $registry = new ScannerRegistry;
            $registry->register(new ShopifySecurityScanner);
            $registry->register(new ShopifyApiScanner);
            $registry->register(new ShopifyAppStoreScanner);
            $registry->register(new ShopifyGovernanceScanner);
            $registry->register(new LaravelSecurityScanner);
            $registry->register(new FrontendScanner);
            $registry->register(new DatabaseScanner);
            $registry->register(new CloudflareScanner);
            $registry->register(new PerformanceScanner);
            $registry->register(new SeoScanner);

            return $registry;
        });

        $this->app->singleton(RuleRegistry::class, function () {
            return new RuleRegistry;
        });

        $this->app->singleton(ApiRegistry::class, function () {
            return new ApiRegistry;
        });

        $this->app->singleton(PartnerPolicyRegistry::class, function () {
            return new PartnerPolicyRegistry;
        });

        $this->app->singleton(AuditOrchestrator::class, function ($app) {
            return new AuditOrchestrator(
                $app->make(ScannerRegistry::class)
            );
        });

        $this->app->singleton(AuditManager::class, function ($app) {
            return new AuditManager(
                $app->make(ProjectDiscovery::class),
                $app->make(ScannerRegistry::class),
                $app->make(RuleRegistry::class),
                $app->make(AuditOrchestrator::class),
                $app->make(ApiRegistry::class),
                $app->make(PartnerPolicyRegistry::class)
            );
        });

        $this->app->singleton(TokenGuard::class, function () {
            return new StandaloneTokenGuard;
        });

        $this->app->singleton(ToolRegistry::class, function () {
            return new ToolRegistry;
        });

        $this->app->singleton(SkillsSecuritySupervisor::class, function () {
            return new SkillsSecuritySupervisor;
        });

        $this->app->singleton(TelemetryManager::class, function () {
            return new TelemetryManager;
        });

        $this->app->singleton(RemediationManager::class, function () {
            return new RemediationManager;
        });

        $this->app->singleton(ReportManager::class, function () {
            return new ReportManager;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/shopify-ai-audit.php' => config_path('shopify-ai-audit.php'),
            ], 'shopify-ai-audit-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/shopify-ai-audit'),
            ], 'shopify-ai-audit-views');

            $this->commands([
                AuditCommand::class,
                HealthCommand::class,
                ShopifyApisCommand::class,
                ShopifyApiVersionsCommand::class,
                ShopifyCheckVersionCommand::class,
                ShopifyAppStoreAuditCommand::class,
                ShopifyPartnerPoliciesCommand::class,
                ShopifyPartnerEnforcementRiskCommand::class,
                ShopifyPartnerSunsetPlanCommand::class,
                RemediationPromptCommand::class,
                MonitoringCommand::class,
                ReportExportCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'guardian');

        if (config('shopify-ai-audit.dashboard.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if (config('shopify-ai-audit.api.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
    }
}
