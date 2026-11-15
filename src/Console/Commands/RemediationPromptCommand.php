<?php

namespace Subhash\ShopifyAiAudit\Console\Commands;

use Illuminate\Console\Command;
use Subhash\ShopifyAiAudit\DTOs\AuditFinding;
use Subhash\ShopifyAiAudit\Enums\AuditCategory;
use Subhash\ShopifyAiAudit\Enums\Confidence;
use Subhash\ShopifyAiAudit\Enums\FindingStatus;
use Subhash\ShopifyAiAudit\Enums\Severity;
use Subhash\ShopifyAiAudit\Enums\VerificationVerdict;
use Subhash\ShopifyAiAudit\Remediation\RemediationManager;

class RemediationPromptCommand extends Command
{
    protected $signature = 'guardian:fix:prompt
                            {rule_id : The rule ID (e.g. SHOPIFY-SEC-006, LARAVEL-SEC-003)}
                            {--file= : File path of the issue}
                            {--agent=antigravity : Target IDE agent (antigravity, claude_code, codex)}';

    protected $description = 'Generate a verified, ready-to-run prompt for IDE agents to fix a specific finding';

    public function handle(): int
    {
        $ruleId = (string) $this->argument('rule_id');
        $file = $this->option('file') ?: 'app/Http/Controllers/ShopifyController.php';
        $agent = (string) $this->option('agent');

        $finding = new AuditFinding(
            id: 'find_sample',
            ruleId: $ruleId,
            title: "Security vulnerability detected for {$ruleId}",
            description: "Detailed description of vulnerability in {$file}",
            category: AuditCategory::SHOPIFY_SECURITY,
            severity: Severity::HIGH,
            confidence: Confidence::HIGH,
            verificationVerdict: VerificationVerdict::CONFIRMED,
            status: FindingStatus::OPEN,
            filePath: $file,
            lineStart: 12,
            lineEnd: 15,
            recommendation: "Apply recommended fix for {$ruleId}",
            suggestedFix: '// Secure replacement code',
        );

        $remediation = new RemediationManager;
        $prompt = $remediation->generateIdePrompt($finding, $agent);

        $this->line($prompt);

        return 0;
    }
}
