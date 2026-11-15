<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class ProviderCapabilities
{
    public function __construct(
        public readonly bool $supportsToolCalling,
        public readonly bool $supportsStructuredOutputs,
        public readonly bool $supportsStreaming,
        public readonly bool $supportsVision,
        public readonly int $contextWindow,
        public readonly int $maxOutputTokens,
    ) {}
}
