<?php

namespace Subhash\ShopifyAiAudit\AI\Skills;

class SkillsSecuritySupervisor
{
    /**
     * @param  string  $skillDirectory  Path to skill folder
     * @return array<string, mixed>
     */
    public function verifySkill(string $skillDirectory): array
    {
        $yamlPath = $skillDirectory.DIRECTORY_SEPARATOR.'skill.yaml';
        $mdPath = $skillDirectory.DIRECTORY_SEPARATOR.'SKILL.md';

        if (! file_exists($yamlPath) || ! file_exists($mdPath)) {
            return [
                'is_verified' => false,
                'status' => 'MISSING_FILES',
                'message' => 'Skill directory must contain both skill.yaml and SKILL.md.',
            ];
        }

        $instructions = (string) file_get_contents($mdPath);

        // Check for prompt injection / shell execution attacks
        $threats = [];

        if (preg_match('/(rm\s+-rf|curl\s+.*\|\s*bash|powershell\s+-enc|eval\s*\(base64)/i', $instructions, $m)) {
            $threats[] = 'Suspicious shell execution pattern detected: '.$m[0];
        }

        if (preg_match('/(ignore previous instructions|disregard system prompt|bypass sandbox)/i', $instructions, $m)) {
            $threats[] = 'Prompt injection instruction detected: '.$m[0];
        }

        $hash = hash_file('sha256', $yamlPath);

        return [
            'is_verified' => empty($threats),
            'status' => empty($threats) ? 'VERIFIED' : 'REJECTED',
            'threats' => $threats,
            'sha256_hash' => $hash,
            'verified_at' => time(),
        ];
    }
}
