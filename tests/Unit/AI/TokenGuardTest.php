<?php

namespace Subhash\ShopifyAiAudit\Tests\Unit\AI;

use Subhash\ShopifyAiAudit\AI\Guard\StandaloneTokenGuard;
use Subhash\ShopifyAiAudit\DTOs\AIExecutionContext;
use Subhash\ShopifyAiAudit\DTOs\AIUsage;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class TokenGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        StandaloneTokenGuard::resetSpend();
    }

    public function test_authorizes_and_reserves_tokens_within_budget(): void
    {
        $guard = new StandaloneTokenGuard;
        $context = new AIExecutionContext(
            taskId: 'task_1',
            agentId: 'coordinator',
            providerId: 'openai',
            model: 'gpt-4o',
            estimatedPromptTokens: 1000,
            estimatedCompletionTokens: 500
        );

        $decision = $guard->authorize($context);
        $this->assertTrue($decision->isAllowed);

        $reservation = $guard->reserve($context);
        $this->assertNotNull($reservation->reservationId);

        // Reconcile spend
        $usage = new AIUsage(1000, 500, 1500, 0.05);
        $guard->reconcile($reservation, $usage);

        $spend = StandaloneTokenGuard::currentSpend();
        $this->assertEquals(0.05, $spend['daily_usd']);
    }

    public function test_rejects_when_budget_exceeded(): void
    {
        config(['shopify-ai-audit.ai.daily_budget_usd' => 1.00]);

        $guard = new StandaloneTokenGuard;
        $context = new AIExecutionContext('task_2', 'agent', 'openai', 'gpt-4o', 1000, 500);

        // Exceed budget
        $reservation = $guard->reserve($context);
        $guard->reconcile($reservation, new AIUsage(100000, 50000, 150000, 2.50));

        $decision = $guard->authorize($context);
        $this->assertFalse($decision->isAllowed);
        $this->assertStringContainsString('Daily AI budget', $decision->reason);
    }
}
