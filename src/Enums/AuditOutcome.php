<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum AuditOutcome: string
{
    case PASSED = 'passed';
    case FAILED = 'failed';
    case WARNING = 'warning';
    case NOT_APPLICABLE = 'not_applicable';
    case NOT_SCANNED = 'not_scanned';
    case NEEDS_REVIEW = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::PASSED => 'Passed',
            self::FAILED => 'Failed',
            self::WARNING => 'Warning',
            self::NOT_APPLICABLE => 'Not Applicable',
            self::NOT_SCANNED => 'Not Scanned',
            self::NEEDS_REVIEW => 'Needs Review',
        };
    }
}
