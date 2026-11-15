<?php

namespace Subhash\ShopifyAiAudit\Tests\Unit\Core;

use Subhash\ShopifyAiAudit\Core\FindingNormalizer;
use Subhash\ShopifyAiAudit\DTOs\RuleResult;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Tests\TestCase;

class FindingNormalizerTest extends TestCase
{
    public function test_normalizes_and_deduplicates_findings(): void
    {
        $normalizer = new FindingNormalizer;

        $scanResult = new ScanResult(
            scannerClass: 'SampleScanner',
            ruleResults: [
                RuleResult::failed('LARAVEL-SEC-001', 'SQLi', Severity::CRITICAL, Confidence::HIGH, 'app/User.php', 10, 10),
                RuleResult::failed('LARAVEL-SEC-001', 'SQLi Duplicate', Severity::CRITICAL, Confidence::HIGH, 'app/User.php', 10, 10),
                RuleResult::passed('LARAVEL-SEC-002', 'Passed rule'),
            ]
        );

        $findings = $normalizer->normalize([$scanResult]);

        // Duplicate ruleId + filePath + lineStart should be filtered
        $this->assertCount(1, $findings);
        $this->assertEquals('LARAVEL-SEC-001', $findings[0]->ruleId);
        $this->assertEquals('app/User.php', $findings[0]->filePath);
    }
}
