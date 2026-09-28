<?php

use App\Http\Controllers\AquariumDeviceController;
use Illuminate\Support\Facades\Route;

Route::prefix('device')->middleware('throttle:120,1')->group(function () {
    Route::post('/pairing/start', [AquariumDeviceController::class, 'startPairing'])
        ->middleware('throttle:5,1');
    Route::post('/telemetry', [AquariumDeviceController::class, 'receiveTelemetry']);
    Route::get('/commands', [AquariumDeviceController::class, 'pendingCommands']);
    Route::post('/commands/{commandId}/ack', [AquariumDeviceController::class, 'completeCommand']);
});