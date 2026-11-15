<?php

namespace Subhash\ShopifyAiAudit\Reports;

use Subhash\ShopifyAiAudit\DTOs\AuditResult;

class ReportManager
{
    /**
     * @return array<int, string>
     */
    public function supportedFormats(): array
    {
        return ['html', 'json', 'sarif', 'markdown', 'csv'];
    }

    public function generate(AuditResult $result, string $format = 'html'): string
    {
        return match (strtolower($format)) {
            'json' => $this->generateJson($result),
            'sarif' => $this->generateSarif($result),
            'markdown' => $this->generateMarkdown($result),
            'csv' => $this->generateCsv($result),
            default => $this->generateHtml($result),
        };
    }

    protected function generateJson(AuditResult $result): string
    {
        return (string) json_encode([
            'audit_id' => $result->auditId,
            'status' => $result->status,
            'duration_ms' => $result->durationMs,
            'risk_score' => $result->riskScore->toArray(),
            'coverage' => $result->coverage->toArray(),
            'manifest' => [
                'base_path' => $result->manifest->basePath,
                'is_shopify' => $result->manifest->isShopifyApp,
                'shopify_type' => $result->manifest->shopifyAppType->value,
                'frameworks' => $result->manifest->detectedFrameworks,
            ],
            'findings' => array_map(fn ($f) => $f->toArray(), $result->findings),
            'generated_at' => date('c'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function generateSarif(AuditResult $result): string
    {
        $rules = [];
        $results = [];

        foreach ($result->findings as $finding) {
            $rules[$finding->ruleId] = [
                'id' => $finding->ruleId,
                'name' => $finding->title,
                'shortDescription' => ['text' => $finding->title],
                'fullDescription' => ['text' => $finding->description],
                'defaultConfiguration' => [
                    'level' => match ($finding->severity->value) {
                        'critical', 'high' => 'error',
                        'medium' => 'warning',
                        default => 'note',
                    },
                ],
            ];

            $results[] = [
                'ruleId' => $finding->ruleId,
                'level' => match ($finding->severity->value) {
                    'critical', 'high' => 'error',
                    'medium' => 'warning',
                    default => 'note',
                },
                'message' => ['text' => $finding->description],
                'locations' => [
                    [
                        'physicalLocation' => [
                            'artifactLocation' => ['uri' => $finding->filePath ?? 'unknown'],
                            'region' => [
                                'startLine' => $finding->lineStart ?? 1,
                                'endLine' => $finding->lineEnd ?? ($finding->lineStart ?? 1),
                            ],
                        ],
                    ],
                ],
            ];
        }

        return (string) json_encode([
            '$schema' => 'https://raw.githubusercontent.com/oasis-tcs/sarif-spec/master/Schemata/sarif-schema-2.1.0.json',
            'version' => '2.1.0',
            'runs' => [
                [
                    'tool' => [
                        'driver' => [
                            'name' => 'Shopify AI Audit Guardian',
                            'semanticVersion' => '1.0.0',
                            'informationUri' => 'https://github.com/subhashladumor1/shopify-ai-audit',
                            'rules' => array_values($rules),
                        ],
                    ],
                    'results' => $results,
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function generateMarkdown(AuditResult $result): string
    {
        $md = "# Shopify AI Audit Report\n\n";
        $md .= "**Audit ID:** `{$result->auditId}`  \n";
        $md .= '**Date:** '.date('Y-m-d H:i:s')."  \n";
        $md .= "**Risk Score:** {$result->riskScore->score}/100 (Grade: **{$result->riskScore->grade}**)  \n";
        $md .= '**Coverage:** '.round($result->coverage->coveragePercentage, 1)."% ({$result->coverage->totalFilesScanned} files scanned)  \n\n";

        $md .= "## Executive Summary\n\n";
        $md .= "| Severity | Count |\n|---|---|\n";
        $md .= "| Critical | {$result->riskScore->criticalCount} |\n";
        $md .= "| High | {$result->riskScore->highCount} |\n";
        $md .= "| Medium | {$result->riskScore->mediumCount} |\n";
        $md .= "| Low | {$result->riskScore->lowCount} |\n\n";

        $md .= "## Findings\n\n";
        if (empty($result->findings)) {
            $md .= "✅ No security vulnerabilities or compliance issues detected!\n";
        } else {
            foreach ($result->findings as $idx => $f) {
                $num = $idx + 1;
                $md .= "### {$num}. [{$f->ruleId}] {$f->title}\n\n";
                $md .= "- **Severity:** `{$f->severity->label()}`\n";
                $md .= "- **File:** `{$f->filePath}`".($f->lineStart ? " (Line {$f->lineStart})" : '')."\n";
                $md .= "- **Description:** {$f->description}\n";
                if ($f->recommendation) {
                    $md .= "- **Recommendation:** {$f->recommendation}\n";
                }
                if ($f->suggestedFix) {
                    $md .= "\n**Suggested Fix:**\n```php\n{$f->suggestedFix}\n```\n";
                }
                $md .= "\n---\n\n";
            }
        }

        return $md;
    }

    protected function generateCsv(AuditResult $result): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Finding ID', 'Rule ID', 'Severity', 'Title', 'File Path', 'Line', 'Recommendation']);

        foreach ($result->findings as $f) {
            fputcsv($output, [
                $f->id,
                $f->ruleId,
                $f->severity->value,
                $f->title,
                $f->filePath ?? '',
                $f->lineStart ?? '',
                $f->recommendation ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return (string) $csv;
    }

    protected function generateHtml(AuditResult $result): string
    {
        $title = htmlspecialchars('Shopify AI Audit - '.$result->auditId);
        $score = round($result->riskScore->score, 1);
        $grade = htmlspecialchars($result->riskScore->grade);
        $level = htmlspecialchars($result->riskScore->level);

        $findingsHtml = '';
        if (empty($result->findings)) {
            $findingsHtml = '<div class="p-6 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200">✅ No vulnerabilities or compliance risks detected in this project.</div>';
        } else {
            foreach ($result->findings as $f) {
                $ruleId = htmlspecialchars($f->ruleId);
                $fTitle = htmlspecialchars($f->title);
                $sev = htmlspecialchars($f->severity->label());
                $badge = $f->severity->badgeClass();
                $file = htmlspecialchars(($f->filePath ?? 'unknown').($f->lineStart ? ":{$f->lineStart}" : ''));
                $rec = htmlspecialchars($f->recommendation ?? '');

                $findingsHtml .= "
                <div class='mb-4 p-5 rounded-lg border border-slate-700 bg-slate-800'>
                    <div class='flex justify-between items-center mb-2'>
                        <span class='font-mono text-sm px-2.5 py-1 rounded border {$badge}'>{$sev}</span>
                        <span class='font-mono text-xs text-slate-400'>{$ruleId}</span>
                    </div>
                    <h3 class='text-base font-semibold text-white mb-1'>{$fTitle}</h3>
                    <p class='text-sm text-slate-400 mb-2 font-mono'>{$file}</p>
                    <p class='text-sm text-slate-300 mb-2'>{$rec}</p>
                </div>";
            }
        }

        return "<!DOCTYPE html>
<html lang='en' class='dark'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-slate-900 text-slate-100 min-h-screen p-8'>
    <div class='max-w-5xl mx-auto'>
        <header class='border-b border-slate-800 pb-6 mb-8 flex justify-between items-center'>
            <div>
                <h1 class='text-3xl font-bold text-white'>Shopify AI Audit</h1>
                <p class='text-slate-400 text-sm mt-1'>Audit ID: <span class='font-mono'>{$result->auditId}</span></p>
            </div>
            <div class='text-right'>
                <div class='text-4xl font-extrabold text-white'>{$score}<span class='text-xl text-slate-400 font-normal'>/100</span></div>
                <div class='text-sm font-semibold text-emerald-400'>Grade {$grade} ({$level})</div>
            </div>
        </header>

        <section class='grid grid-cols-4 gap-4 mb-8'>
            <div class='p-4 bg-slate-800 rounded-lg border border-slate-700'>
                <div class='text-xs text-slate-400 uppercase font-semibold'>Total Findings</div>
                <div class='text-2xl font-bold text-white mt-1'>".count($result->findings)."</div>
            </div>
            <div class='p-4 bg-slate-800 rounded-lg border border-slate-700'>
                <div class='text-xs text-slate-400 uppercase font-semibold'>Critical</div>
                <div class='text-2xl font-bold text-red-500 mt-1'>{$result->riskScore->criticalCount}</div>
            </div>
            <div class='p-4 bg-slate-800 rounded-lg border border-slate-700'>
                <div class='text-xs text-slate-400 uppercase font-semibold'>High</div>
                <div class='text-2xl font-bold text-orange-400 mt-1'>{$result->riskScore->highCount}</div>
            </div>
            <div class='p-4 bg-slate-800 rounded-lg border border-slate-700'>
                <div class='text-xs text-slate-400 uppercase font-semibold'>Coverage</div>
                <div class='text-2xl font-bold text-emerald-400 mt-1'>".round($result->coverage->coveragePercentage, 1)."%</div>
            </div>
        </section>

        <section>
            <h2 class='text-xl font-bold text-white mb-4'>Detailed Findings</h2>
            {$findingsHtml}
        </section>
    </div>
</body>
</html>";
    }
}
