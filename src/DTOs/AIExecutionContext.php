<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class AIExecutionContext
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        public readonly string $taskId,
        public readonly string $agentId,
        public readonly string $providerId,
        public readonly string $model,
        public readonly int $estimatedPromptTokens,
        public readonly int $estimatedCompletionTokens,
        public readonly ?string $userId = null,
        public readonly ?string $projectId = null,
        public readonly array $parameters = [],
    ) {}
}
