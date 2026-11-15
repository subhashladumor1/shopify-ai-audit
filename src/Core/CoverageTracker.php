<?php

namespace Subhash\ShopifyAiAudit\Core;

use Subhash\ShopifyAiAudit\DTOs\CoverageReport;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;

class CoverageTracker
{
    /**
     * @param  array<int, ScanResult>  $scanResults
     */
    public function compute(array $scanResults, int $totalDiscoveredFiles): CoverageReport
    {
        $scannedFiles = [];
        $unscannedFiles = [];
        $categoryCounts = [];
        $totalRulesEvaluated = 0;

        foreach ($scanResults as $result) {
            foreach ($result->scannedFiles as $file) {
                $scannedFiles[$file] = true;
            }
            foreach ($result->skippedFiles as $file => $reason) {
                $unscannedFiles[$file] = $reason;
            }

            $scannerClass = $result->scannerClass;
            $categoryCounts[$scannerClass] = ($categoryCounts[$scannerClass] ?? 0) + count($result->ruleResults);
            $totalRulesEvaluated += count($result->ruleResults);
        }

        $uniqueScannedCount = count($scannedFiles);
        $totalFiles = max($totalDiscoveredFiles, $uniqueScannedCount);

        $coveragePct = $totalFiles > 0
            ? ($uniqueScannedCount / $totalFiles) * 100.0
            : 100.0;

        return new CoverageReport(
            totalFilesDiscovered: $totalFiles,
            totalFilesScanned: $uniqueScannedCount,
            coveragePercentage: min(100.0, $coveragePct),
            scannedFiles: array_keys($scannedFiles),
            unscannedFilesWithReasons: $unscannedFiles,
            categoryCounts: $categoryCounts,
            totalRulesEvaluated: $totalRulesEvaluated
        );
    }
}
