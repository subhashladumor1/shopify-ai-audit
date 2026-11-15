<?php

use Illuminate\Support\Facades\Route;
use Subhash\ShopifyAiAudit\Http\Controllers\DashboardController;

$dashboardPath = config('shopify-ai-audit.dashboard.path', 'guardian');
$middleware = config('shopify-ai-audit.dashboard.middleware', ['web']);

Route::group(['prefix' => $dashboardPath, 'middleware' => $middleware], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('guardian.dashboard');
    Route::get('/findings', [DashboardController::class, 'findings'])->name('guardian.findings');
    Route::get('/shopify', [DashboardController::class, 'shopify'])->name('guardian.shopify');
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('guardian.monitoring');
});
