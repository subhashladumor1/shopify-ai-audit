<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum AuditCategory: string
{
    case SHOPIFY_SECURITY = 'shopify_security';
    case SHOPIFY_API = 'shopify_api';
    case SHOPIFY_APP_STORE = 'shopify_app_store';
    case SHOPIFY_GOVERNANCE = 'shopify_governance';
    case LARAVEL_SECURITY = 'laravel_security';
    case PHP_SECURITY = 'php_security';
    case FRONTEND = 'frontend';
    case DATABASE = 'database';
    case CLOUDFLARE = 'cloudflare';
    case PERFORMANCE = 'performance';
    case SEO = 'seo';
    case AI_SECURITY = 'ai_security';

    public function label(): string
    {
        return match ($this) {
            self::SHOPIFY_SECURITY => 'Shopify Security',
            self::SHOPIFY_API => 'Shopify API Intelligence',
            self::SHOPIFY_APP_STORE => 'Shopify App Store Readiness',
            self::SHOPIFY_GOVERNANCE => 'Shopify Partner Governance',
            self::LARAVEL_SECURITY => 'Laravel Security',
            self::PHP_SECURITY => 'PHP Security',
            self::FRONTEND => 'Frontend Security & Quality',
            self::DATABASE => 'Database & Query Engine',
            self::CLOUDFLARE => 'Cloudflare & Infrastructure',
            self::PERFORMANCE => 'Performance & Core Web Vitals',
            self::SEO => 'Technical SEO',
            self::AI_SECURITY => 'AI Security & Skills Supply Chain',
        };
    }
}
