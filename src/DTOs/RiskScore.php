<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class RiskScore
{
    /**
     * @param  array<string, int>  $findingsBySeverity
     */
    public function __construct(
        public readonly float $score,
        public readonly string $grade,
        public readonly string $level,
        public readonly array $findingsBySeverity = [],
        public readonly int $criticalCount = 0,
        public readonly int $highCount = 0,
        public readonly int $mediumCount = 0,
        public readonly int $lowCount = 0,
        public readonly int $infoCount = 0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'score' => round($this->score, 2),
            'grade' => $this->grade,
            'level' => $this->level,
            'critical' => $this->criticalCount,
            'high' => $this->highCount,
            'medium' => $this->mediumCount,
            'low' => $this->lowCount,
            'informational' => $this->infoCount,
        ];
    }
}
