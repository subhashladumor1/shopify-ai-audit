<?php

namespace Subhash\ShopifyAiAudit\Remediation;

use Subhash\ShopifyAiAudit\Contracts\RemediationInterface;
use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\DTOs\PatchProposal;

class RemediationManager implements RemediationInterface
{
    public function generatePatch(AuditFinding $finding): PatchProposal
    {
        $id = 'patch_'.bin2hex(random_bytes(6));
        $filePath = $finding->filePath ?? 'unknown';

        $diff = "--- a/{$filePath}\n+++ b/{$filePath}\n";
        $diff .= "@@ -{$finding->lineStart},1 +{$finding->lineStart},1 @@\n";
        $diff .= "- // Vulnerable or non-compliant implementation: {$finding->title}\n";
        $diff .= '+ '.($finding->suggestedFix ?? '// Applied security fix for '.$finding->ruleId)."\n";

        return new PatchProposal(
            id: $id,
            findingId: $finding->id,
            title: "Remediate {$finding->ruleId}: {$finding->title}",
            explanation: $finding->recommendation ?? 'Apply recommended security patch.',
            unifiedDiff: $diff,
            affectedFiles: [$filePath],
            verificationSteps: [
                'Review unified diff to ensure no unexpected breaking changes.',
                'Run unit and feature tests: vendor/bin/phpunit',
                'Re-scan finding with php artisan guardian:fix:verify '.$finding->id,
            ],
            approvalStatus: 'PENDING'
        );
    }

    public function generateIdePrompt(AuditFinding $finding, string $targetIde = 'antigravity'): string
    {
        $prompt = "# SECURITY REMEDIATION REQUEST\n";
        $prompt .= '**Target IDE / Agent:** '.ucfirst($targetIde)."\n";
        $prompt .= "**Rule ID:** `{$finding->ruleId}`\n";
        $prompt .= "**Severity:** {$finding->severity->label()}\n";
        $prompt .= "**File:** `{$finding->filePath}`";
        if ($finding->lineStart) {
            $prompt .= " (Lines {$finding->lineStart}-{$finding->lineEnd})";
        }
        $prompt .= "\n\n";

        $prompt .= "## Problem Description\n";
        $prompt .= "{$finding->description}\n\n";

        if ($finding->recommendation) {
            $prompt .= "## Recommended Fix\n";
            $prompt .= "{$finding->recommendation}\n\n";
        }

        if ($finding->suggestedFix) {
            $prompt .= "## Suggested Code Replacement\n```php\n{$finding->suggestedFix}\n```\n\n";
        }

        $prompt .= "## Instructions for Agent\n";
        $prompt .= "1. Inspect the referenced file at the exact line number.\n";
        $prompt .= "2. Replace the insecure pattern with the secure replacement.\n";
        $prompt .= "3. Verify syntax and run tests to ensure no regressions.\n";

        return $prompt;
    }

    public function verifyResolution(AuditFinding $finding): bool
    {
        if (empty($finding->filePath) || ! file_exists($finding->filePath)) {
            return false;
        }

        $content = (string) file_get_contents($finding->filePath);

        // If the rule was APP_DEBUG and file now has APP_DEBUG=false
        if ($finding->ruleId === 'LARAVEL-SEC-003' && str_contains($content, 'APP_DEBUG=false')) {
            return true;
        }

        // If unescaped Blade was fixed with {{
        if ($finding->ruleId === 'LARAVEL-SEC-002' && ! str_contains($content, '{!!')) {
            return true;
        }

        // If hardcoded secret was removed
        if ($finding->ruleId === 'SHOPIFY-SEC-010' && ! preg_match('/shpat_[a-fA-F0-9]{32,}/', $content)) {
            return true;
        }

        return false;
    }
}
