<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum AppType: string
{
    case PUBLIC = 'public';
    case CUSTOM = 'custom';
    case EMBEDDED = 'embedded';
    case HEADLESS = 'headless';
    case THEME = 'theme';
    case EXTENSION = 'extension';
    case FUNCTION = 'function';
    case PAYMENTS = 'payments';
    case UNKNOWN = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public App',
            self::CUSTOM => 'Custom App',
            self::EMBEDDED => 'Embedded App',
            self::HEADLESS => 'Headless Storefront',
            self::THEME => 'Shopify Theme',
            self::EXTENSION => 'App Extension',
            self::FUNCTION => 'Shopify Function',
            self::PAYMENTS => 'Payments Application',
            self::UNKNOWN => 'General Application',
        };
    }
}
