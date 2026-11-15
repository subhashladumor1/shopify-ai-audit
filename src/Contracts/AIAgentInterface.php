<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AIExecutionContext;
use Subhash\ShopifyAiAudit\DTOs\AIResponse;

interface AIAgentInterface
{
    public function id(): string;

    public function name(): string;

    public function description(): string;

    public function systemPrompt(): string;

    /**
     * @return array<int, string>
     */
    public function skills(): array;

    /**
     * @return array<int, string>
     */
    public function tools(): array;

    public function execute(string $task, AIExecutionContext $context): AIResponse;
}
