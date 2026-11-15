<?php

namespace Subhash\ShopifyAiAudit\DTOs;

class PatchProposal
{
    /**
     * @param  array<int, string>  $affectedFiles
     * @param  array<int, string>  $verificationSteps
     */
    public function __construct(
        public readonly string $id,
        public readonly string $findingId,
        public readonly string $title,
        public readonly string $explanation,
        public readonly string $unifiedDiff,
        public readonly array $affectedFiles,
        public readonly array $verificationSteps = [],
        public readonly string $approvalStatus = 'PENDING',
        public readonly ?string $appliedAt = null,
    ) {}
}
