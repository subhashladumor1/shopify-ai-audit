<?php

namespace Subhash\ShopifyAiAudit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Subhash\ShopifyAiAudit\Facades\Guardian;
use Subhash\ShopifyAiAudit\Monitoring\TelemetryManager;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $basePath = base_path();
        $auditResult = Guardian::run(['base_path' => $basePath]);
        $apis = Guardian::apis()->all();
        $policies = Guardian::governance()->policies();
        $telemetry = TelemetryManager::events();

        return view('guardian::dashboard', [
            'audit' => $auditResult,
            'apis' => $apis,
            'policies' => $policies,
            'telemetry' => $telemetry,
        ]);
    }

    public function findings(Request $request): View
    {
        $auditResult = Guardian::run(['base_path' => base_path()]);

        return view('guardian::findings', [
            'findings' => $auditResult->findings,
            'riskScore' => $auditResult->riskScore,
        ]);
    }

    public function shopify(Request $request): View
    {
        $apis = Guardian::apis()->all();
        $versions = Guardian::apis()->versions();
        $policies = Guardian::governance()->policies();
        $actions = Guardian::governance()->enforcementActions();

        return view('guardian::shopify', [
            'apis' => $apis,
            'versions' => $versions,
            'policies' => $policies,
            'actions' => $actions,
        ]);
    }

    public function monitoring(Request $request): View
    {
        $telemetry = TelemetryManager::events();

        return view('guardian::monitoring', [
            'telemetry' => $telemetry,
        ]);
    }
}
