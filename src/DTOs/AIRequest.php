<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class AIRequest
{
    /**
     * @param  array<int, array<string, string>>  $messages
     * @param  array<int, array<string, mixed>>  $tools
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly array $tools = [],
        public readonly float $temperature = 0.2,
        public readonly int $maxTokens = 4096,
        public readonly ?array $responseFormat = null,
        public readonly array $options = [],
    ) {}
}
