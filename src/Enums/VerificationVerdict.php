<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum VerificationVerdict: string
{
    case CONFIRMED = 'confirmed';
    case NEEDS_VALIDATION = 'needs_validation';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::CONFIRMED => 'Confirmed',
            self::NEEDS_VALIDATION => 'Needs Validation',
            self::REJECTED => 'Rejected',
        };
    }
}
