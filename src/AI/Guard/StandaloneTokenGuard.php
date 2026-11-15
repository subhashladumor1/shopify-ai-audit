<?php

namespace Subhash\ShopifyAiAudit\AI\Guard;

use Subhash\ShopifyAiAudit\Contracts\TokenGuard;
use Subhash\ShopifyAiAudit\DTOs\AIExecutionContext;
use Subhash\ShopifyAiAudit\DTOs\AIUsage;
use Subhash\ShopifyAiAudit\DTOs\GuardDecision;
use Subhash\ShopifyAiAudit\DTOs\TokenReservation;

class StandaloneTokenGuard implements TokenGuard
{
    /**
     * @var array<string, float> In-memory tracking fallback
     */
    protected static array $spendTracker = [
        'daily_usd' => 0.0,
        'monthly_usd' => 0.0,
    ];

    public function authorize(AIExecutionContext $context): GuardDecision
    {
        $dailyBudget = (float) config('shopify-ai-audit.ai.daily_budget_usd', 10.00);
        $monthlyBudget = (float) config('shopify-ai-audit.ai.monthly_budget_usd', 100.00);

        if (self::$spendTracker['daily_usd'] >= $dailyBudget) {
            return GuardDecision::rejected(
                reason: "Daily AI budget of \${$dailyBudget} has been reached. AI execution is suspended until tomorrow.",
                policy: 'DAILY_BUDGET_EXCEEDED'
            );
        }

        if (self::$spendTracker['monthly_usd'] >= $monthlyBudget) {
            return GuardDecision::rejected(
                reason: "Monthly AI budget of \${$monthlyBudget} has been reached.",
                policy: 'MONTHLY_BUDGET_EXCEEDED'
            );
        }

        $remaining = max(0.0, $dailyBudget - self::$spendTracker['daily_usd']);

        return GuardDecision::approved('Request approved within budget limits.', $remaining);
    }

    public function reserve(AIExecutionContext $context): TokenReservation
    {
        $reservationId = 'res_'.bin2hex(random_bytes(8));
        $estCost = ($context->estimatedPromptTokens + $context->estimatedCompletionTokens) * 0.000005;

        return new TokenReservation(
            reservationId: $reservationId,
            estimatedInputTokens: $context->estimatedPromptTokens,
            estimatedOutputTokens: $context->estimatedCompletionTokens,
            estimatedCostUsd: $estCost,
            agentId: $context->agentId,
            providerId: $context->providerId,
            model: $context->model,
            createdAtTimestamp: time()
        );
    }

    public function reconcile(TokenReservation $reservation, AIUsage $usage): void
    {
        self::$spendTracker['daily_usd'] += $usage->costUsd;
        self::$spendTracker['monthly_usd'] += $usage->costUsd;
    }

    public static function resetSpend(): void
    {
        self::$spendTracker['daily_usd'] = 0.0;
        self::$spendTracker['monthly_usd'] = 0.0;
    }

    public static function currentSpend(): array
    {
        return self::$spendTracker;
    }
}
