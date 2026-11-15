<?php

namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\PatchProposal;

interface RemediationInterface
{
    /**
     * Generates a unified diff patch proposal for a given audit finding.
     */
    public function generatePatch(AuditFinding $finding): PatchProposal;

    /**
     * Generates a ready-to-use prompt for external IDE coding agents (Antigravity, Claude Code, Codex).
     */
    public function generateIdePrompt(AuditFinding $finding, string $targetIde = 'antigravity'): string;

    /**
     * Verifies whether an applied patch successfully resolves the finding without regressions.
     */
    public function verifyResolution(AuditFinding $finding): bool;
}
