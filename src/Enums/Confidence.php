<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum Confidence: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public function multiplier(): float
    {
        return match ($this) {
            self::HIGH => 1.0,
            self::MEDIUM => 0.7,
            self::LOW => 0.4,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::HIGH => 'High Confidence',
            self::MEDIUM => 'Medium Confidence',
            self::LOW => 'Low Confidence',
        };
    }
}
