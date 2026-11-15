<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AIExecutionContext;
use Subhash\ShopifyAiAudit\DTOs\AIUsage;
use Subhash\ShopifyAiAudit\DTOs\GuardDecision;
use Subhash\ShopifyAiAudit\DTOs\TokenReservation;

interface TokenGuard
{
    /**
     * Authorize whether the AI execution context is allowed to proceed.
     */
    public function authorize(AIExecutionContext $context): GuardDecision;

    /**
     * Reserve an estimated budget of tokens before invoking the AI provider.
     */
    public function reserve(AIExecutionContext $context): TokenReservation;

    /**
     * Reconcile actual token counts and cost once the invocation completes.
     */
    public function reconcile(TokenReservation $reservation, AIUsage $usage): void;
}
