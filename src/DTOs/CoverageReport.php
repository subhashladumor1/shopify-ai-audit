<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class CoverageReport
{
    /**
     * @param  array<int, string>  $scannedFiles
     * @param  array<string, string>  $unscannedFilesWithReasons
     * @param  array<string, int>  $categoryCounts
     */
    public function __construct(
        public readonly int $totalFilesDiscovered,
        public readonly int $totalFilesScanned,
        public readonly float $coveragePercentage,
        public readonly array $scannedFiles = [],
        public readonly array $unscannedFilesWithReasons = [],
        public readonly array $categoryCounts = [],
        public readonly int $totalRulesEvaluated = 0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_files_discovered' => $this->totalFilesDiscovered,
            'total_files_scanned' => $this->totalFilesScanned,
            'coverage_percentage' => round($this->coveragePercentage, 2),
            'scanned_files_count' => count($this->scannedFiles),
            'unscanned_files_count' => count($this->unscannedFilesWithReasons),
            'category_counts' => $this->categoryCounts,
            'total_rules_evaluated' => $this->totalRulesEvaluated,
        ];
    }
}
