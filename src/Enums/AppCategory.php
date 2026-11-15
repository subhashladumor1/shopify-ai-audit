<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum AppCategory: string
{
    case ONLINE_STORE = 'online_store';
    case ADMIN = 'admin';
    case CHECKOUT = 'checkout';
    case SUBSCRIPTIONS = 'subscriptions';
    case PAYMENTS = 'payments';
    case POST_PURCHASE = 'post_purchase';
    case MOBILE_APP = 'mobile_app';
    case PRODUCT_SOURCING = 'product_sourcing';
    case SALES_CHANNEL = 'sales_channel';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE_STORE => 'Online Store (Storefront)',
            self::ADMIN => 'Shopify Admin App',
            self::CHECKOUT => 'Checkout & Functions',
            self::SUBSCRIPTIONS => 'Subscriptions & Recurring Orders',
            self::PAYMENTS => 'Payments Partner App',
            self::POST_PURCHASE => 'Post Purchase App',
            self::MOBILE_APP => 'Mobile App Builder',
            self::PRODUCT_SOURCING => 'Product Sourcing / Dropshipping',
            self::SALES_CHANNEL => 'Sales Channel',
            self::GENERAL => 'General App',
        };
    }
}
