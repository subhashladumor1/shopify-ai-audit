<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AIRequest;
use Subhash\ShopifyAiAudit\DTOs\AIResponse;
use Subhash\ShopifyAiAudit\DTOs\ProviderCapabilities;

interface AIProviderInterface
{
    public function id(): string;

    public function name(): string;

    public function capabilities(string $model): ProviderCapabilities;

    public function isAvailable(): bool;

    public function execute(AIRequest $request): AIResponse;
}
