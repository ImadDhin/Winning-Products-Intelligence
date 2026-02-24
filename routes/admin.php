<?php

use App\Http\Controllers\Admin\ConnectorController;
use App\Http\Controllers\Admin\JobsAuditController;
use App\Http\Controllers\Admin\ScoringWeightsController;
use App\Http\Controllers\Admin\BacktestController;
use App\Http\Controllers\Admin\ComplianceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/connectors', [ConnectorController::class, 'index']);
    Route::get('/connectors/{id}', [ConnectorController::class, 'show']);
    Route::patch('/connectors/{id}', [ConnectorController::class, 'update']);

    Route::get('/jobs-audit', [JobsAuditController::class, 'index']);

    Route::get('/scoring-weights', [ScoringWeightsController::class, 'index']);
    Route::patch('/scoring-weights', [ScoringWeightsController::class, 'update']);

    Route::get('/backtest', [BacktestController::class, 'index']);
    Route::post('/backtest/run', [BacktestController::class, 'run']);

    Route::get('/compliance', [ComplianceController::class, 'index']);
});
