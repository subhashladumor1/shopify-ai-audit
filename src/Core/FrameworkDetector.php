<?php

namespace Subhash\ShopifyAiAudit\Core;

class FrameworkDetector
{
    /**
     * @return array<string, mixed>
     */
    public function detect(string $basePath): array
    {
        $composerJsonPath = $basePath.DIRECTORY_SEPARATOR.'composer.json';
        $packageJsonPath = $basePath.DIRECTORY_SEPARATOR.'package.json';

        $isLaravel = false;
        $laravelVersion = null;
        $composerDeps = [];
        $packageDeps = [];

        if (file_exists($composerJsonPath)) {
            $composerData = json_decode((string) file_get_contents($composerJsonPath), true) ?? [];
            $composerDeps = array_merge($composerData['require'] ?? [], $composerData['require-dev'] ?? []);

            if (isset($composerDeps['laravel/framework']) || isset($composerDeps['illuminate/support'])) {
                $isLaravel = true;
                $laravelVersion = $composerDeps['laravel/framework'] ?? $composerDeps['illuminate/support'] ?? null;
            }
        }

        if (file_exists($packageJsonPath)) {
            $packageData = json_decode((string) file_get_contents($packageJsonPath), true) ?? [];
            $packageDeps = array_merge($packageData['dependencies'] ?? [], $packageData['devDependencies'] ?? []);
        }

        $isShopify = $this->hasShopifySignatures($basePath, $composerDeps, $packageDeps);

        return [
            'is_laravel' => $isLaravel,
            'laravel_version' => $laravelVersion,
            'composer_dependencies' => $composerDeps,
            'package_dependencies' => $packageDeps,
            'is_shopify' => $isShopify,
        ];
    }

    /**
     * @param  array<string, string>  $composerDeps
     * @param  array<string, string>  $packageDeps
     */
    private function hasShopifySignatures(string $basePath, array $composerDeps, array $packageDeps): bool
    {
        // Check composer dependencies for common Shopify packages
        foreach (array_keys($composerDeps) as $dep) {
            if (str_contains(strtolower($dep), 'shopify')) {
                return true;
            }
        }

        // Check package.json dependencies for Shopify App Bridge / Polaris
        foreach (array_keys($packageDeps) as $dep) {
            if (str_contains(strtolower($dep), 'shopify') || str_contains($dep, '@shopify/')) {
                return true;
            }
        }

        // Check shopify.app.toml or shopify.web.toml or extensions
        if (
            file_exists($basePath.DIRECTORY_SEPARATOR.'shopify.app.toml') ||
            file_exists($basePath.DIRECTORY_SEPARATOR.'shopify.web.toml') ||
            is_dir($basePath.DIRECTORY_SEPARATOR.'extensions') ||
            is_dir($basePath.DIRECTORY_SEPARATOR.'shopify-doc-md')
        ) {
            return true;
        }

        return false;
    }
}
