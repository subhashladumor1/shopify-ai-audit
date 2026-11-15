<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\CoverageReport;
use Subhash\ShopifyAiAudit\DTOs\RiskScore;

interface RiskScorerInterface
{
    /**
     * Calculates the overall risk score and grade based on findings and audit coverage.
     *
     * @param  array<int, AuditFinding>  $findings
     */
    public function calculate(array $findings, CoverageReport $coverage): RiskScore;
}
