<?php

namespace Subhash\ShopifyAiAudit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Subhash\ShopifyAiAudit\Facades\Guardian;
use Subhash\ShopifyAiAudit\Monitoring\TelemetryManager;

class ApiController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'package' => 'subhash/shopify-ai-audit',
            'version' => '1.0.0-dev',
            'timestamp' => date('c'),
            'scanners_count' => count(Guardian::scanners()->all()),
        ]);
    }

    public function runAudit(Request $request): JsonResponse
    {
        $options = [
            'base_path' => $request->input('base_path', base_path()),
            'categories' => $request->input('categories', []),
            'min_severity' => $request->input('min_severity', 'low'),
            'include_ai' => (bool) $request->input('include_ai', false),
        ];

        $result = Guardian::run($options);

        return response()->json([
            'data' => $result->toSummaryArray(),
            'findings' => array_map(fn ($f) => $f->toArray(), $result->findings),
        ]);
    }

    public function apis(): JsonResponse
    {
        return response()->json([
            'data' => Guardian::apis()->all(),
            'versions' => Guardian::apis()->versions(),
        ]);
    }

    public function governance(): JsonResponse
    {
        return response()->json([
            'policies' => Guardian::governance()->policies(),
            'enforcement_actions' => Guardian::governance()->enforcementActions(),
        ]);
    }

    public function telemetry(): JsonResponse
    {
        return response()->json([
            'data' => TelemetryManager::events(),
        ]);
    }
}
