<?php

namespace Subhash\ShopifyAiAudit\DTOs;

use Subhash\ShopifyAiAudit\Enums\AppCategory;
use Subhash\ShopifyAiAudit\Enums\AppType;

class ProjectManifest
{
    /**
     * @param  array<int, string>  $detectedFrameworks
     * @param  array<int, string>  $detectedApis
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $basePath,
        public readonly bool $isLaravel,
        public readonly ?string $laravelVersion,
        public readonly bool $isShopifyApp,
        public readonly AppType $shopifyAppType,
        public readonly AppCategory $shopifyCategory,
        public readonly array $detectedFrameworks = [],
        public readonly array $detectedApis = [],
        public readonly bool $hasFrontend = false,
        public readonly bool $hasDatabase = false,
        public readonly bool $hasCloudflare = false,
        public readonly array $metadata = [],
    ) {}
}
