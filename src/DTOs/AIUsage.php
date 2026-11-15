<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class AIUsage
{
    public function __construct(
        public readonly int $promptTokens,
        public readonly int $completionTokens,
        public readonly int $totalTokens,
        public readonly float $costUsd,
        public readonly float $durationSeconds = 0.0,
    ) {}
}
