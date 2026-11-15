<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\Contracts\RiskScorerInterface;
use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\CoverageReport;
use Subhash\ShopifyAiAudit\DTOs\RiskScore;

class RiskScorer implements RiskScorerInterface
{
    /**
     * @param  array<int, AuditFinding>  $findings
     */
    public function calculate(array $findings, CoverageReport $coverage): RiskScore
    {
        $critical = 0;
        $high = 0;
        $medium = 0;
        $low = 0;
        $info = 0;

        $rawScore = 0.0;

        foreach ($findings as $finding) {
            $severity = $finding->severity;
            $confidence = $finding->confidence;

            $weight = $severity->weight();
            $multiplier = $confidence->multiplier();

            $rawScore += ($weight * $multiplier);

            match ($severity->value) {
                'critical' => $critical++,
                'high' => $high++,
                'medium' => $medium++,
                'low' => $low++,
                default => $info++,
            };
        }

        // Cap raw score between 0 and 100
        $finalScore = min(100.0, max(0.0, $rawScore));

        // Determine grade and level
        [$grade, $level] = $this->determineGradeAndLevel($finalScore, $critical, $high);

        return new RiskScore(
            score: $finalScore,
            grade: $grade,
            level: $level,
            findingsBySeverity: [
                'critical' => $critical,
                'high' => $high,
                'medium' => $medium,
                'low' => $low,
                'informational' => $info,
            ],
            criticalCount: $critical,
            highCount: $high,
            mediumCount: $medium,
            lowCount: $low,
            infoCount: $info
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function determineGradeAndLevel(float $score, int $critical, int $high): array
    {
        if ($critical > 0) {
            return ['F', 'Critical'];
        }

        if ($high > 2 || $score >= 50.0) {
            return ['D', 'High'];
        }

        if ($high > 0 || $score >= 30.0) {
            return ['C', 'Moderate'];
        }

        if ($score >= 15.0) {
            return ['B', 'Low'];
        }

        if ($score > 0.0) {
            return ['A', 'Minimal'];
        }

        return ['A+', 'Clean'];
    }
}
