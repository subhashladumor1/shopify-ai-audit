<?php

namespace Subhash\ShopifyAiAudit\Enums;

enum Severity: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';
    case INFORMATIONAL = 'informational';

    public function weight(): int
    {
        return match ($this) {
            self::CRITICAL => 10,
            self::HIGH => 7,
            self::MEDIUM => 4,
            self::LOW => 1,
            self::INFORMATIONAL => 0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CRITICAL => 'Critical',
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
            self::INFORMATIONAL => 'Informational',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CRITICAL => 'bg-red-500/10 text-red-500 border-red-500/20',
            self::HIGH => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
            self::MEDIUM => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
            self::LOW => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
            self::INFORMATIONAL => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }
}
