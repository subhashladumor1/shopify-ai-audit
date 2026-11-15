<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AuditContext;
use Subhash\ShopifyAiAudit\DTOs\ScanResult;

interface AuditScanner
{
    /**
     * Unique identifier for this scanner.
     */
    public function id(): string;

    /**
     * Human-readable name of the scanner.
     */
    public function name(): string;

    /**
     * Determines whether the scanner is applicable for the detected project context.
     */
    public function supports(AuditContext $context): bool;

    /**
     * Executes the scanner and returns findings, files scanned, and telemetry.
     */
    public function scan(AuditContext $context): ScanResult;
}
