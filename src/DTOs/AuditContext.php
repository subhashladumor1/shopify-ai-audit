<?php

namespace Subhash\ShopifyAiAudit\DTOs;

use Subhash\ShopifyAiAudit\Enums\Severity;

class AuditContext
{
    /**
     * @param  array<int, string>  $categories
     * @param  array<int, string>  $excludedPaths
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly string $basePath,
        public readonly ProjectManifest $manifest,
        public readonly string $mode = 'read_only',
        public readonly array $categories = [],
        public readonly Severity $minSeverity = Severity::LOW,
        public readonly array $excludedPaths = [],
        public readonly bool $isIncremental = false,
        public readonly bool $includeAi = false,
        public readonly array $options = [],
    ) {}

    public function shouldScanCategory(string $category): bool
    {
        if (empty($this->categories)) {
            return true;
        }

        return in_array($category, $this->categories, true);
    }
}
