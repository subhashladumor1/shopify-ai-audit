<?php

use Illuminate\Support\Facades\Route;
use Subhash\ShopifyAiAudit\Http\Controllers\ApiController;

$apiPrefix = config('shopify-ai-audit.api.prefix', 'api/guardian');
$middleware = config('shopify-ai-audit.api.middleware', ['api']);

Route::group(['prefix' => $apiPrefix, 'middleware' => $middleware], function () {
    Route::get('/health', [ApiController::class, 'health'])->name('guardian.api.health');
    Route::post('/audits', [ApiController::class, 'runAudit'])->name('guardian.api.audits.run');
    Route::get('/shopify/apis', [ApiController::class, 'apis'])->name('guardian.api.shopify.apis');
    Route::get('/shopify/governance', [ApiController::class, 'governance'])->name('guardian.api.shopify.governance');
    Route::get('/telemetry', [ApiController::class, 'telemetry'])->name('guardian.api.telemetry');
});
