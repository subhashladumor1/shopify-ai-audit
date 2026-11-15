<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AuditResult;

interface ReportGeneratorInterface
{
    /**
     * File format identifier (e.g. html, pdf, json, sarif, csv, xlsx, markdown).
     */
    public function format(): string;

    /**
     * MIME type of generated report.
     */
    public function mimeType(): string;

    /**
     * Generates report content from an AuditResult.
     */
    public function generate(AuditResult $result): string;
}
