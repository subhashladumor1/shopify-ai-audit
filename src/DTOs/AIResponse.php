<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class AIResponse
{
    /**
     * @param  array<int, array<string, mixed>>  $toolCalls
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $content,
        public readonly AIUsage $usage,
        public readonly array $toolCalls = [],
        public readonly ?string $finishReason = null,
        public readonly array $meta = [],
    ) {}
}
