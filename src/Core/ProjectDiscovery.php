<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\DTOs\ProjectManifest;
use Subhash\ShopifyAiAudit\Enums\AppCategory;
use Subhash\ShopifyAiAudit\Enums\AppType;

class ProjectDiscovery
{
    public function __construct(
        protected readonly FrameworkDetector $frameworkDetector = new FrameworkDetector
    ) {}

    public function discover(string $basePath): ProjectManifest
    {
        $detection = $this->frameworkDetector->detect($basePath);

        $appType = $this->detectAppType($basePath, $detection);
        $appCategory = $this->detectAppCategory($basePath, $detection);

        $detectedFrameworks = [];
        if ($detection['is_laravel']) {
            $detectedFrameworks[] = 'laravel';
        }

        $pkgDeps = $detection['package_dependencies'];
        if (isset($pkgDeps['react']) || isset($pkgDeps['react-dom'])) {
            $detectedFrameworks[] = 'react';
        }
        if (isset($pkgDeps['next'])) {
            $detectedFrameworks[] = 'nextjs';
        }
        if (isset($pkgDeps['typescript']) || file_exists($basePath.DIRECTORY_SEPARATOR.'tsconfig.json')) {
            $detectedFrameworks[] = 'typescript';
        }
        if (isset($pkgDeps['vue'])) {
            $detectedFrameworks[] = 'vue';
        }

        $detectedApis = $this->detectApis($basePath);

        $hasFrontend = ! empty(array_intersect($detectedFrameworks, ['react', 'nextjs', 'typescript', 'vue']))
            || is_dir($basePath.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js')
            || is_dir($basePath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'components');

        $hasDatabase = file_exists($basePath.DIRECTORY_SEPARATOR.'database')
            || file_exists($basePath.DIRECTORY_SEPARATOR.'.env');

        $hasCloudflare = file_exists($basePath.DIRECTORY_SEPARATOR.'wrangler.toml')
            || file_exists($basePath.DIRECTORY_SEPARATOR.'.cloudflare');

        return new ProjectManifest(
            basePath: $basePath,
            isLaravel: $detection['is_laravel'],
            laravelVersion: $detection['laravel_version'],
            isShopifyApp: $detection['is_shopify'],
            shopifyAppType: $appType,
            shopifyCategory: $appCategory,
            detectedFrameworks: $detectedFrameworks,
            detectedApis: $detectedApis,
            hasFrontend: $hasFrontend,
            hasDatabase: $hasDatabase,
            hasCloudflare: $hasCloudflare,
            metadata: [
                'composer_dependencies_count' => count($detection['composer_dependencies']),
                'package_dependencies_count' => count($detection['package_dependencies']),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $detection
     */
    protected function detectAppType(string $basePath, array $detection): AppType
    {
        if (file_exists($basePath.DIRECTORY_SEPARATOR.'shopify.app.toml')) {
            $toml = (string) file_get_contents($basePath.DIRECTORY_SEPARATOR.'shopify.app.toml');
            if (str_contains($toml, 'embedded = true')) {
                return AppType::EMBEDDED;
            }
            if (str_contains($toml, 'distribution = "single_merchant"')) {
                return AppType::CUSTOM;
            }
            if (str_contains($toml, 'distribution = "public"')) {
                return AppType::PUBLIC;
            }
        }

        if (is_dir($basePath.DIRECTORY_SEPARATOR.'extensions')) {
            return AppType::EXTENSION;
        }

        if (file_exists($basePath.DIRECTORY_SEPARATOR.'layout'.DIRECTORY_SEPARATOR.'theme.liquid')) {
            return AppType::THEME;
        }

        if ($detection['is_shopify']) {
            return AppType::EMBEDDED;
        }

        return AppType::UNKNOWN;
    }

    /**
     * @param  array<string, mixed>  $detection
     */
    protected function detectAppCategory(string $basePath, array $detection): AppCategory
    {
        if (is_dir($basePath.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.'theme-extension')) {
            return AppCategory::ONLINE_STORE;
        }

        if (is_dir($basePath.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.'checkout-ui')) {
            return AppCategory::CHECKOUT;
        }

        return AppCategory::GENERAL;
    }

    /**
     * @return array<int, string>
     */
    protected function detectApis(string $basePath): array
    {
        $apis = [];

        // Check for Admin GraphQL
        $apis[] = 'admin_graphql';

        // Check if storefront or customer account or payments are present
        if (is_dir($basePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers')) {
            $apis[] = 'storefront_graphql';
        }

        return $apis;
    }
}
