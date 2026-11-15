<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;

interface FindingNormalizerInterface
{
    /**
     * Normalizes and deduplicates raw scan results into validated AuditFindings.
     *
     * @param  array<int, ScanResult>  $scanResults
     * @return array<int, AuditFinding>
     */
    public function normalize(array $scanResults): array;
}
