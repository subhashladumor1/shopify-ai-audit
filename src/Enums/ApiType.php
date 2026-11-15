<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum ApiType: string
{
    case ADMIN_GRAPHQL = 'admin_graphql';
    case STOREFRONT_GRAPHQL = 'storefront_graphql';
    case CUSTOMER_ACCOUNT = 'customer_account';
    case SHOPIFYQL = 'shopifyql';
    case PAYMENTS_APPS = 'payments_apps';
    case LEGACY_ADMIN_REST = 'legacy_admin_rest';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN_GRAPHQL => 'Shopify Admin GraphQL API',
            self::STOREFRONT_GRAPHQL => 'Shopify Storefront API (GraphQL)',
            self::CUSTOMER_ACCOUNT => 'Customer Account API',
            self::SHOPIFYQL => 'ShopifyQL Analytics API',
            self::PAYMENTS_APPS => 'Payments Apps API',
            self::LEGACY_ADMIN_REST => 'Legacy Admin REST API (Deprecated)',
        };
    }
}
