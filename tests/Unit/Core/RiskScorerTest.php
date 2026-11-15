<?php

namespace Subhash\ShopifyAiAudit\Tests\Unit\Core;

use Subhash\ShopifyAiAudit\Core\RiskScorer;
use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\CoverageReport;
use Subhash\ShopifyAiAudit\Enums\AuditCategory;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\FindingStatus;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class RiskScorerTest extends TestCase
{
    public function test_clean_audit_scores_a_plus(): void
    {
        $scorer = new RiskScorer;
        $coverage = new CoverageReport(10, 10, 100.0);
        $score = $scorer->calculate([], $coverage);

        $this->assertEquals(0.0, $score->score);
        $this->assertEquals('A+', $score->grade);
        $this->assertEquals('Clean', $score->level);
    }

    public function test_critical_finding_results_in_f_grade(): void
    {
        $scorer = new RiskScorer;
        $coverage = new CoverageReport(10, 10, 100.0);

        $findings = [
            new AuditFinding(
                id: 'find_1',
                ruleId: 'SHOPIFY-SEC-001',
                title: 'Insecure OAuth',
                description: 'Critical issue',
                category: AuditCategory::SHOPIFY_SECURITY,
                severity: Severity::CRITICAL,
                confidence: Confidence::HIGH,
                verificationVerdict: VerificationVerdict::CONFIRMED,
                status: FindingStatus::OPEN
            ),
        ];

        $score = $scorer->calculate($findings, $coverage);

        $this->assertGreaterThan(0.0, $score->score);
        $this->assertEquals('F', $score->grade);
        $this->assertEquals('Critical', $score->level);
        $this->assertEquals(1, $score->criticalCount);
    }
}
