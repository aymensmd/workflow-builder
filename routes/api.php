<?php

use App\Http\Controllers\Api\AiNodeController;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\WorkflowExecutionController;
use App\Http\Controllers\Api\WorkflowVersionController;
use Illuminate\Support\Facades\Route;

Route::prefix('workflows')->group(function () {
    Route::post('/', [WorkflowController::class, 'store']);
    Route::get('/{workflow}', [WorkflowController::class, 'show']);
    Route::put('/{workflow}', [WorkflowController::class, 'update']);

    Route::post('/{workflow}/versions', [WorkflowVersionController::class, 'store']);
    Route::post('/{workflow}/execute', [WorkflowExecutionController::class, 'trigger']);
});

Route::post('/ai/generate-node', [AiNodeController::class, 'generateNode']);
