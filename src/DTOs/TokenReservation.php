<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class TokenReservation
{
    public function __construct(
        public readonly string $reservationId,
        public readonly int $estimatedInputTokens,
        public readonly int $estimatedOutputTokens,
        public readonly float $estimatedCostUsd,
        public readonly string $agentId,
        public readonly string $providerId,
        public readonly string $model,
        public readonly int $createdAtTimestamp,
    ) {}
}
