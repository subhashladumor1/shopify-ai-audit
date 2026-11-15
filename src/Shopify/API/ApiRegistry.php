<?php

namespace Subhash\ShopifyAiAudit\Shopify\API;

use Subhash\ShopifyAiAudit\Enums\ApiType;

class ApiRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $apis = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $versions = [];

    public function __construct()
    {
        $this->bootApis();
        $this->bootVersions();
    }

    protected function bootApis(): void
    {
        $this->apis = [
            'admin_graphql' => [
                'api_id' => 'admin_graphql',
                'name' => 'Shopify Admin GraphQL API',
                'type' => ApiType::ADMIN_GRAPHQL->value,
                'documentation_url' => 'https://shopify.dev/docs/api/admin-graphql/latest',
                'endpoint_pattern' => 'https://{shop}.myshopify.com/admin/api/{version}/graphql.json',
                'authentication_type' => 'X-Shopify-Access-Token',
                'versioning_strategy' => 'quarterly',
                'latest_stable_version' => '2025-01',
                'supported_versions' => ['2024-04', '2024-07', '2024-10', '2025-01', '2025-04'],
                'deprecated_versions' => ['2023-01', '2023-04', '2023-07', '2023-10', '2024-01'],
                'sunset_dates' => [
                    '2024-01' => '2025-01-01',
                    '2024-04' => '2025-04-01',
                    '2024-07' => '2025-07-01',
                    '2024-10' => '2025-10-01',
                ],
                'required_scopes' => ['read_products', 'write_products', 'read_orders'],
                'access_restrictions' => 'Public & Custom Apps with granted OAuth scopes',
                'last_verified_at' => '2026-11-15',
                'verification_status' => 'verified',
            ],
            'storefront_graphql' => [
                'api_id' => 'storefront_graphql',
                'name' => 'Shopify Storefront API',
                'type' => ApiType::STOREFRONT_GRAPHQL->value,
                'documentation_url' => 'https://shopify.dev/docs/api/storefront/latest',
                'endpoint_pattern' => 'https://{shop}.myshopify.com/api/{version}/graphql.json',
                'authentication_type' => 'X-Shopify-Storefront-Access-Token (Public) / Private Token (Backend)',
                'versioning_strategy' => 'quarterly',
                'latest_stable_version' => '2025-01',
                'supported_versions' => ['2024-04', '2024-07', '2024-10', '2025-01'],
                'deprecated_versions' => ['2023-10', '2024-01'],
                'sunset_dates' => [
                    '2024-01' => '2025-01-01',
                ],
                'required_scopes' => ['unauthenticated_read_product_listings'],
                'access_restrictions' => 'Headless storefronts, Hydrogen, mobile apps',
                'last_verified_at' => '2026-11-15',
                'verification_status' => 'verified',
            ],
            'customer_account' => [
                'api_id' => 'customer_account',
                'name' => 'Shopify Customer Account API',
                'type' => ApiType::CUSTOMER_ACCOUNT->value,
                'documentation_url' => 'https://shopify.dev/docs/api/customer/latest',
                'endpoint_pattern' => 'Discovered via /.well-known/customer-account-api',
                'authentication_type' => 'OAuth2 Bearer (Customer Access Token)',
                'versioning_strategy' => 'quarterly',
                'latest_stable_version' => '2025-01',
                'supported_versions' => ['2024-07', '2024-10', '2025-01'],
                'deprecated_versions' => ['2024-01'],
                'sunset_dates' => [
                    '2024-01' => '2025-01-01',
                ],
                'required_scopes' => ['customer-account-api:read', 'customer-account-api:write'],
                'access_restrictions' => 'New Customer Accounts enabled on store',
                'last_verified_at' => '2026-11-15',
                'verification_status' => 'verified',
            ],
            'shopifyql' => [
                'api_id' => 'shopifyql',
                'name' => 'ShopifyQL Analytics API',
                'type' => ApiType::SHOPIFYQL->value,
                'documentation_url' => 'https://shopify.dev/docs/api/shopifyql/latest',
                'endpoint_pattern' => 'Embedded in Admin GraphQL (shopifyqlQuery)',
                'authentication_type' => 'X-Shopify-Access-Token',
                'versioning_strategy' => 'quarterly',
                'latest_stable_version' => '2025-01',
                'supported_versions' => ['2024-07', '2024-10', '2025-01'],
                'deprecated_versions' => [],
                'sunset_dates' => [],
                'required_scopes' => ['read_analytics', 'read_reports'],
                'access_restrictions' => 'Stores with Advanced or Plus plan',
                'last_verified_at' => '2026-11-15',
                'verification_status' => 'verified',
            ],
            'payments_apps' => [
                'api_id' => 'payments_apps',
                'name' => 'Shopify Payments Apps API',
                'type' => ApiType::PAYMENTS_APPS->value,
                'documentation_url' => 'https://shopify.dev/docs/api/payments-apps/latest',
                'endpoint_pattern' => 'https://{shop}.myshopify.com/payments_apps/api/{version}/graphql.json',
                'authentication_type' => 'Mutual TLS / Partner Tokens',
                'versioning_strategy' => 'quarterly',
                'latest_stable_version' => '2025-01',
                'supported_versions' => ['2024-07', '2024-10', '2025-01'],
                'deprecated_versions' => [],
                'sunset_dates' => [],
                'required_scopes' => ['payments_apps'],
                'access_restrictions' => 'Approved Shopify Payments Partners Only',
                'last_verified_at' => '2026-11-15',
                'verification_status' => 'conditional',
            ],
        ];
    }

    protected function bootVersions(): void
    {
        $this->versions = [
            '2025-01' => ['status' => 'latest_stable', 'release_date' => '2025-01-01', 'sunset_date' => '2026-01-01'],
            '2024-10' => ['status' => 'supported', 'release_date' => '2024-10-01', 'sunset_date' => '2025-10-01'],
            '2024-07' => ['status' => 'supported', 'release_date' => '2024-07-01', 'sunset_date' => '2025-07-01'],
            '2024-04' => ['status' => 'supported', 'release_date' => '2024-04-01', 'sunset_date' => '2025-04-01'],
            '2024-01' => ['status' => 'sunset', 'release_date' => '2024-01-01', 'sunset_date' => '2025-01-01'],
            'unstable' => ['status' => 'development', 'release_date' => null, 'sunset_date' => null],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->apis;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $apiId): ?array
    {
        return $this->apis[$apiId] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function versions(): array
    {
        return $this->versions;
    }

    /**
     * @return array<string, mixed>
     */
    public function checkVersion(string $version): array
    {
        if ($version === 'unstable') {
            return [
                'version' => 'unstable',
                'is_supported' => true,
                'is_latest' => false,
                'is_deprecated' => false,
                'warning' => 'Unstable API version is for development only. Do not deploy to production.',
            ];
        }

        if (! isset($this->versions[$version])) {
            return [
                'version' => $version,
                'is_supported' => false,
                'is_latest' => false,
                'is_deprecated' => true,
                'warning' => "Version {$version} is completely unsupported and retired by Shopify.",
            ];
        }

        $info = $this->versions[$version];
        $isSupported = in_array($info['status'], ['latest_stable', 'supported'], true);
        $isLatest = $info['status'] === 'latest_stable';
        $isDeprecated = $info['status'] === 'sunset';

        return [
            'version' => $version,
            'is_supported' => $isSupported,
            'is_latest' => $isLatest,
            'is_deprecated' => $isDeprecated,
            'release_date' => $info['release_date'],
            'sunset_date' => $info['sunset_date'],
            'warning' => $isDeprecated ? "Version {$version} is sunset and will fail in production." : null,
        ];
    }
}
