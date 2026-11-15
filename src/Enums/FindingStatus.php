<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum FindingStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case FIXED = 'fixed';
    case ACCEPTED_RISK = 'accepted_risk';
    case FALSE_POSITIVE = 'false_positive';
    case REOPENED = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::FIXED => 'Fixed',
            self::ACCEPTED_RISK => 'Accepted Risk',
            self::FALSE_POSITIVE => 'False Positive',
            self::REOPENED => 'Reopened',
        };
    }
}
